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
    'i18n',
    'core/dataProvider/request',
    'core/polling',
    'core/taskQueue',
    'ui/component',
    'tpl!ui/taskQueue/tpl/status'
], function ($, _, __, request, polling, taskQueue, component, statusTpl) {
    'use strict';

    var _status = {
        created: __('not started'),
        running: __('running'),
        finished: __('finished')
    };

    var _defaults = {
        serviceUrl: '',
        taskId: '',
        taskType: '',
        taskStatus: _status.created,
        taskName: ''
    };

    /**
     * Create a status checker for task queue
     *
     * @param {Object} config
     * @param {String} config.serviceUrl - the service be called in ajax to check the status of the task
     * @param {String} config.taskId - the id of the task
     * @param {String} config.taskType - the type of the task
     * @param {String} config.taskName - the name of the task
     * @param {String} config.taskStatus - initial status of the task
     * @returns {*}
     */
    var taskQueueStatusComponent = function taskQueueStatusComponent(config) {

        var initConfig = _.defaults(config || {}, _defaults);
        var taskQueueManager;

        return component({}, _defaults)
            .setTemplate(statusTpl)
            .on('destroy', function () {
                if (taskQueueManager) {
                    taskQueueManager.pollStop();
                }
            })
            .on('render', function () {

                var self = this;
                var $status = this.$component.find('.task-status');

                taskQueueManager = taskQueue({url:{status: initConfig.serviceUrl}})
                    .on('running', function (taskData) {
                        $status.html(_status.running);
                        self.trigger('running', taskData);
                    }).on('finished', function (taskData) {
                        $status.html(_status.finished);
                        //include report in here

                        self.trigger('finished', taskData);
                    }).on('error', function (err) {
                        self.trigger('error', err);
                    }).pollStatus(initConfig.taskId);
            })
            .init(initConfig);
    }

    return taskQueueStatusComponent;
});