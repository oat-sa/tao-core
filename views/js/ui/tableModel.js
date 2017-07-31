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
 * Build a model of a table to allow easier cell access
 * !!! WARNING: does not work with merged cells yet!!!
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'jquery'
], function(_, $) {
    'use strict';

    return function tableModelFactory($table) {
        /**
         * {jQuery[]} - array of jQuery collections of cells by row
         * @example var $row2Cells = cellsByRow[2];
         */
        var cellsByRow;

        /**
         * Synchronize the model with the table markup
         */
        function update() {
            var rowsInOrder = [] // we need multiple selectors to preserve visual order (vs. DOM order)
                .concat($table.find('thead tr').toArray())
                .concat($table.find('tbody tr').toArray())
                .concat($table.find('tfoot tr').toArray());

            cellsByRow = [];

            rowsInOrder.forEach(function (row) {
                var $row = $(row),
                    $rowCells = $row.find('th,td');

                cellsByRow.push($rowCells);
            });
        }

        /**
         * @return {Number} - the number of row in the table
         */
        function getRowCount() {
            return cellsByRow.length;
        }

        /**
         * Return a jQuery selections of all the cells in the given row.
         * @param {Number} index - row index. If negative, starts with the right of the table
         * @returns {jQuery}
         */
        function getRowCells(index) {
            if (index < 0) {
                index = index + cellsByRow.length;
            }
            return cellsByRow[index] || $();
        }

        /**
         * @return {Number} - the number of columns in the table
         */
        function getColCount() {
            return cellsByRow[0].length; // very naive. What happens with merged cells ?
        }

        /**
         * Return a jQuery selections of all the cells in the given column.
         * @param {Number} index - column index. If negative, starts with the right of the table
         * @returns {jQuery}
         */
        function getColCells(index) {
            var columnCells = [];

            if (_.isFinite(index)) {
                cellsByRow.forEach(function($row) {
                    var cell = $row.get(index);
                    if (cell) {
                        columnCells.push(cell);
                    }
                });
            }

            return (columnCells.length) ? $(columnCells) : $();
        }

        if ($table && $table.is('table')) {
            update();
        } else {
            throw new TypeError('$table should contain a reference to a table element');
        }

        return {
            getRowCount: getRowCount,
            getRowCells: getRowCells,
            getColCount: getColCount,
            getColCells: getColCells,
            update:      update
        };
    };


});