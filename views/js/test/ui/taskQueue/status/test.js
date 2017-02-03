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
    'ui/taskQueue/status'
], function($, _, taskQueueStatusFactory){
    'use strict';

    QUnit.module('API');

    QUnit.test('factory', function(assert) {
        QUnit.expect(5);

        var serviceUrl = 'dummy/service/url';
        var taskStatus;
        var taskStatusBis;

        assert.equal(typeof taskQueueStatusFactory, 'function', "The module exposes a function");

        assert.throws(function(){
            taskQueueStatusFactory();
        }, TypeError, 'The component needs to be configured');

        assert.throws(function(){
            taskQueueStatusFactory({context:''});
        }, TypeError, 'The component needs a not empty context');

        taskStatus = taskQueueStatusFactory({serviceUrl:serviceUrl});

        assert.equal(typeof taskStatus, 'object', 'The factory creates an object');

        taskStatusBis = taskQueueStatusFactory({serviceUrl:serviceUrl});
        assert.notDeepEqual(taskStatus, taskStatusBis, 'The factory creates new objects');

    });

    var pluginApi = [
        { name : 'init', title : 'init' },
        { name : 'render', title : 'render' },
        { name : 'destroy', title : 'destroy' },
        { name : 'on', title : 'on' },
        { name : 'off', title : 'off' },
        { name : 'trigger', title : 'trigger' },
        { name : 'start', title : 'start' },
        { name : 'stop', title : 'stop' },
    ];

    QUnit
        .cases(pluginApi)
        .test('component method ', function(data, assert) {
            QUnit.expect(1);

            var serviceUrl = 'dummy/service/url';
            var status = taskQueueStatusFactory({serviceUrl:serviceUrl});

            assert.equal(typeof status[data.name], 'function', 'The component exposes a "' + data.name + '" function');
        });

    QUnit.module('Rendering');

    QUnit.asyncTest('status running', function (assert){
        QUnit.expect(6);

        var serviceUrl = '/tao/views/js/test/ui/taskQueue/status/data-running.json';
        var $fixtureContainer = $('#qunit-fixture');
        var status = taskQueueStatusFactory({
            taskId:'task#123456xyz',
            serviceUrl:serviceUrl
        });

        status
            .on('render', function () {
                var $component = $('.task-queue-status ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
            })
            .on('running', function(){
                status.stop();

                assert.equal(this.$component.find('.component-report').length, 1, 'a report has been attached to the status');
                assert.equal(status.$component.find('.component-report .icon-info').length, 1, 'the status report is of an info type');
                assert.equal(status.$component.find('.component-report .hierarchical').length, 0, 'the status report has no children');
                assert.equal(status.$component.find('.component-report .fold').length, 0, 'the status report has no show details button');

                QUnit.start();
            })
            .render($fixtureContainer)
            .start();
    });

    QUnit.asyncTest('status finished', function (assert){
        QUnit.expect(7);

        var serviceUrl = '/tao/views/js/test/ui/taskQueue/status/data-finished.json';
        var $fixtureContainer = $('#qunit-fixture');
        var status = taskQueueStatusFactory({
            taskId:'task#123456xyz',
            serviceUrl:serviceUrl
        });

        status
            .on('render', function () {
                var $component = $('.task-queue-status ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
            })
            .on('finished', function(){
                status.stop();

                assert.equal(this.$component.find('.component-report').length, 1, 'a report has been attached to the status');
                assert.equal(status.$component.find('.component-report .fold').length, 1, 'the status report has the show details button');
                assert.ok(status.$component.find('.component-report .content > .hierarchical').hasClass('feedback-warning'), 1, 'the status report inherit the type of the report');
                assert.equal(status.$component.find('.component-report .hierarchical').length, 4, 'the report has 4 hierarchical reports');
                assert.equal(status.$component.find('.component-report .leaf').length, 3, 'the report has 3 leaf reports');

                QUnit.start();
            })
            .render($fixtureContainer)
            .start();
    });

    QUnit.module('Behaviour');

    QUnit.asyncTest('toggle details', function (assert){
        QUnit.expect(16);

        var serviceUrl = '/tao/views/js/test/ui/taskQueue/status/data-finished.json';
        var $fixtureContainer = $('#qunit-fixture');
        var status = taskQueueStatusFactory({
            taskId:'task#123456xyz',
            serviceUrl:serviceUrl
        });
        var $checkbox;

        status
            .on('render', function () {
                var $component = $('.task-queue-status ', $fixtureContainer);
                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');
            })
            .on('finished', function(){


                assert.equal(this.$component.find('.component-report').length, 1, 'a report has been attached to the status');
                assert.equal(status.$component.find('.component-report .fold').length, 1, 'the status report has the show details button');
                assert.ok(status.$component.find('.component-report .content > .hierarchical').hasClass('feedback-warning'), 'the status report inherit the type of the report');
                assert.equal(status.$component.find('.component-report .hierarchical').length, 4, 'the report has 4 hierarchical reports');
                assert.equal(status.$component.find('.component-report .leaf').length, 3, 'the report has 3 leaf reports');
                assert.equal(status.$component.find('.component-report .hierarchical:visible').length, 4, 'all hierarchical reports are visible');
                assert.equal(status.$component.find('.component-report .leaf:visible').length, 3, 'all leaf reports are visible');

                $checkbox = status.$component.find('.component-report .fold :checkbox');
                assert.equal($checkbox.length, 1, 'checkbox found');
                $checkbox.click();//show details

            }).on('hideDetails', function(){

                assert.equal(status.$component.find('.component-report .hierarchical:visible').length, 1, '1 hierarchical report is visible');
                assert.equal(status.$component.find('.component-report .leaf:visible').length, 0, 'no leaf report is visible');

                $checkbox.click()//hide details

                QUnit.start();
            }).on('showDetails', function(){

                assert.equal(status.$component.find('.component-report .hierarchical:visible').length, 4, 'all hierarchical reports are visible');
                assert.equal(status.$component.find('.component-report .leaf:visible').length, 3, 'all leaf reports are visible');

            })
            .render($fixtureContainer)
            .start();
    });

});
