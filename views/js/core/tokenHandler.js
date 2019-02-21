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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'lodash',
    'module',
    'core/store',
    'ui/feedback',
    'core/tokenStore'
],
function (_, module, store, feedback, tokenStoreFactory) {
    'use strict';

    var defaults = {
        maxSize: 6,
        tokenTimeLimit: 1000 * 60 * 24
    };

    /**
     * Stores the security token queue
     * @param {Object} [config]
     * @param {String} [config.maxSize]
     * @param {String} [config.tokenTimeLimit]
     * @returns {tokenHandler}
     */
    return function tokenHandlerFactory(config) {

        //in memory storage
        var getConfigStore = function getConfigStore() {
            return store('tokenHandler', store.backends.memory);
        };

        var tokenStore;

        // Convert legacy parameter:
        if (_.isString(config)) {
            config = {
                initialToken: config
            };
        }
        config = _.defaults({}, config, defaults);
        // Initialise storage for tokens:
        tokenStore = tokenStoreFactory(config);

        return {
            /**
             * Gets the next security token from the token queue
             * Causes fresh tokens to be fetched from server, if none available locally
             * Once the token is got, it is erased from the memory (one use only)
             * @returns {Promise<String>} the token value
             */
            getToken: function getToken() {
                var self = this;
                var clientConfigCheck = getConfigStore()
                    .then(function(configStore) {
                        return configStore.getItem('clientConfigFetched')
                            .then(function() {
                                // This doesn't do anything. But for some reason the return value is wrong without a 'then'...
                            });
                    });

                return Promise.all([
                    clientConfigCheck,
                    tokenStore.getSize(),
                    tokenStore.expireOldTokens()
                ])
                .then(function(values) {
                    var clientConfigFetched = !!values[0];
                    var queueSize = values[1];

                    if (queueSize > 0) {
                        // Token available, use it
                        return tokenStore.get().then(function(currentToken) {
                            return currentToken.value;
                        });
                    }
                    else if (!clientConfigFetched) {
                        // Client Config allowed! (first and only time)
                        return self.getClientConfigTokens()
                            .then(function(newTokens) {
                                // Add the fetched tokens to the store, synchronously:
                                // Chaining the promises using Array.prototype.reduce is necessary
                                // to manage token addition & deletion correctly
                                return newTokens.reduce(function(previousPromise, nextToken) {
                                    return previousPromise.then(() => {
                                        return self.setToken(nextToken);
                                    });
                                }, Promise.resolve());
                            })
                            .then(function() {
                                // We assume the store was refilled
                                return tokenStore.get().then(function(currentToken) {
                                    return currentToken.value;
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
             * @returns {Promise<Boolean>} - true if successful
             */
            setToken: function setToken(newToken) {
                return tokenStore.add(newToken)
                    .then(function(added) {
                        return added;
                    });
            },

            /**
             * Extracts tokens from the Client Config which should be received on every page load
             *
             * @returns {Promise<Array>} - an array of locally-timestamped token objects
             */
            getClientConfigTokens() {
                var tokens = _.map(module.config().tokens, function(serverToken) {
                    return {
                        value: serverToken,
                        receivedAt: Date.now()
                    };
                });
                // Store flag in memory saying that this function ran:
                getConfigStore().then(function(configStore) {
                    configStore.setItem('clientConfigFetched', true);
                });

                return Promise.resolve(tokens);
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
