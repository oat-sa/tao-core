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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Anton Tsymuk <anton@taotesting.com>
 */
define([
    'i18n',
    'lodash',
    'ui/component',
    'tpl!ui/dashboard/dashboard',
    'tpl!ui/dashboard/dashboardMetricsList',
], function (__, _, component, dashboardTpl, metricsListTpl) {
    'use strict';

    var defaults = {
        headerText: __('Outlook on the next Synchronization'),
        loadingText: __('Creating report ...'),
        warningText: __('Please contact your system administrator.'),
        loading: false, // should display loading screen
        data: [], // metricts that should be displayed
        scoreState: { // score borders of different metrics states
            error: 32,
            warn: 65,
        }
    }

    /**
     * Dashboard component to display metricts in pass/fail way
     *
     * @param {Object} $container
     * @param {Object} config
     * @param {String} [config.headerText]
     * @param {String} [config.loadingText]
     * @param {String} [config.warningText]
     * @param {Boolean} [config.loading] - should display loading screen
     * @param {Array} [config.data] - metricts that should be displayed
     * @param {String} data[].title - metric title
     * @param {Number} data[].score - metric score
     * @param {Array} data[].info - array of info labels
     * @returns {readinessDashboard}
     */
    function dashboardFactory(config) {
        var specs = {
            /**
             * Clear dashboard
             */
            clearDashboard: function clearDashboard() {
                this.getElement().find('.dashboard-metrics_container').empty();
                this.toggleWarningMessage(false);
            },
            /**
             * Return metric check state according to it socre
             *
             * @param {Number} socre - metric score
             */
            mapScoreToState: function mapScoreToState(score) {
                var scoreState = this.config.scoreState;

                if (score > scoreState.warn) {
                    return 'success';
                } else if (score > scoreState.error) {
                    return 'warn';
                }

                return 'error';
            },
            /**
             * Render list of provided metircs
             *
             * @param {Array} data - metrics data
             * @param {String} data[].title - metric title
             * @param {Number} data[].score - metric score
             * @param {Array} data[].info - array of info labels
             */
            renderMetrics: function renderMetrics(data) {
                var $component = this.getElement();
                var $listContainer = $component.find('.dashboard-metrics_container');
                var self = this;

                if (data && data.length) {
                    _.forEach(data, function (item) {
                        item.state = self.mapScoreToState(item.score);
                    });

                    this.toggleWarningMessage(_.some(
                        data,
                        function (item) { return item.score <= self.config.scoreState.warn; }
                    ));

                    var $metricsList = $(metricsListTpl({ data: data }));

                    $listContainer.append($metricsList);
                }
            },
            /**
             * Toggle loading bar
             */
            toggleLoadingBar: function toggleLoadingBar(display) {
                this.getElement().find('.dashboard-loading').toggle(display);
            },
            /**
             * Toggle warning message
             */
            toggleWarningMessage: function toggleWarningMessage(display) {
                this.getElement().find('.dashboard-warning').toggle(display);
            },
        };

        /**
         * @typedef {dashboard}
         */
        return component(specs, defaults)
            .setTemplate(dashboardTpl)
            .on('init', function () {
                this.setState('loading', this.config.loading);
            })
            .on('render', function () {
                if (!this.is('loading')) {
                    this.renderMetrics(this.config.data);
                } else {
                    this.toggleLoadingBar(true);
                }
            })
            .init(config);
    }

    return dashboardFactory;
});
