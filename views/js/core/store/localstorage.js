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
 * LocalStorage backend of the client store
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'core/promiseQueue',
    'lib/uuid'
], function(_, Promise, promiseQueue, uuid){
    'use strict';

    /**
     * Prefix all databases
     * @type {String}
     */
    var prefix = 'tao-store-';

    /**
     * Alias to the Storage API
     * @type {Storage}
     */
    var storage = window.localStorage;

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

    var writingQueue = promiseQueue();

    /**
     * Set an entry into a store
     * @param {String} storeName - unprefixed store name
     * @param {String} key - entry key
     * @param {*} value - the value to set
     * @returns {Promise<Boolean>}
     */
    var setEntry = function setEntry(storeName, key, value) {
        return new Promise(function(resolve, reject){
            try{
                storage.setItem(prefix + storeName + '.' + key, JSON.stringify(value));
                resolve(true);
            } catch(ex){
                reject(ex);
            }
        });
    };

    /**
     * Get an entry from a store
     * @param {String} storeName - unprefixed store name
     * @param {String} key - entry key
     * @returns {Promise<*>} resolves with the value
     */
    var getEntry = function getEntry(storeName, key) {
        return new Promise(function(resolve, reject){
            var value;
            try{
                value = storage.getItem(prefix + storeName + '.' + key);
                if(value === null){
                    resolve();
                } else {
                    resolve(JSON.parse(value));
                }
            } catch(ex){
                reject(ex);
            }
        });
    };

    /**
     * Gets access to the store that contains the index of known stores.
     * @returns {Promise}
     */
    var getKnownStores = function getKnownStores() {
        return getEntry(knownStoresName, 'stores');
    };

    /**
     * Adds a store into the index of known stores.
     * @param {String} storeName
     * @returns {Promise<Boolean>}
     */
    var registerStore = function registerStore(storeName) {
        return getKnownStores()
            .then(function(stores){
                stores = stores || {};
                stores[storeName] = {
                    name : storeName,
                    lastOpen : Date.now()
                };
                return setEntry(knownStoresName, 'stores', stores);
            })
        ;
    };

    /**
     * Removes a store from the index of known stores.
     * @param {String} storeName
     * @returns {Promise<Boolean>}
     */
    var unregisterStore = function unregisterStore(storeName) {
        return getKnownStores()
            .then(function(stores){
                stores = stores || {};
                delete stores[storeName];
                return setEntry(knownStoresName, 'stores', stores);
            })
        ;
    };

    /**
     * Open and access a store
     * @param {String} storeName - the store name to open
     * @returns {Object} the store backend
     * @throws {TypeError} without a storeName
     */
    var localStorageBackend = function localStorageBackend(storeName){

        var name;
        var registered = false;

        var openStore = function openStore(){
            if(registered){
                return Promise.resolve();
            }
            return registerStore(storeName)
                    .then(function(){
                        registered = true;
                    });
        };
        if(_.isEmpty(storeName) || !_.isString(storeName)){
            throw new TypeError('The store name is required');
        }

        //prefix all storage entries to avoid global keys confusion
        name = prefix + storeName + '.';


        /**
         * The store
         */
        return {

            /**
             * Get an item with the given key
             * @param {String} key
             * @returns {Promise} with the result in resolve, undefined if nothing
             */
            getItem : function getItem(key){
                return writingQueue.serie(function(){
                    return openStore().then(function(){
                        return getEntry(storeName, key);
                    });
                });
            },

            /**
             * Set an item with the given key
             * @param {String} key - the item key
             * @param {*} value - the item value
             * @returns {Promise} with true in resolve if added/updated
             */
            setItem :  function setItem(key, value){
                return writingQueue.serie(function(){
                    return openStore().then(function(){
                        return setEntry(storeName, key, value);
                    });
                });
            },

            /**
             * Remove an item with the given key
             * @param {String} key - the item key
             * @returns {Promise} with true in resolve if removed
             */
            removeItem : function removeItem(key){
                return writingQueue.serie(function(){
                    return openStore().then(function(){
                        storage.removeItem(name + key);
                        return true;
                    });
                });
            },

            /**
             * Get all store items
             * @returns {Promise<Object>} with a collection of items
             */
            getItems: function getItems() {
                var keyPattern = new RegExp('^' + name);
                return writingQueue.serie(function(){
                    return openStore().then(function(){
                        return  _(storage)
                            .map(function(entry, index){
                                return storage.key(index);
                            })
                            .filter(function(key){
                                return keyPattern.test(key);
                            })
                            .reduce(function(acc, key){
                                var value;
                                var exposedKey = key.replace(name, '');
                                try {
                                    value = storage.getItem(key);
                                    if(value !== null){
                                        acc[exposedKey] = JSON.parse(value);
                                    }
                                }
                                catch(ex){
                                    acc[exposedKey] = null;
                                }
                                return acc;
                            }, {});
                    });
                });
            },

            /**
             * Clear the current store
             * @returns {Promise} with true in resolve once cleared
             */
            clear : function clear(){
                var keyPattern = new RegExp('^' + name);
                return writingQueue.serie(function(){
                    return openStore().then(function(){
                        _(storage)
                            .map(function(entry, index){
                                return storage.key(index);
                            })
                            .filter(function(key){
                                return keyPattern.test(key);
                            })
                            .forEach(function(key){
                                storage.removeItem(key);
                            });
                        return true;
                    });
                });
            },

            /**
             * Delete the database related to the current store
             * @returns {Promise} with true in resolve once cleared
             */
            removeStore : function removeStore() {
                return this.clear().then(function(){
                    return unregisterStore(storeName);
                });
            }
        };
    };

    /**
     * Removes all storage
     * @param {Function} [validate] - An optional callback that validates the store to delete
     * @returns {Promise} with true in resolve once cleaned
     */
    localStorageBackend.removeAll = function removeAll(validate) {
        if (!_.isFunction(validate)) {
            validate = null;
        }
        return getKnownStores().then(function(stores){
            var removing = _(stores)
                .filter(function(store, storeName){
                    return validate ? validate(storeName, store) : true;
                })
                .map(function(store){
                    if(store && store.name){
                        return localStorageBackend(store.name).removeStore();
                    }
                    return Promise.resolve();
                })
                .value();

            return Promise.all(removing);
        });
    };


    /**
     * Get all stores
     * @param {Function} [validate] - An optional callback that validates the stores to retrieve
     * @returns {Promise<String[]>} resolves with the list of stores
     */
    localStorageBackend.getAll = function getAll(validate) {
        return getKnownStores().then(function(stores){
            return _(stores)
                .filter(function(store, storeName){
                    return validate ? validate(storeName, store) : true;
                })
                .map(function(store){
                    return store.name;
                })
                .value();
        });
    };

    /**
     * Get the identifier of the storage
     * @returns {Promise} that resolves with the store identifier
     */
    localStorageBackend.getStoreIdentifier = function getStoreIdentifier(){
        var idStore = localStorageBackend(idStoreName);

        //we use the storeName also as the id
        return idStore.getItem(idStoreName).then(function(id){
            if(!_.isEmpty(id)){
                return id;
            }
            id = uuid();

            return idStore.setItem(idStoreName, id).then(function(){
                return id;
            });
        });
    };

    return localStorageBackend;
});
