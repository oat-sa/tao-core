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
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/collections'
], function ($, _, collections) {
    'use strict';

    var _defaults = {
        eraseOnRead: false,
        keys: []
    };

    /**
     * Filters the data with respect to the list of handled entries
     * @param {Object} data
     * @param {Map} [keys]
     * @returns {Object}
     */
    function filter(data, keys) {
        var filteredData = {};

        if (keys && keys.size) {
            _.forEach(data, function(value, key) {
                var descriptor = keys.get(key);
                if (descriptor && (!_.isFunction(descriptor.validate) || descriptor.validate(value) !== false)) {
                    filteredData[key] = value;
                }
            });
        } else {
            _.merge(filteredData, data);
        }

        return filteredData;
    }

    /**
     * Builds the list of keys descriptors from the config
     * @param {Map} keys
     * @param {Array} config
     */
    function buildKeys(keys, config) {
        _.forEach(config, function(entry) {
            if (_.isString(entry)) {
                entry = {
                    key: entry
                };
            }
            if (entry.key) {
                keys.set(entry.key, entry);
            }
        });
    }

    /**
     * Defines an HTML data proxy implementation.
     * Will request the DOM to fetch data.
     */
    return {
        name: 'htmlData',

        /**
         * Initializes the proxy, sets the implemented actions.
         *
         * @param {Object} config
         * @param {String|jQuery|HTMLElement} config.container - The DOM element that contains the data-xxx attributes.
         * @param {Array} [config.keys] - The list data entries to address. Can be a list of strings, or a list of
         * descriptors that will contain the name of the entry and a callback to validate the content.
         * A full descriptor looks like:
         * ```
         * {
         *      name: "foo",
         *      validate: function(data) {
         *          // should return false if the data is not valid
         *      }
         * }
         * ```
         * @param {Boolean} [config.eraseOnRead] - Erase the data-attribute in the DOM after read (default: false)
         */
        init: function htmlDataInit(config) {
            var self = this;
            var keys = new collections.Map();

            // extracts the data from the dom
            this.get = function get(params) {
                var data = filter(this.$container.data(), keys);
                var filterKeys = params && params.keys;

                if (filterKeys) {
                    data = _.pick(data, params.keys);
                }

                if (config.eraseOnRead) {
                    _.forEach(data, function(value, key) {
                        self.$container.removeData(key);
                    });
                }

                return data;
            };

            // write the data to the dom
            this.set = function set(data) {
                var output = filter(data, keys);
                _.forEach(output, function(value, key) {
                    self.$container.data(key, value);
                });

                return output;
            };

            // erase the data by keys
            this.erase = function erase(list) {
                _.forEach(list, function(key) {
                    self.$container.removeData(key).removeAttr(key);
                });
            };

            _.defaults(config, _defaults);
            buildKeys(keys, config.keys);
            this.$container = $(config.container);

            // remove the container from the config to avoid memory leak
            config.container = null;
        },

        /**
         * Cleans up the instance when destroying
         */
        destroy: function htmlDataDestroy() {
            this.$container = null;
            this.get = null;
            this.set = null;
            this.erase = null;
        },

        /**
         * Sets the params as data-attributes into the DOM
         * @param {Object} params
         */
        create: function htmlDataCreate(params) {
            var data = this.get(params);
            this.erase(_.keys(data));
            return this.set(params);
        },

        /**
         * Extracts the data from the DOM
         * @param {Object} params
         * @param {Array} [params.keys] - The list of entries to read
         */
        read: function htmlDataRead(params) {
            return this.get(params);
        },

        /**
         * Sets the params as data-attributes into the DOM
         * @param {Object} params
         */
        write: function htmlDataWrite(params) {
            return this.set(params);
        },

        /**
         * Removes all the data from the DOM
         * @param {Object} params
         * @param {Array} [params.keys] - The list of entries to remove
         */
        remove: function htmlDataRemove(params) {
            var data = this.get(params);
            this.erase(_.keys(data));
        }
    };
});
