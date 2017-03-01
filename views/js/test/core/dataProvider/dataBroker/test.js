/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'core/dataProvider/dataBroker'
], function (_, Promise, dataBrokerFactory) {
    'use strict';

    var dataBrokerApi = [
        {title: 'destroy'},
        {title: 'getProvider'},
        {title: 'hasProvider'},
        {title: 'addProvider'},
        {title: 'loadProviders'},
        {title: 'readProvider'},
        {title: 'getConfig'},
        {title: 'getMiddlewares'}
    ];


    QUnit.module('dataBroker');


    QUnit.test('module', function (assert) {
        QUnit.expect(3);

        assert.equal(typeof dataBrokerFactory, 'function', "The dataBroker module exposes a function");
        assert.equal(typeof dataBrokerFactory(), 'object', "The dataBroker factory produces an object");
        assert.notStrictEqual(dataBrokerFactory(), dataBrokerFactory(), "The dataBroker factory provides a different object on each call");
    });


    QUnit
        .cases(dataBrokerApi)
        .test('instance API ', function (data, assert) {
            var instance = dataBrokerFactory();
            QUnit.expect(1);
            assert.equal(typeof instance[data.title], 'function', 'The dataBroker instance exposes a "' + data.title + '" function');
        });


    QUnit.asyncTest('factory', function (assert) {
        var initConfig = {
            foo: 'bar',
            middlewares: {
                use: _.noop,
                apply: _.noop
            }
        };
        var expectedConfig = {
            foo: 'bar',
            middlewares: {
                use: _.noop,
                apply: _.noop
            }
        };
        var dataBroker;

        QUnit.expect(6);

        assert.throws(function() {
            dataBrokerFactory({middlewares: {}});
        }, 'Should throw an error if a wrong middlewares handler is provided');

        dataBroker = dataBrokerFactory(initConfig)
            .on('destroy', function () {
                assert.ok(true, 'The dataBroker has triggered the `destroy` event');
                QUnit.start();
            });

        assert.equal(typeof dataBroker, 'object', "The dataBroker factory produces an object");

        assert.deepEqual(dataBroker.getConfig(), expectedConfig, 'The dataBroker instance has the expected config');
        assert.deepEqual(dataBroker.getMiddlewares(), initConfig.middlewares, 'The dataBroker instance has the expected middlewares handler');

        dataBroker.destroy();

        assert.equal(dataBroker.getConfig(), null, 'The dataBroker has been destroyed');
    });


    QUnit.asyncTest('dataBroker.addProvider()', function (assert) {
        var dataBroker;
        var fooProvider = {
            name: 'foo',
            read: function() {},
            setMiddlewares: function(m) {
                this.middlewares = m;
            }
        };

        QUnit.expect(14);

        dataBroker = dataBrokerFactory()
            .on('addprovider', function (name, provider) {
                assert.ok(true, 'The dataBroker has triggered the `addprovider` event');
                assert.equal(name, fooProvider.name, 'The dataProvider has added the expected provider name');
                assert.equal(provider, fooProvider, 'The dataProvider has added the expected provider');
                QUnit.start();
            });

        assert.equal(typeof dataBroker, 'object', "The dataBroker factory produces an object");

        assert.throws(function() {
            dataBroker.addProvider();
        }, "The addProvider() method should throw an error if no provider nor name was provided");

        assert.throws(function() {
            dataBroker.addProvider('foo');
        }, "The addProvider() method should throw an error if no provider was provided");

        assert.throws(function() {
            dataBroker.addProvider('', fooProvider);
        }, "The addProvider() method should throw an error if the name is empty");

        assert.equal(false, dataBroker.hasProvider('foo'), "The dataBroker does not have provider");
        assert.equal(null, dataBroker.getProvider('foo'), "The provider 'foo' does not exists");
        assert.equal(typeof fooProvider.middlewares, 'undefined', "The provider 'foo' does not have a middlewares");

        assert.equal(dataBroker.addProvider(fooProvider), dataBroker, 'The addProvider() method returns the instance');

        assert.equal(true, dataBroker.hasProvider('foo'), "The dataBroker now have provider");
        assert.equal(fooProvider, dataBroker.getProvider('foo'), "The provider 'foo' now exists");
        assert.equal(fooProvider.middlewares, dataBroker.getMiddlewares(), "The provider 'foo' now have a middlewares");

    });


    QUnit.asyncTest('dataBroker.readProvider()', function (assert) {
        var dataBroker;
        var expectedParams = {
            foo: 'bar'
        };
        var expectedError = new Error("Test");
        var successProvider = {
            name: 'success',
            read: function(params) {
                return Promise.resolve(params);
            }
        };
        var failureProvider = {
            name: 'failure',
            read: function(params) {
                return Promise.reject(params);
            },
            off: function(name) {
                assert.equal(name, 'error.dataBroker', 'The dataBroker has cleaned the error channel');
                return this;
            },
            on: function(name, cb) {
                assert.equal(name, 'error.dataBroker', 'The dataBroker is listening to the error channel');
                assert.equal(typeof cb, 'function', 'The dataBroker has registered an error handler');
                cb(expectedError);
                return this;
            }
        };

        QUnit.expect(15);

        dataBroker = dataBrokerFactory()
            .on('error', function(err) {
                assert.equal(err, expectedError, 'The dataBroker has caught the right error');
            })
            .addProvider(successProvider)
            .addProvider(failureProvider)
            .on('readprovider', function(name, params) {
                assert.ok(_.indexOf(['success', 'failure'], name) >= 0, "The `readprovider` event has been triggered as expected");
                assert.equal(typeof params, 'object', "The params have been provided to the event");
            })
            .on('data', function (response, name, params) {
                assert.ok(_.indexOf(['success', 'failure'], name) >= 0, "The `data` event has been triggered as expected");
                assert.deepEqual(params, expectedParams, "The params have been provided to the event");
                assert.equal(typeof response, 'object', "Data has been provided");
            });

        assert.equal(typeof dataBroker, 'object', "The dataBroker factory produces an object");

        dataBroker.readProvider('unknown')
            .then(function() {
                assert.ok(false, "Unkwnown provider should raise error!");
                QUnit.start();
            })
            .catch(function() {
                assert.ok(true, "Unkwnown provider should raise error!");

                return dataBroker.readProvider('failure', expectedParams);
            })
            .then(function() {
                assert.ok(false, "This provider should always reject the request!");
                QUnit.start();
            })
            .catch(function(err) {
                assert.deepEqual(err, expectedParams, "Should provide the expected reason");

                return dataBroker.readProvider('success', expectedParams);
            })
            .then(function(data) {
                assert.deepEqual(data, expectedParams, "Should provide the expected data");
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('dataBroker.loadProviders()', function (assert) {
        var dataBroker;
        var fooProvider = {
            name: 'foo',
            read: function(params) {
                return Promise.resolve(params);
            }
        };
        var barProvider = {
            name: 'bar',
            read: function(params) {
                return Promise.resolve(params);
            }
        };

        QUnit.expect(8);

        dataBroker = dataBrokerFactory();

        dataBroker.loadProviders()
            .then(function(broker) {
                assert.ok(true, "The loadProviders has resolved the promise, even if no provider has been given");
                assert.equal(broker, dataBroker, "The data broker should be provided");

                assert.ok(!dataBroker.hasProvider('foo'), 'There is not a foo provider');
                assert.ok(!dataBroker.hasProvider('bar'), 'There is not a bar provider');

                return dataBroker.loadProviders({
                    foo: fooProvider,
                    bar: Promise.resolve(barProvider)
                });
            })
            .then(function(broker) {
                assert.ok(true, "The loadProviders has resolved the promise");
                assert.equal(broker, dataBroker, "The data broker should be provided");

                assert.equal(dataBroker.getProvider('foo'), fooProvider, 'The foo provider has been registered');
                assert.equal(dataBroker.getProvider('bar'), barProvider, 'The bar provider has been registered');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });


    });
});
