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
 * Loads plugins
 *
 * It provides 2 distinct way of loading plugins :
 *  1. The "required" plugins that are necessary. Provided as factory (function)
 *  2. The "dynamic" plugins that are loaded on demand, they are provided as AMD modules. The module is loaded using the load method.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/moduleLoader'
], function(_, moduleLoaderFactory) {
    'use strict';

    /**
     * Creates a loader with the list of required plugins
     * @param {String: Function[]} requiredPlugins - where the key is the category and the value are an array of plugins
     * @returns {loader} the plugin loader
     * @throws TypeError if something is not well formated
     */
    return function pluginLoaderFactory(requiredPlugins) {
        return moduleLoaderFactory(requiredPlugins, _.isFunction, {
            /**
             * Get the resolved plugin list.
             * Load needs to be called before to have the dynamic plugins.
             * @param {String} [category] - to get the plugins for a given category, if not set, we get everything
             * @returns {Function[]} the plugins
             */
            getPlugins: function getPlugins(category) {
                return this.getModules(category);
            }
        });
    };
});
