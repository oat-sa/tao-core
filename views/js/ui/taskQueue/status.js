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
        taskType: '',
        taskStatus: _status.loading,
        taskName: ''
    };

    var statusComponent = {
        start:function start(){
            if (this.taskQueueManager) {
                this.taskQueueManager.pollStatus(this.config.taskId);
            }
            return this;
        },
        stop: function stop(){
            if (this.taskQueueManager) {
                this.taskQueueManager.pollStop();
            }
            return this;
        },
        _createReport : function _createReport(reportType, message, taskReport){
            var self = this;
            var reportData = {
                type: reportType,
                message: message,
            };
            if(_.isPlainObject(taskReport) && taskReport.type){
                reportData.children = [taskReport];
            }
            return report({
                replace : true,
                noBorder : true
            }, reportData)
                .on('showDetails', function(){
                    self.trigger('showDetails');
                }).on('hideDetails', function(){
                    self.trigger('hideDetails');
                })
                .render(self.$component);
        }
    }

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

        var config = _.defaults(config || {}, _defaults);

        if (_.isEmpty(config.serviceUrl)) {
            throw new TypeError('The task queue status needs to be configured with a service url');
        }

        return component(statusComponent, _defaults)
            .setTemplate(statusTpl)
            .on('destroy', function () {
                if (this.taskQueueManager) {
                    this.taskQueueManager.pollStop();
                }
            })
            .on('render', function () {

                var self = this;

                self.report = self._createReport('info', __('Loading task status ...'));

                this.taskQueueManager = taskQueue({url:{status: config.serviceUrl}})
                    .on('running', function (taskData) {
                        if(self.status !== 'running'){
                            self.report = self._createReport('info', messageTpl({
                                name : taskData.label,
                                status : _status.running
                            }));
                        }
                        self.status = 'running';
                        self.trigger('running', taskData);
                    }).on('finished', function (taskData) {
                        if(self.status !== 'finished'){
                            self.report = self._createReport(taskData.report.type || 'info', messageTpl({
                                name : taskData.label,
                                status : _status.running
                            }), taskData.report || {});
                        }
                        self.status = 'finished';
                        self.trigger('finished', taskData);
                    }).on('error', function (err) {
                        self.trigger('error', err);
                    })
            })
            .init(config);
    }

    return taskQueueStatusComponent;
});