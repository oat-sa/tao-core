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
    'ui/feedback',
    'core/tokenStore'
],
function ($, _, __, feedback, tokenStoreFactory) {
    'use strict';

    var defaults = {
        maxSize: 4, // TR should set this to 1 to force sequential AJAX requests
        tokenTimeLimit: 1000 * 15
    };

    /**
     * Stores the security token queue
     * @param {Object} [config]
     * @param {String} [config.maxPoolSize]
     * @param {String} [config.tokenTimeLimit]
     * @returns {tokenHandler}
     */
    return function tokenHandlerFactory(config) {

        // Initialise queue, empty queue will produce a null token
        var tokenStore;
        config = _.defaults({}, config, defaults);
        tokenStore = tokenStoreFactory(config);

        return {
            /**
             * Gets the next security token from the token queue
             * Causes fresh tokens to be fetched from server, if none available locally
             * Once the token is got, it is erased from the memory (one use only)
             * @returns {Promise<Object>} the token object
             */
            getToken: function getToken() {
                var self = this;
                return tokenStore.expireOldTokens().then(function() {
                    if (tokenStore.isEmpty()) {
                        // Fetch again if we're truly out of tokens
                        return self.fetchNewTokens()
                            .then(function(tokens) {
                                // Add the fetched tokens to the store (async):
                                return Promise.all(
                                    _.map(tokens, function(token) {
                                        return self.setToken(token);
                                    })
                                )
                                .then(function() {
                                    return tokenStore.log();
                                })
                                .then(function() {
                                    // Store should be refilled, try to get one token:
                                    if (!tokenStore.isEmpty()) {
                                        return tokenStore.get().then(function(currentToken) {
                                            console.log('tokenHandler.getToken (shift)', currentToken);
                                            return currentToken;
                                        });
                                    }
                                    else {
                                        throw new Error('Store not refilled!');
                                    }
                                });
                            });
                    }
                    else {
                        return tokenStore.get().then(function(currentToken) {
                            console.log('tokenHandler.getToken (shift)', currentToken);
                            return currentToken;
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
                return tokenStore.add(newToken)
                    .then(function(added) {
                        console.log('tokenHandler.setToken (push)', newToken);
                        return added;
                    });
            },

            /**
             * Makes a request to the CSRF tokens endpoint for a new set of tokens
             *
             * @returns {Promise<Array>} - an array of locally-timestamped token objects
             */
            fetchNewTokens: function fetchNewTokens() {
                return new Promise(function(resolve, reject){
                    $.ajax({
                        url: '/tao/ClientConfig/tokens',
                        //dataType: 'json',
                        data : null,
                        success: function(response) {
                            console.log('ClientConfig response', JSON.parse(response));
                            resolve(_.map(JSON.parse(response), function(token) {
                                return {
                                    value: token.value,
                                    receivedAt: Date.now()
                                };
                            }));
                        },
                        error: function() {
                            feedback().error('No tokens retrieved'); // TODO: improve
                            reject([]);
                        }
                    });
                });
            },

            /**
             * Getter for the current queue length
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
            }

        };
    };
});
