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
    
    var defaults = {
        name: 'provided',
        eventifier: true
    };

    var _slice = [].slice;

    /**
     * Creates a function that delegates api calls to an provider
     * @param {Object} api - The api providing the calls
     * @param {Object} provider - The provider on which delegate the calls
     * @param {Object} [config] - An optional configuration set
     * @param {String} [config.name] - The name of the provider
     * @param {Boolean} [config.eventifier] - Enable the eventifier support (default: true)
     * @param {Boolean} [config.forward] - Forward the calls to the provider instead of delegate (default: false)
     * @param {Function} [config.defaultProvider] - An optional default delegated function called if the provider do not have the requested target.
     * @param {Function} [config.wrapper] - An optional function that will wrap the response
     * @param {Boolean} [config.required] - Throws exception if a delegated method is missing (default: false)
     * @returns {delegate} - The delegate function
     */
    function delegator(api, provider, config) {
        var extendedConfig = _.defaults(config || {}, defaults);
        var eventifier = !!(extendedConfig.eventifier && api && api.trigger);
        var context = extendedConfig.forward ? provider : api;
        var defaultProvider = _.isFunction(extendedConfig.defaultProvider) ? extendedConfig.defaultProvider : _.noop;
        var wrapper = _.isFunction(extendedConfig.wrapper) ? extendedConfig.wrapper : null;
        var name = extendedConfig.name;

        if (extendedConfig.required) {
            defaultProvider = null;
        }

        /**
         * Delegates a function call from the api to the provider.
         * If the api supports eventifier, fires the related event
         *
         * @param {String} fnName - The name of the delegated method to call
         * @param {Object} ... - Following parameters will be forwarded as is
         * @returns {Object} - The delegated method must return a response
         * @private
         * @throws Error
         */
        function delegate(fnName) {
            var response, args;

            if (provider) {
                if (_.isFunction(provider[fnName]) || defaultProvider) {
                    // need real array of params, even if empty
                    args = _slice.call(arguments, 1);

                    // delegate the call to the provider
                    response = (provider[fnName] || defaultProvider).apply(context, args);

                    if (wrapper) {
                        response = wrapper(response);
                    }

                    // if supported fires the method related event
                    if (eventifier) {
                        // the response has to be provided as first argument in all events
                        api.trigger.apply(api, [fnName, response].concat(args));
                    }
                } else {
                    throw new Error('There is no method called ' + fnName + ' in the ' + name + ' provider!');
                }
            } else {
                throw new Error('There is no ' + name + ' provider!');
            }

            return response;
        }

        return delegate;
    }

    return delegator;
});
