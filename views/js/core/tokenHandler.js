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
define(function () {
    'use strict';

    /**
     * Stores the security token
     * @param {String} initialToken
     * @returns {tokenHandler}
     */
    function tokenHandlerFactory(initialToken) {
        //var tokenTimeLimit = 10000;
        //var token = initialToken || null;
        // Initialise queue, empty queue will produce a null token
        var tokenQueue = [];
        if (initialToken) {
            tokenQueue.push({
                value: initialToken,
                receivedAt: Date.now()
            });
        }

        // function isExpired(token) {
        //     return Date.now() - token.receivedAt < tokenTimeLimit;
        // }

        // time out tokens here?

        return {
            /**
             * Gets the current security token.
             * Once the token is got, it is erased from the memory and a new token must be provided.
             * @returns {String}
             */
            getToken: function getToken() {
                //var currentToken = token;
                //token = null;
                var currentToken = tokenQueue.length ? tokenQueue.shift().value : null ;
                console.log('tokenHandler.getToken (shift)', currentToken);
                return currentToken;
            },

            /**
             * Sets the current security token
             * @param {String} newToken
             * @returns {Object} - this
             */
            setToken: function setToken(newToken) {
                //token = newToken;
                tokenQueue.push({
                    value: newToken,
                    receivedAt: Date.now()
                });
                console.log('tokenHandler.setToken (push)', newToken);
                return this;
            }
        };
    }

    return tokenHandlerFactory;
});
