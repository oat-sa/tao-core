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
    'jquery.cookie'
],
function ($, _, __, feedback) {
    'use strict';

    var defaults = {
        maxPoolSize: 6, // TR should set this to 1 to force sequential AJAX requests
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
    function tokenHandlerFactory(config) { // initialToken) {
        console.log('tokenHandlerFactory()'); // called 3 times on TR load

        // Initialise queue, empty queue will produce a null token
        var tokenQueue = [];

        config = _.defaults({}, config, defaults);

        // if (config.initialToken) {
        //     tokenQueue.push({
        //         value: config.initialToken,
        //         receivedAt: Date.now()
        //     });
        // }

        // Hardcode cookie tokens
        //$.cookie('tao_tokens', 'a;b;c;d;e;f;g;h;i;j;k;l'); // these basic tokens work in backend, not in TR
        //$.cookie('tao_tokens', null);

        // if (tokenQueue.length === 0) {
        //     console.log('initial cookie read');
        //     setQueue(readCookieTokens('tao_tokens'));
        //     console.info('Q:', tokenQueue);
        // }

        /**
         * Reads token strings from a cookie
         * @param {String} name - name of the cookie
         * @returns {Array}
         */
        // function readCookieTokens(name) {
        //     console.log('reading cookie');
        //     var tokenList = $.cookie(name);
        //     $.cookie(name, null);
        //     if (tokenList) {
        //         console.log('Found', tokenList.split(';').length, 'new tokens in token cookie');
        //         return tokenList.split(';');
        //     }
        //     return [];
        // }

        /**
         * Sets the whole queue of tokens in one go
         * (but won't exceed maxPoolSize)
         * @param {Array} tokenStrings - the token strings
         */
        function setQueue(tokenStrings) {
            tokenQueue = _.chain(tokenStrings)
                .map(function(token) {
                    return {
                        value: token,
                        receivedAt: Date.now()
                    };
                })
                .take(config.maxPoolSize)
                .value();
        }

        /**
         * Checks if a token in the pool is expired
         * @param {String} token
         * @returns {Boolean}
         */
        // function isExpired(token) {
        //     return Date.now() - token.receivedAt > config.tokenTimeLimit;
        // }

        return {
            /**
             * Gets the next security token from the token queue
             * Once the token is got, it is erased from the memory (one use only)
             * @returns {String}
             */
            getToken: function getToken() {
                var currentToken;
                console.log('getToken');
                if (tokenQueue.length === 0) {
                    // check the cookie again if we're truly out of tokens
                    //setQueue(readCookieTokens('tao_tokens'));
                    this.fetchNewTokens()
                        .then(function(tokens) {
                            setQueue(_.map(tokens, 'value'));
                            console.info('Q:', tokenQueue);
                            currentToken = tokenQueue.length ? tokenQueue.shift().value : null;
                            console.log('tokenHandler.getToken (shift)', currentToken);
                            return currentToken;
                        });
                }
                currentToken = tokenQueue.length ? tokenQueue.shift().value : null;
                console.log('tokenHandler.getToken (shift)', currentToken);
                return currentToken;
            },

            /**
             * Adds a new security token to the token queue
             * Deletes old tokens to keep queue within maximum pool size
             * @param {String} newToken
             * @returns {Object} - this
             */
            setToken: function setToken(newToken) {
                // check against max pool size, if queue is full we should bump oldest token
                while (tokenQueue.length >= config.maxPoolSize) {
                    tokenQueue.shift();
                }
                tokenQueue.push({
                    value: newToken,
                    receivedAt: Date.now()
                });
                console.log('tokenHandler.setToken (push)', newToken);
                console.info('Q:', tokenQueue);
                return this;
            },

            /**
             * Makes a request to the CSRF tokens endpoint for a new set of tokens
             *
             * @returns {Promise} - array of tokens
             */
            fetchNewTokens: function fetchNewTokens() {
                return new Promise(function(resolve, reject){
                    $.ajax({
                        url: 'http://127.0.0.1:3697/csrf-tokens',
                        dataType: 'json',
                        data : null,
                    })
                    .success(function(response) {
                        resolve(response);
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
                return tokenQueue.length;
            }

            /**
             * Set the max size of the internal tokenHandler's token pool
             * @param {Number} size - the new token pool size
             */
            // setMaxPoolSize: function setMaxPoolSize(size) {
            //     config.maxPoolSize = size;
            // }
        };
    }

    return tokenHandlerFactory;
});
