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
    'lodash',
    'core/promise',
    'core/providerRegistry',
    'core/delegator',
    'core/eventifier'
], function (_, Promise, providerRegistry, delegator, eventifier) {
    'use strict';

    /**
     * Some default config values
     * @type {Object}
     * @private
     */
    var defaults = {
        timeout: 30 * 1000
    };

    /**
     * Creates a communicator implementation.
     * The communicator relies on a provider to execute the actions.
     * Most of the delegated methods must return promises.
     *
     * Some standard channels are reserved, and must be implemented by the providers:
     * - error: to carry on error purpose messages
     * - malformed: to carry on malformed received messages
     *
     * @param {String} providerName - The name of the provider instance,
     *                                which MUST be defined before through a `.registerProvider()` call.
     * @param {Object} [config] - Optional config set
     * @param {String} [config.service] - The address of the remote service to request
     * @param {Number} [config.timeout] - The communication timeout, in milliseconds (default: 30000)
     * @returns {communicator}
     */
    function communicatorFactory(providerName, config) {

        /**
         * The communicator config set
         * @type {Object}
         */
        var extendedConfig = _(config || {}).defaults(defaults).value();

        /**
         * The communicator implementation
         * @type {Object}
         */
        var communicator;

        /**
         * The function used to delegate the calls from the API to the provider.
         * @type {Function}
         */
        var delegate;

        /**
         * The current states of the communicator
         * @type {Object}
         */
        var states = {};

        /**
         * The selected communication provider
         * @type {Object}
         */
        var provider = communicatorFactory.getProvider(providerName);

        // creates the implementation by setting an API and delegating calls to the provider
        communicator = eventifier({
            /**
             * Initializes the communication implementation.
             * Sets the `ready` state.
             * @returns {Promise} The delegated provider's method must return a promise
             * @fires init
             * @fires ready
             */
            init: function init() {
                var self = this;

                if (this.getState('ready')) {
                    return Promise.resolve();
                }

                return delegate('init')
                    .then(function () {
                        self.setState('ready')
                            .trigger('ready');
                    });
            },

            /**
             * Tears down the communication implementation.
             * Clears the states.
             * @returns {Promise} The delegated provider's method must return a promise
             * @fires destroy
             * @fires destroyed
             */
            destroy: function destroy() {
                var self = this;
                var stepPromise;

                if (self.getState('open')) {
                    stepPromise = self.close();
                } else {
                    stepPromise = Promise.resolve();
                }

                return stepPromise
                    .then(function () {
                        return delegate('destroy')
                            .then(function () {
                                self.trigger('destroyed');
                                states = {};
                            })
                    });
            },

            /**
             * Opens the connection.
             * Sets the `open` state.
             * @returns {Promise} The delegated provider's method must return a promise
             * @fires open
             * @fires opened
             */
            open: function open() {
                var self = this;

                if (this.getState('open')) {
                    return Promise.resolve();
                }

                return delegate('open')
                    .then(function () {
                        self.setState('open')
                            .trigger('opened');
                    });
            },

            /**
             * Closes the connection.
             * Clears the `open` state.
             * @returns {Promise} The delegated provider's method must return a promise
             * @fires close
             * @fires closed
             */
            close: function close() {
                var self = this;
                return delegate('close')
                    .then(function () {
                        self.setState('open', false)
                            .trigger('closed');
                    });
            },

            /**
             * Sends an messages through the communication implementation.
             * @param {String} channel - The name of the communication channel to use
             * @param {Object} message - The message to send
             * @returns {Promise} The delegated provider's method must return a promise
             * @fires send
             * @fires sent
             */
            send: function send(channel, message) {
                var self = this;

                if (!this.getState('open')) {
                    return Promise.reject();
                }

                return delegate('send', channel, message)
                    .then(function (response) {
                        self.trigger('sent', channel, message, response);
                        return Promise.resolve(response);
                    });
            },

            /**
             * Registers a listener on a particular channel
             * @param {String} name - The name of the channel to listen
             * @param {Function} handler - The listener callback
             * @returns {communicator}
             * @throws TypeError if the name is missing or the handler is not a callback
             */
            channel: function channel(name, handler) {
                if (!_.isString(name) || name.length <= 0) {
                    throw new TypeError('A channel must have a name');
                }

                if (!_.isFunction(handler)) {
                    throw new TypeError('A handler must be attached to a channel');
                }

                this.on('channel-' + name, handler);

                return this;
            },

            /**
             * Gets the implementation config set
             * @returns {Object}
             */
            getConfig: function getConfig() {
                return extendedConfig;
            },

            /**
             * Sets a state
             * @param {String} name - The name of the state to set
             * @param {Boolean} [state] - The state itself (default: true)
             * @returns {communicator}
             */
            setState: function setState(name, state) {
                if (_.isUndefined(state)) {
                    state = true;
                }
                states[name] = !!state;
                return this;
            },

            /**
             * Gets a state
             * @param {String} name - The name of the state to get
             * @returns {Boolean}
             */
            getState: function getState(name) {
                return !!states[name];
            }
        });

        // all messages comes through a message event, then each is dispatched to the right channel
        communicator.on('message', function (channel, message) {
            this.trigger('channel-' + channel, message);
        });

        // use a delegate function to make a bridge between API and provider
        delegate = delegator(communicator, provider, {name: 'communicator'});

        return communicator;
    }

    return providerRegistry(communicatorFactory);
});
