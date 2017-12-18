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
 * Loads providers, and feeds a registry if provided
 *
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/moduleLoader'
], function (_, moduleLoaderFactory) {
    'use strict';

    /**
     * Checks a provider object
     * @param provider
     * @returns {Boolean}
     */
    function validateProvider(provider) {
        return _.isPlainObject(provider) &&
            _.isFunction(provider.init) &&
            _.isString(provider.name) &&
            !_.isEmpty(provider.name);
    }

    /**
     * Creates a loader with the list of required providers
     * @param {String: Object[]} requiredProviders - A list of mandatory providers, where the key is the category and the value are an array of providers
     * @returns {loader} the provider loader
     * @throws TypeError if something is not well formatted
     */
    return function providerLoader(requiredProviders) {
        return moduleLoaderFactory(requiredProviders, validateProvider, {
            /**
             * Get the resolved provider list.
             * Load needs to be called before to have the dynamic providers.
             * @param {String} [category] - to get the providers for a given category, if not set, we get everything
             * @returns {Function[]} the providers
             */
            getProviders: function getProviders(category) {
                return this.getModules(category);
            }
        });
    };
});
