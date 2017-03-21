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
 */
define([
    'lodash'
], function(_) {
    'use strict';

    return {
        /**
         * This helper is useful when a boolean value might be passed as a string, as it will prevent the string "false" being evaluated to true
         * It also deals with boolean values to prevent the confusing case where strToBool(true) would return false
         * @param {String|Boolean|Undefined} value
         * @param {Boolean} defaultValue
         * @returns {Boolean} true if value === "true", defaultValue if set, false if defaultValue not set
         */
        strToBool: function strToBool(value, defaultValue) {
            if(_.isBoolean(value)) {
                return value;

            } else if (_.isString(value)) {
                return value.toLowerCase() === "true";

            } else {
                return defaultValue || false;
            }
        }
    };
});