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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

/**
 * The testItem data provider
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'util/url',
    'core/dataProvider/request'
], function (_, __, urlUtil, request) {
    'use strict';

    /**
     * Per function requests configuration.
     */
    var defaultConfig = {
        getClasses : {
            url : urlUtil.route('getAll', 'RestClass', 'tao')
        },
        getResources : {
            url : urlUtil.route('getAll', 'RestResource', 'tao')
        },
        getClassProperties : {
            url : urlUtil.route('create', 'RestResource', 'tao')
        }
    };

    /**
     * Creates a configured provider
     *
     * @param {Object} [config] - to override the default config
     * @returns {resourceProvider} the new provider
     */
    return function resourceProviderFactory(config){

        config = _.defaults(config || {}, defaultConfig);

        /**
         * @typedef {resourceProvider}
         */
        return {

            /**
             * Get the list of classes and sub classes
             * @param {String} classUri - the root class URI
             * @returns {Promise} that resolves with the classes
             */
            getClasses: function getClasses(classUri){
                return request(config.getClasses.url, { classUri : classUri });
            },

            /**
             * Get QTI Items in different formats
             * @param {Object} [params] - the parameters to pass through the request
             * @returns {Promise} that resolves with the classes
             */
            getResources : function getResources(params){
                return request(config.getResources.url, params);
            },

            /**
             * Get the properties of a the given item class
             * @param {String} classUri - the item class URI
             * @returns {Promise} that resolves with the classes
             */
            getClassProperties: function getClassProperties(classUri) {
                return request(config.getClassProperties.url, { classUri : classUri });
            }
        };
    };
});
