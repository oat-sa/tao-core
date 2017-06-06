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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * @author Aleh Hutnikau
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/providerRegistry',
    'ui/datatable/filterStrategy/multiple',
    'ui/datatable/filterStrategy/single'
], function ($, _, __, providerRegistry, multipleStrategy, singleStrategy) {
    'use strict';

    var filter;

    /**
     * Datatable filter strategy
     * @param {Object} datatableOptions
     */
    function filterStrategy(datatableOptions) {
        var strategy;

        var filterElement = {
            /**
             * Init filter strategy
             */
            init : function init() {
                var strategyId = datatableOptions.filterStrategy || 'single';
                strategy = filterStrategy.getProvider(strategyId);
                return this;
            },
            /**
             * Get query data
             * @param {jQuery} $table - table element
             * @param {jQuery} $filter - filter input
             * @param {object} options - options
             */
            getQueryData : function getQueryData($table, $filter, options) {
                return strategy.getQueryData($table, $filter, options);
            },
            getFiltersData : function getFiltersData($table, $filter, options) {
                return strategy.getFiltersData($table, $filter, options);
            },
            render : function render($table, options) {
                return strategy.render($table, options);
            }
        };

        return filterElement.init();
    }

    filter = providerRegistry(filterStrategy);
    filter.registerProvider('single', singleStrategy);
    filter.registerProvider('multiple', multipleStrategy);

    return filter;
});
