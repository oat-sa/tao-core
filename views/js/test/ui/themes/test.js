define([
    'lodash',
    'jquery',
    'ui/themes'
], function(_, $, themesHandler){
    'use strict';
    
    var configWithoutNs = {
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
        'items_wk': {
            base: 'base.css',
            default: 'blue',
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
        }
    };
    
    var configWithNs = _.clone(configWithoutNs);
    configWithNs.activeNamespace = 'wk';
    
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
        var itemsWk = 'items_wk';

        QUnit.expect(3);

        mockThemeConfig(configWithoutNs);
        
        assert.deepEqual(themesHandler.get('items'), configWithoutNs.items, 'returns items themes');
        assert.deepEqual(themesHandler.get('items_wk'), configWithoutNs[itemsWk], 'returns items_wk themes');
        assert.deepEqual(themesHandler.get('i/dont/exists'), undefined, 'returns undefined if target is not found');
    });

    QUnit.test('get(what, ns)', function(assert){
        var itemsWk = 'items_wk';

        QUnit.expect(3);

        mockThemeConfig(configWithoutNs);
        
        assert.deepEqual(themesHandler.get('items', 'wk'), configWithoutNs[itemsWk], 'returns "items_ns" entry if namespace is specified');
        assert.deepEqual(themesHandler.get('items', 9), configWithoutNs.items, 'returns "items" entry if namespace is not a string');
        assert.deepEqual(themesHandler.get('items', 'none'), undefined, 'returns undefined if namespace is not found');
    });
    
    QUnit.test('getAvailable(what)', function(assert){
        var itemsWk = 'items_wk';

        QUnit.expect(3);

        mockThemeConfig(configWithoutNs);
        
        assert.deepEqual(themesHandler.getAvailable('items'), configWithoutNs.items.available, 'returns available items themes');
        assert.deepEqual(themesHandler.getAvailable('items_wk'), configWithoutNs[itemsWk].available, 'returns available items_wk themes');
        assert.deepEqual(themesHandler.getAvailable('i/dont/exists'), [], 'returns empty array if target is not found');
    });

    QUnit.test('getAvailable(what, ns)', function(assert){
        var itemsWk = 'items_wk';

        QUnit.expect(3);

        mockThemeConfig(configWithoutNs);
        
        assert.deepEqual(themesHandler.getAvailable('items', 'wk'), configWithoutNs[itemsWk].available, 'returns available items themes of entry "items_wk"');
        assert.deepEqual(themesHandler.getAvailable('items', 9), configWithoutNs.items.available, 'returns "items" entry if namespace is not a string');
        assert.deepEqual(themesHandler.getAvailable('items', 'none'), [], 'returns empty array if namespace is not found');
    });



});
