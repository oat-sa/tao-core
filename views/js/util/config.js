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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

/**
 * Some config related helpers
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash'
], function (_) {
    'use strict';

    return {
        /**
         * Builds a config object, ensure default values are set.
         * @param {Object} config
         * @param {Object} [defaults]
         * @returns {Object}
         * @throws Error if a required entry is missing
         */
        build: function build(config, defaults) {
            return _.defaults(config || {}, defaults);
        },

        /**
         * Builds a config object by picking entries in the provided data.
         * Sets the defaults values and validates that the required entries are provided.
         * @param {Object} source - The source data
         * @param {Object} [entries] - The list of entries to pick up in the provided data.
         *                             Each required entry must be to true, while optional entries must be set to false.
         * @param {Object} [defaults] - Some default values
         * @returns {Object}
         * @throws Error if a required entry is missing
         */
        from: function from(source, entries, defaults) {
            var config = {};
            _.forEach(entries, function (value, name) {
                if ('undefined' !== typeof source[name]) {
                    config[name] = source[name];
                } else if (value) {
                    throw new Error('The config entry "' + name + '" is required!');
                }
            });
            return _.defaults(config, defaults);
        }
    };
});
