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

    /**
     * Let you access to platform themes
     * @exports ui/themes
     */
    return {

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
            var config = module.config();
            
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
        }
    };
});
