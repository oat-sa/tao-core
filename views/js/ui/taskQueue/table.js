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
    'ui/component',
    'ui/report',
    'ui/datatable',
    'ui/modal'
], function ($, _, __, moment, component, report) {
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

    var showReport = function showReport(taskId){
        console.log('showReport', arguments);

        var $report = $('<div class="modal">').modal();
        $report.append('AAAA');
        $('body').append($report);
    };

    var deleteTask = function deleteTask(taskId){
        console.log('deleteTask', arguments);
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
        return component({}, config)
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
                    .on('beforeload.datatable', function (e, dataSet) {
                        if (dataSet && dataSet.data) {
                            //eligibilities = dataSet.data;
                        }
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

                                self.trigger('refresh');

                                return;
                                //refresh
                                this.$component.datatable('refresh');
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
                        },{
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
                                action: deleteTask
                            }, {
                                id: 'report',
                                icon: 'templates',
                                title: __('View report'),
                                disabled: function () {
                                    return !isReportable(this);
                                },
                                action: showReport
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
