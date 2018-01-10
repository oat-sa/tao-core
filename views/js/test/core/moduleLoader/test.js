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
 * Module loader's test
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'core/moduleLoader',
    'core/promise'
], function (_, moduleLoader, Promise){
    'use strict';


    QUnit.module('API');

    QUnit.test('module', function (assert){
        QUnit.expect(3);

        assert.equal(typeof moduleLoader, 'function', "The module loader exposes a function");
        assert.equal(typeof moduleLoader(), 'object', "The module loader produces an object");
        assert.notStrictEqual(moduleLoader(), moduleLoader(), "The module loader provides a different object on each call");
    });

    QUnit.test('loader methods', function (assert){
        var loader;
        var specs = {
            getFoo: function(foo) {
                assert.ok(true, 'The getFoo method has been called!');
                assert.equal(this, loader, 'The context is set on the loader');
                assert.equal(foo, 'foo', 'The parameter has been forwarded');
            }
        };
        loader = moduleLoader([], _.isFunction, specs);

        QUnit.expect(12);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.equal(typeof loader.add, 'function', "The loader exposes the add method");
        assert.equal(typeof loader.addList, 'function', "The loader exposes the addList method");
        assert.equal(typeof loader.append, 'function', "The loader exposes the append method");
        assert.equal(typeof loader.prepend, 'function', "The loader exposes the prepend method");
        assert.equal(typeof loader.load, 'function', "The loader exposes the load method");
        assert.equal(typeof loader.getModules, 'function', "The loader exposes the getModules method");
        assert.equal(typeof loader.getCategories, 'function', "The loader exposes the getCategories method");
        assert.equal(typeof loader.getFoo, 'function', "The loader exposes the added getFoo method");
        loader.getFoo('foo');
    });


    QUnit.module('required');

    QUnit.test('required module format', function (assert){
        QUnit.expect(7);

        assert.throws(function(){
            moduleLoader({ 12 : {} });
        }, TypeError, 'Wrong category format');

        assert.throws(function(){
            moduleLoader([{}]);
        }, TypeError, 'Wrong category format');

        assert.throws(function(){
            moduleLoader({ 'foo' : true });
        }, TypeError, 'The module list must be an array');

        assert.throws(function(){
            moduleLoader({ 'foo' : [true] });
        }, TypeError, 'The module list must be an array of objects');

        assert.throws(function(){
            moduleLoader({ 'foo' : [_.noop] });
        }, TypeError, 'The module list must be an array of objects');

        assert.throws(function(){
            moduleLoader({ 'foo' : [{}] }, _.isFunction);
        }, TypeError, 'The module list must be an array of functions');

        assert.throws(function(){
            moduleLoader({ 'foo' : ['true', {}] });
        }, TypeError, 'The module list must be an array with only objects');

        moduleLoader({
            foo : [{}],
            bar : [{}, {}]
        });

        moduleLoader({
            foo : [_.noop],
            bar : [_.noop, _.noop]
        }, _.isFunction);
    });

    QUnit.test('required module loading', function (assert){
        var a = function a (){ return 'a'; };
        var b = function b (){ return 'b'; };
        var c = function c (){ return 'c'; };
        var modules = {
            foo : [a],
            bar : [b, c]
        };

        var loader = moduleLoader(modules, _.isFunction);

        QUnit.expect(5);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.getCategories(), ['foo', 'bar'], "The modules categories are correct");
        assert.deepEqual(loader.getModules(), [a, b, c], "The modules have been registered");
        assert.deepEqual(loader.getModules('foo'), modules.foo, "The modules are registered under the right category");
        assert.deepEqual(loader.getModules('bar'), modules.bar, "The modules are registered under the right category");
    });


    QUnit.module('dynamic');

    QUnit.test('add module module format', function (assert){
        var loader = moduleLoader([], _.isFunction);

        QUnit.expect(5);

        assert.equal(typeof loader, 'object', "The loader is an object");

        assert.throws(function(){
            loader.add(12);
        }, TypeError, 'Add requires an object');

        assert.throws(function(){
            loader.add({
                foo: '12',
                bar  : true
            });
        }, TypeError, 'Add requires an object with a module and a category');

        assert.throws(function(){
            loader.add({
                module: 'foo'
            });
        }, TypeError, 'Add requires an object with a category');

        assert.throws(function(){
            loader.add({
                category: 'foo'
            });
        }, TypeError, 'Add requires an object with a module');

        loader.add({
            module : 'foo',
            category: 'foo'
        });
    });

    QUnit.asyncTest('load a module', function (assert){
        var module = {
            module : 'test/core/moduleLoader/mockModule',
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = moduleLoader([], _.isFunction);

        assert.equal(typeof loader, 'object', "The loader is an object");

        assert.deepEqual(loader.append(module), loader, 'The loader chains');

        promise = loader.load();

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getModules('mock'), [], 'The loader mock category is empty');

        promise.then(function(){
            assert.equal(loader.getModules('mock').length, 1, 'The mock category contains now a module');
            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('load a wrong module', function (assert) {
        var provider = {
            module : 'test/core/moduleLoader/mockModule',
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = moduleLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");

        assert.deepEqual(loader.add(provider), loader, 'The loader chains');

        promise = loader.load();

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getModules('mock'), [], 'The loader mock category is empty');

        promise.then(function () {
            assert.ok(false, 'The promise should fail since the loaded provider is wrong');
            QUnit.start();
        }).catch(function (e) {
            assert.ok(true, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('load a bundle', function (assert){
        var module = {
            module : 'test/core/moduleLoader/mockAModule',
            bundle: 'test/core/moduleLoader/mockBundle.min',
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = moduleLoader([], _.isFunction);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.add(module), loader, 'The loader chains');

        promise = loader.load(true);

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getModules('mock'), [], 'The loader mock category is empty');

        promise.then(function(){
            assert.equal(loader.getModules('mock').length, 1, 'The mock category contains now one module');

            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('load load multiple modules from a bundle', function (assert){
        var modules = [{
            module : 'test/core/moduleLoader/mockAModule',
            bundle: 'test/core/moduleLoader/mockBundle.min',
            category: 'mock',
            position: 1
        }, {
            module : 'test/core/moduleLoader/mockBModule',
            bundle: 'test/core/moduleLoader/mockBundle.min',
            category: 'mock',
            position : 0
        }];
        var loader, promise;

        QUnit.expect(5);

        loader = moduleLoader([], _.isFunction);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.addList(modules), loader, 'The loader chains');

        promise = loader.load(true);

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getModules('mock'), [], 'The loader mock category is empty');

        promise.then(function(){
            assert.equal(loader.getModules('mock').length, 2, 'The mock category contains now one module');

            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('remove a module', function (assert){
        var module = {
            module : 'test/core/moduleLoader/mockModule',
            category: 'mock'
        };
        var loader;

        QUnit.expect(4);

        loader = moduleLoader([], _.isFunction);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.prepend(module), loader, 'The prepend chains');

        assert.deepEqual(loader.remove('mock'), loader, 'The loader chains');

        loader.load().then(function(){
            assert.equal(loader.getModules('test/core/moduleLoader/mockModule').length, 0, 'The mock module has been removed');
            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });

    });
});
