/*
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
 * Copyright (c) 2017 Open Assessment Technologies SA
 */

/**
 * Cache/store for tokens on memory as a FIFO list
 * Modeled on taoQtiTest/views/js/runner/proxy/cache/itemStore.js
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'lodash',
    'core/store'
], function(_, store) {
    'use strict';

    /**
     * The default number of tokens to store
     */
    var defaultConfig = {
        maxSize: 6,
        tokenTimeLimit: 1000 * 3600
    };

    /**
     * Create a token store
     * @param {Object} [options]
     * @param {Number} [options.maxSize = 6] - the store limit
     * @param {String} [options.tokenTimeLimit]
     * @returns {tokenStore}
     */
    return function tokenStoreFactory(options) {

        var config = _.defaults(options || {}, defaultConfig);

        //in memory storage
        var getStore = function getStore(){
            return store('tokenStore', store.backends.memory);
        };

        // maintain an index which will act as a queue
        // push newly received token(s) onto the back end
        // shift oldest token off the front end, to use
        var index = [];

        /**
         * @typedef tokenStore
         */
        return {

            /**
             * Get the oldest token from the queue
             * Remove its store entry as well
             *
             * @returns {Promise<Object>} the token object
             */
            get: function get() {
                var self = this;
                var key = index.shift();
                return getStore().then(function(tokenStore){
                    return tokenStore.getItem(key).then(function(token) {
                        self.remove(key);
                        return token;
                    });
                });
            },

            /**
             * Add a new token object to the queue
             * Add an entry to the store as well
             *
             * @param {Object} token - the token object
             * @param {String} [token.value] - long alphanumeric string
             * @param {Number} [token.receivedAt] - timestamp
             * @returns {Promise<Boolean>} - true if added
             */
            add: function add(token) {
                var self = this;
                return getStore().then(function(tokenStore){
                    return tokenStore.setItem(token.value, token)
                        .then(function(updated){
                            var oldest;
                            if (updated) {
                                if (!_.contains(index, token.value)) {
                                    index.push(token.value);
                                }

                                // Did we reach the limit? then remove the oldest
                                if (index.length > 1 && index.length > config.maxSize) {
                                    oldest = _.first(index);
                                    return self.remove(oldest).then(function(removed){
                                        return updated && removed;
                                    });
                                }
                                return true;
                            }
                            return false;
                        });
                });
            },

            /**
             * Check whether the given token is in the store
             *
             * @param {String} key - token string
             * @returns {Boolean}
             */
            has: function has(key) {
                return _.contains(index, key);
            },

            /**
             * Remove the token from the queue and the store
             *
             * @param {String} key - token string
             * @returns {Promise<Boolean>} resolves once removed
             */
            remove: function remove(key) {
                if(this.has(key)){
                    return getStore().then(function(tokenStore){

                        index = _.without(index, key);

                        return tokenStore.removeItem(key);
                    });
                }
                return Promise.resolve(false);
            },

            /**
             * Empty the queue and store
             * @returns {Promise}
             */
            clear: function clear() {
                return getStore().then(function(tokenStore){
                    index = [];
                    return tokenStore.clear();
                });
            },

            /**
             * Log queue contents & store contents
             */
            log: function log() {
                this.getTokens().then(function(items) {
                    console.info('Q2i', index);
                    console.table(items);
                });
            },

            /**
             * Get the current size of the queue
             * @returns {Number}
             */
            getSize: function getSize() {
                return index.length;
            },

            /**
             * Checks if the queue is currently empty
             * @returns {Boolean}
             */
            isEmpty: function isEmpty() {
                return index.length === 0;
            },

            /**
             * Gets all tokens in the store
             * @returns {Promise<Array>} - token objects
             */
            getTokens: function getTokens() {
                return getStore().then(function(tokenStore){
                    return tokenStore.getItems();
                });
            },

            /**
             * Checks all the tokens in the store and removes them if expired
             * @returns {Promise}
             */
            expireOldTokens: function expireOldTokens() {
                var self = this;
                return this.getTokens().then(function(tokens) {

                    return Promise.all(_.map(_.values(tokens), function(token) {

                        if (Date.now() - token.receivedAt > config.tokenTimeLimit) {
                            return self.remove(token.value).then(function(removed) {
                                return removed;
                            });
                        }
                        return Promise.resolve(true);
                    }));
                });
            }
        };
    };
});
