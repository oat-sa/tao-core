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
    'ui/component',
    'core/taskQueue/taskQueueModel',
    'ui/taskQueueButton/taskable'
], function($, _, componentFactory, taskQueueModelFactory, makeTaskable) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        assert.expect(1);
        assert.ok(typeof makeTaskable === 'function', 'The module expose a function');
    });

    QUnit
        .cases.init([
            {title: 'createTask', method: 'createTask'},
            {title: 'displayReport', method: 'displayReport'},
            {title: 'setTaskConfig', method: 'setTaskConfig'}
        ])
        .test('component API', function(data, assert) {
            var component = makeTaskable(componentFactory());

            assert.expect(1);
            assert.equal(typeof component[data.method], 'function', 'The component has the method ' + data.method);
        });

    QUnit.module('Creation');

    QUnit.test('enqueued', function(assert) {
        var ready = assert.async();
        var taskQueue = taskQueueModelFactory({
            url: {
                all: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json'
            }
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue: taskQueue,
            taskCreationUrl: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData: {some: 'data'}
        };

        makeTaskable(componentFactory()).on('init', function() {
            assert.deepEqual(this.config, taskableConfig, 'config correctly set');
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        }).on('finished', function() {
            assert.ok(false, 'should not be finished');
        }).on('enqueued', function() {
            assert.ok(true, 'task enqueued');
            ready();
        }).init(taskableConfig).createTask();
    });

    QUnit.test('finished', function(assert) {
        var ready = assert.async();
        var taskQueue = taskQueueModelFactory({
            url: {
                all: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json',
                archive: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/archive-success.json'
            }
        }).on('pollSingle', function() {

            //After the first poll, simulate a prompt completion of the task to trigger the finished even on time
            this.setEndpoints({
                get: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne-completed.json'
            });
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue: taskQueue,
            taskCreationUrl: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData: {some: 'data'}
        };

        makeTaskable(componentFactory()).on('init', function() {
            assert.deepEqual(this.config, taskableConfig, 'config correctly set');
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        }).on('finished', function() {
            assert.ok(true, 'finished !');
            ready();
        }).on('enqueued', function() {
            assert.ok(false, 'should not be enqueued');
        }).init(taskableConfig).createTask();
    });

    QUnit.test('setTaskConfig', function(assert) {
        var ready = assert.async();
        var taskQueue = taskQueueModelFactory({
            url: {
                all: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json',
                archive: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/archive-success.json'
            }
        }).on('pollSingle', function() {

            //After the first poll, simulate a prompt completion of the task to trigger the finished even on time
            this.setEndpoints({
                get: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne-completed.json'
            });
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue: taskQueue,
            taskCreationUrl: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData: {some: 'data'}
        };

        makeTaskable(componentFactory()).on('init', function() {
            assert.deepEqual(this.config, {}, 'config is intiially empty');
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        }).on('finished', function() {
            assert.ok(true, 'finished !');
            ready();
        }).on('enqueued', function() {
            assert.ok(false, 'should not be enqueued');
        }).init().setTaskConfig(taskableConfig).createTask();
    });

    QUnit.test('taskCreationData as function', function(assert) {
        var ready = assert.async();
        var taskQueue = taskQueueModelFactory({
            url: {
                all: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json',
                archive: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/archive-success.json'
            }
        }).on('pollSingle', function() {

            //After the first poll, simulate a prompt completion of the task to trigger the finished even on time
            this.setEndpoints({
                get: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne-completed.json'
            });
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue: taskQueue,
            taskCreationUrl: '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData: function() {
                return {some: 'data'};
            }
        };

        makeTaskable(componentFactory()).on('init', function() {
            assert.deepEqual(this.config, taskableConfig, 'config is intiially empty');
        }).on('error', function() {
            assert.ok(false, 'should not have any error');
        }).on('finished', function() {
            assert.ok(true, 'finished !');
            ready();
        }).on('enqueued', function() {
            assert.ok(false, 'should not be enqueued');
        }).init(taskableConfig).createTask();
    });

    QUnit.module('Error');

    QUnit.test('missing request url', function(assert) {
        var ready = assert.async();
        makeTaskable(componentFactory()).on('error', function(err) {
            assert.equal('the request url is required to create a task', err, 'error correctly catched');
            ready();
        }).init().createTask();
    });

    QUnit.test('task queue model', function(assert) {
        var ready = assert.async();
        makeTaskable(componentFactory()).on('error', function(err) {
            assert.equal('the taskQueue model is required to create a task', err, 'error correctly catched');
            ready();
        }).init({
            taskCreationUrl: 'someUrl'
        }).createTask();
    });

});
