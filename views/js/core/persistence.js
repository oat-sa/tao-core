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
    'core/promise',
    'core/eventifier',
    'core/store'
], function (Promise, eventifier, store) {
    'use strict';

    /**
     * The default name of the key storage indexing the persisted data
     * @type {String}
     */
    var defaultKey = 'persistence';

    /**
     * Builds a data accessor with persistence.
     *
     * Loads data from a storage, then maintains persistence of changed values.
     *
     * @param {String} storageName
     * @param {String} storageKey
     * @returns {Promise} Returns a promise that will be resolved with a data accessor
     */
    function persistenceFactory(storageName, storageKey) {

        storageKey = storageKey || defaultKey;

        return new Promise(function(resolve, reject) {

            store(storageName)
                .then(function(storage) {

                    return storage.getItem(storageKey)
                        .then(function(data) {
                            // just provide a data accessor that:
                            // - immediately gets the values
                            // - stores the changes through a promise.
                            var handler = eventifier({
                                /**
                                 * Gets a value from the data
                                 * @param {String} name
                                 * @returns {Object}
                                 */
                                get : function getPersistenceValue(name) {
                                    return data[name];
                                },

                                /**
                                 * Sets a value in the data, then ensure the data will persist
                                 * @param {String} name
                                 * @param {Object} value
                                 * @returns {Promise} Returns a promise that will be resolved if the data have been successfully stored
                                 * @fires set when the storage has been updated
                                 * @fires error when the storage fails to write the value
                                 */
                                set : function setPersistenceValue(name, value) {
                                    data[name] = value;

                                    var storePromise = storage.setItem(storageKey, data);

                                    storePromise
                                        .then(function() {
                                            handler.trigger('set', name, value);
                                        })
                                        .catch(function(error) {
                                           handler.trigger('error', error);
                                        });

                                    return storePromise;
                                },

                                /**
                                 * Removes a value from the data, then synchronise the data set with the storage
                                 * @param {String} name
                                 * @returns {Promise} Returns a promise that will be resolved if the data have been successfully stored
                                 * @fires remove when the storage has been updated
                                 * @fires error when the storage fails to write the value
                                 */
                                remove : function removePersistenceValue(name) {
                                    data[name] = undefined;

                                    var storePromise = storage.setItem(storageKey, data);

                                    storePromise
                                        .then(function() {
                                            handler.trigger('remove', name);
                                        })
                                        .catch(function(error) {
                                            handler.trigger('error', error);
                                        });

                                    return storePromise;
                                },

                                /**
                                 * Clears the full data set
                                 * @returns {Promise} Returns a promise that will be resolved if the data have been successfully erased
                                 * @fires clear when the storage has been updated
                                 * @fires error when the storage fails to write the value
                                 */
                                clear : function clearPersistence() {
                                    var storePromise = storage.removeItem(storageKey);

                                    data = {};

                                    storePromise
                                        .then(function() {
                                            handler.trigger('clear');
                                        })
                                        .catch(function(error) {
                                            handler.trigger('error', error);
                                        });

                                    return storePromise;
                                }
                            });

                            // the persisted data set is always an object
                            data = data || {};

                            resolve(handler);
                        });

                })
                .catch(reject);

        });
    }

    return persistenceFactory;
});
