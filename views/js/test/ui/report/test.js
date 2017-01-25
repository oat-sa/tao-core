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

define([
    'jquery',
    'lodash',
    'ui/report',
    'css!taoCss/tao-3.css',
    'css!taoCss/tao-main-style.css'
], function($, _, report) {
    'use strict';

    // toggle the sample display
    var showSample = true;

    var sampleData = {
        "type": "warning",
        "message": "Data not imported. All records are invalid.",
        "data": null,
        "children": [{
            "type": "error",
            "message": "Row 1 Student Number Identifier: Duplicated student \"92001\"",
            "data": null,
            "children": [{
                "type": "error",
                "message": "This is but a sub-report Z",
                "data": null,
                "children": []
            }]
        },{
            "type": "success",
            "message": "Row 2 Student Number Identifier OK",
            "data": null,
            "children": [{
                "type": "success",
                "message": "This is but a sub-report A",
                "data": null,
                "children": []
            },{
                "type": "info",
                "message": "This is but a sub-report B",
                "data": null,
                "children": []
            }]
        }]
    };

    // display a sample of the component
    if (showSample) {
        report({renderTo: $('body')}, sampleData).on('showDetails', function() {
            console.log('details displayed');
        });
    }

    QUnit.module('report');


    QUnit.test('module', 3, function(assert) {
        assert.equal(typeof report, 'function', "The report module exposes a function");
        assert.equal(typeof report(), 'object', "The report factory produces an object");
        assert.notStrictEqual(report(), report(), "The report factory provides a different object on each call");
    });


    var datalistApi = [
        { name : 'init', title : 'init' },
        { name : 'destroy', title : 'destroy' },
        { name : 'render', title : 'render' },
        { name : 'show', title : 'show' },
        { name : 'hide', title : 'hide' },
        { name : 'enable', title : 'enable' },
        { name : 'disable', title : 'disable' },
        { name : 'is', title : 'is' },
        { name : 'setState', title : 'setState' },
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getElement', title : 'getElement' },
        { name : 'getTemplate', title : 'getTemplate' },
        { name : 'setTemplate', title : 'setTemplate' }
    ];

    QUnit
        .cases(datalistApi)
        .test('instance API ', function(data, assert) {
            var instance = report();
            assert.equal(typeof instance[data.name], 'function', 'The report instance exposes a "' + data.title + '" function');
        });


    QUnit.test('init', function(assert) {
        var config = {
            nothing: undefined,
            dummy: null,
            keyName : 'key',
            labelName : 'name',
            labelText : 'A label',
            title: 'My Title',
            textEmpty: 'Nothing to list',
            textNumber: 'Number',
            textLoading: 'Please wait',
            selectable : true
        };
        var instance = report(config);

        assert.notEqual(instance.config, config, 'The report instance must duplicate the config set');
        assert.equal(instance.hasOwnProperty('nothing'), false, 'The report instance must not accept undefined config properties');
        assert.equal(instance.hasOwnProperty('dummy'), false, 'The report instance must not accept null config properties');
        assert.equal(instance.config.keyName, config.keyName, 'The report instance must catch the keyName config');
        assert.equal(instance.config.labelName, config.labelName, 'The report instance must catch the labelName config');
        assert.equal(instance.config.labelText, config.labelText, 'The report instance must catch the labelText config');
        assert.equal(instance.config.title, config.title, 'The report instance must catch the title config');
        assert.equal(instance.config.textNumber, config.textNumber, 'The report instance must catch the textNumber config');
        assert.equal(instance.config.textEmpty, config.textEmpty, 'The report instance must catch the textNumber config');
        assert.equal(instance.config.textLoading, config.textLoading, 'The report instance must catch the textNumber config');
        assert.equal(instance.config.selectable, config.selectable, 'The report instance must catch the selectable config');
        assert.equal(instance.is('rendered'), false, 'The report instance must not be rendered');

        instance.destroy();
    });


});
