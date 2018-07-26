define([
    'lodash',
    'jquery',
    'ui/themes'
], function(_, $, themesHandler){
    'use strict';

    var config = {
        'items': {
            base: 'base.css',
            default: 'blue',
            available: [{
                id: 'blue',
                path: 'blue.css',
                name: 'Blue'
            }, {
                id: 'green',
                path: 'green.css',
                name: 'Green'
            }]
        },
        'items_ns1': {
            base: 'base.css',
            default: 'orange',
            available: [{
                id: 'red',
                path: 'red.css',
                name: 'Red'
            }, {
                id: 'orange',
                path: 'orange.css',
                name: 'Orange'
            }, {
                id: 'yellow',
                path: 'yellow.css',
                name: 'Yellow'
            }]
        },
        'items_ns2': {
            base: 'base.css',
            default: 'pink',
            available: [{
                id: 'pink',
                path: 'pink.css',
                name: 'Pink'
            }]
        }
    };

    var itemsNs1 = 'items_ns1';
    var itemsNs2 = 'items_ns2';

    function mockThemeConfig(themeConfig) {
        require.config({
            config : {
                'ui/themes' : themeConfig
            }
        });
    }

    QUnit.module('Themes config');

    QUnit.test('module', function(assert){
        assert.ok(typeof themesHandler !== 'undefined', 'The module exports something');
        assert.ok(typeof themesHandler === 'object', 'The module exports an object');
    });

    QUnit.test('get(what)', function(assert){
        QUnit.expect(7);

        config.activeNamespace = undefined;
        mockThemeConfig(config);
        assert.deepEqual(themesHandler.get('items'), config.items, 'returns items themes');
        assert.deepEqual(themesHandler.get('items_ns1'), config[itemsNs1], 'returns items_ns1 themes');
        assert.deepEqual(themesHandler.get('items_ns2'), config[itemsNs2], 'returns items_ns1 themes');
        assert.deepEqual(themesHandler.get('unknown'), undefined, 'returns undefined if target is not found');

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('ns1');
        assert.deepEqual(themesHandler.get('items'), config[itemsNs1], 'automatically returns namespaced entry if an active namespace exists');

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('ns2');
        assert.deepEqual(themesHandler.get('items'), config[itemsNs2], 'automatically returns namespaced entry if an active namespace exists');

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('unknown');
        assert.deepEqual(themesHandler.get('items'), config.items, 'returns item themes if specified namespace is not found');
    });

    QUnit.test('get(what, ns)', function(assert){
        QUnit.expect(2);

        config.activeNamespace = 'ns2';
        mockThemeConfig(config);
        assert.deepEqual(themesHandler.get('items', 'ns1'), config[itemsNs1], 'returns "items_ns" entry if namespace is specified, ignoring active namespace');
        assert.deepEqual(themesHandler.get('items', 'unknown'), undefined, 'returns undefined if namespace is not found');
    });

    QUnit.test('getAvailable(what)', function(assert){
        QUnit.expect(6);

        config.activeNamespace = undefined;
        mockThemeConfig(config);
        assert.deepEqual(themesHandler.getAvailable('items'), config.items.available, 'returns available items themes');
        assert.deepEqual(themesHandler.getAvailable('items_ns1'), config[itemsNs1].available, 'returns available items_ns1 themes');
        assert.deepEqual(themesHandler.getAvailable('unknown'), [], 'returns empty array if target is not found');

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('ns1');
        assert.deepEqual(themesHandler.getAvailable('items'), config[itemsNs1].available, 'returns namespaced entry if an active namespace exists');

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('ns2');
        assert.deepEqual(themesHandler.getAvailable('items'), config[itemsNs2].available, 'returns namespaced entry if an active namespace exists');

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('unknown');
        assert.deepEqual(themesHandler.getAvailable('items'), config.items.available, 'returns items entry if active namespace is not found');
    });

    QUnit.test('getAvailable(what, ns)', function(assert){
        QUnit.expect(2);

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('ns2');
        assert.deepEqual(themesHandler.getAvailable('items', 'ns1'), config[itemsNs1].available, 'returns available items themes of entry "items_ns1"');
        assert.deepEqual(themesHandler.getAvailable('items', 'unknown'), [], 'returns empty array if namespace is not found');
    });

    QUnit.test('getActiveNamespace()', function(assert){
        QUnit.expect(1);

        config.activeNamespace = 'ns2';
        mockThemeConfig(config);
        assert.deepEqual(themesHandler.getActiveNamespace(), 'ns2', 'Returns active namespace from the config');
    });

    QUnit.test('setActiveNamespace(ns)', function(assert){
        QUnit.expect(1);

        config.activeNamespace = itemsNs2;
        mockThemeConfig(config);
        themesHandler.setActiveNamespace(itemsNs1);
        assert.deepEqual(themesHandler.getActiveNamespace(), itemsNs1, 'Returns items_ns1 as it was just set through setActiveNamespace call');
    });

    QUnit.test('getCurrentThemeData(what)', function(assert){
        QUnit.expect(3);

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('');
        assert.deepEqual(themesHandler.getCurrentThemeData('items'), config.items, 'returns items themes');

        mockThemeConfig(config);
        themesHandler.setActiveNamespace('ns1');
        assert.deepEqual(themesHandler.getCurrentThemeData('items'), config[itemsNs1], 'returns items_ns1 themes');
        assert.deepEqual(themesHandler.getCurrentThemeData(), config[itemsNs1], 'returns items_ns1 themes');
    });

});
