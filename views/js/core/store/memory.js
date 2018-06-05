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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * In Memory backend of the client store
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'lib/uuid'
], function(_, Promise, uuid){
    'use strict';

    /**
     * where data dwelves
     */
    var memoryStore = {};

    /**
     * The storage identifier
     */
    var idStore;

    /**
     * Open and access a store
     * @param {String} storeName - the store name to open
     * @returns {Object} the store backend
     * @throws {TypeError} without a storeName
     */
    var memoryStorageBackend = function memoryStorageBackend(storeName){

        if(_.isEmpty(storeName) || !_.isString(storeName)){
            throw new TypeError('The store name is required');
        }

        memoryStore[storeName] = memoryStore[storeName] || {};

        /**
         * The store
         */
        return {

            /**
             * Get an item with the given key
             * @param {String} key
             * @returns {Promise} with the result in resolve, undfined if nothing
             */
            getItem : function getItem(key){
                if (! _.isPlainObject(memoryStore[storeName])) {
                    return Promise.resolve();
                }
                return Promise.resolve( memoryStore[storeName][key] );
            },

            /**
             * Set an item with the given key
             * @param {String} key - the item key
             * @param {*} value - the item value
             * @returns {Promise} with true in resolve if added/updated
             */
            setItem : function setItem(key, value){
                if (! _.isPlainObject(memoryStore[storeName])) {
                    memoryStore[storeName] = {};
                }
                memoryStore[storeName][key] = value;
                return Promise.resolve(true);
            },

            /**
             * Remove an item with the given key
             * @param {String} key - the item key
             * @returns {Promise} with true in resolve if removed
             */
            removeItem : function removeItem(key){
                memoryStore[storeName] = _.omit(memoryStore[storeName], key);
                return Promise.resolve(typeof memoryStore[storeName][key] === 'undefined');
            },

            /**
             * Get all store items
             * @returns {Promise<Object>} with a collection of items
             */
            getItems : function getItems(){
                return Promise.resolve(memoryStore[storeName]);
            },

            /**
             * Clear the current store
             * @returns {Promise} with true in resolve once cleared
             */
            clear : function clear(){
                memoryStore[storeName] = {};
                return Promise.resolve(true);
            },

            /**
             * Delete the database related to the current store
             * @returns {Promise} with true in resolve once cleared
             */
            removeStore : function removeStore() {
                memoryStore = _.omit(memoryStore, storeName);
                return Promise.resolve(typeof memoryStore[storeName] === 'undefined');
            }
        };
    };

    /**
     * Removes all storage
     * @param {Function} [validate] - An optional callback that validates the store to delete
     * @returns {Promise} with true in resolve once cleaned
     */
    memoryStorageBackend.removeAll = function removeAll(validate) {
        if (!_.isFunction(validate)) {
            validate = null;
        }
        memoryStore = _.omit(memoryStore, function(store, storeName){
            return validate ? validate(storeName) : true;
        });
        return Promise.resolve(true);
    };

    /**
     * Get all stores
     * @param {Function} [validate] - An optional callback that validates the stores to retrieve
     * @returns {Promise<String[]>} resolves with the list of stores
     */
    memoryStorageBackend.getAll = function getAll(validate) {
        var storeNames = [];
        if (!_.isFunction(validate)) {
            validate = null;
        }
        storeNames = _(memoryStore)
            .map(function(store, storeName){
                return storeName;
            })
            .filter(function(storeName){
                return validate ? validate(storeName) : true;
            })
            .value();

        return Promise.resolve(storeNames);
    };

    /**
     * Get the identifier of the storage
     * @returns {Promise} that resolves with the store identifier
     */
    memoryStorageBackend.getStoreIdentifier = function getStoreIdentifier(){

        //we use the storeName also as the id
        if(_.isEmpty(idStore)){
            idStore = uuid();
        }
        return Promise.resolve(idStore);
    };

    return memoryStorageBackend;
});
