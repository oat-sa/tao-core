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
    'lodash',
    'core/dataProvider/request',
    'core/promise'
], function (_, request, Promise) {
    'use strict';

    var _defaults = {
        noCache: true,
        actions: {}
    };

    /**
     * Defines an AJAX proxy implementation.
     * Will request a server to fetch data.
     */
    return {
        name: 'ajax',

        /**
         * Initializes the proxy, sets the implemented actions.
         *
         * @param {Object} config
         * @param {Object} config.actions - The list of supported actions.
         * Each action is represented by a name and a descriptor. The descriptor can be either a string (URL), or an
         * object. When the descriptor is an object, it must provide an URL, optionally a request method. It can also
         * provide a callback that will validate the parameters. A full descriptor looks like:
         * ```
         * {
         *      url: "http://my.url/to/call",
         *      method: "POST", // or "GET", or other accepted HTTP method
         *      validate: function(params) {
         *          // should return false if at least a parameter is not valid
         *      }
         * }
         * ```
         *
         * The following actions have dedicated API, and should be implemented,
         * otherwise a reject will be made when calling them:
         * - 'create' (POST)
         * - 'read'   (GET)
         * - 'write'  (POST)
         * - 'remove' (GET)
         *
         * Other actions are applied with POST method by default. You can override the method in each action descriptor.
         *
         * @param {Boolean} [config.noCache] - Prevent the request to be cached by the client (default: true)
         */
        init: function ajaxInit(config) {
            _.defaults(config, _defaults);

            // Gets a descriptor for a particular action
            function getAction(action) {
                var descriptor = config.actions[action];
                if (_.isString(descriptor)) {
                    descriptor = {url: descriptor};
                }
                return descriptor;
            }

            // Gets the URL related to a particular action
            function getActionUrl(action, params) {
                var descriptor = getAction(action);
                var url;
                if (descriptor && (!_.isFunction(descriptor.validate) || descriptor.validate(params) !== false)) {
                    url = descriptor.url;
                }
                return url;
            }

            // Gets the HTTP method related to a particular action
            function getActionMethod(action, method) {
                var descriptor = getAction(action);
                if (descriptor && descriptor.method) {
                    method = descriptor.method;
                }
                return method || 'GET';
            }

            // Will request the server for the wanted action.
            // May reject the request if the action is not implemented.
            this.processRequest = function processRequest(action, params, method) {
                var url = getActionUrl(action, params);

                if (url) {
                    if (config.noCache) {
                        params = _.merge({_: (new Date).getTime()}, params);
                    }

                    return request(url, params, getActionMethod(action, method));
                } else {
                    // action not implemented
                    return Promise.reject({
                        success: false,
                        type: 'notimplemented',
                        action: action,
                        params: params
                    });
                }
            };
        },

        /**
         * Requests the server for a create action
         * @param {Object} params
         */
        create: function ajaxCreate(params) {
            return this.processRequest('create', params, 'POST');
        },

        /**
         * Requests the server for a read action
         * @param {Object} params
         */
        read: function ajaxRead(params) {
            return this.processRequest('read', params, 'GET');
        },

        /**
         * Requests the server for a write action
         * @param {Object} params
         */
        write: function ajaxWrite(params) {
            return this.processRequest('write', params, 'POST');
        },

        /**
         * Requests the server for a remove action
         * @param {Object} params
         */
        remove: function ajaxRemove(params) {
            return this.processRequest('remove', params, 'GET');
        },

        /**
         * Requests the server using a particular action
         * @param {String} action
         * @param {Object} params
         */
        action: function ajaxAction(action, params) {
            return this.processRequest(action, params, 'POST');
        }
    };
});
