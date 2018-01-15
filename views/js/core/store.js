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
 * Browser storage, multiple backends
 *
 * @example
 *      store('foo', store.backends.indexDb);
 *         .setItem('hello', { who : 'world'))
 *         .then(function(added){
 *              //yeah!
 *         })
 *         .catch(function(err){
 *              //OOops!
 *         });
 *
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'core/store/localstorage',
    'core/store/indexdb',
    'core/store/memory'
], function(_, Promise, localStorageBackend, indexedDBBackend, memoryBackend){
    'use strict';

    var supportsIndexedDB = false;
    var dectectionDone    = false;

    /**
     * The list of required methods exposed by a store backend
     * @type {String[]}
     */
    var backendApi = ['removeAll', 'getAll', 'getStoreIdentifier'];

    /**
     * The list of required methods exposed by a store implementation
     * @type {String[]}
     */
    var storeApi = ['getItem', 'setItem', 'removeItem', 'clear', 'removeStore'];

    /**
     * Detect IndexedDB support.
     * Due to a bug in Firefox private mode, we need to try to open a database to be sure it's available.
     * @returns {Promise} that resolve the result
     */
    var isIndexDBSupported = function isIndexDBSupported(){
        if(dectectionDone){
            return Promise.resolve(supportsIndexedDB);
        }
        return new Promise(function(resolve){
            var test, indexedDB;
            var done = function done(result){
                supportsIndexedDB = !!result;
                dectectionDone = true;
                return resolve(supportsIndexedDB);
            };
            try {
                indexedDB = window.indexedDB || window.webkitIndexedDB ||
                            window.mozIndexedDB || window.OIndexedDB ||
                            window.msIndexedDB;
                if(!indexedDB){
                    return done(false);
                }

                //we need to try to open a db, for example FF in private browsing will fail.
                test = indexedDB.open('__feature_test', 1);
                test.onsuccess = function(){
                    if(test.result){
                        test.result.close();
                        return done(true);
                    }
                };
                //if we can't open a DB, we assume, we fallback
                test.onerror = function(e) {
                    e.preventDefault();
                    done(false);
                    return false;
                };
            } catch(err) {
                //a sync err, we fallback
                done(false);
            }
        });
    };

    /**
     * Check the backend object complies with the API
     * @param {Object} backend - the backend object to check
     * @returns {Boolean} true if valid
     */
    var isBackendApiValid = function isBackendApiValid(backend) {
        return _.all(backendApi, function methodExists(method){
            return _.isFunction(backend[method]);
        });
    };

    /**
     * Check the storage object complies with the Storage API
     * @param {Storage} storage - the storage object to check
     * @returns {Boolean} true if valid
     */
    var isStorageApiValid = function isStorageApiValid(storage) {
        return _.all(storeApi, function methodExists(method){
            return _.isFunction(storage[method]);
        });
    };

    /**
     * Load the backend based either on the pre-selected and the current support
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise} that resolves with the backend
     */
    var loadBackend = function loadBackend(preselectedBackend) {
        return isIndexDBSupported().then(function(){
            var backend = preselectedBackend || (supportsIndexedDB ? indexedDBBackend : localStorageBackend);

            if(!_.isFunction(backend)){
                return Promise.reject(new TypeError('No backend, no storage!'));
            }
            if(!isBackendApiValid(backend)){
                return Promise.reject(new TypeError('This backend does comply with the store backend API'));
            }
            return backend;
        });
    };

    /**
     * Create a new store
     *
     * @param {String} storeName - the name of the store
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise} that resolves with the Storage a Storage Like instance
     */
    var store = function store(storeName, preselectedBackend) {

        return loadBackend(preselectedBackend).then(function(backend){

            var storeInstance = backend(storeName);

            if(!isStorageApiValid(storeInstance)){
                return Promise.reject(new TypeError('The backend does not comply with the Storage interface'));
            }

            return storeInstance;
        });
    };

    /**
     * The available backends,
     * exposed.
     */
    store.backends = {
        localStorage : localStorageBackend,
        indexDb      : indexedDBBackend,
        memory       : memoryBackend
    };

    /**
     * Removes all storage
     * @param {Function} [validate] - An optional callback that validates the store to delete
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise} with true in resolve once cleaned
     */
    store.removeAll = function removeAll(validate, preselectedBackend) {
        return loadBackend(preselectedBackend).then(function(backend){
            return backend.removeAll(validate);
        });
    };

    /**
     * Get the name/key of all storages
     * @param {Function} [validate] - An optional callback that validates the store
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise<String[]>} resolves with the names of the stores
     */
    store.getAll = function getAll(validate, preselectedBackend) {
        return loadBackend(preselectedBackend).then(function(backend){
            return backend.getAll(validate);
        });
    };

    /**
     * Get the identifier of either the current (or the pre-selected store)
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise} that resolves with the identifier
     */
    store.getIdentifier = function getIdentifier(preselectedBackend) {
        return loadBackend(preselectedBackend).then(function(backend){
            return backend.getStoreIdentifier();
        });
    };

    return store;
});
