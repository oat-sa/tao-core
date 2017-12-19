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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash'
], function ($, _) {
    'use strict';

    /**
     * The list of saved AJAX managers
     * @type {Array}
     */
    var backup = [];

    /**
     * Provides a simple AJAX mock API
     */
    return {
        /**
         * Backups the current AJAX manager, and optionally replace it with the provided mock.
         * @param {Function} [mock] - Optional mock that will replace the current AJAX manager
         */
        push: function push(mock) {
            backup.push($.ajax);

            if (_.isFunction(mock)) {
                $.ajax = mock;
            }
        },

        /**
         * Restores the last saved AJAX manager
         */
        pop: function pop() {
            if (backup.length) {
                $.ajax = backup.pop();
            }
        },

        /**
         * A simple AJAX mock factory that setup fakes ajax calls.
         * It will replace the existing AJAX manager.
         * @param {Boolean|Function} success - Tells if the AJAX call should be a success or should fail.
         * @param {Object|Function} response - The mock data used as a response. Could be a callback that returns the data.
         * @param {Function} [validator] - An optional function called as the ajax method
         * @returns {Promise}
         */
        mock: function mock(success, response, validator) {
            $.ajax = function() {
                var resolve = success;
                var data = response;

                if (_.isFunction(success)) {
                    resolve = success.apply(this, arguments);
                }

                if (_.isFunction(response)) {
                    data = response.apply(this, arguments);
                }

                if (_.isFunction(validator)) {
                    validator.apply(this, arguments);
                }

                return $.Deferred()[resolve ? 'resolve' : 'reject'](data).promise();
            };
        }
    };
});
