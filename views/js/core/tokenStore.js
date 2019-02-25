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
    'core/store',
], function(_, store) {
    'use strict';

    /**
     * The default number of tokens to store
     */
    var defaultConfig = {
        maxSize: 6,
        tokenTimeLimit: 1000 * 60 * 24
    };

    /**
     * Create a token store
     * @param {Object} [options]
     * @param {Number} [options.maxSize = 6] - the store limit
     * @param {Number} [options.tokenTimeLimit] - time in milliseconds each token remains valid for
     * @returns {tokenStore}
     */
    return function tokenStoreFactory(options) {

        var config = _.defaults(options || {}, defaultConfig);

        //in memory storage
        var getStore = function getStore() {
            return store('tokenStore.tokens', store.backends.memory);
        };

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
                return self.getIndex().then(function(latestIndex) {
                    var key = _.first(latestIndex);
                    if (!key) return Promise.resolve();

                    return getStore()
                    .then(function(storage) {
                        return storage.getItem(key);
                    })
                    .then(function(token) {
                        return self.remove(key).then(function() {
                            return token;
                        });
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
                return getStore().then(function(storage){
                    return storage.setItem(token.value, token);
                })
                .then(function(updated){
                    if (updated) {
                        return self.enforceMaxSize().then(true);
                    }
                    return false;
                });
            },

            /**
             * Generate a new (chronologically-sorted) index from the store contents
             * (because it would not be unique if stored in the module)
             *
             * @returns {Promise<Array>}
             */
            getIndex: function getIndex() {
                return this.getTokens().then(function(tokens) {
                    return _.chain(tokens)
                        .values()
                        .sort(function(t1,t2) {
                            return t1.receivedAt - t2.receivedAt;
                        })
                        .map('value')
                        .value();
                });
            },

            /**
             * Check whether the given token is in the store
             *
             * @param {String} key - token string
             * @returns {Boolean}
             */
            has: function has(key) {
                return this.getIndex().then(function(latestIndex) {
                    return _.contains(latestIndex, key);
                });
            },

            /**
             * Remove the token from the queue and the store
             *
             * @param {String} key - token string
             * @returns {Promise<Boolean>} resolves once removed
             */
            remove: function remove(key) {
                return this.has(key).then(function(result) {
                    if (result) {
                        return getStore()
                            .then(function(storage){
                                return storage.removeItem(key);
                            })
                            .then(function(removed) {
                                return removed;
                            });
                    }
                    return Promise.resolve(false);
                });
            },

            /**
             * Empty the queue and store
             * @returns {Promise}
             */
            clear: function clear() {
                return getStore().then(function(storage){
                    return storage.clear();
                });
            },

            /**
             * Log queue contents & store contents
             */
            log: function log(msg) {
                var self = this;
                return self.getTokens().then(function(items) {
                    return self.getIndex().then(function(latestIndex) {
                        console.log('logging from', msg);
                        console.log('maxSize', config.maxSize);
                        console.log('genIndex', latestIndex);
                        console.table(_.values(items));
                    });
                });
            },

            /**
             * Gets all tokens in the store
             * @returns {Promise<Array>} - token objects
             */
            getTokens: function getTokens() {
                return getStore().then(function(storage) {
                    return storage.getItems();
                });
            },

            /**
             * Gets the current size of the store
             * @returns {Promise<Integer>}
             */
            getSize: function getSize() {
                return this.getIndex().then(function(latestIndex) {
                    return latestIndex.length;
                });
            },

            /**
             * Setter for maximum pool size
             * @param {Integer} size
             */
            setMaxSize: function setMaxSize(size) {
                var self = this;
                if (_.isNumber(size) && size > 0 && size !== config.maxSize) {
                    config.maxSize = size;
                    self.enforceMaxSize();
                }
            },

            /**
             * Removes oldest tokens, if the pool is above its size limit
             * (Could happen if maxSize is reduced during the life of the tokenStore)
             * @returns {Promise} - resolves when done
             */
            enforceMaxSize: function enforceMaxSize() {
                var self = this;
                return this.getIndex().then(function(latestIndex) {
                    var keysToRemove;
                    var excess = latestIndex.length - config.maxSize;
                    if (excess > 0) {
                        keysToRemove = latestIndex.slice(0, excess);
                        return Promise.all(_.map(keysToRemove, function(key) {
                            return self.remove(key);
                        }));
                    }
                    return Promise.resolve(true);
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
