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

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.ok(typeof makeTaskable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'createTask', method: 'createTask' },
            { title: 'displayReport', method: 'displayReport' },
            { title: 'setTaskConfig', method: 'setTaskConfig' }
        ])
        .test('component API', function(data, assert) {
            var component = makeTaskable(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.method], 'function', 'The component has the method ' + data.method);
        });


    QUnit.module('Creation');

    QUnit.asyncTest('enqueued', function(assert) {
        var taskQueue = taskQueueModelFactory({
            url : {
                all : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json',
            }
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue : taskQueue,
            taskCreationUrl : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData : {some: 'data'}
        };

        makeTaskable(componentFactory()).on('init', function(){
            assert.deepEqual(this.config, taskableConfig, 'config correctly set');
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        }).on('finished', function(){
            assert.ok(false, 'should not be finished');
        }).on('enqueued', function(){
            assert.ok(true, 'task enqueued');
            QUnit.start();
        }).init(taskableConfig).createTask();
    });

    QUnit.asyncTest('finished', function(assert) {
        var taskQueue = taskQueueModelFactory({
            url : {
                all : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json',
                archive : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/archive-success.json'
            }
        }).on('pollSingle', function(){
            //after the first poll, simulate a prompt completion of the task to trigger the finished even on time
            this.setEndpoints({
                get : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne-completed.json'
            });
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue : taskQueue,
            taskCreationUrl : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData : {some: 'data'}
        };

        makeTaskable(componentFactory()).on('init', function(){
            assert.deepEqual(this.config, taskableConfig, 'config correctly set');
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        }).on('finished', function(){
            assert.ok(true, 'finished !');
            QUnit.start();
        }).on('enqueued', function(){
            assert.ok(false, 'should not be enqueued');
        }).init(taskableConfig).createTask();
    });

    QUnit.asyncTest('setTaskConfig', function(assert) {
        var taskQueue = taskQueueModelFactory({
            url : {
                all : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json',
                archive : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/archive-success.json'
            }
        }).on('pollSingle', function(){
            //after the first poll, simulate a prompt completion of the task to trigger the finished even on time
            this.setEndpoints({
                get : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne-completed.json'
            });
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue : taskQueue,
            taskCreationUrl : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData : {some: 'data'}
        };

        makeTaskable(componentFactory()).on('init', function(){
            assert.deepEqual(this.config, {}, 'config is intiially empty');
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        }).on('finished', function(){
            assert.ok(true, 'finished !');
            QUnit.start();
        }).on('enqueued', function(){
            assert.ok(false, 'should not be enqueued');
        }).init().setTaskConfig(taskableConfig).createTask();
    });

    QUnit.asyncTest('taskCreationData as function', function(assert) {
        var taskQueue = taskQueueModelFactory({
            url : {
                all : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getAll.json',
                get : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne.json',
                archive : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/archive-success.json'
            }
        }).on('pollSingle', function(){
            //after the first poll, simulate a prompt completion of the task to trigger the finished even on time
            this.setEndpoints({
                get : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/getOne-completed.json'
            });
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        });
        var taskableConfig = {
            taskQueue : taskQueue,
            taskCreationUrl : '/tao/views/js/test/ui/taskQueueButton/taskable/samples/newTaskCreationResult.json',
            taskCreationData : function(){
                return {some: 'data'};
            }
        };

        makeTaskable(componentFactory()).on('init', function(){
            assert.deepEqual(this.config, taskableConfig, 'config is intiially empty');
        }).on('error', function(){
            assert.ok(false, 'should not have any error');
        }).on('finished', function(){
            assert.ok(true, 'finished !');
            QUnit.start();
        }).on('enqueued', function(){
            assert.ok(false, 'should not be enqueued');
        }).init(taskableConfig).createTask();
    });

    QUnit.module('Error');

    QUnit.asyncTest('missing request url', function(assert) {
        makeTaskable(componentFactory()).on('error', function(err){
            assert.equal('the request url is required to create a task', err, 'error correctly catched');
            QUnit.start();
        }).init().createTask();
    });

    QUnit.asyncTest('task queue model', function(assert) {
        makeTaskable(componentFactory()).on('error', function(err){
            assert.equal('the taskQueue model is required to create a task', err, 'error correctly catched');
            QUnit.start();
        }).init({
            taskCreationUrl : 'someUrl'
        }).createTask();
    });

});