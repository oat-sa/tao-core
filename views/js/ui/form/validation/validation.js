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

define([
    'jquery',
    'lodash'
], function(
    $,
    _
) {
    'use strict';

    /**
     * The validation factory
     * @param {jQuery} options.container - Validation's container
     * @returns {ui/component}
     */
    function validationFactory(options) {

        var container = options.container;
        var validations = [];

        return {
            /**
             * Add a validation check
             * @param {Regex|Function} predicate
             * @param {String} message
             */
            add: function add(predicate, message) {
                // todo - add to validations
            },

            /**
             * Clear all validation messages
             */
            clear: function clear() {
                // todo - clear all validation messages
            },

            /**
             * Run all validations
             */
            run: function run() {
                // todo - run all validations
            }
        };
    }

    return validationFactory;
});