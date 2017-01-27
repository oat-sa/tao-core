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
define([
    'jquery',
    'lodash',
    'i18n',
    'moment',
    'core/taskQueue',
    'ui/component',
    'ui/taskQueue/status',
    'ui/datatable',
    'ui/modal'
], function ($, _, __, moment, taskQueueApi, component, taskQueueStatusFactory) {
    'use strict';

    var _defaults = {
        context: '',
        urls: {
            listing: '',
            remove: ''
        }
    };

    var formatStatus = function formatStatus(status) {
        return status;
    };

    var formatDate = function formatDate(date) {
        return moment.unix(date).fromNow();
    };

    var isRemovable = function isRemovable(row) {
        return (row.status === 'finished');
    };

    var isReportable = function isReportable(row) {
        return (row.status === 'finished');
    };

    var taskQueueTable = {
        showReport: function showReport(taskId) {
            var status;
            var $report = this.$component.find('.report-container');
            var $dataTable = this.$component.find('.datatable-wrapper');

            if(!$report.length){
                $report = $('<div class="report-container"></div>');
                this.$component.append($report);
            }

            //toggle display fo queue table
            $dataTable.hide();

            status = taskQueueStatusFactory({
                replace : true,
                taskId: taskId,
                serviceUrl: this.config.statusUrl,
                back : true
            }).on('showDetails', function(){
                $report.height(640);//fix this
            }).on('hideDetails', function(){
                $report.height('auto');
            }).on('back', function(){
                status.destroy();
                $dataTable.show();
            })
            .render($report)
            .start();
        },
        remove:function remove(taskId){
            var self = this;
            this.taskQueueApi.remove(taskId).then(function(){
                self.$component.datatable('refresh');
                self.trigger('removed', taskId);
            }).catch(function(err){
                self.trigger('error', err);
            });
        }
    };

    /**
     * Creates the taskQueueListing component
     *
     * @param {String} testCenterId - the test center URI
     * @returns {taskQueueListing} the component
     * @throws {TypeError} when the context is absent in the config
     */
    return function taskQueueTableFactory(config) {

        config = _.defaults(config, _defaults);

        if (_.isEmpty(config.context)) {
            throw new TypeError('The task queue provider needs to be initalized with a context');
        }

        /**
         * The component.
         *
         * @see ui/component
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
                    .on('load.datatable', function (e) {
                        self.trigger('loaded');
                    })
                    .datatable({
                        url: this.config.dataUrl,
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
