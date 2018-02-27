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
 * IndexedDB backend of the client store
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'lib/store/idbstore',
    'lib/uuid'
], function(_, Promise, IDBStore, uuid) {
    'use strict';

    /**
     * Prefix all databases
     * @type {String}
     */
    var prefix = 'tao-store-';

    /**
     * Access to the index of known stores.
     * This index is needed to maintain the list of stores created by TAO, in order to apply an auto clean up.
     * @type {Promise}
     */
    var knownStores;

    /**
     * The name of the store that contains the index of known stores.
     * @type {String}
     */
    var knownStoresName = 'index';

    /**
     * The name of the store that contains the store id
     * @type {String}
     */
    var idStoreName = 'id';

    /**
     * Check if we're using the v2 of IndexedDB
     * @type {Boolean}
     */
    var isIndexedDB2 = 'getAll' in IDBObjectStore.prototype;

    /**
     * Opens a store
     * @returns {Promise} with store instance in resolve
     */
    var openStore = function openStore(storeName) {
        return new Promise(function(resolve, reject) {
            var store = new IDBStore({
                dbVersion: 1,
                storeName: storeName,
                storePrefix: prefix,
                keyPath: 'key',
                autoIncrement: true,
                onStoreReady: function onStoreReady() {
                    // auto closes when the changed version reflects a DB deletion
                    store.db.onversionchange = function onversionchange(e) {
                        if (!e || !e.newVersion) {
                            store.db.close();
                        }
                    };
                    resolve(store);
                },
                onError: reject
            });
        });
    };

    /**
     * Sets an entry into a particular store
     * @param store
     * @param key
     * @param value
     * @returns {Promise}
     */
    var setEntry = function setEntry(store, key, value) {
        return new Promise(function(resolve, reject) {
            var entry = {
                key: key,
                value: value
            };
            var success = function success(returnKey) {
                resolve(returnKey === key);
            };
            store.put(entry, success, reject);
        });
    };

    /**
     * Gets an entry from a particular store
     * @param store
     * @param key
     * @returns {Promise}
     */
    var getEntry = function getEntry(store, key) {
        return new Promise(function(resolve, reject) {
            var success = function success(entry) {
                if (!entry || typeof entry.value === 'undefined') {
                    return resolve(entry);
                }

                resolve(entry.value);
            };
            store.get(key, success, reject);
        });
    };

    /**
     * Get entries from a store
     * @param store
     * @returns {Promise<Object>} entries
     */
    var getEntries = function getEntries(store) {
        return new Promise(function(resolve, reject) {
            var success = function success(entries) {
                if (! _.isArray(entries)) {
                    return resolve({});
                }

                resolve(_.reduce(entries, function(acc, entry){
                    if(entry.key && entry.value){
                        acc[entry.key] = entry.value;
                    }
                    return acc;
                }, {}));
            };
            store.getAll(success, reject);
        });
    };

    /**
     * Remove an entry from a particular store
     * @param store
     * @param key
     * @param value
     * @returns {Promise}
     */
    var removeEntry = function removeEntry(store, key) {
        return new Promise(function(resolve, reject) {
            var success = function success(result) {
                resolve(result !== false);
            };
            store.remove(key, success, reject);
        });
    };

    /**
     * Gets access to the store that contains the index of known stores.
     * @returns {Promise}
     */
    var getKnownStores = function getKnownStores() {
        if (!knownStores) {
            knownStores = openStore(knownStoresName);
        }
        return knownStores;
    };

    /**
     * Adds a store into the index of known stores.
     * @param {String} storeName
     * @returns {Promise}
     */
    var registerStore = function registerStore(storeName) {
        return getKnownStores().then(function(store) {
            return setEntry(store, storeName, {
                name : storeName,
                lastOpen : Date.now()
            });
        });
    };

    /**
     * Removes a store from the index of known stores.
     * @param {String} storeName
     * @returns {Promise}
     */
    var unregisterStore = function unregisterStore(storeName) {
        return getKnownStores().then(function(store) {
            return removeEntry(store, storeName);
        });
    };

    /**
     * Deletes a store, then removes it from the index of known stores.
     * @param store
     * @param storeName
     * @returns {Promise}
     */
    var deleteStore = function deleteStore(store, storeName) {
        return new Promise(function(resolve, reject) {
            var success = function success() {
                unregisterStore(storeName)
                    .then(function() {
                        resolve(true);
                    })
                    .catch(reject);
            };
            //with old implementation, deleting a store is
            //either unsupported or buggy
            if(isIndexedDB2){
                store.deleteDatabase(success, reject);
            } else {
                store.clear(success, reject);
            }
        });
    };

    /**
     * Open and access a store
     * @param {String} storeName - the store name to open
     * @returns {Object} the store backend
     * @throws {TypeError} without a storeName
     */
    var indexDbBackend = function indexDbBackend(storeName) {

        //keep a ref of the running store
        var innerStore;

        /**
         * Get the store
         * @returns {Promise} with store instance in resolve
         */
        var getStore = function getStore() {
            if (!innerStore) {
                innerStore = openStore(storeName).then(function(store) {
                    return registerStore(storeName).then(function() {
                        return Promise.resolve(store);
                    });
                });
            }
            return innerStore;
        };

        //keep a ref to the promise actually writing
        var writePromise;

        /**
         * Ensure write promises are executed in series
         * @param {Function} getWritingPromise - the function that run the promise
         * @returns {Promise} the original one
         */
        var ensureSerie = function ensureSerie(getWritingPromise) {

            //first promise, keep the ref
            if (!writePromise) {
                writePromise = getWritingPromise();
                return writePromise;
            }

            //create a wrapping promise
            return new Promise(function(resolve, reject) {
                //run the current request
                var runWrite = function() {
                    var p = getWritingPromise();
                    writePromise = p; //and keep the ref
                    p.then(resolve).catch(reject);
                };

                //wait the previous to resolve or fail and run the current one
                writePromise.then(runWrite).catch(runWrite);
            });
        };

        if (_.isEmpty(storeName) || !_.isString(storeName)) {
            throw new TypeError('The store name is required');
        }

        /**
         * The store
         */
        return {

            /**
             * Get an item with the given key
             * @param {String} key
             * @returns {Promise} with the result in resolve, undefined if nothing
             */
            getItem: function getItem(key) {
                return ensureSerie(function getWritingPromise() {
                    return getStore().then(function(store) {
                        return getEntry(store, key);
                    });
                });
            },

            /**
             * Set an item with the given key
             * @param {String} key - the item key
             * @param {*} value - the item value
             * @returns {Promise} with true in resolve if added/updated
             */
            setItem: function setItem(key, value) {
                return ensureSerie(function getWritingPromise() {
                    return getStore().then(function(store) {
                        return setEntry(store, key, value);
                    });
                });
            },

            /**
             * Remove an item with the given key
             * @param {String} key - the item key
             * @returns {Promise} with true in resolve if removed
             */
            removeItem: function removeItem(key) {
                return ensureSerie(function getWritingPromise() {
                    return getStore().then(function(store) {
                        return removeEntry(store, key);
                    });
                });
            },

            /**
             * Get all store items
             * @returns {Promise<Object>} with a collection of items
             */
            getItems: function getItems() {
                return ensureSerie(function getWritingPromise() {
                    return getStore().then(function(store) {
                        return getEntries(store);
                    });
                });
            },

            /**
             * Clear the current store
             * @returns {Promise} with true in resolve once cleared
             */
            clear: function clear() {
                return ensureSerie(function getWritingPromise() {
                    return getStore().then(function(store) {
                        return new Promise(function(resolve, reject) {
                            var success = function success() {
                                resolve(true);
                            };
                            store.clear(success, reject);
                        });
                    });
                });
            },

            /**
             * Delete the database related to the current store
             * @returns {Promise} with true in resolve once cleared
             */
            removeStore: function removeStore() {
                return ensureSerie(function getWritingPromise() {
                    return getStore().then(function(store) {
                        return deleteStore(store, storeName);
                    });
                });
            }
        };
    };

    /**
     * Removes all storage
     * @param {Function} [validate] - An optional callback that validates the store to delete
     * @param {Function} [backend] - An optional storage handler to use
     * @returns {Promise} with true in resolve once cleaned
     */
    indexDbBackend.removeAll = function removeAll(validate) {
        if (!_.isFunction(validate)) {
            validate = null;
        }
        return getKnownStores().then(function(store) {
            return new Promise(function(resolve, reject) {
                function cleanUp(entries) {
                    var all = [];
                    _.forEach(entries, function(entry) {
                        var storeName = entry && entry.key;
                        if (storeName) {
                            all.push(openStore(storeName).then(function(storeToRemove) {
                                if (!validate || validate(storeName, entry.value)) {
                                    return deleteStore(storeToRemove, storeName);
                                }
                            }));
                        }
                    });

                    Promise.all(all).then(resolve).catch(reject);
                }
                store.getAll(cleanUp, reject);
            });
        });
    };

    /**
     * Get all storage
     * @param {Function} [validate] - An optional callback that validates the store to delete
     * @param {Function} [backend] - An optional storage handler to use
     * @returns {Promise} with true in resolve once cleaned
     */
    indexDbBackend.getAll = function getAll(validate) {
        if (!_.isFunction(validate)) {
            validate = function valid() {
                return true;
            };
        }
        return getKnownStores().then(function(store) {
            return new Promise(function(resolve, reject) {
                store.getAll(function(entries) {

                    var storeNames = _(entries)
                        .filter(function(entry) {
                            return entry && entry.key && validate(entry.key, entry.value);
                        })
                        .map(function(entry){
                            return entry.key;
                        })
                        .value();

                    return resolve(storeNames);
                }, reject);
            });
        });
    };

    /**
     * Get the identifier of the storage
     * @returns {Promise} that resolves with the store identifier
     */
    indexDbBackend.getStoreIdentifier = function getStoreIdentifier() {

        return openStore(idStoreName)
            .then(function(store) {
                return getEntry(store, idStoreName).then(function(id) {
                    if (!_.isEmpty(id)) {
                        return id;
                    }
                    id = uuid();

                    return setEntry(store, idStoreName, id).then(function() {
                        return id;
                    });
                });
            });
    };

    return indexDbBackend;
});
