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
    'core/promise'
], function (_, Promise) {
    'use strict';

    /**
     * The data required by the provider loader
     *
     * @typedef {Object} providerdata
     * @property {String} module - AMD module name of the provider
     * @property {String} bundle - AMD module name of the provider's bundle
     * @property {String} category - the provider category
     * @property {String} name - the provider name
     */

    /**
     * Creates a loader with the list of required providers
     * @param {String: Object[]} requiredProviders - A list of mandatory providers, where the key is the category and the value are an array of providers
     * @returns {loader} the provider loader
     * @throws TypeError if something is not well formatted
     */
    return function providerLoader(requiredProviders) {
        /**
         * The list of providers
         */
        var providers = {};

        /**
         * Retains the AMD modules to load
         */
        var modules = {};

        /**
         * The providers to exclude
         */
        var excludes = [];

        /**
         * Bundles to require
         */
        var bundles = [];

        /**
         * The provider loader
         * @typedef {loader}
         */
        var loader = {

            /**
             * Adds a list of dynamic providers to load
             * @param {providerdata[]} providerList - the providers to add
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            addList: function addList(providerList) {
                _.forEach(providerList, this.add, this);
                return this;
            },


            /**
             * Adds a dynamic provider to load
             * @param {providerdata} provider - the provider to add
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            add: function add(provider) {
                if (_.isEmpty(provider.module) || !_.isString(provider.module)) {
                    throw new TypeError('An AMD module must be defined');
                }
                if (_.isEmpty(provider.category) || !_.isString(provider.category)) {
                    throw new TypeError('Providers must belong to a category');
                }

                modules[provider.category] = modules[provider.category] || [];
                modules[provider.category].push(provider.module);

                if (provider.bundle && !_.contains(bundles, provider.bundle)) {
                    bundles.push(provider.bundle);
                }
                return this;
            },

            /**
             * Remove a provider from the loading stack
             * @param {String} module - the provider's module
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            remove: function remove(module) {
                excludes.push(module);
                return this;
            },

            /**
             * Loads the dynamic providers : trigger the dependency resolution
             * @param {Boolean} [loadBundles = false] - does load the bundles
             * @returns {Promise}
             */
            load: function load(loadBundles) {
                var self = this;

                //compute the providers dependencies
                var dependencies = _(modules).values().flatten().uniq().difference(excludes).value();

                /**
                 * Load AMD modules and wrap then into a Promise
                 * @param {String[]} amdModules - the list of modules to require
                 * @returns {Promise}
                 */
                var loadModules = function loadModules(amdModules) {
                    if (_.isArray(amdModules) && amdModules.length) {
                        return new Promise(function (resolve, reject) {
                            require(amdModules, function () {
                                //resolve with an array of loaded modules
                                resolve([].slice.call(arguments));
                            }, reject);
                        });
                    }
                    return Promise.resolve();
                };

                // 1. load bundles
                // 2. load dependencies
                // 3. add them to the providers list
                return loadModules(loadBundles ? bundles : [])
                    .then(function () {
                        return loadModules(dependencies);
                    })
                    .then(function (loadedModules) {
                        _.forEach(dependencies, function (dependency, index) {
                            var provider = loadedModules[index];
                            var category = _.findKey(modules, function (val) {
                                return _.contains(val, dependency);
                            });

                            if (!validateProvider(provider)) {
                                throw new TypeError('Invalid provider');
                            }

                            if (_.isString(category)) {
                                providers[category] = providers[category] || [];
                                providers[category].push(provider);
                            }
                        });
                        return self.getProviders();
                    });
            },

            /**
             * Get the resolved provider list.
             * Load needs to be called before to have the dynamic providers.
             * @param {String} [category] - to get the providers for a given category, if not set, we get everything
             * @returns {Function[]} the providers
             */
            getProviders: function getProviders(category) {
                if (_.isString(category)) {
                    return providers[category] || [];
                }

                return _(providers).values().flatten().uniq().value();
            },

            /**
             * Get the provider categories
             * @returns {String[]} the categories
             */
            getCategories: function getCategories() {
                return _.keys(providers);
            }
        };

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

        //verify and add the required providers
        _.forEach(requiredProviders, function (providerList, category) {
            if (_.isEmpty(category) || !_.isString(category)) {
                throw new TypeError('Providers must belong to a category');
            }

            if (!_.isArray(providerList) || !_.all(providerList, validateProvider)) {
                throw new TypeError('A providers list must be an array of objects, and have names in them');
            }

            if (providers[category]) {
                providers[category] = providers[category].concat(providerList);
            } else {
                providers[category] = providerList;
            }
        });

        return loader;
    };
});
