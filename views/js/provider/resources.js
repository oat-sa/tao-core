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
            url : urlUtil.route('create', 'RestFormItem', 'taoItems')
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
         * @typedef {resoureProvider}
         */
        return {

            /**
             * Get the list of classes and sub classes
             * @param {String} classUri - the root class URI
             * @returns {Promise} that resolves with the classes
             */
            getClasses: function getClasses(classUri, params){
                return request(config.getClasses.url, {
                    classUri : classUri
                });

                //.then(function(results){
                    //var format = function format(entry){
                        //var newEntry = {
                            //uri : entry.attributes['data-uri'],
                            //label : entry.data
                        //};
                        //if(entry.children && entry.children.length){
                            //newEntry.children = _.map(entry.children, format);
                        //}
                        //return newEntry;
                    //};
                    //if(!_.isArray(results)){
                        //results = [results];
                    //}
                    //return _.map(results, format);
                //});
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
            getItemClassProperties: function getItemClassProperties(classUri) {
                return request(config.getItemClassProperties.url, { classUri : classUri });
            }
        };
    };
});
