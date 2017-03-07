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
    'core/delegator',
    'core/eventifier',
    'core/promise',
    'core/providerRegistry',
    'core/tokenHandler'
], function (_, delegator, eventifier, Promise, providerRegistry, tokenHandlerFactory) {
    'use strict';

    var _defaults = {};

    /**
     * Defines a CRUD proxy bound to a particular adapter. Each adapter will have to provide the following API:
     *
     * `init(config, params)`
     * `destroy()`
     * `create(params)`
     * `read(params)`
     * `write(params)`
     * `remove(params)`
     * `action(name, params)`
     *
     * @param {String} proxyName - The name of the proxy adapter to use in the returned proxy instance
     * @param {middlewareHandler} [middlewares] - An optional middlewares handler
     * @returns {proxy} - The proxy instance, bound to the selected proxy adapter
     */
    function crudProxyFactory(proxyName, middlewares) {

        var proxyAdapter = crudProxyFactory.getProvider(proxyName);
        var tokenHandler = tokenHandlerFactory();
        var extraParams = {};
        var initialized = false;
        var initConfig;

        /**
         * @typedef {proxy}
         */
        var proxy = eventifier({
            /**
             * Initializes the proxy
             * @param {Object} [config] - Some optional config depending of implementation,
             *                            this object will be forwarded to the proxy adapter
             * @returns {Promise} - Returns a promise that provide the proxy.
             *                      The proxy will be fully initialized on resolve.
             *                      Any error will be provided if rejected.
             * @fires init
             */
            init: function init(config) {
                initConfig = _.defaults({}, config, _defaults);

                /**
                 * @event init
                 * @param {Promise} promise
                 * @param {Object} params
                 */
                return delegate('init', initConfig).then(function () {
                    // If the delegate call succeed the proxy is initialized.
                    initialized = true;
                    return proxy;
                });
            },

            /**
             * Uninstalls the proxy
             * @returns {Promise} - Returns a promise. The proxy will be fully uninstalled on resolve.
             *                      Any error will be provided if rejected.
             * @fires destroy
             */
            destroy: function destroy() {
                /**
                 * @event destroy
                 * @param {Promise} promise
                 */
                return delegate('destroy').then(function () {
                    // The proxy is now destroyed. A call to init() is mandatory to be able to use it again.
                    initialized = false;
                    initConfig = null;
                    extraParams = {};
                });
            },

            /**
             * Creates data
             * @param {Object} [params] - An optional list of parameters
             * @returns {Promise} - Returns a promise. The data will be provided on resolve.
             *                      Any error will be provided if rejected.
             * @fires create
             */
            create: function create(params) {
                /**
                 * @event create
                 * @param {Promise} promise
                 * @param {Object} params
                 */
                return delegate('create', getParams(params));
            },

            /**
             * Reads data
             * @param {Object} [params] - An optional list of parameters
             * @returns {Promise} - Returns a promise. The data will be provided on resolve.
             *                      Any error will be provided if rejected.
             * @fires read
             */
            read: function read(params) {
                /**
                 * @event read
                 * @param {Promise} promise
                 * @param {Object} params
                 */
                return delegate('read', getParams(params));
            },

            /**
             * Writes data
             * @param {Object} [params] - An optional list of parameters
             * @returns {Promise} - Returns a promise. The data will be provided on resolve.
             *                      Any error will be provided if rejected.
             * @fires write
             */
            write: function write(params) {
                /**
                 * @event write
                 * @param {Promise} promise
                 * @param {Object} params
                 */
                return delegate('write', getParams(params));
            },

            /**
             * Removes data
             * @param {Object} [params] - An optional list of parameters
             * @returns {Promise} - Returns a promise. The data will be provided on resolve.
             *                      Any error will be provided if rejected.
             * @fires remove
             */
            remove: function remove(params) {
                /**
                 * @event remove
                 * @param {Promise} promise
                 * @param {Object} params
                 */
                return delegate('remove', getParams(params));
            },

            /**
             * Call a particular action
             * @param {String} name - The name of the action to call
             * @param {Object} [params] - An optional list of parameters
             * @returns {Promise} - Returns a promise. The data will be provided on resolve.
             *                      Any error will be provided if rejected.
             * @fires action
             */
            action: function action(name, params) {
                /**
                 * @event action
                 * @param {Promise} promise
                 * @param {String} action
                 * @param {Object} params
                 */
                return delegate('action', name, getParams(params));
            },

            /**
             * Add extra parameters that will be added to the next request
             * @param {Object} params - the extra parameters
             * @returns {proxy}
             */
            addExtraParams: function addExtraParams(params) {
                if (_.isPlainObject(params)) {
                    _.merge(extraParams, params);
                }
                return this;
            },

            /**
             * Gets the security token handler
             * @returns {tokenHandler}
             */
            getTokenHandler: function getTokenHandler() {
                return tokenHandler;
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
            getMiddlewares: function getMidlewares() {
                return middlewares;
            },

            /**
             * Sets the middlewares handler
             * @param {middlewareHandler} [handler] - An optional middlewares handler
             * @returns {proxy}
             */
            setMiddlewares: function setMidlewares(handler) {
                middlewares = handler;
                return this;
            }
        });

        var delegateProxy = delegator(proxy, proxyAdapter, {
            name: 'proxy',
            wrapper: function proxyWrapper(response) {
                return Promise.resolve(response);
            }
        });

        /**
         * Gets parameters merged with extra parameters
         * @param {Object} [params]
         * @return {Object}
         * @private
         */
        function getParams(params) {
            var mergedParams = _.merge({}, params, extraParams);
            extraParams = {};
            return mergedParams;
        }

        /**
         * Delegates the call to the proxy implementation and apply the middleware.
         *
         * @param {String} fnName - The name of the delegated method to call
         * @returns {Promise} - The delegated method must return a promise
         * @private
         * @throws Error
         */
        function delegate(fnName) {
            var request = {command: fnName, params: Array.prototype.slice.call(arguments, 1)};
            if (!initialized && fnName !== 'init') {
                return Promise.reject(new Error('Proxy is not properly initialized or has been destroyed!'));
            }
            return delegateProxy.apply(null, arguments)
                .then(function (data) {
                    if (middlewares) {
                        return middlewares.apply(request, data);
                    }
                    return data;
                })
                .catch(function (err) {
                    proxy.trigger('error', err);
                    return Promise.reject(err);
                });
        }


        return proxy;
    }

    return providerRegistry(crudProxyFactory);
});
