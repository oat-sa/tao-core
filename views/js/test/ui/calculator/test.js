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
define([
    'jquery',
    'lodash',
    'ui/calculator/build.amd',
    'ui/calculator'
], function($, _, calculatorRaw, calculator){
    'use strict';

    QUnit.module('Calculator');

//    QUnit.test('render (all options)', function(assert){
//        
//        QUnit.expect(0);
//        
//        var $containerA = $('#fixture-1 #calcA');
//        var $containerB = $('#fixture-1 #calcB');
//        
//        calculatorRaw.init($containerA);
//        calculatorRaw.init($containerB);
//    });

    QUnit.test('module', 3, function(assert) {
        assert.equal(typeof calculator, 'function', "The calculator module exposes a function");
        assert.equal(typeof calculator(), 'object', "The calculator factory produces an object");
        assert.notStrictEqual(calculator(), calculator(), "The calculator factory provides a different object on each call");
    });

    var testReviewApi = [
        { name : 'init', title : 'init' },
        { name : 'destroy', title : 'destroy' },
        { name : 'render', title : 'render' },
        { name : 'show', title : 'show' },
        { name : 'hide', title : 'hide' },
        { name : 'enable', title : 'enable' },
        { name : 'disable', title : 'disable' },
        { name : 'is', title : 'is' },
        { name : 'setState', title : 'setState' },
        { name : 'getElement', title : 'getElement' },
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getTemplate', title : 'getTemplate' },
        { name : 'setTemplate', title : 'setTemplate' }
    ];

//    QUnit
//        .cases(testReviewApi)
//        .test('instance API ', function(data, assert) {
//            var instance = calculator();
//            assert.equal(typeof instance[data.name], 'function', 'The calculator instance exposes a "' + data.title + '" function');
//            instance.destroy();
//        });
        
    QUnit.test('init', function(assert) {
        var config = {};
        var instance = calculator(config);

        assert.equal(instance.is('rendered'), false, 'The calculator instance must not be rendered');

        instance.destroy();
    });
    
    QUnit.test('render', function(assert) {
        var $dummy = $('<div class="dummy" />');
        var $container = $('#fixture-0')
            .css({
                height : 1000,
                backgroundColor : '#eee'
            })
            .append($dummy);
        var config = {
            renderTo: $container,
            replace: true
        };
        var instance;

        assert.equal($container.children().length, 1, 'The container already contains an element');
        assert.equal($container.children().get(0), $dummy.get(0), 'The container contains the dummy element');
        assert.equal($container.find('.dummy').length, 1, 'The container contains an element of the class dummy');

        instance = calculator(config);
        
        return;
        instance.destroy();

        assert.equal($container.children().length, 0, 'The container is now empty');
        assert.equal(instance.getElement(), null, 'The calculator instance has removed its rendered content');
    });
});
