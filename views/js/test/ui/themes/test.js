define([
    'jquery',
    'ui/themes'
], function($, themesHandler){
    'use strict';
    
    var themeConfig = {
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
    
    require.config({
        config : {
            'ui/themes' : themeConfig
        }
    });
    
    QUnit.module('Themes config');

    QUnit.test('module', function(assert){
        assert.ok(typeof themesHandler !== 'undefined', 'The module exports something');
        assert.ok(typeof themesHandler === 'object', 'The module exports an object');
    });

    QUnit.test('get(what)', function(assert){
        var itemsWk = 'items_wk';

        QUnit.expect(3);

        assert.deepEqual(themesHandler.get('items'), themeConfig.items, 'returns items themes');
        assert.deepEqual(themesHandler.get('items_wk'), themeConfig[itemsWk], 'returns items_wk themes');
        assert.deepEqual(themesHandler.get('i/dont/exists'), undefined, 'returns undefined if target is not found');
    });

    // QUnit.test('get(what, ns)', function(assert){
    //     var itemsWk = 'items_wk';
    //
    //     QUnit.expect(3);
    //
    //     assert.deepEqual(themesHandler.get('items', 'wk'), themeConfig[itemsWk], 'returns items_wk themes');
    //     assert.deepEqual(themesHandler.get('items', 'none'), undefined, 'returns undefined if target is not found');
    // });

    QUnit.test('getAvailable(what)', function(assert){
        var itemsWk = 'items_wk';

        QUnit.expect(3);

        assert.deepEqual(themesHandler.getAvailable('items'), themeConfig.items.available, 'returns available items themes');
        assert.deepEqual(themesHandler.getAvailable('items_wk'), themeConfig[itemsWk].available, 'returns available items_wk themes');
        assert.deepEqual(themesHandler.getAvailable('i/dont/exists'), [], 'returns empty array if target is not found');
    });



});
