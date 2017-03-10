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
    'core/eventifier',
    'core/promise'
], function (_, eventifier, Promise) {
    'use strict';

    /**
     * Defines a middlewares chain handler.
     * It will manage a chain of middlewares that can interact with request responses.
     * Each middleware must return a promise.
     *
     * @returns {middlewareHandler} - The middlewares handler instance
     */
    function middlewareFactory() {
        /**
         * The registered middlewares
         * @type {Object}
         */
        var middlewares = {};

        /**
         * @typedef {middlewareHandler}
         */
        var middlewareHandler = eventifier({
            /**
             * Add a middleware
             * @param {String} [command] The command queue in which add the middleware (default: 'all')
             * @param {Function} [callback] A middleware callback. Must accept 2 parameters (request and response) and can return a promise.
             * @returns {proxy}
             */
            use: function use(command) {
                var queue = command && _.isString(command) ? command : 'all';
                var list = middlewares[queue] || [];
                middlewares[queue] = list;

                _.forEach(arguments, function (cb) {
                    if (_.isFunction(cb)) {
                        list.push(cb);

                        /**
                         * @event add
                         * @param {String} command
                         * @param {Function} callback
                         */
                        middlewareHandler.trigger('add', command, cb);
                    }
                });
                return this;
            },

            /**
             * Applies the list of registered middlewares onto the received response
             * @param {Object} request - The request descriptor
             * @param {String} request.command - The name of the requested command
             * @param {Object} request.params - The map of provided parameters
             * @param {Object} response The response descriptor
             * @param {String} response.success The status of the response
             * @param {Object} [context] - An optional context object to apply on middlewares
             * @returns {Promise}
             */
            apply: function apply(request, response, context) {
                var stack = getMiddlewares(request.command);
                var pointer = 0;

                // apply each middleware in series, then resolve or reject the promise
                return new Promise(function (resolve, reject) {
                    function next() {
                        var middleware = stack[pointer ++];
                        if (middleware) {
                            Promise.resolve(middleware.call(context, request, response))
                                .then(function(res) {
                                    if (res !== false) {
                                        next();
                                    } else {
                                        resolve();
                                    }
                                })
                                .catch(reject);
                        } else {
                            resolve();
                        }
                    }

                    next();
                }).then(function() {
                    // handle implicit error from response descriptor
                    if (response.success === false) {
                        return Promise.reject(response);
                    }

                    /**
                     * @event applied
                     * @param {Object} request - The request descriptor
                     * @param {Object} response The response descriptor
                     * @param {Object} context - The call context
                     */
                    middlewareHandler.trigger('applied', request, response, context);

                    return response;
                }).catch(function(err) {
                    /**
                     * @event failed
                     * @param {Object} request - The request descriptor
                     * @param {Object} response The response descriptor
                     * @param {Object} context - The call context
                     */
                    middlewareHandler.trigger('failed', request, response, context);

                    return Promise.reject(err);
                });
            }

        });

        /**
         * Gets the aggregated list of middlewares for a particular queue name
         * @param {String} queue - The name of the queue to get
         * @returns {Array}
         */
        function getMiddlewares(queue) {
            var list = middlewares[queue] || [];
            if (middlewares.all) {
                list = list.concat(middlewares.all);
            }
            return list;
        }

        return middlewareHandler;
    }

    return middlewareFactory;
});
