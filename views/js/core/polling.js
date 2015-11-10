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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'core/promise',
    'core/eventifier'
], function ($, _, Promise, eventifier) {
    'use strict';

    /**
     * The default value of the polling interval
     * @type {Number}
     * @private
     */
    var _defaultInterval = 60 * 1000;

    /**
     * Create a polling manager for a particular action
     * @param {Object|Function} [config] - A config object, or the action called on each iteration
     * @param {Function} [config.action] - The callback action called on each iteration
     * @param {Number|String} [config.interval] - The minimal time between two iterations
     * @param {Boolean} [config.autoStart] - Whether or not the polling should start immediately
     * @param {Object} [config.context] - An optional context to apply on each action call
     * @param {Function} [config.oneventname] - Bind an event handler to the event eventname
     * @returns {polling}
     */
    var pollingFactory = function pollingFactory(config) {
        var stopped, timer, promise, interval, action, context, autoStart;

        /**
         * Fires a new timer
         */
        var startTimer = function startTimer() {
            timer = setTimeout(iteration, interval);
            stopped = false;
        };

        /**
         * Stops the current timer
         */
        var stopTimer = function stopTimer() {
            clearTimeout(timer);
            timer = null;
            stopped = true;
        };

        /**
         * Runs an iteration of the polling loop
         */
        var iteration = function iteration() {
            /**
             * Notifies the action is about to be called
             * @event polling#call
             * @param {polling} polling
             */
            polling.trigger('call', polling);

            action.call(context, polling);

            if (promise) {
                promise.then(function() {
                    promise = null;

                    /**
                     * Notifies the polling continues
                     * @event polling#resolved
                     * @param {polling} polling
                     */
                    polling.trigger('resolved', polling);

                    // next iteration
                    startTimer();
                }).catch(function() {
                    promise = null;

                    /**
                     * Notifies the polling has been halted
                     * @event polling#rejected
                     * @param {polling} polling
                     */
                    polling.trigger('rejected', polling);

                    // breaks the polling
                    polling.stop();
                });
            } else {
                if (!stopped) {
                    // next iteration
                    startTimer();
                }
            }
        };

        /**
         * Defines the polling manager
         * @type {Object}
         */
        var polling = {
            /**
             * Sets the current action to asynchronous mode.
             * The next iteration won't be executed until the resolve method has been called.
             * If the reject method is called, the polling is then stopped!
             * @returns {Object}
             */
            async : function async() {
                var cb = {};

                promise = new Promise(function(resolve, reject) {
                    cb.resolve = resolve;
                    cb.reject = reject;
                });

                _.assign(promise, cb);

                /**
                 * Notifies the current action is asynchronous
                 * @event polling#async
                 * @param {Object} async
                 * @param {Function} async.resolve
                 * @param {Function} async.reject
                 * @param {polling} polling
                 */
                polling.trigger('async', cb, polling);

                return cb;
            },

            /**
             * Forces the next iteration to be executed now, unless it is already running.
             * If the polling has been stopped, start it again.
             * @returns {polling}
             */
            next : function next() {
                stopTimer();
                stopped = false;

                if (!promise) {
                    /**
                     * Notifies the action
                     * @event polling#next
                     * @param {polling} polling
                     */
                    this.trigger('next', this);

                    iteration();
                }
                return this;
            },

            /**
             * Starts the polling if it is not currently running
             * @returns {polling}
             */
            start : function start() {
                if (!timer) {
                    startTimer();

                    /**
                     * Notifies the start
                     * @event polling#start
                     * @param {polling} polling
                     */
                    this.trigger('start', this);
                }
                return this;
            },

            /**
             * Stops the polling if it is currently running
             * @returns {polling}
             */
            stop : function stop() {
                stopTimer();

                /**
                 * Notifies the stop
                 * @event polling#stop
                 * @param {polling} polling
                 */
                this.trigger('stop', this);

                return this;
            },

            /**
             * Sets the minimum time interval between two actions
             * @param {Number|String} value
             * @returns {polling}
             */
            setInterval : function setInterval(value) {
                interval = parseInt(value, 10) || _defaultInterval;

                /**
                 * Notifies the interval change
                 * @event polling#setinterval
                 * @param {Number} interval
                 * @param {polling} polling
                 */
                this.trigger('setinterval', interval, this);

                return this;
            },

            /**
             * Gets the minimum time interval between two actions
             * @returns {Number}
             */
            getInterval : function getInterval() {
                return interval;
            },

            /**
             * Sets the polling action
             * @param {Function} fn
             * @returns {polling}
             */
            setAction : function setAction(fn) {
                action = fn;

                /**
                 * Notifies the action change
                 * @event polling#setaction
                 * @param {Function} action
                 * @param {polling} polling
                 */
                this.trigger('setaction', action, this);

                return this;
            },

            /**
             * Gets the polling action
             * @returns {Function}
             */
            getAction : function getAction() {
                return action;
            },

            /**
             * Sets the context applied on each action call
             * @param {Object} ctx
             * @returns {polling}
             */
            setContext : function setContext(ctx) {
                context = ctx || this;

                /**
                 * Notifies the context change
                 * @event polling#setcontext
                 * @param {Object} context
                 * @param {polling} polling
                 */
                this.trigger('setcontext', ctx, this);

                return this;
            },

            /**
             * Gets the context applied on each action call
             * @returns {Object}
             */
            getContext : function getContext() {
                return context;
            }
        };

        eventifier(polling);

        // some defaults
        interval = _defaultInterval;
        context = polling;
        action = null;
        stopped = true;
        autoStart = false;

        // maybe only the action is provided
        if (_.isFunction(config)) {
            polling.setAction(config);
            config = null;
        }

        // loads the config
        if (_.isObject(config)) {
            polling.setAction(config.action);
            polling.setInterval(config.interval || arguments[1]);
            polling.setContext(config.context);
            autoStart = !!config.autoStart;

            // install the events handlers
            _.forEach(config, function(handler, name) {
                if (!name.indexOf('on')) {
                    polling.on(name.substr(2), handler);
                }
            });
        }

        if (autoStart) {
            polling.start();
        }

        return polling;
    };

    return pollingFactory;
});
