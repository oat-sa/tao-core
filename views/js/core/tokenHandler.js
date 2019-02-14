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
    'jquery',
    'lodash',
    'i18n',
    'module',
    'ui/feedback',
    'core/tokenStore'
],
function ($, _, __, module, feedback, tokenStoreFactory) {
    'use strict';

    var defaults = {
        maxSize: 6,
        tokenTimeLimit: 1000 * 45 // temporary value
    };

    /**
     * Stores the security token queue
     * @param {Object} [config]
     * @param {String} [config.maxSize]
     * @param {String} [config.tokenTimeLimit]
     * @returns {tokenHandler}
     */
    return function tokenHandlerFactory(config) {

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
                return tokenStore.expireOldTokens().then(function() {
                    if (tokenStore.isEmpty()) {
                        console.log('No valid tokens available!');
                        // Fetch again if we're truly out of tokens
                        return self.getClientConfigTokens()
                            .then(function(tokens) {
                                // Add the fetched tokens to the store, synchronously:
                                // Chaining the promises using Array.prototype.reduce is necessary
                                // to manage token addition & deletion correctly
                                return tokens.reduce(function(previousPromise, nextToken) {
                                    return previousPromise.then(() => {
                                        return self.setToken(nextToken);
                                    });
                                }, Promise.resolve())
                                .then(function() {
                                    return tokenStore.log('tokenHandler.getToken()');
                                })
                                .then(function() {
                                    // Store should be refilled, try to get one token:
                                    if (!tokenStore.isEmpty()) {
                                        return tokenStore.get().then(function(currentToken) {
                                            console.log('tokenHandler.getToken (shift)', currentToken.value);
                                            return currentToken.value;
                                        });
                                    }
                                    else {
                                        return Promise.resolve(null);
                                        // return Promise.reject(new Error('Store not refilled!'));
                                    }
                                });
                            });
                    }
                    else {
                        return tokenStore.get().then(function(currentToken) {
                            console.log('tokenHandler.getToken (shift)', currentToken.value);
                            return currentToken.value;
                        });
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
                console.log('tokenHandler.setToken (push)', newToken);
                return tokenStore.add(newToken)
                    .then(function(added) {
                        tokenStore.log('tokenHandler.setToken()');
                        return added;
                    });
            },

            /**
             * Extracts tokens from the Client Config which should be received on every page load
             *
             * @returns {Promise<Array>} - an array of locally-timestamped token objects
             */
            getClientConfigTokens() {
                console.log('ClientConfig data:', module.config());
                return Promise.resolve(_.map(module.config().tokens, function(serverToken) {
                    return {
                        value: serverToken.value,
                        receivedAt: Date.now()
                    };
                }));
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
             * @returns {Integer}
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
