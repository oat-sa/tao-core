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
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'core/communicator',
    'core/polling',
    'core/promise',
    'core/tokenHandler'
], function ($, _, communicator, pollingFactory, Promise, tokenHandlerFactory) {
    'use strict';

    /**
     * Some default config values
     * @type {Object}
     * @private
     */
    var defaults = {
        timeout: 30 * 1000,
        interval: 30 * 1000,
        throttle: 1000
    };

    /**
     * Defines a communication implementation based on remote service polling.
     *
     * The remote service must accept JSON payload using this format:
     * ```
     * [{
     *      channel: "a channel's name",
     *      message: {a: "message", with: "some data"}
     * }, {
     *      ...
     * ]
     * ```
     *
     * The remote service must respond using JSON notation like this:
     * ```
     * {
     *      responses: [
     *          "some responses",
     *          "indexed with the same order as the request"
     *      ],
     *      messages: [{
     *          channel: "a channel's name",
     *          message: {a: "message", with: "some data"}
     *      }, {
     *          ...
     *      }]
     * }
     * ```
     *
     * A security token can be added, in the header `X-Auth-Token` for the request and in the `token` field for the response.
     *
     * Business logic errors can be implemented using the `error` channel.
     * Network errors have to be handled by the AJAX implementation and rejected promises.
     *
     * @param {String} config.service - The address of the remote service to request
     * @param {Number} [config.timeout] - The communication timeout, in milliseconds (default: 30000)
     * @param {Number} [config.interval] - The poll interval, in milliseconds (default: 30000)
     * @param {Number} [config.throttle] - Gather several calls to send() by throttle period, in milliseconds (default: 1000)
     * @param {String} [config.token] - An optional initial security token
     * @type {Object}
     */
    var pollProvider = {
        /**
         * Initializes the communication implementation
         * @returns {Promise}
         */
        init: function init() {
            var self = this;
            var config = _.defaults(this.getConfig(), defaults);
            var tokenHandler = tokenHandlerFactory(config.token);

            // validate the config
            if (!config.service) {
                // a remote service is needed to build a long poll communication
                return Promise.reject('You must provide a service URL');
            }

            // there is no message in the queue at this moment
            this.messagesQueue = [];

            // prepare the polling of the remote service
            // it will be started by the open() method
            this.polling = pollingFactory({
                interval: config.interval,
                autoStart: false,
                action: function communicatorPoll() {
                    var async = this.async();
                    var headers = {};
                    var token = tokenHandler.getToken();

                    // split promises and their related messages
                    // then reset the list of pending messages
                    var promises = [];
                    var request = _.map(self.messagesQueue, function (msg) {
                        promises.push(msg.promise);
                        return {
                            channel: msg.channel,
                            message: msg.message
                        };
                    });
                    self.messagesQueue = [];

                    if (token) {
                        headers['X-Auth-Token'] = token;
                    }

                    // send messages to the remote service
                    $.ajax({
                            url: config.service,
                            type: 'POST',
                            cache: false,
                            headers: headers,
                            data: request,
                            async: true,
                            dataType: 'json',
                            contentType: 'application/json',
                            timeout: config.timeout
                        })
                        // when the request succeeds...
                        .done(function (response) {
                            response = response || {};

                            // receive optional security token
                            if (response.token) {
                                tokenHandler.setToken(response.token);
                            }

                            // resolve each message promises
                            _.forEach(promises, function (promise, idx) {
                                promise.resolve(response.responses && response.responses[idx]);
                            });

                            // receive server messages
                            _.forEach(response.messages, function (msg) {
                                self.trigger('message', msg.channel, msg.message);
                            });

                            self.trigger('receive', response);

                            async.resolve();
                        })

                        // when the request fails...
                        .fail(function (jqXHR, textStatus, errorThrown) {
                            // reject all message promises
                            _.forEach(promises, function (promise) {
                                promise.reject(errorThrown || textStatus);
                            });

                            // the request promise must be resolved, even if failed, to continue the polling
                            async.resolve();
                        });
                }
            });

            // adjust the message sending by throttle periods
            this.throttledSend = _.throttle(function () {
                self.polling.next();
            }, config.throttle);

            return Promise.resolve();
        },

        /**
         * Tears down the communication implementation
         * @returns {Promise}
         */
        destroy: function destroy() {
            if (this.polling) {
                this.polling.stop();
            }
            this.polling = this.throttledSend = this.messagesQueue = null;

            return Promise.resolve();
        },

        /**
         * Opens the connection with the remote service.
         * @returns {Promise}
         */
        open: function open() {
            var self = this;
            return new Promise(function (resolve, reject) {
                if (self.polling) {
                    self.polling.start();
                    resolve();
                } else {
                    reject('The communicator has not been properly initialized');
                }
            });
        },

        /**
         * Closes the connection with the remote service.
         * @returns {Promise}
         */
        close: function close() {
            var self = this;
            return new Promise(function (resolve, reject) {
                if (self.polling) {
                    self.polling.stop();
                    resolve();
                } else {
                    reject('The communicator has not been properly initialized');
                }
            });
        },

        /**
         * Sends an messages through the communication implementation
         * @param {String} channel - The name of the communication channel to use
         * @param {Object} message - The message to send
         * @returns {Promise}
         */
        send: function send(channel, message) {
            // queue the message, it will be sent soon
            var pending = {
                channel: channel,
                message: message
            };
            var promise = new Promise(function(resolve, reject) {
                pending.promise = {
                    resolve: resolve,
                    reject: reject
                };
            });
            this.messagesQueue.push(pending);

            // force a send in the next throttle period
            this.throttledSend();

            return promise;
        }
    };

    return pollProvider;
});
