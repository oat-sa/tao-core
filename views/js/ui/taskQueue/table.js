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
    'ui/modal'
], function ($, _, __, moment, taskQueueApi, component, taskQueueStatusFactory, reportTpl) {
    'use strict';

    var _defaults = {
        context: '',
        urls: {
            listing: '',
            remove: ''
        }
    };

    /**
     * Format the status string
     *
     * @param {String} status
     * @returns {String}
     */
    var formatStatus = function formatStatus(status) {
        return status;
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
     * Check if the datatable row is removable
     * @param {Object} row - datatable row
     * @returns {boolean}
     */
    var isRemovable = function isRemovable(row) {
        return (row.status === 'finished');
    };

    /**
     * Check if the datatable row can display a report
     * @param {Object} row - datatable row
     * @returns {boolean}
     */
    var isReportable = function isReportable(row) {
        return (row.status === 'finished');
    };

    var taskQueueTable = {
        /**
         * Display a report for a task
         * @param taskId
         * @returns {taskQueueTable}
         */
        showReport : function showReport(taskId) {
            var status;
            var $report = this.$component.find('.report-container');
            var $dataTable = this.$component.find('.datatable-wrapper');

            if(!$report.length){
                $report = $(reportTpl());
                this.$component.append($report);
            }

            //toggle display fo queue table
            $dataTable.hide();

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
                }]
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
        }
    };

    /**
     * Creates the taskQueueTable component
     *
     * @param {String} testCenterId - the test center URI
     * @returns {taskQueueTable} the component
     * @throws {TypeError} when the task queue context (type) is absent in the config
     */
    return function taskQueueTableFactory(config) {

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
        return component(taskQueueTable, config)
            .on('init', function(){
                this.taskQueueApi = taskQueueApi({url:{
                    status: this.config.serviceUrl,
                    remove: this.config.removeUrl
                }});
            })
            .on('render', function () {
                var self = this;

                //set up the ui/datatable
                this.$component
                    .on('query.datatable', function () {
                        self.trigger('loading');
                    })
                    .on('load.datatable', function () {
                        self.trigger('loaded');
                    })
                    .datatable({
                        url: this.config.dataUrl,
                        rows : this.config.rows,
                        filtercolumns: {type: this.config.context},
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
                            transform: function (value) {
                                return formatStatus(value);
                            }
                        }, {
                            id: 'actions',
                            label: __('Actions'),
                            type: 'actions',
                            actions: [{
                                id: 'delete',
                                icon: 'bin',
                                title: __('Remove'),
                                disabled: function () {
                                    return !isRemovable(this);
                                },
                                action: function (id) {
                                    self.remove(id);
                                }
                            }, {
                                id: 'report',
                                icon: 'templates',
                                title: __('View report'),
                                disabled: function () {
                                    return !isReportable(this);
                                },
                                action: function (id) {
                                    self.showReport(id);
                                }
                            }]
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
