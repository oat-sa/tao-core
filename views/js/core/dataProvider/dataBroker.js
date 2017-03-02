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
    'lodash',
    'core/eventifier',
    'core/promise',
    'core/middleware'
], function (_, eventifier, Promise, middlewareHandlerFactory) {
    'use strict';

    var _defaults = {};

    /**
     * Implements a data broker that will serve data using proxies (readonly).
     * Each time a data is requested, the data broker will first request a default proxy,
     * then fallback to a more specific one. This behaviors allows, for instance,
     * to target both DOM content data and remote data.
     *
     * @param {Object} config - Some optional config entries
     * @param {middlewareHandler} [config.middlewares] - An optional middlewares handler that will be set to every provider.
     *                                                   When this option is missing a default instance is created.
     * @returns {dataBroker}
     */
    function dataBrokerFactory(config) {
        var initConfig = _.defaults({}, config, _defaults);
        var providers = {};
        var middlewares;

        /**
         * @typedef {dataBroker}
         */
        var dataBroker = eventifier({
            /**
             * Cleans up and destroys the instance.
             * @fires destroy
             */
            destroy: function destroy() {
                /**
                 * @event destroy
                 */
                dataBroker.trigger('destroy');
                initConfig = null;
                providers = null;
            },

            /**
             * Gets a registered data provider.
             * @param {String} name
             * @returns {proxy}
             */
            getProvider: function getProvider(name) {
                return providers && providers[name];
            },

            /**
             * Tells if a provider has been registered.
             * @param {String} name
             * @returns {Boolean}
             */
            hasProvider: function hasProvider(name) {
                return !!dataBroker.getProvider(name);
            },

            /**
             * Adds a data provider. It should at least implement a `read()` method.
             * A data provider must be related to a particular entry.
             * @param {String|Object} name - The name of the provider, or the provider itself (in that case it must contains its name)
             * @param {proxy} [provider] - The provider to register.
             * @returns {dataBroker}
             * @throws {TypeError} if the name is missing or if the provider is invalid
             * @fires addprovider
             */
            addProvider: function addProvider(name, provider) {
                if (_.isPlainObject(name)) {
                    provider = name;
                    name = provider.name;
                }

                if (!name || !_.isString(name)) {
                    throw new TypeError('Yous must provide a name for the provider!');
                }

                if (!_.isPlainObject(provider) || !_.isFunction(provider.read)) {
                    throw new TypeError('Yous must provide a valid provider!');
                }

                providers[name] = provider;

                if (_.isFunction(provider.setMiddlewares)) {
                    provider.setMiddlewares(middlewares);
                }

                if (_.isFunction(provider.on)) {
                    provider.off('error.dataBroker').on('error.dataBroker', function(err) {
                        dataBroker.trigger('error', err);
                    });
                }

                /**
                 * @event addprovider
                 * @param {String} name
                 * @param {Object} provider
                 */
                dataBroker.trigger('addprovider', name, provider);
                return this;
            },

            /**
             * Loads a list of named providers. Accept promises that resolve with instance of provider.
             * @param {Object} providers - A list of providers (can be promises) to load.
             * @returns {Promise} - Returns a promise that should be resolved with the dataBroker
             * instance once all providers have been loaded.
             */
            loadProviders: function loadProviders(providers) {
                var initChain = [];

                providers = providers || {};
                _.forEach(providers, function (provider, name) {
                    if (provider instanceof Promise) {
                        initChain.push(provider.then(function (instance) {
                            providers[name] = instance;
                        }));
                    }
                });

                return Promise.all(initChain).then(function () {
                    _.forEach(providers, function (provider, name) {
                        dataBroker.addProvider(name, provider);
                    });

                    return dataBroker;
                });
            },

            /**
             * Read data using a particular provider.
             * @param {String} name
             * @param {Object} [params]
             * @returns {Promise}
             * @fires readprovider
             * @fires data
             */
            readProvider: function readProvider(name, params) {
                var provider = dataBroker.getProvider(name);
                if (provider) {
                    /**
                     * @event readprovider
                     * @param {String} name
                     * @param {Object} params
                     */
                    dataBroker.trigger('readprovider', name, params);

                    return provider.read(params)
                        .then(function(data) {
                            /**
                             * @event data
                             * @param {Object} data
                             * @param {String} name
                             * @param {Object} params
                             */
                            dataBroker.trigger('data', data, name, params);
                            return data;
                        });
                }
                return Promise.reject(new TypeError('The provider ' + name + ' is not implemented'));
            },

            /**
             * Gets the config object
             * @returns {Object}
             */
            getConfig: function getConfig() {
                return initConfig;
            },

            /**
             * Gets the middlewares handler
             * @returns {middlewareHandler}
             */
            getMiddlewares: function getMiddlewares() {
                return middlewares;
            }
        });

        if (initConfig.middlewares &&
            (!_.isPlainObject(initConfig.middlewares) || !_.isFunction(initConfig.middlewares.apply))) {
            throw new TypeError('You must provide a valid middlewares handler');
        }

        middlewares = initConfig.middlewares || middlewareHandlerFactory();

        return dataBroker;
    }

    return dataBrokerFactory;
});
