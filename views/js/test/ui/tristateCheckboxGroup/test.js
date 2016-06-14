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
    'ui/tristateCheckboxGroup'
], function ($, _, tristateCheckboxGroup){
    'use strict';

    QUnit.module('Tri-state Checkboxes');

    QUnit.test('module', 3, function (assert){
        assert.equal(typeof tristateCheckboxGroup, 'function', "The tristateCheckbox module exposes a function");
        assert.equal(typeof tristateCheckboxGroup(), 'object', "The tristateCheckbox factory produces an object");
        assert.notStrictEqual(tristateCheckboxGroup(), tristateCheckboxGroup(), "The tristateCheckbox factory provides a different object on each call");
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
            var instance = tristateCheckboxGroup();
            assert.equal(typeof instance[data.name], 'function', 'The tristateCheckbox instance exposes a "' + data.title + '" function');
            instance.destroy();
        });


    QUnit.test('init', function (assert){
        var config = {};
        var instance = tristateCheckboxGroup(config);

        assert.equal(instance.is('rendered'), false, 'The tristateCheckbox instance must not be rendered');

        instance.destroy();
    });

    QUnit.test('render', function (assert){
        var $container = $('#fixture-0');
        var config = {
            renderTo : $container,
            replace : true,
            list : [
                {label : 'choice not selected', value : '0'},
                {checked : true, label : 'choice selected', value : '1'},
                {indeterminate : true, label : 'choice intermediate', value : '2'}
            ]
        };
        var triCbox = tristateCheckboxGroup(config);
        assert.equal($container.find('.tristate-checkbox-group').length, 1, 'container ok');
        assert.equal($container.find('input[type=checkbox]').length, 3, '3 checkboxes');
        assert.equal($container.find('input[type=checkbox]:checked').length, 1, '1 checked');
        assert.equal($container.find('input[type=checkbox]:indeterminate').length, 1, '1 indeterminate');
    });

    QUnit.test('setElements', function (assert){
        var $container = $('#fixture-0');
        var config = {
            renderTo : $container,
            replace : true
        };
        var triCbox = tristateCheckboxGroup(config).setElements([
            {label : 'choice not selected', value : '0'},
            {checked : true, label : 'choice selected', value : '1'},
            {indeterminate : true, label : 'choice intermediate', value : '2'}
        ]);
        assert.equal($container.find('.tristate-checkbox-group').length, 1, 'container ok');
        assert.equal($container.find('input[type=checkbox]').length, 3, '3 checkboxes');
        assert.equal($container.find('input[type=checkbox]:checked').length, 1, '1 checked');
        assert.equal($container.find('input[type=checkbox]:indeterminate').length, 1, '1 indeterminate');

        //set new element + edit existing one
        triCbox.setElements([
            {value : '0', indeterminate : true, label : 'new value for 0'},
            {value : '3', checked : true, label : 'new element'}
        ]);
        assert.equal($container.find('input[type=checkbox]').length, 4, '4 checkboxes');
        assert.equal($container.find('input[type=checkbox]:checked').length, 2, '2 checked');
        assert.equal($container.find('input[type=checkbox]:indeterminate').length, 2, '2 indeterminate');
    });

    QUnit.test('getValues/setValues', function (assert){
        var $container = $('#fixture-0');
        var config = {
            renderTo : $container,
            replace : true,
            list : [
                {label : 'choice not selected', value : '0'},
                {checked : true, label : 'choice selected', value : '1'},
                {indeterminate : true, label : 'choice intermediate', value : '2'}
            ]
        };
        var triCbox = tristateCheckboxGroup(config);
        var values = triCbox.getValues();
        assert.ok(_.isArray(values.checked), 'get checked elements');
        assert.ok(_.isArray(values.indeterminate), 'get indeterminate elements');
        assert.equal(values.checked[0], '1');
        assert.equal(values.indeterminate[0], '2');

        triCbox.setValues({
            checked : ['0'],
            indeterminate : ['1']
        });
        values = triCbox.getValues();
        assert.ok(_.isArray(values.checked), 'get checked elements');
        assert.ok(_.isArray(values.indeterminate), 'get indeterminate elements');
        assert.equal(values.checked[0], '0');
        assert.equal(values.indeterminate[0], '1');
    });

    QUnit.asyncTest('change', function (assert){
        var $container = $('#fixture-0');
        var config = {
            renderTo : $container,
            replace : true,
            list : [
                {label : 'choice not selected', value : '0'},
                {checked : true, label : 'choice selected', value : '1'},
                {indeterminate : true, label : 'choice intermediate', value : '2'}
            ]
        };
        var triCbox = tristateCheckboxGroup(config).on('change', function (values){
            assert.ok(_.isArray(values.checked), 'get checked elements');
            assert.ok(_.isArray(values.indeterminate), 'get indeterminate elements');
            assert.equal(values.checked.length, 2);
            assert.equal(values.indeterminate.length, 1);
            assert.equal(values.checked[0], '0');
            assert.equal(values.checked[1], '1');
            assert.equal(values.indeterminate[0], '2');
            QUnit.start();
        });

        var values = triCbox.getValues();
        assert.ok(_.isArray(values.checked), 'get checked elements');
        assert.ok(_.isArray(values.indeterminate), 'get indeterminate elements');
        assert.equal(values.checked.length, 1);
        assert.equal(values.indeterminate.length, 1);
        assert.equal(values.checked[0], '1');
        assert.equal(values.indeterminate[0], '2');

        $container.find('input[value="0"]').click();
    });

    QUnit.asyncTest('maxSelection', function (assert){
        
        QUnit.expect(3);
        
        var $container = $('#fixture-0');
        var config = {
            renderTo : $container,
            replace : true,
            list : [
                {label : 'choice not selected', value : '0'},
                {label : 'choice selected', value : '1'},
                {label : 'choice intermediate', value : '2'}
            ],
            maxSelection : 1
        };
        var triCbox = tristateCheckboxGroup(config).on('change', function (values){
            assert.equal(values.checked.length, 1);
            assert.equal(values.indeterminate.length, 0);
            assert.equal(values.checked[0], '0');
            QUnit.start();
        });
        
        $container.find('input[value="0"]').click();//first selection s allowed
        $container.find('input[value="1"]').click();//this one will not
    });

    QUnit.test('render (visual test)', function (assert){

        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            replace : true,
            list : [
                {label : 'choice not selected', value : '0'},
                {checked : true, label : 'choice selected', value : '1'},
                {indeterminate : true, label : 'choice intermediate', value : '2'}
            ],
            maxSelection : 1
        };
        var tristateCbox = tristateCheckboxGroup(config);

        tristateCbox.setElements([
            {value : '0', indeterminate : true},
            {value : '3', checked : true, label : 'new value'}
        ]);
        tristateCbox.setValues({
            checked : ['0', '1'],
            indeterminate : ['1', '2', '3']
        });

        assert.equal($container.find('.tristate-checkbox-group').length, 1, 'container ok');
    });


});
