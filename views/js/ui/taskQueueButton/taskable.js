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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

/**
 * Add the task creation capability to a component
 *
 * @example
 * makeTaskable(component)
 *    .setTaskConfig({
 *        taskQueue : taskQueue,
 *        taskCreationUrl : 'the/url/to/task/creation/service',
 *        taskCreationData : function(){
 *            return {some: 'data'};
 *        }
 *    }).createTask();
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/promise',
    'ui/report',
    'ui/feedback',
    'layout/loading-bar',
    'tpl!ui/taskQueueButton/tpl/report',
    'css!ui/taskQueueButton/css/taskable'
], function ($, _, __, Promise, reportFactory, feedback, loadingBar, reportTpl) {
    'use strict';

    var defaultConfig = {
    };

    var taskableComponent = {

        /**
         * Set configuration for task creation
         * @param config
         * @returns {taskableComponent}
         */
        setTaskConfig : function setTaskConfig(config){
            _.assign(this.config, config);
            return this;
        },

        /**
         * Create a task
         * @param requestUrl
         * @param requestData
         */
        createTask : function createTask(){
            var self = this;
            var taskQueue,
                requestUrl,
                requestData = {};

            //prepare the request parameter if applicable
            if(_.isFunction(this.config.taskCreationData)){
                requestData = this.config.taskCreationData.call(this);
            }else if(_.isPlainObject(this.config.taskCreationData)){
                requestData = this.config.taskCreationData;
            }

            if(!this.config.taskCreationUrl){
                return this.trigger('error', 'the request url is required to create a task');
            }
            requestUrl = this.config.taskCreationUrl;

            if(!this.config.taskQueue){
                return this.trigger('error', 'the taskQueue model is required to create a task');
            }
            taskQueue = this.config.taskQueue;

            loadingBar.start();
            taskQueue.pollAllStop();
            taskQueue.create(requestUrl, requestData).then(function (result) {
                var infoBox,
                    message,
                    task = result.task;

                if (result.finished) {
                    if(task.hasFile){
                        //download if its is a export-typed task
                        taskQueue.download(task.id).then(function(){
                            //immediately archive the finished task as there is no need to display this task in the queue list
                            return taskQueue.archive(task.id);
                        }).then(function () {
                            self.trigger('finished', result);
                            taskQueue.pollAll();
                        }).catch(function(err){
                            self.trigger('error', err);
                            taskQueue.pollAll();
                        });
                    }else{
                        //immediately archive the finished task as there is no need to display this task in the queue list
                        taskQueue.archive(task.id).then(function(){
                            self.trigger('finished', result);
                            taskQueue.pollAll();
                        }).catch(function(err){
                            self.trigger('error', err);
                            taskQueue.pollAll();
                        });
                    }
                } else {
                    //enqueuing process:
                    message = __('<strong> %s </strong> has been moved to the background.', task.taskLabel);
                    infoBox = feedback(null, {
                        encodeHtml : false,
                        timeout : {info: 8000}
                    }).info(message);

                    taskQueue.trigger('taskcreated', {task : task, sourceDom : infoBox.getElement()});
                    self.trigger('enqueued', result);
                }
                loadingBar.stop();
            }).catch(function (err) {
                //in case of error display it and continue task queue activity
                taskQueue.pollAll();
                loadingBar.stop();
                self.trigger('error', err);
            });
        },

        /**
         * prepare the given container to display the final report
         * @param {Object} report - the standard report object
         * @param {String} title - the report title
         * @param {String} result - raw result data from the task creation action
         */
        displayReport : function displayReport(report, title, result) {
            var self = this,
                $reportContainer;

            if(this.config.taskReportContainer instanceof $){
                $reportContainer = $(reportTpl({
                    title: title
                }));

                this.config.taskReportContainer.html($reportContainer);

                return reportFactory({
                        actions: [{
                            id: 'continue',
                            icon: 'right',
                            title: 'continue',
                            label: __('Continue')
                        }]
                    }, report)
                    .on('action-continue', function(){
                        self.trigger('continue', result);
                    }).render($reportContainer.find('.report'));
            }
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config - task queue creation specific config
     * @param {Object} config.taskQueue - the task queue model to be used
     * @param {String} config.taskCreationUrl - endpoint to create a task
     * @param {Object|Function} [config.taskCreationData] - the parameters that will be send to the task creation request
     * @param {JQuery} [config.taskReportContainer] - the container where the inline report can be printed to
     * @return {taskableComponent}
     */
    return function makeTaskable(component, config) {
        _.assign(component, taskableComponent);

        /**
         * @typedef {ui/component} taskableComponent
         */
        return component
            .off('.taskable')
            .on('init.taskable', function() {
                _.defaults(this.config, config || {}, defaultConfig);
            });
    };
});
