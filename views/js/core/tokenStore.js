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
 * Copyright (c) 2019 Open Assessment Technologies SA
 */

/**
 * Store for tokens in memory as a FIFO list
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
     * @param {Number} [options.tokenTimeLimit] - time in milliseconds each token remains valid for
     * @param {String} [options.initialToken] - first token to put in the store
     * @returns {tokenStore}
     */
    return function tokenStoreFactory(options) {

        // maintain an index which will act as a queue
        // push newly received token(s) onto the back end
        // shift oldest token off the front end, to use
        var index = [];
        var config = _.defaults(options || {}, defaultConfig);

        //in memory storage
        var getStore = function getStore() {
            return store('tokenStore.tokens', store.backends.memory);
        };
        var getSizeStore = function getSizeStore() {
            return store('tokenStore.maxSize', store.backends.memory);
        };

        // retrieve stored maxSize
        getSizeStore()
        .then(function(sizeStore) {
            return sizeStore.getItem('size');
        })
        .then(function(maxSize) {
            if (maxSize) {
                config.maxSize = maxSize;
            }
            console.warn('tokenStore established with maxSize', config.maxSize);

        });

        if (config.initialToken) {
            this.add({
                value: config.initialToken,
                receivedAt: Date.now()
            });
        }

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
                var key = _.first(index);
                if (!key) return Promise.resolve();

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
             * @param {String} token.value - long alphanumeric string
             * @param {Number} token.receivedAt - timestamp
             * @returns {Promise<Boolean>} - true if added
             */
            add: function add(token) {
                var self = this;
                // Handle legacy param type:
                if (_.isString(token)) {
                    token = {
                        value: token,
                        receivedAt: Date.now()
                    };
                }
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
                                        self.log('tokenStore.add()');
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
                if (this.has(key)) {
                    return getStore().then(function(tokenStore){
                        return tokenStore.removeItem(key)
                            .then(function(removed) {
                                index = _.without(index, key);
                                return removed;
                            });
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
            log: function log(msg) {
                return this.getTokens().then(function(items) {
                    console.log('logging from', msg);
                    console.log('maxSize', config.maxSize);
                    console.log('Q', index);
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
             * Setter for maximum pool size
             * @param {Integer} size
             */
            setMaxSize: function setMaxSize(size) {
                var self= this;
                if (_.isNumber(size) && size > 0 && size !== config.maxSize) {
                    config.maxSize = size;
                    getSizeStore().then(function(sizeStore) {
                        sizeStore.setItem('size', size);
                        console.warn('tokenStore maxSize set to', size);
                        self.enforceMaxSize();
                    });
                }
            },

            /**
             * Removes oldest tokens, if the pool is above its size limit
             * (Could happen if maxSize is reduced during the life of the tokenStore)
             * @returns {Promise} - resolves when done
             */
            enforceMaxSize: function enforceMaxSize() {
                var keysToRemove;
                var excess = this.getSize() - config.maxSize;
                if (excess > 0) {
                    keysToRemove = index.slice(0, excess);
                    return Promise.all(_.map(keysToRemove, function(key) {
                        return self.remove(key);
                    }));
                }
                return Promise.resolve();
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
             * Checks one token and removes it from the store if expired
             * @param {Object} token - the token object
             * @returns {Promise<Boolean>}
             */
            checkExpiry: function checkExpiry(token) {
                var self = this;
                if (Date.now() - token.receivedAt > config.tokenTimeLimit) {
                    return self.remove(token.value).then(function(removed) {
                        return removed;
                    });
                }
                return Promise.resolve(true);
            },

            /**
             * Checks all the tokens in the store to see if they expired
             * @returns {Promise<Boolean>} - resolves to true
             */
            expireOldTokens: function expireOldTokens() {
                var self = this;
                return self.getTokens().then(function(tokens) {
                    // Check each token's expiry, synchronously:
                    return Object.values(tokens).reduce(function(previousPromise, nextToken) {
                        return previousPromise.then(() => {
                            return self.checkExpiry(nextToken);
                        });
                    }, Promise.resolve())
                    .then(function() {
                        // All done
                        return true;
                    });
                });
            }
        };
    };
});
