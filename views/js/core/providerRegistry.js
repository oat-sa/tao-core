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
 * Enables to register providers to a target.
 *
 * @author Sam <sam@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['lodash'], function (_) {
    'use strict';

    /**
     * Transfers the target into a a provider registry
     * It adds two methods registerProvider() and getProvider();
     *
     * @param {Object} target
     * @param {Function} [validator] - a function to validate the provider to be registered
     *
     * @returns {Object} the target itself
     */
    function providerRegistry(target, validator) {

        var _providers = {};
        target = target || {};


        /**
         * Registers a <i>provider</i> into the provider registry.
         * The provider provides the behavior required by the target object.
         *
         * @param {String} name - the provider name will be used to select the provider while instantiating the target object
         *
         * @param {Object} provider - the Provider as a plain object. The target object forwards, encapsulates and delegates calls to the provider.
         * @param {Function} provider.init - the provider initializes the target object from it's config
         *
         * @returns {registerProvider}
         *
         * @throws TypeError when a wrong provider is given or an empty name.
         */
        function registerProvider(name, provider) {

            var valid = true;

            //type checking
            if (!_.isString(name) || name.length <= 0) {
                throw new TypeError('It is required to give a name to your provider.');
            }
            if (!_.isPlainObject(provider) || (!_.isFunction(provider.init))) {
                throw new TypeError('A provider is an object that contains at least an init function.');
            }
            valid = validator && _.isFunction(validator) ? validator(provider) : valid;

            if (valid) {
                _providers[name] = provider;
            }

            return this;
        }

        /**
         * Gets a registered provider by its name
         *
         * @param {String} providerName
         *
         * @returns {Object} provider
         */
        function getProvider(providerName) {

            var provider;

            //check a provider is available
            if (!_providers || _.size(_providers) === 0) {
                throw new Error('No provider registered');
            }

            if (_.isString(providerName) && providerName.length > 0) {
                provider = _providers[providerName];
            } else if (_.size(_providers) === 1) {

                //if there is only one provider, then we take this one
                providerName = _.keys(_providers)[0];
                provider = _providers[providerName];
            }

            //now we should have a provider
            if (!provider) {
                throw new Error('No candidate found for the provider');
            }

            return provider;
        }

        /**
         * Expose the list of registered providers
         * @return {String[]} the list of provider names
         */
        function getAvailableProviders(){
            return _.keys(_providers);
        }

        /**
         * Clears the registered providers
         *
         * @returns {registerProvider}
         */
        function clearProviders() {
            _providers = {};
            return this;
        }

        target.registerProvider = registerProvider;
        target.getProvider = getProvider;
        target.getAvailableProviders = getAvailableProviders;
        target.clearProviders = clearProviders;

        return target;
    }

    return providerRegistry;
});
