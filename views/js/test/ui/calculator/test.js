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
    'ui/calculator'
], function ($, _, calculator){
    'use strict';

    QUnit.module('Calculator');

    QUnit.test('module', 3, function (assert){
        assert.equal(typeof calculator, 'function', "The calculator module exposes a function");
        assert.equal(typeof calculator(), 'object', "The calculator factory produces an object");
        assert.notStrictEqual(calculator(), calculator(), "The calculator factory provides a different object on each call");
    });

    var testReviewApi = [
        {name : 'init', title : 'init'},
        {name : 'destroy', title : 'destroy'},
        {name : 'render', title : 'render'},
        {name : 'show', title : 'show'},
        {name : 'hide', title : 'hide'},
        {name : 'enable', title : 'enable'},
        {name : 'disable', title : 'disable'},
        {name : 'is', title : 'is'},
        {name : 'setState', title : 'setState'},
        {name : 'getContainer', title : 'getContainer'},
        {name : 'getElement', title : 'getElement'},
        {name : 'getTemplate', title : 'getTemplate'},
        {name : 'setTemplate', title : 'setTemplate'},
        {name : 'reset', title : 'reset'},
        {name : 'resetPosition', title : 'resetPosition'},
        {name : 'resetSize', title : 'resetSize'},
        {name : 'press', title : 'press'}
    ];

    QUnit
        .cases(testReviewApi)
        .test('instance API ', function (data, assert){
            var instance = calculator();
            assert.equal(typeof instance[data.name], 'function', 'The calculator instance exposes a "' + data.title + '" function');
            instance.destroy();
        });

    QUnit.test('init', function (assert){
        var config = {};
        var instance = calculator(config);

        assert.equal(instance.is('rendered'), false, 'The calculator instance must not be rendered');

        instance.destroy();
    });

    QUnit.test('render (visual test)', function (assert){

        var $container = $('#fixture-0')
            .css({
                height : 1000,
                width : 1000,
                position : 'relative',
                backgroundColor : '#ccc'
            });
        var config = {
            renderTo : $container,
            replace : true
        };
        calculator(config);

        assert.equal($container.find('.dynamic-component-container .calcContainer').length, 1, 'calculator container ok');
        assert.equal($container.find('.dynamic-component-container .calcContainer .calcDisplay').length, 1, 'calculator display ok');
        assert.equal($container.find('.dynamic-component-container .calcContainer .calcFunction').length, 9, 'calculator function button ok');
        assert.equal($container.find('.dynamic-component-container .calcContainer .calcClear').length, 3, 'calculator clear button ok');
        assert.equal($container.find('.dynamic-component-container .calcContainer .calcDigit').length, 10, 'calculator digit button ok');
    });

    QUnit.asyncTest('render (visual test)', function (assert){

        var $container = $('#fixture-1')
            .css({
                height : 1000,
                width : 1000,
                position : 'relative',
                backgroundColor : '#ccc'
            });

        require(['tpl!tao/test/ui/calculator/alt-template'], function(alternativeTemplate){
            var config = {
                renderTo : $container,
                replace : true,
                alternativeTemplate : alternativeTemplate
            };
            calculator(config);

            assert.equal($container.find('.dynamic-component-container .calcContainer').length, 1, 'calculator container ok');
            assert.equal($container.find('.dynamic-component-container .calcContainer .calcDisplay').length, 1, 'calculator display ok');
            assert.equal($container.find('.dynamic-component-container .calcContainer .calcFunction').length, 8, 'calculator function button ok');
            assert.equal($container.find('.dynamic-component-container .calcContainer .calcClear').length, 3, 'calculator clear button ok');
            assert.equal($container.find('.dynamic-component-container .calcContainer .calcDigit').length, 10, 'calculator digit button ok');

            QUnit.start();
        });
    });

    QUnit.test('press', function (assert){

        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            replace : true
        };
        var instance = calculator(config);
        var $display = $container.find('.dynamic-component-container .calcContainer .calcDisplay');

        instance.press('1').press('2').press('3').press('.').press('4');
        assert.equal($display.val(), '123.4', 'calculator display ok');

        instance.press('C').press('1').press('2').press('3').press('.').press('4').press('DEL').press('DEL').press('DEL').press('DEL').press('DEL');
        assert.equal($display.val(), '', 'DEL ok');

        instance.press('C').press('1').press('2').press('3').press('.').press('4').press('C');
        assert.equal($display.val(), '0', 'C ok');

        instance.press('C').press('1').press('+').press('2').press('=');
        assert.equal($display.val(), '3', 'sum ok');

        instance.press('C').press('1').press('-').press('2').press('=');
        assert.equal($display.val(), '-1', 'difference ok');

        instance.press('C').press('.').press('1').press('*').press('.').press('1').press('=');
        assert.equal($display.val(), '0.01', 'multiplication ok');

        instance.press('C').press('.').press('1').press('/').press('.').press('1').press('=');
        assert.equal($display.val(), '1', 'division ok');

        instance.press('C').press('1').press('+').press('2').press('CE').press('3').press('=');
        assert.equal($display.val(), '4', 'CE ok');

        instance.press('C').press('1').press('2').press('+').press('2').press('%').press('=');
        assert.equal($display.val(), '12.24', '% ok');

        instance.press('C').press('2').press('sqrt');
        assert.equal($display.val(), '1.4142135623730951', 'sqrt ok');

        instance.press('C').press('2').press('sqrt').press('pow').press('2').press('=');
        assert.equal($display.val(), '2', 'pow ok');

        instance.press('C').press('.').press('0').press('0').press('1').press('1/x');
        assert.equal($display.val(), '1000', '1/x ok');

        instance.press('C').press('.').press('0').press('0').press('1').press('1/x').press('1/x');
        assert.equal($display.val(), '0.001', '1/x ok');

    });

    QUnit.asyncTest('reset', function (assert){
        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            replace : true
        };
        var instance = calculator(config)
            .after('reset', function (){
                assert.equal($container.find('.dynamic-component-container .calcContainer .calcDisplay').val(), '0', 'display reset');
                QUnit.start();
            });

        instance.press(1);
        assert.equal($container.find('.dynamic-component-container .calcContainer .calcDisplay').val(), '1', 'display ok');

        instance.reset();
    });

    QUnit.asyncTest('destroy', function (assert){
        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            replace : true
        };
        var instance = calculator(config)
            .after('destroy', function (){
                assert.equal($container.find('.dynamic-component-container .calcContainer .calcDisplay').length, 0, 'container destroyed');
                QUnit.start();
            });

        assert.equal($container.find('.dynamic-component-container .calcContainer').length, 1, 'container rendered');

        instance.destroy();
    });
    
    QUnit.asyncTest('show', function (assert){
        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            replace : true
        };
        calculator(config)
            .after('show', function (){
                _.delay(function (){
                    //check focus
                    assert.ok($container.find('.calcDisplay')[0] === document.activeElement, 'calculator display on focus');
                    QUnit.start();
                }, 100);
            })
            .show();
    });
});
