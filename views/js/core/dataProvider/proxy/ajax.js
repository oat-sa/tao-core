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
        noToken: false,
        actions: {}
    };

    /**
     * Builds a reject descriptor for a particular action context
     * @param {String} type
     * @param {String} action
     * @param {Object} params
     * @returns {Promise}
     */
    function rejectAction(type, action, params) {
        return Promise.reject({
            success: false,
            type: type,
            action: action,
            params: params
        });
    }

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
         * @param {Boolean} [config.noToken] - Prevent the request to be use the security token when available (default: false)
         */
        init: function init(config) {
            // Will request the server for the wanted action.
            // May reject the request if the action is not implemented.
            this.processRequest = function processRequest(action, params, method) {
                var descriptor = config.actions[action];
                var headers = {};
                var tokenHandler = this.getTokenHandler();
                var token;

                if (_.isString(descriptor)) {
                    descriptor = {
                        url: descriptor
                    };
                }

                if (descriptor && descriptor.url) {
                    if (_.isFunction(descriptor.validate) && descriptor.validate(params) === false) {
                        // invalid parameter
                        return rejectAction('invalid', action, params);
                    }
                } else {
                    // action not implemented
                    return rejectAction('notimplemented', action, params);
                }

                if (config.noCache) {
                    params = _.merge({_: (new Date).getTime()}, params);
                }

                if (!config.noToken) {
                    token = tokenHandler.getToken();
                    if (token) {
                        headers['X-Auth-Token'] = token;
                    }
                }

                return request(descriptor.url, params, descriptor.method || method, headers)
                    .then(function(data) {
                        if (data && data.token) {
                            tokenHandler.setToken(data.token);
                        }
                        return data;
                    })
                    .catch(function(err) {
                        var t = err.response && (err.response.token || (err.response.data && err.response.data.token));
                        if (t) {
                            tokenHandler.setToken(t);
                        } else if (!config.noToken) {
                            tokenHandler.setToken(token);
                        }

                        return Promise.reject(err);
                    });
            };

            _.defaults(config, _defaults);
        },

        /**
         * Cleans up the instance when destroying
         */
        destroy: function destroy() {
            this.processRequest = null;
        },

        /**
         * Requests the server for a create action
         * @param {Object} params
         */
        create: function create(params) {
            return this.processRequest('create', params, 'POST');
        },

        /**
         * Requests the server for a read action
         * @param {Object} params
         */
        read: function read(params) {
            return this.processRequest('read', params, 'GET');
        },

        /**
         * Requests the server for a write action
         * @param {Object} params
         */
        write: function write(params) {
            return this.processRequest('write', params, 'POST');
        },

        /**
         * Requests the server for a remove action
         * @param {Object} params
         */
        remove: function remove(params) {
            return this.processRequest('remove', params, 'GET');
        },

        /**
         * Requests the server using a particular action
         * @param {String} actionName
         * @param {Object} params
         */
        action: function action(actionName, params) {
            return this.processRequest(actionName, params, 'POST');
        }
    };
});
