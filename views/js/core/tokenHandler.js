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
 */
define([
    'jquery',
    'lodash'
],
function ($, _) {
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

        // Initialise queue, empty queue will produce a null token
        var tokenQueue = [];

        config = _.defaults({}, config, defaults);

        if (config.initialToken) {
            tokenQueue.push({
                value: config.initialToken,
                receivedAt: Date.now()
            });
        }

        // Hardcode cookie tokens
        $.cookie('tao_tokens', 'a;b;c;d;e');

        if (tokenQueue.length === 0) {
            setQueue(readCookieTokens('tao_tokens'));
            console.info('Q:', tokenQueue);
        }

        /**
         * Reads token strings from a cookie
         * @param {String} name - name of the cookie
         * @returns {Array}
         */
        function readCookieTokens(name) {
            var tokenList = $.cookie(name);
            $.cookie(name, '');
            if (tokenList) {
                return tokenList.split(';');
            }
            return [];
        }

        /**
         * Sets the whole queue of tokens in one go
         * @param {Array} tokens - the token strings
         */
        function setQueue(tokens) {
            tokenQueue = _.map(tokens, function(token) {
                return {
                    value: token,
                    receivedAt: Date.now()
                };
            });
        }

        /**
         * Checks if a token in the pool is expired
         * @param {String} token
         * @returns {Boolean}
         */
        function isExpired(token) {
            return Date.now() - token.receivedAt > config.tokenTimeLimit;
        }

        return {
            /**
             * Gets the next security token from the token queue
             * Once the token is got, it is erased from the memory (one use only)
             * @returns {String}
             */
            getToken: function getToken() {
                var currentToken = tokenQueue.length ? tokenQueue.shift().value : null ;
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
            }
        };
    }

    return tokenHandlerFactory;
});
