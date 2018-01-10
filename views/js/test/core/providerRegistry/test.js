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
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define(['core/providerRegistry'], function(providerRegistry) {
    'use strict';

    QUnit.module('providerRegistry');

    QUnit.test('module', function(assert) {
        var target = {};

        QUnit.expect(3);
        assert.equal(typeof providerRegistry, 'function', "The providerRegistry module exposes a function");
        assert.equal(providerRegistry(target), target, 'The providerRegistry helper returns the target');
        assert.notEqual(providerRegistry(), providerRegistry(), 'The providerRegistry helper returns a different object if called with no target');
    });

    QUnit.cases([
        { name : 'registerProvider', title : 'registerProvider' },
        { name : 'getProvider', title : 'getProvider' },
        { name : 'getAvailableProviders', title : 'getAvailableProviders' },
        { name : 'clearProviders', title : 'clearProviders' }
    ]).test('exported API ', function(data, assert) {
        var registry = providerRegistry();

        QUnit.expect(1);
        assert.equal(typeof registry[data.name], 'function', 'The providerRegistry helper has injected the "' + data.name + '" API');
    });

    QUnit.cases([
        { title : 'a provider have a name', provider: {init:function(){}} },
        { title : 'a provider have a not empty name', name: '', provider: {init:function(){}} },
        { title : 'a provider is an object', name: 'provider', provider: 'provider' },
        { title : 'a provider must come with an init function', name: 'provider', provider: {} }
    ]).test('registerProvider errors ', function(data, assert) {
        QUnit.expect(1);
        assert.throws(function() {
            providerRegistry().registerProvider(data.name, data.provider);
        }, 'registerProvider must throw error when ' + data.title);
    });

    QUnit.test('registerProvider validator', function(assert) {
        var provider = {init: function() {}};
        var registry = {};
        var name = 'provider2';
        var valid = false;

        QUnit.expect(10);

        function validator(p) {
            assert.ok(true, 'registerProvider has called the validator');
            assert.equal(p, provider, 'registerProvider has provided the provider to validate');
            return valid;
        }

        assert.equal(providerRegistry(registry, validator), registry, 'The providerRegistry helper has returned the target');
        assert.equal(registry.registerProvider(name, provider), registry, 'The registerProvider method returns the registry');

        assert.throws(function() {
            registry.getProvider(name);
        }, 'The validator must prevent to register the provider');

        valid = true;
        assert.equal(providerRegistry(registry, validator), registry, 'The providerRegistry helper has returned the target');
        assert.equal(registry.registerProvider(name, provider), registry, 'The registerProvider method returns the registry');
        assert.equal(registry.getProvider(name), provider, 'The provider is registered');
    });

    QUnit.test('registerProvider', function(assert) {
        var provider = {init: function() {}};
        var registry = {};
        var name = 'provider3';

        QUnit.expect(3);

        assert.equal(providerRegistry(registry), registry, 'The providerRegistry helper has returned the target');
        assert.equal(registry.registerProvider(name, provider), registry, 'The registerProvider method returns the registry');
        assert.equal(registry.getProvider(name), provider, 'The provider is registered');
    });

    QUnit.test('getProvider errors', function(assert) {
        QUnit.expect(3);

        assert.throws(function() {
            providerRegistry().getProvider('test');
        }, 'The registry must throw error if getProvider is called while no provider is registered');

        assert.throws(function() {
            providerRegistry().getProvider();
        }, 'The registry must throw error if getProvider is called while no provider is registered');

        assert.throws(function() {
            providerRegistry()
                .registerProxy('provider1', {init: function() {}})
                .registerProxy('provider2', {init: function() {}})
                .getProvider('test');
        }, 'The registry must throw error if the provider is unknown');
    });

    QUnit.test('getProvider', function(assert) {
        var provider1 = {init: function() {}};
        var provider2 = {init: function() {}};
        var registry = {};

        QUnit.expect(7);

        assert.equal(providerRegistry(registry), registry, 'The providerRegistry helper has returned the target');
        assert.equal(registry.registerProvider('provider1', provider1), registry, 'The registerProvider method returns the registry');
        assert.equal(registry.getProvider(), provider1, 'When the registry contains only one provider, always return it');

        assert.equal(registry.registerProvider('provider2', provider2), registry, 'The registerProvider method returns the registry');
        assert.equal(registry.getProvider('provider1'), provider1, 'Returns the requested provider');
        assert.equal(registry.getProvider('provider2'), provider2, 'Returns the requested provider');

        assert.throws(function() {
            registry.getProvider();
        }, 'The registry must throw error if getProvider is called without name and there are many providers registered');
    });

    QUnit.test('clearProviders', function(assert) {
        var provider1 = {init: function() {}};
        var provider2 = {init: function() {}};
        var registry = {};

        QUnit.expect(10);

        assert.equal(providerRegistry(registry), registry, 'The providerRegistry helper has returned the target');

        assert.throws(function() {
            registry.getProvider();
        }, 'No provider is registered');

        assert.equal(registry.registerProvider('provider1', provider1), registry, 'The registerProvider method returns the registry');
        assert.equal(registry.registerProvider('provider2', provider2), registry, 'The registerProvider method returns the registry');
        assert.equal(registry.getProvider('provider1'), provider1, 'Returns the requested provider');
        assert.equal(registry.getProvider('provider2'), provider2, 'Returns the requested provider');

        assert.equal(registry.clearProviders(), registry, 'The clearProviders method returns the registry');

        assert.throws(function() {
            registry.getProvider('provider1');
        }, 'Provider 1 been removed');

        assert.throws(function() {
            registry.getProvider('provider2');
        }, 'Provider 2 been removed');

        assert.throws(function() {
            registry.getProvider();
        }, 'All the providers has been removed');
    });

    QUnit.test('getAvailableProviders', function(assert) {
        var providerMock = {init: function() {}};
        var registry = providerRegistry({});

        QUnit.expect(3);

        assert.deepEqual(registry.getAvailableProviders(), [], 'No provider available');

        registry.registerProvider('provider1', providerMock);
        registry.registerProvider('provider2', providerMock);

        assert.deepEqual(registry.getAvailableProviders(), ['provider1', 'provider2'], 'Registered providers are available');

        registry.clearProviders();

        assert.deepEqual(registry.getAvailableProviders(), [], 'No provider available anymore');
    });

});
