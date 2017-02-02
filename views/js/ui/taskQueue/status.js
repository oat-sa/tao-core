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

/**
 * Create a task queue status component to poll task's status
 *
 * @example
 * taskQueueStatusFactory({
 *       serviceUrl:'http://my.server/taskQueue/Status',
 *       taskId:'task#123456xyz'
 * })
 * .on('finished')
 * .render('body')
 * .start();
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/dataProvider/request',
    'core/polling',
    'core/taskQueue',
    'ui/component',
    'ui/report',
    'tpl!ui/taskQueue/tpl/status',
    'tpl!ui/taskQueue/tpl/statusMessage'
], function ($, _, __, request, polling, taskQueue, component, report, statusTpl, messageTpl) {
    'use strict';

    var _status = {
        loading: __('loading status information'),
        created: __('not started'),
        running: __('running'),
        finished: __('finished')
    };

    var _defaults = {
        serviceUrl: '',
        taskId: '',
        taskStatus: _status.loading,
        showDetailsButton : true,
        actions : []
    };

    var statusComponent = {

        /**
         * Starts the task status polling
         * @returns {statusComponent}
         */
        start : function start(){
            if (this.taskQueueApi) {
                this.taskQueueApi.pollStatus(this.config.taskId);
            }
            return this;
        },

        /**
         * Stops the task status polling
         * @returns {statusComponent}
         */
        stop : function stop(){
            if (this.taskQueueApi) {
                this.taskQueueApi.pollStop();
            }
            return this;
        }
    }

    /**
     * Create a status checker for task queue
     *
     * @param {Object} config
     * @param {String} config.serviceUrl - the service be called in ajax to check the status of the task
     * @param {String} config.taskId - the id of the task
     * @param {Boolean} [config.showDetailsButton=true] - display the show/hide details toggle
     * @param {Array} [config.actions] - possibility to add more button controls on the report
     * @returns {*}
     */
    return function taskQueueStatusComponent(config) {

        var taskQueueStatus;

        config = _.defaults(config || {}, _defaults);

        if (_.isEmpty(config.serviceUrl)) {
            throw new TypeError('The task queue status needs to be configured with a service url');
        }

        /**
         * Create a report
         *
         * @param {String} reportType - the top report type
         * @param {String} message - the top report message
         * @param taskReport
         * @returns {Object} a ui/report component
         * @private
         * @see ui/report
         * @fires reportComponent#showDetails
         * @fires reportComponent#hideDetails
         * @fires reportComponent#action
         * @fires reportComponent#action-{custom action name}
         */
        var createReport = function createReport(reportType, message, taskReport){
            var reportData = {
                type: reportType,
                message: message,
            };

            if(_.isPlainObject(taskReport) && taskReport.type){
                reportData.children = [taskReport];
            }

            return report({
                replace : true,
                noBorder : true,
                showDetailsButton : config.showDetailsButton,
                actions : config.actions
            }, reportData)
                .on('action', function(actionId){
                    taskQueueStatus.trigger('action-' + actionId);
                    taskQueueStatus.trigger('action', actionId);
                }).on('showDetails', function(){
                    taskQueueStatus.trigger('showDetails');
                }).on('hideDetails', function(){
                    taskQueueStatus.trigger('hideDetails');
                })
                .render(taskQueueStatus.getElement());
        }

        /**
         * The task queue status component
         * @typedef taskQueueStatus
         * @see ui/component
         * @fires taskQueueStatus#running after every loop
         * @fires taskQueueStatus#finished when the task is complete
         * @fires taskQueueStatus#statechange on each task state change
         */
        taskQueueStatus = component(statusComponent)
            .setTemplate(statusTpl)
            .on('destroy', function () {
                if (this.taskQueueApi) {
                    this.taskQueueApi.pollStop();
                }
            })
            .on('render', function () {

                var self = this;

                self.report = createReport('info', __('Loading task status ...'));

                this.taskQueueApi = taskQueue({url:{status: config.serviceUrl}})
                    .on('running', function (taskData) {
                        if(self.status !== 'running'){
                            self.report = createReport('info', messageTpl({
                                name : taskData.label,
                                status : _status.running
                            }));
                            self.status = 'running';
                            self.trigger('statechange', self.status);
                        }
                        self.trigger('running', taskData);
                    }).on('finished', function (taskData) {
                        if(self.status !== 'finished'){
                            self.report = createReport(taskData.report.type || 'info', messageTpl({
                                    name : taskData.label,
                                    status : _status.finished
                                }), taskData.report || {})
                                .showDetails();
                            self.status = 'finished';
                            self.trigger('finished', taskData);
                            self.trigger('statechange', self.status);
                        }
                    }).on('error', function (err) {
                        self.trigger('error', err);
                    })
            })
            .init(config);

        return taskQueueStatus;
    }
});