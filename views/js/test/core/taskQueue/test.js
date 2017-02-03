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
    'core/taskQueue',
    'core/promise',
], function($, _, taskQueueApi, Promise){
    'use strict';

    QUnit.module('API');

    QUnit.test('factory', function(assert) {

        var taskQueue = taskQueueApi();

        assert.equal(typeof taskQueueApi, 'function', "The module exposes a function");
        assert.equal(typeof taskQueue, 'object', 'The factory creates an object');
        assert.notDeepEqual(taskQueue, taskQueueApi(), 'The factory creates new objects');
    });

    var pluginApi = [
        { name : 'on', title : 'on' },
        { name : 'off', title : 'off' },
        { name : 'trigger', title : 'trigger' },
        { name : 'getStatus', title : 'getStatus' },
        { name : 'pollStatus', title : 'pollStatus' },
        { name : 'pollStop', title : 'pollStop' },
        { name : 'remove', title : 'remove' }
    ];

    QUnit
        .cases(pluginApi)
        .test('component method ', function(data, assert) {
            QUnit.expect(1);
            var taskQueue = taskQueueApi();
            assert.equal(typeof taskQueue[data.name], 'function', 'The api exposes a "' + data.name + '" function');
        });


    QUnit.asyncTest('get status', function (assert){

        QUnit.expect(4);

        var statusUrl = '/tao/views/js/test/core/taskQueue/data-status-running.json';
        var taskQueue = taskQueueApi({url:{status: statusUrl}});
        var status = taskQueue.getStatus('task#65480abc');

        assert.ok(status instanceof Promise, 'getStatus returns a Promise');

        status.then(function(taskData){
            assert.equal(taskData.id, 'task#65480abc', 'task id ok');
            assert.equal(taskData.status, 'running', 'task status ok');
            assert.equal(taskData.label, 'Mass import DDD', 'task label ok');
            QUnit.start();
        });
    });

    QUnit.asyncTest('poll status (running)', function (assert){

        QUnit.expect(6);

        var statusUrl = '/tao/views/js/test/core/taskQueue/data-status-running.json';
        var taskQueue = taskQueueApi({url:{status: statusUrl}})
            .on('pollStart', function () {
                assert.ok(true, 'polling status started');
            })
            .on('pollStop', function () {
                assert.ok(true, 'polling status stopped');
                QUnit.start();
            })
            .on('running', function (taskData) {

                assert.ok(true, 'running status event');
                assert.equal(taskData.id, 'task#65480abc', 'task id ok');
                assert.equal(taskData.status, 'running', 'task status ok');
                assert.equal(taskData.label, 'Mass import DDD', 'task label ok');

                //stop polling after one poll
                taskQueue.pollStop();

            }).on('finished', function () {
                assert.ok(false, 'should not be triggered here');
            }).on('error', function () {
                assert.ok(false, 'should not be triggered here');
            }).pollStatus('task#65480abc');
    });

    QUnit.asyncTest('remove', function (assert){

        QUnit.expect(4);

        var statusUrl = '/tao/views/js/test/core/taskQueue/data-status-archived.json';
        var taskQueue = taskQueueApi({url:{remove: statusUrl}});
        var status = taskQueue.remove('task#65480abc');

        assert.ok(status instanceof Promise, 'getStatus returns a Promise');

        status.then(function(taskData){
            assert.equal(taskData.id, 'task#65480abc', 'task id ok');
            assert.equal(taskData.status, 'archived', 'task status ok');
            assert.equal(taskData.label, 'Mass import DDD', 'task label ok');
            QUnit.start();
        });
    });

    QUnit.module('Error handling');

    QUnit.asyncTest('get status (invalid server data)', function (assert){

        QUnit.expect(4);

        var statusUrl = '/tao/views/js/test/core/taskQueue/data-error.json';
        var error;
        var taskQueue = taskQueueApi({url:{status: statusUrl}})
            .on('error', function(err){
                assert.ok(err instanceof Error, 'error returned');
                error = err;
            });
        var status = taskQueue.getStatus('task#65480abc')

        assert.ok(status instanceof Promise, 'getStatus returns a Promise');

        status.then(function(){
            assert.ok(false, 'should not be triggered here');
        }).catch(function(err){
            assert.ok(err instanceof Error, 'error returned');
            assert.equal(err, error, 'same error returned via catch and event');
            QUnit.start();
        });
    });

    QUnit.test('get status (no url)', function (assert){

        QUnit.expect(1);

        assert.throws(function(){
            taskQueueApi({}).getStatus('task#65480abc');
        }, TypeError, 'config.url.status is not configured while getStatus() is being called');
    });

    QUnit.test('poll status (no url)', function (assert){

        QUnit.expect(1);

        assert.throws(function(){
            taskQueueApi({}).pollStatus('task#65480abc');
        }, TypeError, 'config.url.status is not configured while getStatus() is being called');
    });

    QUnit.asyncTest('remove (invalid server data)', function (assert){

        QUnit.expect(4);

        var statusUrl = '/tao/views/js/test/core/taskQueue/data-error.json';
        var error;
        var taskQueue = taskQueueApi({url:{remove: statusUrl}})
            .on('error', function(err){
                assert.ok(err instanceof Error, 'error returned');
                error = err;
            });
        var status = taskQueue.remove('task#65480abc')

        assert.ok(status instanceof Promise, 'remove() returns a Promise');

        status.then(function(){
            assert.ok(false, 'should not be triggered here');
        }).catch(function(err){
            assert.ok(err instanceof Error, 'error returned');
            assert.equal(err, error, 'same error returned via catch and event');
            QUnit.start();
        });
    });

    QUnit.test('remove (no url)', function (assert){

        QUnit.expect(1);

        assert.throws(function(){
            taskQueueApi({}).remove('task#65480abc')
        }, TypeError, 'config.url.remove is not configured while remove is being called');
    });

    QUnit.asyncTest('remove (wrong status)', function (assert){

        QUnit.expect(4);

        var serviceUrl = '/tao/views/js/test/core/taskQueue/data-status-finished.json';
        var error;
        var taskQueue = taskQueueApi({url:{remove: serviceUrl}})
            .on('error', function(err){
                assert.ok(err instanceof Error, 'error returned');
                error = err;
            })
        var status = taskQueue.remove('task#65480abc');

        assert.ok(status instanceof Promise, 'remove() returns a Promise');

        status.then(function(){
            assert.ok(false, 'should not be triggered here');
        }).catch(function(err){
            assert.ok(err instanceof Error, 'error returned');
            assert.equal(err, error, 'same error returned via catch and event');
            QUnit.start();
        });
    });

});
