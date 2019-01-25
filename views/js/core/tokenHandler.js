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
        maxPoolSize: 1, // TR should set this to 1 to force sequential AJAX requests
        tokenTimeLimit: 1000 * 60 * 15
    };

    /**
     * Stores the security token queue
     * @param {Object} [config]
     * @param {String} [config.initialToken]
     * @param {String} [config.maxPoolSize]
     * @param {String} [config.tokenTimeLimit]
     * @returns {tokenHandler}
     */
    return function tokenHandlerFactory(config) {
        //console.log('tokenHandlerFactory()'); // FIXME: called 3 times on TR load

        // Initialise queue, empty queue will produce a null token
        var tokenStorage;
        config = _.defaults({}, config, defaults);
        tokenStorage = tokenStoreFactory({ maxSize: config.maxPoolSize });

        return {
            /**
             * Gets the next security token from the token queue
             * Causes fresh tokens to be fetched from server, if none available locally
             * Once the token is got, it is erased from the memory (one use only)
             * @returns {Promise<Object>} the token object
             */
            getToken: function getToken() {
                var self = this;
                return tokenStorage.expireOldTokens().then(function() {
                    if (tokenStorage.isEmpty()) {
                        // Fetch again if we're truly out of tokens
                        return self.fetchNewTokens()
                            .then(function(tokens) {
                                var added;
                                // TODO: Must add the tokens 1 by 1, not asynchronously
                                _.forEach(tokens, function(token) {
                                    added = tokenStorage.add(token); // true
                                });

                                tokenStorage.log();
                                return tokenStorage.get().then(function(currentToken) {
                                    console.log('tokenHandler.getToken (shift)', currentToken);
                                    return currentToken;
                                });
                            });
                    }
                    else {
                        return tokenStorage.get().then(function(currentToken) {
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
             * @returns {Object} - this
             */
            setToken: function setToken(newToken) {
                var self = this;
                return tokenStorage.add(newToken)
                    .then(function() {
                        console.log('tokenHandler.setToken (push)', newToken);
                        tokenStorage.log();
                        return self;
                    });
            },

            /**
             * Makes a request to the CSRF tokens endpoint for a new set of tokens
             *
             * @returns {Promise} - array of tokens
             */
            fetchNewTokens: function fetchNewTokens() {
                return new Promise(function(resolve, reject){
                    $.ajax({
                        // url: 'http://127.0.0.1:3697/csrf-tokens',
                        url: '/tao/ClientConfig/tokens',
                        //dataType: 'json',
                        data : null,
                    })
                    .success(function(response) {
                        console.log('ClientConfig response', JSON.parse(response));
                        resolve(JSON.parse(response));
                    })
                    .error(function() {
                        feedback().error(__('No tokens retrieved'));
                        reject([]);
                    });
                });
            },

            /**
             * Getter for the current queue length
             * @returns {Integer}
             */
            getQueueLength: function getQueueLength() {
                return tokenStorage.getSize();
            },

            /**
             * Set the max size of the internal tokenHandler's token pool
             * @param {Number} size - the new token pool size
             */
            // setMaxPoolSize: function setMaxPoolSize(size) {
            //     config.maxPoolSize = size;
            // }
        };
    };
});
