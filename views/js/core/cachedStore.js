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
define([
    'core/store'
], function (store) {
    'use strict';

    /**
     * The default name of the key storage indexing the persisted data
     * @type {String}
     */
    var defaultKey = 'cachedData';

    /**
     * Builds a cached store.
     *
     * Loads a data set from a storage, then maintains persistence of changed values.
     *
     * @param {String} storageName
     * @param {String} storageKey
     * @returns {Promise} Returns a promise that will be resolved with a data accessor
     */
    function cachedStoreFactory(storageName, storageKey) {

        storageKey = storageKey || defaultKey;

        return store(storageName).then(function(storage) {

            return storage.getItem(storageKey)
                .then(function(data) {
                    // the persisted data set is always an object
                    data = data || {};

                    // just provide a data accessor that:
                    // - immediately gets the values
                    // - stores the changes through a promise.
                    return {
                        /**
                         * Gets a value from the data
                         * @param {String} name
                         * @returns {Object}
                         */
                        getItem : function getItem(name) {
                            return data[name];
                        },

                        /**
                         * Sets a value in the data, then ensure the data will persist
                         * @param {String} name
                         * @param {Object} value
                         * @returns {Promise} Returns a promise that will be resolved if the data have been successfully stored
                         */
                        setItem : function setItem(name, value) {
                            data[name] = value;
                            return storage.setItem(storageKey, data);
                        },

                        /**
                         * Removes a value from the data, then synchronise the data set with the storage
                         * @param {String} name
                         * @returns {Promise} Returns a promise that will be resolved if the data have been successfully stored
                         */
                        removeItem : function removeItem(name) {
                            data[name] = undefined;
                            return storage.setItem(storageKey, data);
                        },

                        /**
                         * Clears the full data set
                         * @returns {Promise} Returns a promise that will be resolved if the data have been successfully erased
                         */
                        clear : function clear() {
                            data = {};
                            return storage.removeItem(storageKey);
                        },

                        /**
                         * Delete the database related to the current store
                         * @returns {Promise} with true in resolve once cleared
                         */
                        removeStore : function removeStore() {
                            data = {};
                            return storage.removeStore();
                        }
                    };
                });
        });
    }

    return cachedStoreFactory;
});
