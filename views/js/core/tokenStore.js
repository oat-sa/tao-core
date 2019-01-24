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
        maxSize: 6
    };

    /**
     * Create a token store
     * @param {Object} [options]
     * @param {Number} [options.maxSize = 6] - the store limit
     *
     * @returns {tokenStore}
     */
    return function tokenStoreFactory(options) {

        var config = _.defaults(options || {}, defaultConfig);

        //in memory storage
        var getStore = function getStore(){
            return store('tokens', store.backends.memory);
        };

        //maintain an index which will act as a queue
        var index = [];

        /**
         * @typedef tokenStore
         */
        return {

            /**
             * Get the oldest token from the queue
             * @returns {Promise<Object>} the token object
             */
            get: function get() {
                var key = index.shift();
                return getStore().then(function(tokenStorage){
                    return tokenStorage.getItem(key);
                });
            },

            /**
             * Add a new token object to the queue
             * @param {Object} token - the token object
             * @param {String} [token.value] - long alphanumeric string
             * @param {Number} [token.createdAt] - timestamp
             * @returns {Promise<Boolean>} chains
             */
            add: function add(token) {
                var self = this;
                return getStore().then(function(tokenStorage){
                    return tokenStorage.setItem(token.value, token)
                        .then(function(updated){
                            if(updated){
                                if(!_.contains(index, token.value)){
                                    index.push(token.value);
                                }
                            }

                            // Did we reach the limit? then remove the oldest
                            if (index.length > 1 && index.length > config.maxSize) {
                                return self.getOldest().then(function(oldest) {
                                    console.log('lose:', oldest);
                                    return self.remove(oldest.value).then(function(removed){
                                        return updated && removed;
                                    });
                                });
                            }

                            console.log('max', config.maxSize, 'got', index.length);
                            return updated;
                        });

                });
            },

            /**
             * Check whether the given token is in the store
             * @param {String} key - something identifier
             * @returns {Boolean}
             */
            has: function has(key) {
                return _.contains(index, key);
            },

            /**
             * Remove the token from the store
             * @param {String} key - something identifier
             * @returns {Promise<Boolean>} resolves once removed
             */
            remove: function remove(key) {
                if(this.has(key)){
                    return getStore().then(function(tokenStorage){

                        index = _.without(index, key);

                        return tokenStorage.getItem(key)
                            .then(function(){
                                return tokenStorage.removeItem(key);
                            });
                    });
                }
                return Promise.resolve(false);
            },

            /**
             * Empties the store
             * @returns {Promise}
             */
            clear: function clear() {
                return getStore().then(function(tokenStorage){
                    index = [];
                    return tokenStorage.clear();
                });
            },

            log: function log() {
                console.log('Q2i', index);
                this.getItems().then(function(items) {
                    console.log(Object.assign({}, items));
                });
            },

            /**
             * Gets the current size of the pool
             * @returns {Number}
             */
            getSize: function getSize() {
                return index.length;
            },

            /**
             * Checks if the pool is currently empty
             * @returns {Boolean}
             */
            isEmpty: function isEmpty() {
                return index.length === 0;
            },

            /**
             * Gets all tokens in the pool
             * @returns {Promise<Object>} - token objects
             */
            getItems: function getItems() {
                return getStore().then(function(tokenStorage){
                    return tokenStorage.getItems();
                });
            },

            /**
             * Finds the oldest token in the pool by creation date
             * @returns {Promise<Object>} - the token object
             */
            getOldest: function getOldest() {
                return this.getItems().then(function(tokens) {
                    return _.chain(tokens)
                        .values()
                        .sortBy('createdAt')
                        .first()
                        .value();
                });
            },

            /**
             * Checks all the tokens in the pool and removes them if expired
             */
            expireOldTokens: function expireOldTokens() {
                this.getItems().then(function(tokens) {
                    _.forEach(tokens, function(token) {
                        if (Date.now() - token.receivedAt > config.tokenTimeLimit) {
                            if (self.remove(token.value)) console.log('expired', token.value);
                        }
                    });
                });
            }
        };
    };
});
