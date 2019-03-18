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
 * Copyright (c) 2016-2019 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'lodash',
    'module',
    'core/tokenStore',
    'core/promiseQueue'
],
function (_, module, tokenStoreFactory, promiseQueue) {
    'use strict';

    var clientConfigFetched = false;

    var defaults = {
        maxSize: 6,
        tokenTimeLimit: 1000 * 60 * 24
    };

    /**
     * Stores the security token queue
     * @param {Object} [options]
     * @param {String} [options.maxSize]
     * @param {String} [options.tokenTimeLimit]
     * @param {String} [options.initialToken]
     * @returns {tokenHandler}
     */
    return function tokenHandlerFactory(options) {

        var tokenStore;

        // Convert legacy parameter:
        if (_.isString(options)) {
            options = {
                initialToken: options
            };
        }
        options = _.defaults({}, options, defaults);
        // Initialise storage for tokens:
        tokenStore = tokenStoreFactory(options);

        return {
            /**
             * Gets the next security token from the token queue
             * If none are available, it can check the ClientConfig (once only per page)
             * Once the token is got, it is erased from the store (because they are single-use by design)
             *
             * @returns {Promise<String>} the token value
             */
            getToken: function getToken() {
                var self = this;
                var initialToken = options.initialToken;

                // If set, initialToken will be provided directly, without using store:
                if (initialToken) {
                    options.initialToken = null;
                    return Promise.resolve(initialToken);
                }

                // Some async checks before we go for the token:
                return tokenStore.expireOldTokens()
                    .then (function() {
                        return tokenStore.getSize();
                    })
                    .then(function(queueSize) {
                        if (queueSize > 0) {
                            // Token available, use it
                            return tokenStore.dequeue().then(function(currentToken) {
                                return currentToken.value;
                            });
                        }
                        else if (!clientConfigFetched) {
                            // Client Config allowed! (first and only time)
                            return self.getClientConfigTokens()
                                .then(function() {
                                    return tokenStore.dequeue().then(function(currentToken) {
                                        if (currentToken) {
                                            return currentToken.value;
                                        }
                                        return null;
                                    });
                                });
                        }
                        else {
                            // No more token options, refresh needed
                            return Promise.reject(new Error('No tokens available. Please refresh the page.'));
                        }
                    });
            },

            /**
             * Adds a new security token to the token queue
             * Internally, old tokens are deleted to keep queue within maximum pool size
             * @param {String} newToken
             * @returns {Promise<Boolean>} - resolves true if successful
             */
            setToken: function setToken(newToken) {
                return tokenStore.enqueue(newToken)
                    .then(function(added) {
                        return added;
                    });
            },

            /**
             * Extracts tokens from the Client Config which should be received on every page load
             * @returns {Promise<Boolean>} - resolves true when completed
             */
            getClientConfigTokens: function getClientConfigTokens() {
                var self = this;
                var clientTokens = _.map(module.config().tokens, function(serverToken) {
                    return {
                        value: serverToken,
                        receivedAt: Date.now()
                    };
                });

                // Record that this function ran:
                clientConfigFetched = true;

                return Promise.resolve(clientTokens).then(function(newTokens) {
                    // Add the fetched tokens to the store
                    // Uses a promiseQueue to ensure synchronous adding
                    var setTokenQueue = promiseQueue();

                    _.forEach(newTokens, function(token){
                        setTokenQueue.serie(function(){
                            return self.setToken(token);
                        });
                    });

                    return setTokenQueue.serie(function() {
                        return true;
                    });
                });
            },

            /**
             * Clears the token store
             * @returns {Promise<Boolean>} - resolves to true when cleared
             */
            clearStore: function clearStore() {
                return tokenStore.clear();
            },

            /**
             * Getter for the current queue length
             * @returns {Promise<Integer>}
             */
            getQueueLength: function getQueueLength() {
                return tokenStore.getSize();
            },

            /**
             * Setter for maximum pool size
             * @param {Integer} size
             */
            setMaxSize: function setMaxSize(size) {
                tokenStore.setMaxSize(size);
            }
        };
    };
});
