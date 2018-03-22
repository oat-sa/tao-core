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
 * Copyright (c) 2016-2018 (original work) Open Assessment Technologies SA ;
 */

/**
 * Browser storage, multiple backends
 *
 * @example
 *      store('foo', store.backends.indexedDB);
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
    'moment',
    'module',
    'core/logger',
    'core/promise',
    'core/store/localstorage',
    'core/store/indexeddb',
    'core/store/memory'
], function(_, moment, module, loggerFactory, Promise, localStorageBackend, indexedDBBackend, memoryBackend){
    'use strict';

    var supportsIndexedDB = false;
    var dectectionDone    = false;
    var quotaChecked      = false;

    /**
     * The exported store module, can be used as a function to get one store
     * or as an object to run methods on multiple stores.
     *
     * @type {Function|Object}
     */
    var store;

    /**
     * The list of required methods exposed by a store backend
     * @type {String[]}
     */
    var backendApi = ['removeAll', 'getAll', 'getStoreIdentifier'];

    /**
     * The list of required methods exposed by a store implementation
     * @type {String[]}
     */
    var storeApi = ['getItem', 'setItem', 'removeItem', 'getItems', 'clear', 'removeStore'];

    /**
     * Dedicated logger
     */
    var logger = loggerFactory('core/store');

    /**
     * Main config
     */
    var config = _.defaults(module.config() || {}, {

        /**
         * Percent of used space (ie. 80% used)
         * to consider the browser as having low space
         * @type {Number}
         */
        lowSpaceRatio : 80,

        /**
         * Default duration thresholds to invalidate stores
         *
         * @type {Object<String>} ISO 8601  duration
         */
        invalidation : {
            //candidate for invalidation if we're going over quota
            staled : 'P2W',

            //candidate for upfront invalidation if estimates are low
            oldster : 'P2M'
        }
    });

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
     * Check storage estimates and invalidate old
     * Estimates aren't widely supported,
     * but that worth to try it (progressive enhancement)
     */
    var checkQuotas = function checkQuotas(){
        if(!quotaChecked && 'storage' in window.navigator && window.navigator.storage.estimate){
            window.navigator.storage.estimate()
                .then(function(estimate){
                    var usedRatio = 0;
                    if (_.isNumber(estimate.usage) &&
                        _.isNumber(estimate.quota) &&
                        estimate.quota > 0){

                        usedRatio = (estimate.usage / estimate.quota);
                        if(usedRatio > config.lowSpaceRatio){
                            logger.warn('The browser storage is getting low ' + usedRatio.toFixed(2) + '% used', estimate);
                            logger.warn('We will attempt to clean oldster databases in persistent backends');
                            store.cleanUpSpace(config.invalidation.oldster, [], localStorageBackend);
                            if(isIndexDBSupported){
                                store.cleanUpSpace(config.invalidation.oldster, [],indexedDBBackend);
                            }
                        } else {
                            logger.debug('Browser storage estimate : ' + usedRatio.toFixed(2) + '% used', estimate);
                        }
                    }
                })
                .catch(function(err){
                    logger.warn('Unable to retrieve quotas : ' + err.message);
                });
        }
        quotaChecked = true;
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
                return Promise.reject(new TypeError('This backend doesn\'t comply with the store backend API'));
            }

            //attempt to check the quotas
            if(backend !== memoryBackend){
                checkQuotas();
            }

            return backend;
        });
    };

    /**
     * Loads a store
     *
     * @param {String} storeName - the name of the store
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise} that resolves with the Storage a Storage Like instance
     */
    store = function storeLoader(storeName, preselectedBackend) {

        return loadBackend(preselectedBackend).then(function(backend){

            var storeInstance = backend(storeName);

            if(!isStorageApiValid(storeInstance)){
                return Promise.reject(new TypeError('The store doesn\'t comply with the Storage interface'));
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
        indexedDB    : indexedDBBackend,
        memory       : memoryBackend
    };

    /**
     * Removes all storage
     * @param {validateStore} [validate] - An optional callback that validates the store to delete
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise} with true in resolve once cleaned
     */
    store.removeAll = function removeAll(validate, preselectedBackend) {
        return loadBackend(preselectedBackend).then(function(backend){

            /**
             * @callback validateStore
             * @param {String} storeName - the name of the store
             * @param {Object} store - the store details
             */
            return backend.removeAll(validate);
        });
    };

    /**
     * Clean up storage meeting the invalidation conditions
     * @param {Number|String} [since] - unix timestamp in ms or ISO duration, to compare with lastOpen
     * @param {RegExp} [storeNamePattern] - applies only on store names that matches the pattern
     * @param {Object} [preselectedBackend] - the backend to force the selection
     * @returns {Promise<Boolean>}
     */
    store.cleanUpSpace = function cleanUpSpace(since, storeNamePattern, preselectedBackend) {
        var tsThreshold;

        /**
         * Create the invalidation callback
         * @type {validateStore}
         */
        var invalidate = function invalidate(storeName, storeEntry){

            if(!storeName || !storeEntry){
                return false;
            }

            //storeName matches ?
            if ( storeNamePattern instanceof RegExp &&
                ! storeNamePattern.test(storeName) ){

                return false;
            }
            return  _.isNumber(storeEntry.lastOpen) &&
                    _.isNumber(tsThreshold) &&
                    storeEntry.lastOpen <= tsThreshold;
        };

        if(_.isNumber(since) && since > 0){
            tsThreshold = since;
        } else {
            if(!_.isString(since)){
                since = config.invalidation.oldster;
            }
            tsThreshold = moment().subtract(moment.duration(since)).valueOf();
        }

        logger.info('Trying to remove stores lastly opened before ' + tsThreshold + '(' + since + ')');

        return store.removeAll(invalidate, preselectedBackend);
    };

    /**
     * Get the name/key of all storages
     * @param {validateStore} [validate] - An optional callback that validates the store
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
