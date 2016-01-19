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
define(['lib/polyfill/performance-now'], function () {
    'use strict';

    /**
     * Gets a timer
     * @param {Object|Boolean} config - The init config. Can be either false to disable autoStart, or an object
     * @param {Boolean} [config.autoStart] - Auto start the timer (default: true)
     * @returns {timer}
     */
    function timerFactory(config) {
        var begin = now();
        var last = begin;
        var duration = 0;
        var state = {};
        var disableAutoStart = false === config || config && false === config.autoStart;

        /**
         * The timer instance
         * @type {timer}
         */
        var timer = {
            /**
             * Starts the timer
             * @returns {timer}
             */
            start : function start() {
                begin = now();
                last = begin;
                duration = 0;
                state.running = true;
                state.started = true;

                return this;
            },

            /**
             * Gets the time elapsed since the last tick
             * @returns {number}
             */
            tick : function tick() {
                var timestamp = now();
                var elapsed = timestamp - last;
                last = timestamp;
                return elapsed;
            },

            /**
             * Pause the timer
             * @returns {timer}
             */
            pause : function pause() {
                if (state.running) {
                    duration += now() - begin;
                    state.running = false;
                }

                return this;
            },

            /**
             * Resume the timer
             * @returns {timer}
             */
            resume : function resume() {
                if (!state.running) {
                    begin = now();
                    state.started = true;
                    state.running = true;
                }

                return this;
            },

            /**
             * Stops the timer
             * @returns {timer}
             */
            stop : function stop() {
                if (state.running) {
                    duration += now() - begin;
                }

                state.running = false;
                state.started = false;

                return this;
            },

            /**
             * Gets the time elapsed since the last start.
             * If the timer is stopped, gets the total duration between start and stop.
             * @returns {number}
             */
            getDuration : function getDuration() {
                if (state.running) {
                    return duration + (now() - begin);
                }
                return duration;
            },

            /**
             * Checks if the timer is in a particular state
             * @param {String} stateName
             * @returns {Boolean}
             */
            is : function is(stateName) {
                return !!state[stateName];
            }
        };

        /**
         * Simple wrapper around the time provider
         * @returns {Number}
         */
        function now() {
            return window.performance.now();
        }

        if (!disableAutoStart) {
            timer.start();
        }

        return timer;
    }

    return timerFactory;
});
