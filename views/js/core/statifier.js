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
 * Makes an object a states handler.
 *
 * @example simple usage
 * var statesHandler = statifier();
 * ...
 * statesHandler.setState("alive", true);
 * ...
 * if (statesHandler.getState("alive")) {
 *     ...
 * }
 * ...
 * // without explicit value, the state is always set
 * statesHandler.setState("ready");
 *
 * // return `true`
 * statesHandler.getState("ready");
 *
 * @example extend existing object
 * var myObj = {...};
 * statifier(myObj);
 * ...
 * myObj.setState("alive", true);
 * ...
 * if (myObj.getState("alive")) {
 *     ...
 * }
 *
 * @example do not expose all API (nest/delegate)
 * var statesHandler = statifier();
 * var myObj = {
 *    ...
 *    getState: function getState(name) {
 *        return statesHandler.getState(name);
 *    }
 *    ...
 * };
 * ...
 * // should not be possible!
 * myObj.setState("alive", true);
 *
 * // but this one should be ok
 * statesHandler.setState("alive", true);
 *
 * ...
 * // the only exposed state API
 * if (myObj.getState("alive")) {
 *     ...
 * }
 *
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash'
], function (_) {
    'use strict';

    /**
     * Makes the target a states handler by delegating calls to the states API.
     * @param {Object} [target = {}] - the target object, a new plain object is created when omitted.
     * @returns {Object} the target for convenience
     */
    function statifierFactory(target) {
        var states = {};
        var statesApi = {
            /**
             * Tells if the state is set
             * @param {String} name
             * @returns {Boolean}
             */
            getState: function getState(name) {
                return !!states[name];
            },

            /**
             * Sets a state.
             * Without value, the state is always set.
             * @example
             * statesHandler.setState("ready");
             *
             * // return `true`
             * statesHandler.getState("ready");
             *
             * @param {String} name
             * @param {Boolean} [value]
             * @returns {statesApi}
             */
            setState: function setState(name, value) {
                if (typeof(value) === 'undefined') {
                    value = true;
                }
                states[name] = !!value;
                return this;
            },

            /**
             * Cleans up all states
             * @returns {statesApi}
             */
            clearStates: function clearStates() {
                states = {};
                return this;
            },

            /**
             * Returns all current states set
             * @returns {Array}
             */
            getStates: function getStates() {
                return _.reduce(states, function(result, state, key) {
                    if (state) {
                        result.push(key);
                    }
                    return result;
                }, []);
            }
        };

        target = target || {};

        _(statesApi).functions().forEach(function(method){
            target[method] = function delegate(){
                var args =  [].slice.call(arguments);
                return statesApi[method].apply(target, args);
            };
        });

        return target;
    }

    return statifierFactory;
});
