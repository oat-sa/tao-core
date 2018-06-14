/*
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
 *
 */

/**
 * Create a task queue status component to poll task's status
 *
 * @example
 * taskQueueTableFactory({
 *       context:'studentimport',
 *       dataUrl : 'http://my.server/taskQueue/list',
 *       statusUrl : 'http://my.server/taskQueue/status',
 *       removeUrl : 'http://my.server/taskQueue/remove'
 *   }).init().render('body')
 *
 * @deprecated may be removed along the old task queue
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'moment',
    'core/taskQueue',
    'ui/component',
    'ui/taskQueue/status',
    'tpl!ui/taskQueue/tpl/report',
    'ui/datatable',
    'ui/modal',
    'jquery.fileDownload'
], function ($, _, __, moment, taskQueueApi, component, taskQueueStatusFactory, reportTpl) {
    'use strict';

    var _defaults = {
        context: '',
        urls: {
            listing: '',
            remove: '',
            download: ''
        },
        status : {
            loading: __('Loading status'),
            created: __('Not started'),
            running: __('In progress'),
            finished: __('Completed'),
            finishedSuccess: __('Completed'),
            finishedError: __('Completed - Error')
        },
        statusFilter : [
            "loading",
            "created",
            "running",
            "finished",
            "finishedSuccess",
            "finishedError"
        ]
    };

    /**
     * Format the input timestamp into a user friendly format
     *
     * @param {String} date
     * @returns {String}
     */
    var formatDate = function formatDate(date) {
        return moment.unix(date).fromNow();
    };

    /**
     * Check if the object is a report
     *
     * @param {Object} report
     * @returns {boolean}
     */
    var isReport = function isReport(report){
        return (_.isPlainObject(report) && report.type && report.message);
    }

    /**
     * Check if the report is of a error type
     * @param {Object} report
     * @returns {boolean}
     */
    var isTaskErrorReport = function isTaskErrorReport(report){
        if(isReport(report) && _.isArray(report.children) && isReport(report.children[0])){
            return (report.children[0].type === 'error');
        }
    }
    /**
     * Creates the taskQueueTable component
     *
     * @param {String} testCenterId - the test center URI
     * @returns {taskQueueTable} the component
     * @throws {TypeError} when the task queue context (type) is absent in the config
     */
    return function taskQueueTableFactory(config) {

        var tasks,
            errorRows;

        config = _.defaults(config, _defaults);

        if (_.isEmpty(config.context)) {
            throw new TypeError('The task queue provider needs to be initalized with a context');
        }

        /**
         * The task queue table component
         * @typedef taskQueueTable
         * @see ui/component
         * @fires taskQueueTable#loading when the table is loading
         * @fires taskQueueTable#loaded when the data is loaded
         * @fires taskQueueTable#refresh when refreshing table content
         */
        return component({
            /**
             * Display a report for a task
             * @param taskId
             * @returns {taskQueueTable}
             */
            showReport : function showReport(taskId) {
                var status, data;
                var $report = this.$component.find('.report-container');
                var $dataTable = this.$component.find('.datatable-wrapper');

                if(!$report.length){
                    $report = $(reportTpl());
                    this.$component.append($report);
                }

                //toggle display fo queue table
                $dataTable.hide();

                var task = _.find(tasks, {id : taskId});
                if(task && task.status === 'finished' && task.report){
                    data = task;
                }

                status = taskQueueStatusFactory({
                    replace : true,
                    taskId: taskId,
                    serviceUrl: this.config.statusUrl,
                    showDetailsButton : false,
                    actions : [{
                        id: 'back',
                        icon: 'backward',
                        title: __('Back to listing'),
                        label: __('Back')
                    }],
                    data : data
                }).on('action-back', function(){
                    status.destroy();
                    $dataTable.show();
                })
                    .render($report)
                    .start();

                return this;
            },

            /**
             * Remove a task from the datatable
             * @param taskId
             * @returns {taskQueueTable}
             * @fires taskQueueTable#removed
             * @fires taskQueueTable#error
             */
            remove : function remove(taskId){
                var self = this;
                this.taskQueueApi.remove(taskId).then(function(){
                    self.$component.datatable('refresh');
                    self.trigger('removed', taskId);
                }).catch(function(err){
                    self.trigger('error', err);
                });
                return this;
            },
            download : function download(taskId){
                var self = this;

                $.fileDownload(this.config.downloadUrl, {
                    data: {taskId:taskId},
                    failCallback: function () {
                        self.trigger('error', __('File download failed'));
                    }
                });
                return this;
            }
        }, config)
            .on('init', function(){
                this.taskQueueApi = taskQueueApi({url:{
                        status: this.config.serviceUrl,
                        remove: this.config.removeUrl
                    }});
            })
            .on('render', function () {
                var self = this;
                var $component = this.getElement();
                var actions  = [
                    {
                        id: 'delete',
                        icon: 'bin',
                        title: __('Remove'),
                        disabled: function disabled(){
                            if(this.status === config.status.finished
                                || this.status === config.status.finishedError
                                || this.status === config.status.finishedSuccess){
                                return false
                            }
                            return true;
                        },
                        action: function action(id) {
                            self.remove(id);
                        }
                    }, {
                        id: 'report',
                        icon: 'templates',
                        title: __('View report'),
                        disabled: function disabled(){
                            if(this.status !== config.status.created){
                                return false
                            }
                            return true;
                        },
                        action: function action(id) {
                            self.showReport(id);
                        }
                    }
                ];

                if (typeof this.config.downloadUrl !== 'undefined' && this.config.downloadUrl !== ''){
                    actions.push({
                        id: 'download',
                        icon: 'download',
                        title: __('Download'),
                        disabled: function disabled(){
                            if(this.status === config.status.finished
                                || this.status === config.status.finishedSuccess){
                                return false
                            }
                            return true;
                        },
                        action: function action(id) {
                            self.download(id);
                        }
                    });
                }

                //set up the ui/datatable
                $component
                    .addClass('task-queue-table')
                    .on('beforeload.datatable', function(e, dataSet){
                        if(dataSet && dataSet.data){
                            tasks = dataSet.data;
                        }
                    })
                    .on('query.datatable', function () {
                        errorRows = [];
                        self.trigger('loading');
                    })
                    .on('load.datatable', function () {
                        // highlight rows
                        if (_.isArray(errorRows) && errorRows.length) {
                            _.forEach(errorRows, function (id) {
                                $component.datatable('addRowClass', id, 'error');
                            });
                        }
                        self.trigger('loaded');
                    })
                    .datatable({
                        url: this.config.dataUrl,
                        rows : this.config.rows,
                        sortorder: 'desc',
                        filtercolumns: {type: this.config.context, status: this.config.statusFilter},
                        status: {
                            empty: __('No Task yet'),
                            available: __('Task Listing'),
                            loading: __('Loading')
                        },
                        tools: [{
                            id: 'refresh',
                            icon: 'reset',
                            title: __('Refresh'),
                            label: __('Refresh'),
                            action: function () {
                                self.$component.datatable('refresh');
                                self.trigger('refresh');
                            }
                        }],
                        model: [{
                            id: 'label',
                            label: __('Task Name')
                        }, {
                            id: 'creationDate',
                            label: __('Created'),
                            transform: function (value) {
                                return formatDate(value, self.config);
                            }
                        }, {
                            id: 'status',
                            label: __('Status'),
                            transform: function (value, row) {
                                if (row.status === 'finished') {
                                    if(isTaskErrorReport(row.report)){
                                        errorRows.push(row.id);
                                        return config.status.finishedError;
                                    }else{
                                        return config.status.finishedSuccess;
                                    }
                                }else{
                                    return config.status[row.status] || '';
                                }
                            }
                        }, {
                            id: 'actions',
                            label: __('Actions'),
                            type: 'actions',
                            actions: actions
                        }],
                        selectable: false
                    });
            })
            .on('reload', function () {
                if (this.$component) {
                    this.$component.datatable('refresh');
                }
            });
    };
});