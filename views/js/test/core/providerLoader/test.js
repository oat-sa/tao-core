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
 * Provider loader's test
 *
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/providerLoader',
    'core/promise'
], function (_, providerLoader, Promise) {
    'use strict';


    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(3);

        assert.equal(typeof providerLoader, 'function', "The provider loader exposes a function");
        assert.equal(typeof providerLoader(), 'object', "The provider loader produces an object");
        assert.notStrictEqual(providerLoader(), providerLoader(), "The provider loader provides a different object on each call");
    });

    QUnit.test('loader methods', function (assert) {
        var loader = providerLoader();

        QUnit.expect(5);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.equal(typeof loader.add, 'function', "The loader exposes the add method");
        assert.equal(typeof loader.addList, 'function', "The loader exposes the addList method");
        assert.equal(typeof loader.load, 'function', "The loader exposes the load method");
        assert.equal(typeof loader.getProviders, 'function', "The loader exposes the getProviders method");

    });


    QUnit.module('required');

    QUnit.test('required provider format', function (assert) {
        QUnit.expect(6);

        assert.throws(function () {
            providerLoader([{name: 'foo', init: _.noop}]);
        }, TypeError, 'Wrong category format');

        assert.throws(function () {
            providerLoader({12: {name: 'foo', init: _.noop}});
        }, TypeError, 'Wrong category format');

        assert.throws(function () {
            providerLoader({'foo': true});
        }, TypeError, 'The provider list must be an array');

        assert.throws(function () {
            providerLoader({'foo': [true]});
        }, TypeError, 'The provider list must be an array of objects');

        assert.throws(function () {
            providerLoader({'foo': ['true', {name: 'foo', init: _.noop}]});
        }, TypeError, 'The provider list must be an array with only objects');

        assert.throws(function () {
            providerLoader({'foo': [{init: _.noop}]});
        }, TypeError, 'The providers must be named');

        providerLoader({
            foo: [{name: 'foo1', init: _.noop}],
            bar: [{name: 'bar1', init: _.noop}, {name: 'bar2', init: _.noop}]
        });
    });

    QUnit.test('required provider loading', function (assert) {
        var a = {name: 'a', init: _.noop};
        var b = {name: 'b', init: _.noop};
        var c = {name: 'c', init: _.noop};
        var providers = {
            foo: [a],
            bar: [b, c]
        };

        var loader = providerLoader(providers);

        QUnit.expect(5);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.getCategories(), ['foo', 'bar'], "The providers categories are correct");
        assert.deepEqual(loader.getProviders(), [a, b, c], "The providers have been registered");
        assert.deepEqual(loader.getProviders('foo'), providers.foo, "The providers are registered under the right category");
        assert.deepEqual(loader.getProviders('bar'), providers.bar, "The providers are registered under the right category");
    });


    QUnit.module('dynamic');

    QUnit.test('add provider module format', function (assert) {
        var loader = providerLoader();

        QUnit.expect(5);

        assert.equal(typeof loader, 'object', "The loader is an object");

        assert.throws(function () {
            loader.add(12);
        }, TypeError, 'Add requires an object');

        assert.throws(function () {
            loader.add({
                foo: '12',
                bar: true
            });
        }, TypeError, 'Add requires an object with a module and a category');

        assert.throws(function () {
            loader.add({
                module: 'foo'
            });
        }, TypeError, 'Add requires an object with a category');

        assert.throws(function () {
            loader.add({
                category: 'foo'
            });
        }, TypeError, 'Add requires an object with a module');

        loader.add({
            module: 'foo',
            category: 'foo'
        });
    });

    QUnit.asyncTest('load a provider', function (assert) {
        var provider = {
            module: 'test/core/providerLoader/mockProvider',
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = providerLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");

        assert.deepEqual(loader.add(provider), loader, 'The loader chains');

        promise = loader.load();

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getProviders('mock'), [], 'The loader mock category is empty');

        promise.then(function () {
            assert.equal(loader.getProviders('mock').length, 1, 'The mock category contains now a provider');
            QUnit.start();
        }).catch(function (e) {
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.cases([{
        title: 'missing name',
        module: 'test/core/providerLoader/mockProviderMissingName'
    }, {
        title: 'missing init',
        module: 'test/core/providerLoader/mockProviderMissingInit'
    }]).asyncTest('load a wrong provider ', function (data, assert) {
        var provider = {
            module: data.module,
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = providerLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");

        assert.deepEqual(loader.add(provider), loader, 'The loader chains');

        promise = loader.load();

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getProviders('mock'), [], 'The loader mock category is empty');

        promise.then(function () {
            assert.ok(false, 'The promise should fail since the loaded provider is wrong');
            QUnit.start();
        }).catch(function (e) {
            assert.ok(true, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('load a bundle', function (assert) {
        var provider = {
            module: 'test/core/providerLoader/mockAProvider',
            bundle: 'test/core/providerLoader/mockBundle.min',
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = providerLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.add(provider), loader, 'The loader chains');

        promise = loader.load(true);

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getProviders('mock'), [], 'The loader mock category is empty');

        promise.then(function () {
            assert.equal(loader.getProviders('mock').length, 1, 'The mock category contains now one provider');

            QUnit.start();
        }).catch(function (e) {
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('load multiple providers from a bundle', function (assert) {
        var providers = [{
            module: 'test/core/providerLoader/mockAProvider',
            bundle: 'test/core/providerLoader/mockBundle.min',
            category: 'mock',
            position: 1
        }, {
            module: 'test/core/providerLoader/mockBProvider',
            bundle: 'test/core/providerLoader/mockBundle.min',
            category: 'mock',
            position: 0
        }];
        var loader, promise;

        QUnit.expect(5);

        loader = providerLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.addList(providers), loader, 'The loader chains');

        promise = loader.load(true);

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getProviders('mock'), [], 'The loader mock category is empty');

        promise.then(function () {
            assert.equal(loader.getProviders('mock').length, 2, 'The mock category contains now one provider');

            QUnit.start();
        }).catch(function (e) {
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('remove a provider', function (assert) {
        var provider = {
            module: 'test/core/providerLoader/mockProvider',
            category: 'mock'
        };
        var loader;

        QUnit.expect(4);

        loader = providerLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.add(provider), loader, 'The prepend chains');

        assert.deepEqual(loader.remove('mock'), loader, 'The loader chains');

        loader.load().then(function () {
            assert.equal(loader.getProviders('test/core/providerLoader/mockProvider').length, 0, 'The mock provider has been removed');
            QUnit.start();
        }).catch(function (e) {
            assert.ok(false, e);
            QUnit.start();
        });

    });
});
