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
 * Copyright (c) 2018 Open Assessment Technologies SA;
 */

/**
 * Manage resources and actions permissions.
 * The model is based on actions expecting some permissions (requiredRights) for a given parameter.
 * Then each resource has it's own permissions (READ, WRITE, GRANT) that we store.
 *
 * We can check if a resource has a given permission (can read for example)
 * or to validate a context that must contains all required parameters.
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'uri',
], function(_, uriUtil){
    'use strict';

    /**
     * store permissions per resource
     * @type {Object}
     */
    var permissionStore = {};

    /**
     * The list of supported rights
     * @type {String[]}
     */
    var supportedRights = [];

    /**
     * The permissions manager
     * @typedef {Object} permissionsManager
     */
    var permissionsManager = {

        /**
         * set the rights, none by defaults
         * @param {String[]} rights
         * @returns {permissionsManager} chains
         */
        setSupportedRights : function setSupportedRights(rights){
            if (_.isArray(rights)) {
                supportedRights = _.filter(rights, _.isString);
            }
        },

        /**
         * Get the current rights
         * @returns {String[]} the rights
         */
        getRights : function getRights(){
            return supportedRights;
        },

        /**
         * Check if the given right is supported
         * @param {String} right - the right to check
         * @returns {Boolean}
         */
        isSupported : function isSupported(right){
            return _.contains(supportedRights, right);
        },

        /**
         * Add permissions to the store.
         *
         * Polymorphic.
         * @example permissionsManager.addPermissions('http://uri.foo/a', ['READ', 'WRITE']);
         * @example permissionsManager.addPermissions({
         *      'http://uri.foo/a' : ['READ', 'WRITE'],
         *      'http://uri.foo/b' : ['READ']
         *  });
         *
         *
         * @param {String} [uri] - the resource URI
         * @param {Array|Object} permissions - either an object where the keys are the URIs or directly the permissions
         * @returns {permissionsManager} chains
         */
        addPermissions : function addPermissions(uri, permissions){
            if(_.isString(uri) && _.isArray(permissions)){
                permissionStore[uri] = _.intersection(permissions, _.values(this.getRights()));
            }

            if(_.isUndefined(permissions) && _.isPlainObject(uri)){
                permissions = uri;
                _.forEach(permissions, function(value, key){
                    this.addPermissions(key, value);
                }, this);
            }

            return this;
        },

        /**
         * Retrieve the permissions for the given resource
         * @param {String} uri - the resource URI
         * @returns {Array} the permissions
         */
        getPermissions : function getPermissions(uri){
            return permissionStore[uri];
        },

        /**
         * Check if the given resource has the permission
         * @param {String} uri - the resource URI
         * @param {String} permission - the permission to check
         * @returns {Boolean}
         */
        hasPermission : function hasPermission(uri, permission){

            //if no right is defined, it's open bar
            if( supportedRights.length === 0 ) {
                return true;
            }
            if(typeof permissionStore[uri] !== 'undefined'){
                return _.contains(permissionStore[uri], permission);
            }
            return false;
        },

        /**
         * Clear all permissions
         * @returns {permissionsManager} chains
         */
        clear : function clear(){
            permissionStore = {};
            return this;
        },

        /**
         * Check if the given context is allowed to execute an action with required rights.
         * @param {Object} requiredRights - the action required rights (parameterName : permission)
         * @param {Object} resourceContext - the context to verify
         * @returns {Boolean}
         */
        isContextAllowed : function isContextAllowed(requiredRights, resourceContext){
            var self    = this;
            if(! requiredRights || _.size(requiredRights) === 0 || supportedRights.length === 0){
                return true;
            }
            if(!_.isPlainObject(resourceContext)){
                return false;
            }
            return _.all(requiredRights, function(right, requiredParameter){
                var parameterValue;

                // translate muti-id into single-id
                if (requiredParameter === 'ids') {
                    requiredParameter = 'id';
                }

                if(typeof resourceContext[requiredParameter] === 'undefined' || !self.isSupported(right)){
                    return false;
                }

                //some values in the context are still URI encoded
                parameterValue = uriUtil.decode(resourceContext[requiredParameter]);

                return self.hasPermission(parameterValue, right);
            });
        },

        /**
         * For a given resource compute the permission mode:
         *  - allowed : no supported rights required or has the permission on all supported rights
         *  - partial : has the permission only on some supported rights
         *  - denied  : has the permission on none of the supported rights
         *  @param {String} uri - the resource URI
         *  @returns {String} the mode
         */
        getResourceAccessMode : function getResourcesAccessMode(uri) {
            var self  = this;
            var mode  = 'allowed';
            var rights = this.getRights();
            var count = _.reduce(rights, function(acc, right){
                if(self.hasPermission(uri, right)){
                    acc++;
                }
                return acc;
            }, 0);

            if (rights.length > 0 && count !== rights.length) {
                if(count === 0){
                    mode  = 'denied';
                } else {
                    mode = 'partial';
                }
            }
            return mode;
        }
    };

    return permissionsManager;
});
