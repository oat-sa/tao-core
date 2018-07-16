/*
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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA
 *
 */

/**
 * Themes configuration, enables you to access the available themes.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'module'
], function(_, module){
    'use strict';

    var themesConfig;
    var defaultNamespacePrefix = 'items';

    /**
     * Let you access to platform themes
     * @exports ui/themes
     */
    return {

        /**
         * Gets module config clone. Checks if there are differences between actual module config and a clone. If needed -
         * clones it again.
         *
         * @returns {Object}
         */
        getConfig : function getConfig() {
            var initialConfig;

            if(!themesConfig){
                initialConfig = module.config();
                themesConfig = _.cloneDeep(initialConfig);
            }
            return themesConfig;
        },

        /**
         * Gets default namespace prefix - currently 'items'
         *
         * @returns {string}
         */
        getDefaultNamespacePrefix : function getDefaultNamespacePrefix() {
            return defaultNamespacePrefix;
        },

        /**
         * Get the themes config.
         * @example themes().get('items');
         *
         * If the config contains a activeNamespace property (for example, 'ns1'), then it will appended to the requested key
         * For example, this will actually returns entries registered in 'items_ns1'
         * @example themes().get('items');
         *
         * Namespace can by manually specified by a parameter. In that case, activeNamespace property is ignored.
         * @example themes().get('items', 'ns2');
         *
         * @param {String} what - themes are classified, what is the theme for ?
         * @param {String} [ns] - namespace of the 'what'
         * @returns {Object?} the themes config
         */
        get : function get(what, ns){
            var config = this.getConfig();

            if (ns) {
                what += '_' + ns;

            } else if (config.activeNamespace && config[what + '_' + config.activeNamespace]) {
                what += '_' + config.activeNamespace;
            }
            if(_.isPlainObject(config[what])){
                return config[what];
            }
        },

        /**
         * Get the list of available themes.
         *
         * @example themes().getAvailable('items');
         *
         * If the config contains a activeNamespace property (for example, 'ns1'), then it will appended to the requested key
         * For example, this will actually returns entries registered in 'items_ns1'
         * @example themes().getAvailable('items');
         *
         * Namespace can by manually specified by a parameter. In that case, activeNamespace property is ignored.
         * @example themes().getAvailable('items', 'ns2');
         * *
         * @param {String} what - themes are classified, what is the theme for ?
         * @param {String} [ns] - namespace of the 'what'
         * @returns {Array} the themes
         */
        getAvailable : function getAvailable(what, ns){
            var available = [];
            var themes = this.get(what, ns);
            if(themes && _.isArray(themes.available)){
                available = themes.available;
            }
            return available;
        },

        /**
         * Get active namespace for themes
         *
         * Get activeNamespace property value from the config
         *
         * @returns {String} activeNamespace
         */
        getActiveNamespace : function getActiveNamespace(){
            return this.getConfig().activeNamespace;
        },

        /**
         * Set active namespace for themes
         *
         * Explicitly sets activeNamespace property into config.
         *
         * @param {String} ns - activeNamespace value to be set into config
         */
        setActiveNamespace : function setActiveNamespace(ns){
            this.getConfig().activeNamespace = ns;
        },

        /**
         * Gets the current theme data from config
         *
         * @param {String} what - if provided themes data is loaded for provided argument. If not - will return "default" data - for `items`
         *
         * @example themes().getCurrentThemeData('items');
         * @example themes().getCurrentThemeData();
         * Both examples will return same data. Next example will load the theme data for 'platform' (if it exists):
         * @example themes().getCurrentThemeData('platform');
         *
         * @returns {Object} the current theme data
         */
        getCurrentThemeData : function getCurrentThemeData(what){
            var themeNamespace = this.getActiveNamespace();

            if (!what) {
                what = this.getDefaultNamespacePrefix();
            }
            return this.get(what, themeNamespace);
        }
    };
});
