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
    'core/promise'
], function(_, Promise) {
    'use strict';

    /**
     * The data required by the plugin loader
     *
     * @typedef {Object} plugindata
     * @property {String} module - AMD module name of the plugin
     * @property {String} bundle - AMD module name of the plugin's bundle
     * @property {String} category - the plugin category
     * @property {String} name - the plugin name
     * @property {String|Number} [plugin.position = 'append'] - append, prepend or plugin position within the category
     */

    /**
     * Creates a loader with the list of required plugins
     * @param {String: Function[]} requiredPlugins - where the key is the category and the value are an array of plugins
     * @returns {loader} the plugin loader
     * @throws TypeError if something is not well formated
     */
    return function pluginLoader(requiredPlugins) {

        /**
         * The list of plugins
         */
        var plugins  = {};

        /**
         * Retains the AMD modules to load
         */
        var modules  = {};

        /**
         * The plugins to exclude
         */
        var excludes = [];

        /**
         * Bundles to require
         */
        var bundles  = [];

        /**
         * The plugin loader
         * @typedef {loader}
         */
        var loader = {

            /**
             * Add a a list of dynamic plugins to load
             * @param {plugindata[]} pluginList - the plugins to add
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            addList : function addList(pluginList){
                _.forEach(pluginList, this.add, this);
                return this;
            },


            /**
             * Add a new dynamic plugin to load
             * @param {plugindata} plugin - the plugin to add
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            add: function add(plugin) {
                if(!_.isString(plugin.module)){
                    throw new TypeError('An AMD module must be defined');
                }
                if(!_.isString(plugin.category)){
                    throw new TypeError('Plugins must belong to a category');
                }

                modules[plugin.category] = modules[plugin.category] || [];

                if(_.isNumber(plugin.position)){
                    modules[plugin.category][plugin.position] = plugin.module;
                }
                else if(plugin.position === 'prepend' || plugin.position === 'before'){
                    modules[plugin.category].unshift(plugin.module);
                } else {
                    modules[plugin.category].push(plugin.module);
                }

                if(plugin.bundle && !_.contains(bundles, plugin.bundle)){
                    bundles.push(plugin.bundle);
                }
                return this;
            },

            /**
             * Append a new dynamic plugin
             * @param {plugindata} plugin - the plugin to add
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            append: function append(plugin){
                return this.add(_.merge({position : 'append'}, plugin));
            },

            /**
             * Prepend a new dynamic plugin to a category
             * @param {plugindata} plugin - the plugin to add
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            prepend: function prepend(plugin) {
                return this.add(_.merge({position : 'prepend'}, plugin));
            },

            /**
             * Remove a plugin from the loading stack
             * @param {String} module - the plugin's module
             * @returns {loader} chains
             * @throws {TypeError} misuse
             */
            remove : function remove(module){
                excludes.push(module);
                return this;
            },

            /**
             * Loads the dynamic plugins : trigger the dependency resolution
             * @param {Boolean} [loadBundles = false] - does load the bundles
             * @returns {Promise}
             */
            load: function load(loadBundles) {
                var self = this;

                //compute the plugins depencies
                var dependencies = _(modules).values().flatten().uniq().difference(excludes).value();

                /**
                 * Load AMD modules and wrap then into a Promise
                 * @param {String[]} amdModules - the list of modules to require
                 * @returns {Promise}
                 */
                var loadModules = function loadModules(amdModules){
                    if(_.isArray(amdModules) && amdModules.length){
                        return new Promise(function(resolve, reject){
                            require(amdModules, function(){
                                //resovle with an array of loaded modules
                                resolve([].slice.call(arguments));
                            }, reject);
                        });
                    }
                    return Promise.resolve();
                };

                // 1. load bundles
                // 2. load dependencies
                // 3. add them to the plugins list
                return loadModules( loadBundles ? bundles : [])
                    .then(function(){
                        return loadModules(dependencies);
                    })
                    .then(function(loadedModules){
                        _.forEach(dependencies, function(dependency, index){
                            var plugin = loadedModules[index];
                            var category = _.findKey(modules, function(val){
                                return _.contains(val, dependency);
                            });
                            if(_.isFunction(plugin) && _.isString(category)){
                                plugins[category] = plugins[category] || [];
                                plugins[category].push(plugin);
                            }
                        });
                        return self.getPlugins();
                    });
            },

            /**
             * Get the resolved plugin list.
             * Load needs to be called before to have the dynamic plugins.
             * @param {String} [category] - to get the plugins for a given category, if not set, we get everything
             * @returns {Function[]} the plugins
             */
            getPlugins: function getPlugins(category) {
                if(_.isString(category)){
                    return plugins[category] || [];
                }

                return _(plugins).values().flatten().uniq().value();
            },

            /**
             * Get the plugin categories
             * @returns {String[]} the categories
             */
            getCategories: function getCategories(){
                return _.keys(plugins);
            }
        };

        //verify and add the required plugins
        _.forEach(requiredPlugins, function(pluginList, category){
            if(!_.isString(category)){
                throw new TypeError('Plugins must belong to a category');
            }

            if(!_.isArray(pluginList) || !_.all(pluginList, _.isFunction)){
                throw new TypeError('A plugin must be an array of function');
            }

            if(plugins[category]){
                plugins[category] = plugins[category].concat(pluginList);
            } else {
                plugins[category] = pluginList;
            }
        });

        return loader;
    };
});
