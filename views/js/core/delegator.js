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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'lodash'
], function (_) {
    'use strict';

    var _slice = [].slice;

    /**
     * Creates a function that delegates api calls to an adapter
     * @param {Object} api - The api providing the calls
     * @param {Object} adapter - The adapter on which delegate the calls
     * @param {String} name - The name of the adapter
     * @returns {delegate} - The delegate function
     */
    function delegator(api, adapter, name) {

        var eventifier = !!(api && api.trigger);

        if (!name) {
            name = 'provided';
        }

        /**
         * Delegates a function call from the api to the adapter.
         * If the api supports eventifier, fires the related event
         *
         * @param {String} fnName - The name of the delegated method to call
         * @param {Array} [args] - An optional array of arguments to apply to the method
         * @returns {Object} - The delegated method must return a response
         * @private
         * @throws Error
         */
        function delegate(fnName, args) {
            var response;

            if (adapter) {
                if (_.isFunction(adapter[fnName])) {
                    // need real array of params, even if empty
                    args = args ? _slice.call(args) : [];

                    // delegate the call to the adapter
                    response = adapter[fnName].apply(api, args);

                    // if supported fire the method related event
                    if (eventifier) {
                        // the response has to be provided as first argument in all events
                        api.trigger.apply(api, [fnName, response].concat(args));
                    }
                } else {
                    throw new Error('There is no method called ' + fnName + ' in the ' + name + ' adapter!');
                }
            } else {
                throw new Error('There is no ' + name + ' adapter!');
            }

            return response;
        }

        return delegate;
    }

    return delegator;
});
