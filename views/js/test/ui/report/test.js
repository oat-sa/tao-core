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
    'ui/report'
], function ($, _, report) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof report, 'function', "The report module exposes a function");
        assert.equal(typeof report(), 'object', "The report factory produces an object");
        assert.notStrictEqual(report(), report(), "The report factory provides a different object on each call");
    });

    var reportApi = [
        {name: 'init', title: 'init'},
        {name: 'destroy', title: 'destroy'},
        {name: 'render', title: 'render'},
        {name: 'show', title: 'show'},
        {name: 'hide', title: 'hide'},
        {name: 'enable', title: 'enable'},
        {name: 'disable', title: 'disable'},
        {name: 'is', title: 'is'},
        {name: 'setState', title: 'setState'},
        {name: 'getContainer', title: 'getContainer'},
        {name: 'getElement', title: 'getElement'},
        {name: 'getTemplate', title: 'getTemplate'},
        {name: 'setTemplate', title: 'setTemplate'}
    ];

    QUnit
        .cases(reportApi)
        .test('instance API ', function (data, assert) {
            QUnit.expect(1);
            var instance = report();
            assert.equal(typeof instance[data.name], 'function', 'The report instance exposes a "' + data.title + '" function');
        });


    QUnit.test('init', function (assert) {

        QUnit.expect(12);

        var config = {
            nothing: undefined,
            dummy: null,
            keyName: 'key',
            labelName: 'name',
            labelText: 'A label',
            title: 'My Title',
            textEmpty: 'Nothing to list',
            textNumber: 'Number',
            textLoading: 'Please wait',
            selectable: true
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

    QUnit.module('Rendering');

    QUnit.asyncTest('simple report', function (assert) {

        QUnit.expect(7);

        var $fixtureContainer = $('#qunit-fixture');
        var sampleData = {
            "type": "warning",
            "message": "<em>Data not imported. All records are <strong>invalid.</strong></em>",
            "data": null
        };

        report({}, sampleData)
            .on('render', function () {
                var $component = $('.component-report ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
                assert.equal($component.find('.fold').length, 0, 'the report component has no show details button');
                assert.ok($component.find('.content > .leaf').hasClass('feedback-warning'), 'the report component the type warning');
                assert.equal($component.find('.hierarchical').length, 0, 'the report has no hierarchical report');
                assert.equal($component.find('.leaf').length, 1, 'the report has only one leaf report');
                assert.equal($component.find('.actions .action').length, 0, 'the report has no action button');
                QUnit.start();
            }).render($fixtureContainer);
    });

    QUnit.asyncTest('simple report with actions', function (assert) {

        QUnit.expect(9);

        var $fixtureContainer = $('#qunit-fixture');
        var sampleData = {
            "type": "warning",
            "message": "<em>Data not imported. All records are <strong>invalid.</strong></em>",
            "data": null
        };

        report({
            actions: [{
                id: 'rollback',
                icon: 'reset',
                title: 'Rollback to previous state',
                label: 'Rollback'
            }, {
                id: 'continue',
                icon: 'right',
                title: 'Continue to next step',
                label: 'Continue'
            }]
        }, sampleData)
            .on('render', function () {
                var $component = $('.component-report ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
                assert.equal($component.find('.fold').length, 0, 'the report component has no show details button');
                assert.ok($component.find('.content > .leaf').hasClass('feedback-warning'), 'the report component the type warning');
                assert.equal($component.find('.hierarchical').length, 0, 'the report has no hierarchical report');
                assert.equal($component.find('.leaf').length, 1, 'the report has only one leaf report');

                //check action buttons
                assert.equal($component.find('.actions .action').length, 2, 'the report has 2 actions button');
                assert.equal($component.find('.actions .action[data-trigger="rollback"]').length, 1, 'the report has one rollback button');
                assert.equal($component.find('.actions .action[data-trigger="continue"]').length, 1, 'the report has one continue button');

                QUnit.start();
            }).render($fixtureContainer);

    });

    QUnit.asyncTest('hierarchical report', function (assert) {

        QUnit.expect(6);

        var $fixtureContainer = $('#qunit-fixture');
        var sampleData = {
            "type": "warning",
            "message": "<em>Data not imported. All records are <strong>invalid.</strong></em>",
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
            }, {
                "type": "success",
                "message": "Row 2 Student Number Identifier OK",
                "data": null,
                "children": [{
                    "type": "success",
                    "message": "This is but a sub-report A",
                    "data": null,
                    "children": []
                }, {
                    "type": "info",
                    "message": "This is but a sub-report B",
                    "data": null,
                    "children": []
                }]
            }]
        };

        report({}, sampleData)
            .on('render', function () {
                var $component = $('.component-report ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
                assert.equal($component.find('.fold').length, 1, 'the report component has the show details button');
                assert.ok($component.find('.content > .hierarchical').hasClass('feedback-warning'), 1, 'the report component has the type warning');
                assert.equal($component.find('.hierarchical').length, 3, 'the report has 3 hierarchical reports');
                assert.equal($component.find('.leaf').length, 3, 'the report has 3 leaf reports');

                QUnit.start();
            }).render($fixtureContainer);

    });

    QUnit.module('Behaviour');

    QUnit.asyncTest('trigger actions', function (assert) {

        QUnit.expect(11);

        var $fixtureContainer = $('#qunit-fixture');
        var sampleData = {
            "type": "warning",
            "message": "<em>Data not imported. All records are <strong>invalid.</strong></em>",
            "data": null
        };

        report({
            actions: [{
                id: 'rollback',
                icon: 'reset',
                title: 'Rollback to previous state',
                label: 'Rollback'
            }, {
                id: 'continue',
                icon: 'right',
                title: 'Continue to next step',
                label: 'Continue'
            }]
        }, sampleData)
            .on('render', function () {
                var $component = $('.component-report ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
                assert.equal($component.find('.fold').length, 0, 'the report component has no show details button');
                assert.ok($component.find('.content > .leaf').hasClass('feedback-warning'), 'the report component the type warning');
                assert.equal($component.find('.hierarchical').length, 0, 'the report has no hierarchical report');
                assert.equal($component.find('.leaf').length, 1, 'the report has only one leaf report');

                //check action buttons
                assert.equal($component.find('.actions .action').length, 2, 'the report has 2 actions button');
                assert.equal($component.find('.actions .action[data-trigger="rollback"]').length, 1, 'the report has one rollback button');
                assert.equal($component.find('.actions .action[data-trigger="continue"]').length, 1, 'the report has one continue button');

                $component.find('.actions .action[data-trigger="continue"]').click();

            }).on('action-continue', function () {
                var $component = $('.component-report ', $fixtureContainer);
                $component.find('.actions .action[data-trigger="rollback"]').click();
                assert.ok(true, 'continue event triggered');
            }).on('action-rollback', function () {
                assert.ok(true, 'rollback event triggered');
                QUnit.start();
            }).render($fixtureContainer);
    });


    QUnit.asyncTest('toggle details', function (assert) {

        QUnit.expect(14);

        var $fixtureContainer = $('#qunit-fixture');
        var sampleData = {
            "type": "warning",
            "message": "<em>Data not imported. All records are <strong>invalid.</strong></em>",
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
            }, {
                "type": "success",
                "message": "Row 2 Student Number Identifier OK",
                "data": null,
                "children": [{
                    "type": "success",
                    "message": "This is but a sub-report A",
                    "data": null,
                    "children": []
                }, {
                    "type": "info",
                    "message": "This is but a sub-report B",
                    "data": null,
                    "children": []
                }]
            }]
        };

        report({}, sampleData)
            .on('render', function () {
                var $component = $('.component-report ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
                assert.equal($component.find('.fold').length, 1, 'the report component has the show details button');
                assert.ok($component.find('.content > .hierarchical').hasClass('feedback-warning'), 1, 'the report component has the type warning');
                assert.equal($component.find('.hierarchical').length, 3, 'the report has 3 hierarchical reports');
                assert.equal($component.find('.leaf').length, 3, 'the report has 3 leaf reports');

                //check hierarchical report visibility
                assert.equal($component.find('.hierarchical:visible').length, 1, 'one hierarchical report is visible');
                assert.equal($component.find('.leaf:visible').length, 0, 'no leaf report is visible');

                //show details
                $component.find('.fold input').click();

            }).on('showDetails', function () {

                var $component = $('.component-report ', $fixtureContainer);

                assert.ok(true, 'showDetails event triggered');

                //check hierarchical report visibility
                assert.equal($component.find('.hierarchical:visible').length, 3, 'all hierarchical reportr are visible');
                assert.equal($component.find('.leaf:visible').length, 3, 'all leaf report are visible');

                $component.find('.fold input').click();

            }).on('hideDetails', function () {

                var $component = $('.component-report ', $fixtureContainer);
                assert.ok(true, 'hideDetails event triggered');

                //check hierarchical report visibility
                assert.equal($component.find('.hierarchical:visible').length, 1, 'one hierarchical report is visible');
                assert.equal($component.find('.leaf:visible').length, 0, 'no leaf report is visible');

                QUnit.start();
            }).render($fixtureContainer);

    });

});
