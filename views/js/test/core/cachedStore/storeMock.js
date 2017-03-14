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
    'core/promise'
], function (Promise) {
    'use strict';

    var _stores = {};

    var _config = {};

    /**
     * Fakes a persistent storage
     * @param {String} name
     * @returns {Promise}
     */
    function storeMock(name) {
        return new Promise(function(resolve, reject) {
            var data = _stores[name] || {};
            var config = _config[name] || {};
            _stores[name] = data;

            if (config.failedStore) {
                reject(new Error('Cannot access storage!'));
            } else {
                resolve({
                    getItem : function getItem(key){
                        if (config.failedGet) {
                            return Promise.reject(new Error('Cannot access storage!'));
                        }

                        return Promise.resolve(data[key]);
                    },
                    setItem : function setItem(key, value){
                        if (config.failedSet) {
                            return Promise.reject(new Error('Cannot access storage!'));
                        }

                        data[key] = value;
                        return Promise.resolve(true);
                    },
                    removeItem : function removeItem(key){
                        if (config.failedRemove) {
                            return Promise.reject(new Error('Cannot access storage!'));
                        }

                        delete data[key];
                        return Promise.resolve(true);
                    },
                    clear : function clear(){
                        if (config.failedClear) {
                            return Promise.reject(new Error('Cannot access storage!'));
                        }

                        _stores[name] = data = {};
                        return Promise.resolve(true);
                    },
                    removeStore : function removeStore(){
                        if (config.failedRemoveStore) {
                            return Promise.reject(new Error('Cannot access storage!'));
                        }

                        data = {};
                        _stores[name] = null;
                        return Promise.resolve(true);
                    }
                });
            }
        });
    }

    /**
     * Sets the mock config
     * @param {String} name
     * @param {Object} config
     * @param {Boolean} [config.failedStore] The mock will reject the promise when creating a new instance
     * @param {Boolean} [config.failedGet] The mock will reject the promise when reading a value
     * @param {Boolean} [config.failedSet] The mock will reject the promise when setting a value
     * @param {Boolean} [config.failedRemove] The mock will reject the promise when removing a value
     * @param {Boolean} [config.failedClear] The mock will reject the promise when clearing a value
     */
    storeMock.setConfig = function setConfig(name, config) {
        if (name && config) {
            _config[name] = config;
        }
    };

    return storeMock;
});
