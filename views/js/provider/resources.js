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
 * The resource data provider
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'util/url',
    'core/promise',
    'core/dataProvider/request',
    'layout/permissions'
], function (_, __, urlUtil, Promise, request, permissionsManager) {
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
        },
        copyTo : {
            //unset because it belongs to sub controllers, see /taoItems/Items/copyInstance,
            //so it needs to be defined
        },
        moveTo : {
            //unset because it belongs to sub controllers, see /taoItems/Items/moveResource,
            //so it needs to be defined
        }
    };

    /**
     * Recursively compute the access mode (permissions) for the given resource hierarchy
     * @param {Object[]} nodes
     * @returns {Object[]} the nodes augmented of the "accessMode=<partial|denied|allowed>" property
     */
    var computeNodeAccessMode = function computeNodeAccessMode(nodes){
        return _.map(nodes, function(node){
            node.accessMode = permissionsManager.getResourceAccessMode(node.uri);
            if(_.isArray(node.children)){
                node.children = computeNodeAccessMode(node.children);
            }
            return node;
        });
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
             * @param {Boonlean} [computePermissions = false] - do we compute the resources permissions
             * @returns {Promise} that resolves with the classes
             */
            getResources : function getResources(params, computePermissions){
                return request(config.getResources.url, params).then(function(results){
                    var resources;
                    var currentRights;

                    if(results && results.resources){
                        resources = results.resources;
                    } else {
                        resources = results;
                    }

                    //each time we retrieve resources,
                    //the list of their permission can come along them
                    //in that case, we update the main permission manager
                    //and compute the permission mode for each received resource
                    //by filling the property "accessMode"
                    if(computePermissions && results.permissions){
                        currentRights = permissionsManager.getRights();

                        if(results.permissions.supportedRights &&
                            results.permissions.supportedRights.length &&
                            currentRights.length === 0) {

                            permissionsManager.setSupportedRights(results.permissions.supportedRights);

                        }
                        if(results.permissions.data){
                            permissionsManager.addPermissions(results.permissions.data);
                        }

                        //compute the mode for each resource
                        if(resources.nodes){
                            resources.nodes = computeNodeAccessMode(resources.nodes);
                        } else {
                            resources = computeNodeAccessMode(resources);
                        }
                    }
                    return resources;
                });
            },

            /**
             * Get the properties of the given resource class
             * @param {String} classUri - the class URI
             * @returns {Promise} that resolves with the classes
             */
            getClassProperties: function getClassProperties(classUri) {
                return request(config.getClassProperties.url, { classUri : classUri });
            },

            /**
             * Copy a resource into another class
             * @param {String} uri - the resource to copy
             * @param {String} destinationClassUri - the destination class
             * @returns {Promise<Object>} resolves with the data of the new resource
             */
            copyTo : function copyTo(uri, destinationClassUri) {
                if(_.isEmpty(config.copyTo.url)){
                    return Promise.reject('Please define the action URL');
                }
                if(_.isEmpty(uri)){
                    return Promise.reject('The URI of the resource to copy must be defined');
                }
                if(_.isEmpty(destinationClassUri)){
                    return Promise.reject('The URI of the destination class must be defined');
                }
                return request(config.copyTo.url, {
                    uri : uri,
                    destinationClassUri : destinationClassUri
                }, 'POST');
            },

            /**
             * Move resources into another class
             * @param {String|String[]} ids - the resources to move
             * @param {String} destinationClassUri - the destination class
             * @returns {Promise<Object>} resolves with the data of the new resource
             */
            moveTo: function moveTo(ids, destinationClassUri) {
                var params = {
                    destinationClassUri: destinationClassUri
                };

                if (!ids) {
                    ids = [];
                } else if (!_.isArray(ids)) {
                    ids = [ids];
                }
                if (ids.length === 1) {
                    params.uri = ids[0];
                } else {
                    params.ids = ids;
                }

                if (_.isEmpty(config.moveTo.url)) {
                    return Promise.reject('Please define the action URL');
                }
                if (_.isEmpty(ids) || _.some(ids, _.isEmpty)) {
                    return Promise.reject('The URI of the resource to move must be defined');
                }
                if (_.isEmpty(destinationClassUri)) {
                    return Promise.reject('The URI of the destination class must be defined');
                }

                return request(config.moveTo.url, params, 'POST');
            }
        };
    };
});
