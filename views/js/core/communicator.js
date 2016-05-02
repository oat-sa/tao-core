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
    'core/providerRegistry',
    'core/delegator',
    'core/eventifier'
], function (_, providerRegistry, delegator, eventifier) {
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
     * Creates a communicator implementation
     * @param {Object} providerName - The name of the provider instance,
     *                                which MUST be defined before through a `.registerProvider()` call.
     * @param {Object} [config] - Optional config set
     * @param {Number} [config.timeout] - The communication timeout, in milliseconds (default: 30000)
     * @returns {communicator}
     */
    function communicatorFactory(providerName, config) {

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
         * The selected communication provider
         * @type {Object}
         */
        var provider = communicatorFactory.getProvider(providerName);

        config = _(config || {})
            .omit(function(value){
                return value === null || value === undefined;
            })
            .defaults(defaults)
            .value();

        // creates the implementation by setting an API an delegating calls to the provider
        communicator = eventifier({
            /**
             * Initializes the communication implementation
             * @returns {Object}
             */
            init: function init() {
                return delegate('init', arguments);
            },

            /**
             * Tears down the communication implementation
             * @returns {Object}
             */
            destroy: function destroy() {
                return delegate('destroy', arguments);
            },

            /**
             * Sends an messages through the communication implementation
             * @param {String} channel - The name of the communication channel to use
             * @param {Object} message - The message to send
             * @returns {Object}
             */
            send: function send(channel, message) {
                return delegate('send', arguments);
            },

            /**
             * Registers a listener on a particular channel
             * @param {String} name - The name of the channel to listen
             * @param {Function} handler - The listener callback
             * @returns {communicator}
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
                return config;
            }
        });

        // all messages comes through a message event, the each is dispatched to the right channel
        communicator.on('message', function(channel, message) {
            this.trigger('channel-' + channel, message);
        });

        // use a delegate function to make a bridge between API and provider
        delegate = delegator(communicator, provider, 'communicator');

        return communicator;
    }

    return providerRegistry(communicatorFactory);
});
