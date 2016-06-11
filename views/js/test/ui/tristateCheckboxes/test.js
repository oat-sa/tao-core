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
    'ui/tristateCheckboxes'
], function ($, _, tristateCheckboxes){
    'use strict';

    QUnit.module('Tri-state Checkboxes');

    QUnit.test('module', 3, function (assert){
        assert.equal(typeof tristateCheckboxes, 'function', "The tristateCheckbox module exposes a function");
        assert.equal(typeof tristateCheckboxes(), 'object', "The tristateCheckbox factory produces an object");
        assert.notStrictEqual(tristateCheckboxes(), tristateCheckboxes(), "The tristateCheckbox factory provides a different object on each call");
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
        {name : 'getValues', title : 'getValues'},
        {name : 'setValues', title : 'setValues'},
        {name : 'setElements', title : 'setElements'}
    ];

    QUnit
        .cases(testReviewApi)
        .test('instance API ', function (data, assert){
            var instance = tristateCheckboxes();
            assert.equal(typeof instance[data.name], 'function', 'The tristateCheckbox instance exposes a "' + data.title + '" function');
            instance.destroy();
        });
    
    
    QUnit.test('init', function (assert){
        var config = {};
        var instance = tristateCheckboxes(config);

        assert.equal(instance.is('rendered'), false, 'The tristateCheckbox instance must not be rendered');

        instance.destroy();
    });

    QUnit.test('render (visual test)', function (assert){

        var $container = $('#fixture-0');
        var config = {
            renderTo : $container,
            replace : true,
            list : [
                {label : 'choice not selected', value: '0'},
                {checked : true, label : 'choice selected', value: '1'},
                {indeterminate : true, label : 'choice intermediate', value: '2'}
            ],
            max : 1
        };
        var tristateCbox = tristateCheckboxes(config).on('change', function(values){
            console.log('values', values);
        });
        console.log(tristateCbox.getValues());
        
        tristateCbox.setElements([
            {value: '0', indeterminate : true},
            {value: '3', checked : true, label : 'new value'}
        ]);
        tristateCbox.setValues({
            checked : ['0', '1'],
            indeterminate : ['1', '2', '3']
        });
        
        console.log(tristateCbox.getValues());
        assert.equal($container.find('.tristate-checkboxs').length, 1, 'container ok');
    });
    
    
});
