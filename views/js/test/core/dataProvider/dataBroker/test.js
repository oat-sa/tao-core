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

    var readCases;
    var readErrorCases;
    var dataBrokerApi = [
        {title: 'destroy'},
        {title: 'getProvider'},
        {title: 'hasProvider'},
        {title: 'addProvider'},
        {title: 'readProvider'},
        {title: 'read'},
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
            }
        };

        QUnit.expect(8);

        dataBroker = dataBrokerFactory()
            .addProvider(successProvider)
            .addProvider(failureProvider)
            .on('readprovider', function(name, params) {
                assert.ok(_.indexOf(['success', 'failure'], name) >= 0, "The `readprovider` event has been triggered as expected");
                assert.equal(typeof params, 'object', "The params have been provided to the event");
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


    readCases = [{
        title: 'data in default',
        expected: {
            foo: 'bar'
        },
        params: {},
        defaultProvider: {
            read: function() {
                return Promise.resolve({
                    foo: 'bar'
                });
            }
        },
        dataProvider: {
            read: function() {
                return Promise.reject();
            }
        }
    }, {
        title: 'data not in default',
        expected: {
            foo: 'bar'
        },
        params: {},
        defaultProvider: {
            read: function() {
                return Promise.resolve();
            }
        },
        dataProvider: {
            read: function() {
                return Promise.resolve({
                    foo: 'bar'
                });
            }
        }
    }, {
        title: 'default cannot serve data',
        expected: {
            foo: 'bar'
        },
        params: {},
        defaultProvider: {
            read: function() {
                return Promise.reject();
            }
        },
        dataProvider: {
            read: function() {
                return Promise.resolve({
                    foo: 'bar'
                });
            }
        }
    }];

    QUnit
        .cases(readCases)
        .asyncTest('dataBroker.read() ', function (data, assert) {
            var dataBroker;

            QUnit.expect(9);

            dataBroker = dataBrokerFactory()
                .addProvider('default', data.defaultProvider)
                .addProvider('data', data.dataProvider)
                .on('read', function (name, params) {
                    assert.ok(_.indexOf(['default', 'data'], name) >= 0, "The `read` event has been triggered as expected");
                    assert.deepEqual(params, data.params, "The params have been provided to the event");
                })
                .on('data', function (response, name, params) {
                    assert.ok(_.indexOf(['default', 'data'], name) >= 0, "The `data` event has been triggered as expected");
                    assert.deepEqual(params, data.params, "The params have been provided to the event");
                    assert.equal(typeof response, 'object', "Data has been provided");
                });

            assert.equal(typeof dataBroker, 'object', "The dataBroker factory produces an object");

            dataBroker.read('unknown', data.params)
                .then(function() {
                    assert.ok(false, "Unknown provider should raise error!");
                    QUnit.start();
                })
                .catch(function(err) {
                    assert.ok(true, "Unknown provider should raise error!");
                    assert.deepEqual(err, {
                        success: false,
                        type: 'notimplemented',
                        action: 'unknown',
                        params: data.params
                    }, "The dataBroker has has thrown the expected error");

                    return dataBroker.read('data', data.params);
                })
                .then(function(response) {
                    assert.deepEqual(response, data.expected, "Should provide the expected data");
                    QUnit.start();
                })
                .catch(function (err) {
                    assert.ok(false, 'The promise should not be rejected');
                    console.error(err);
                    QUnit.start();
                });
        });


    readErrorCases = [{
        title: 'default provider raises error',
        expected: {
            success: false,
            message: 'oops'
        },
        params: {},
        defaultProvider: {
            read: function() {
                return Promise.reject({
                    success: false,
                    message: 'oops'
                });
            }
        },
        dataProvider: {
            read: function() {
                return Promise.resolve({
                    foo: 'bar'
                });
            }
        }
    }, {
        title: 'data provider raises error',
        expected: {
            success: false,
            message: 'oops'
        },
        params: {},
        defaultProvider: {
            read: function() {
                return Promise.reject();
            }
        },
        dataProvider: {
            read: function() {
                return Promise.reject({
                    success: false,
                    message: 'oops'
                });
            }
        }
    }];

    QUnit
        .cases(readErrorCases)
        .asyncTest('dataBroker.read() #error ', function (data, assert) {
            var dataBroker;

            QUnit.expect(4);

            dataBroker = dataBrokerFactory()
                .addProvider('default', data.defaultProvider)
                .addProvider('data', data.dataProvider)
                .on('read', function (name, params) {
                    assert.ok(_.indexOf(['default', 'data'], name) >= 0, "The `read` event has been triggered as expected");
                    assert.deepEqual(params, data.params, "The params have been provided to the event");
                })
                .on('data', function () {
                    assert.ok(false, "Should not trigger the `data` event");
                });

            assert.equal(typeof dataBroker, 'object', "The dataBroker factory produces an object");

            dataBroker.read('data', data.params)
                .then(function() {
                    assert.ok(false, "The provider should raise error!");
                    QUnit.start();
                })
                .catch(function(err) {
                    assert.deepEqual(err, data.expected, "Should provide the expected error");
                    QUnit.start();
                });
        });
});
