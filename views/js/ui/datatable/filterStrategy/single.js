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
    'lodash'
], function ($, _) {
    'use strict';

    var filter = {
        init : function () {},
        /**
         * @param {jQuery} $table - table element
         * @param {jQuery} $filter - filter input
         * @param {object} options - datatable options
         */
        getQueryData : function getQueryData($table, $filter, options) {
            var data = {};
            var column = $filter.data('column');
            var model = _.find(options.model, function (o) {
                return o.id === column;
            });

            if ($filter.length === 0) {
                return;
            }

            data.filterquery = $filter.find(':input').filter(function () {
                return $(this).val();
            }).val();

            if (model && 'function' === typeof model.filterTransform) {
                data.filterquery = model.filterTransform(data.filterquery);
            }
            data.filtercolumns = column ? column.split(',') : options.filtercolumns;

            return data;
        },
        /**
         * @param {jQuery} $table - table element
         * @param {jQuery} $filter - filter input
         * @param {object} options - datatable options
         */
        getFiltersData : function getFiltersData($table, $filter, options) {
            var data = {};
            var column = $filter.data('column');

            if ($filter.length === 0) {
                return;
            }

            data.filterquery = $filter.find(':input').filter(function () {
                return $(this).val();
            }).val();
            data.filtercolumns = column ? column.split(',') : options.filter.columns;

            return data;
        },
        render : function render($table, options) {
            var filterColumns = options.filtercolumns ? options.filtercolumns : [];

            _.forEach($('.filter', $table), function (filter) {
                var $filter = $(filter);
                var column = $filter.data('column');
                var filterSelector = options.filterSelector || 'select, input';
                var $filterInput = $(filterSelector, $filter);

                var model = _.find(options.model, function (o) {
                    return o.id === column;
                });

                // set value to filter field
                if (options.filterquery && column === filterColumns.join()) {
                    $filterInput.val(options.filterquery).addClass('focused');
                }

                if (model && model.customFilter) {
                    if ('function' === typeof model.customFilter.callback) {
                        model.customFilter.callback($filterInput);
                    }
                }
            });
        }
    };

    return filter;
});
