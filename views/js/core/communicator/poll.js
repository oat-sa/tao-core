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
    'i18n',
    'core/communicator',
    'core/polling',
    'core/promise',
    'core/tokenHandler'
], function ($, _, __, communicator, pollingFactory, Promise, tokenHandlerFactory) {
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
     * Business logic errors can be implemented using the `error` *channel*.
     * Network errors are handled by the AJAX implementation, and are forwarded to the `error` *event*.
     * Additional network error handling can be achieve by the rejected send promises.
     *
     * Malformed messages will be issued through the `malformed` channel
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
                return Promise.reject(new Error('You must provide a service URL'));
            }

            // there is no message in the queue at this moment
            this.messagesQueue = [];

            this.request = function request(){
                return new Promise(function(resolve){
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
                        data: JSON.stringify(request),
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

                        if (!self.polling.is('stopped')) {
                            // receive server messages
                            _.forEach(response.messages, function (msg) {
                                if (msg.channel) {
                                    self.trigger('message', msg.channel, msg.message);
                                } else {
                                    self.trigger('message', 'malformed', msg);
                                }
                            });
                        }

                        self.trigger('receive', response);

                        resolve();
                    })

                    // when the request fails...
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        var error = {
                            source: 'network',
                            purpose: 'communicator',
                            context: this,
                            sent: jqXHR.readyState > 0,
                            code: jqXHR.status,
                            type: textStatus || 'error',
                            message: errorThrown || __('An error occurred!')
                        };

                        // reset the security token on error
                        if (token) {
                            tokenHandler.setToken(token);
                        }

                        // reject all message promises
                        _.forEach(promises, function (promise) {
                            promise.reject(error);
                        });

                        self.trigger('error', error);

                        // the request promise must be resolved, even if failed, to continue the polling
                        resolve();
                    });
                });
            };

            // prepare the polling of the remote service
            // it will be started by the open() method
            this.polling = pollingFactory({
                interval: config.interval,
                autoStart: false,
                action: function communicatorPoll() {
                    var async = this.async();
                    self.request().then(function(){
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
            var self = this;
            var stopped;

            if (this.polling) {
                stopped = new Promise(function(resolve) {
                    self.polling.off('stop.api').on('stop.api', resolve).stop();
                });
            } else {
                stopped = Promise.resolve();
            }

            return stopped.then(function() {
                self.polling = self.throttledSend = self.messagesQueue = null;
            });
        },

        /**
         * Opens the connection with the remote service.
         * @returns {Promise}
         */
        open: function open() {
            var self = this;
            var started;

            if (this.polling) {
                started = new Promise(function(resolve) {
                    self.polling.off('next.api').on('next.api', resolve).start().next();
                });
            } else {
                started = Promise.reject(new Error('The communicator has not been properly initialized'));
            }

            return started;
        },

        /**
         * Closes the connection with the remote service.
         * @returns {Promise}
         */
        close: function close() {
            var self = this;
            var stopped;

            if (this.polling) {
                stopped = new Promise(function(resolve) {
                    self.polling.off('stop.api').on('stop.api', resolve).stop();
                });
            } else {
                stopped = Promise.reject(new Error('The communicator has not been properly initialized'));
            }

            return stopped;
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
