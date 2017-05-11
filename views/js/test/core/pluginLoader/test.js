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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */

/**
 * Plugin loader's test
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/pluginLoader',
    'core/promise',
], function (_, pluginLoader, Promise){
    'use strict';


    QUnit.module('API');

    QUnit.test('module', function (assert){
        QUnit.expect(3);

        assert.equal(typeof pluginLoader, 'function', "The plugin loader exposes a function");
        assert.equal(typeof pluginLoader(), 'object', "The plugin loader produces an object");
        assert.notStrictEqual(pluginLoader(), pluginLoader(), "The plugin loader provides a different object on each call");
    });

    QUnit.test('loader methods', function (assert){
        var loader = pluginLoader();

        QUnit.expect(7);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.equal(typeof loader.add, 'function', "The loader exposes the add method");
        assert.equal(typeof loader.addList, 'function', "The loader exposes the addList method");
        assert.equal(typeof loader.append, 'function', "The loader exposes the append method");
        assert.equal(typeof loader.prepend, 'function', "The loader exposes the prepend method");
        assert.equal(typeof loader.load, 'function', "The loader exposes the load method");
        assert.equal(typeof loader.getPlugins, 'function', "The loader exposes the getPlugins method");

    });


    QUnit.module('required');

    QUnit.test('required plugin format', function (assert){
        QUnit.expect(4);

        assert.throws(function(){
            pluginLoader({ 12 : _.noop });
        }, TypeError, 'Wrong category format');

        assert.throws(function(){
            pluginLoader({ 'foo' : true });
        }, TypeError, 'The plugin list must be an array');

        assert.throws(function(){
            pluginLoader({ 'foo' : [true] });
        }, TypeError, 'The plugin list must be an array of function');

        assert.throws(function(){
            pluginLoader({ 'foo' : ['true', _.noop] });
        }, TypeError, 'The plugin list must be an array with only functions');

        pluginLoader({
            foo : [_.noop],
            bar : [_.noop, _.noop]
        });
    });

    QUnit.test('required plugin loading', function (assert){
        var a = function a (){ return 'a'; };
        var b = function b (){ return 'b'; };
        var c = function c (){ return 'c'; };
        var plugins = {
            foo : [a],
            bar : [b, c]
        };

        var loader = pluginLoader(plugins);

        QUnit.expect(5);

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.getCategories(), ['foo', 'bar'], "The plugins categories are correct");
        assert.deepEqual(loader.getPlugins(), [a, b, c], "The plugins have been registered");
        assert.deepEqual(loader.getPlugins('foo'), plugins.foo, "The plugins are registered under the right category");
        assert.deepEqual(loader.getPlugins('bar'), plugins.bar, "The plugins are registered under the right category");
    });


    QUnit.module('dynamic');

    QUnit.test('add plugin module format', function (assert){
        var loader = pluginLoader();

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

    QUnit.asyncTest('load a plugin', function (assert){
        var plugin = {
            module : 'test/core/pluginLoader/mockPlugin',
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = pluginLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");

        assert.deepEqual(loader.append(plugin), loader, 'The loader chains');

        promise = loader.load();

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getPlugins('mock'), [], 'The loader mock category is empty');

        promise.then(function(){
            assert.equal(loader.getPlugins('mock').length, 1, 'The mock category contains now a plugin');
            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('load a bundle', function (assert){
        var plugin = {
            module : 'test/core/pluginLoader/mockAPlugin',
            bundle: 'test/core/pluginLoader/mockBundle.min',
            category: 'mock'
        };
        var loader, promise;

        QUnit.expect(5);

        loader = pluginLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.add(plugin), loader, 'The loader chains');

        promise = loader.load(true);

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getPlugins('mock'), [], 'The loader mock category is empty');

        promise.then(function(){
            assert.equal(loader.getPlugins('mock').length, 1, 'The mock category contains now one plugin');

            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('load load multiple plugins from a bundle', function (assert){
        var plugins = [{
            module : 'test/core/pluginLoader/mockAPlugin',
            bundle: 'test/core/pluginLoader/mockBundle.min',
            category: 'mock',
            position: 1
        }, {
            module : 'test/core/pluginLoader/mockBPlugin',
            bundle: 'test/core/pluginLoader/mockBundle.min',
            category: 'mock',
            position : 0
        }];
        var loader, promise;

        QUnit.expect(5);

        loader = pluginLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.addList(plugins), loader, 'The loader chains');

        promise = loader.load(true);

        assert.ok(promise instanceof Promise, "The load method returns a Promise");
        assert.deepEqual(loader.getPlugins('mock'), [], 'The loader mock category is empty');

        promise.then(function(){
            assert.equal(loader.getPlugins('mock').length, 2, 'The mock category contains now one plugin');

            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });
    });

    QUnit.asyncTest('remove a plugin', function (assert){
        var plugin = {
            module : 'test/core/pluginLoader/mockPlugin',
            category: 'mock'
        };
        var loader;

        QUnit.expect(4);

        loader = pluginLoader();

        assert.equal(typeof loader, 'object', "The loader is an object");
        assert.deepEqual(loader.prepend(plugin), loader, 'The prepend chains');

        assert.deepEqual(loader.remove('mock'), loader, 'The loader chains');

        loader.load().then(function(){
            assert.equal(loader.getPlugins('test/core/pluginLoader/mockPlugin').length, 0, 'The mock plugin has been removed');
            QUnit.start();
        }).catch(function(e){
            assert.ok(false, e);
            QUnit.start();
        });

    });
});
