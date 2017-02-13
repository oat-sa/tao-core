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
    'i18n'
], function ($, _, __) {
    'use strict';

    var filter = {
        init : function () {},
        /**
         * @param {jQuery} $table - table element
         * @param {jQuery} $filter - filter input
         * @param {object} options - datatable options
         */
        getQueryData : function getQueryData($table, $filter, options) {
            var data = {filtercolumns : {}};

            _.forEach($('.filter', $table), function (filter) {
                var $filter = $(filter);
                var filterSelector = options.filterSelector || 'select, input';
                var $filterInput = $(filterSelector, $filter);
                if ($filterInput.length === 0) {
                    return;
                }
                var name = $filterInput.attr('name').replace(/^filter\[(.+)\]$/, '$1');
                if ($filterInput.val()) {
                    data.filtercolumns[name] = $filterInput.val();
                }
            });

            return data;
        },
        render : function render($table, options) {
            _.forEach($('.filter', $table), function (filter) {
                var $filter = $(filter);
                var column = $filter.data('column');
                var filterSelector = options.filterSelector || 'select, input';
                var $filterInput = $(filterSelector, $filter);
                if ($filterInput.length === 0) {
                    return;
                }
                var model = _.find(options.model, function (o) {
                    return o.id === column;
                });
                var name = $filterInput.attr('name').replace(/^filter\[(.+)\]$/, '$1');

                if (options.filtercolumns && options.filtercolumns[name]) {
                    $filterInput.val(options.filtercolumns[name]);
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
