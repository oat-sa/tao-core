/*
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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Make an object an event emitter.
 *
 * @example
 * var emitter = eventifier({});
 * emitter.on('hello', function(who){
 *      console.log('Hello ' + who);
 * });
 * emitter.trigger('hello', 'world');
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['lodash'], function(_){
    'use strict';

    /**
     * The API itself is just a placeholder, all methods will be delegated to a target.
     */
    var eventApi = {

        /**
         * Attach an handler to an event.
         * Calling `on` with the same eventName multiple times add callbacks: they
         * will all be executed.
         *
         * @example target.on('foo', function(bar){ console.log('Cool ' + bar) } );
         *
         * @this the target
         * @param {String} name - the name of the event to listen
         * @param {Function} handler - the callback to run once the event is triggered
         * @returns {Object} the target object
         */
        on : function on(name, handler){
            if(typeof handler === 'function'){
                this._events[name] = this._events[name] || [];
                this._events[name].push(handler);
            }
            return this;
        },

        /**
         * Remove ALL handlers for an event.
         *
         * @example target.off('foo');
         *
         * @this the target
         * @param {String} name - the name of the event
         * @returns {Object} the target object
         */
        off : function off(name){
            this._events[name] = [];
            return this;
        },

        /**
         * Trigger an event.
         *
         * @example target.trigger('foo', 'Awesome');
         *
         * @this the target
         * @param {String} name - the name of the event to trigger
         * @returns {Object} the target object
         */
        trigger : function trigger(name){
            var self = this;
            var args = [].slice.call(arguments, 1);
            var execution = true;
            if(this._before[name] && _.isArray(this._before[name])){
                execution = _.reduce(this._before[name], function(exec, handler){
                    return exec && handler.apply(self, args);
                }, execution);
            }
            if(this._events[name] && _.isArray(this._events[name]) && execution){
                _.forEach(this._events[name], function(handler){
                    handler.apply(self, args);
                });
            }
            return this;
        },
        
        /**
         * Register a callback that is executed before the given event name
         * Provides an opportunity to cancel the execution of the event if one of the returned value is false
         * 
         * @this the target
         * @param {String} name
         * @returns {Object} the target object
         */
        before : function before(name, handler){
            if(typeof handler === 'function'){
                this._before[name] = this._before[name] || [];
                this._before[name].push(handler);
            }
            return this;
        }
    };

    /**
     * Makes the target an event emitter by delegating calls to the event API.
     * @param {Object} [target = {}] - the target object, a new plain object is created when omited.
     * @returns {Object} the target for conveniance
     */
    function eventifier(target){

        target = target || {};
        target._events = {};
        target._before = {};

        _(eventApi).functions().forEach(function(method){
            target[method] = function delegate(){
                return eventApi[method].apply(target, [].slice.call(arguments));
            };
        });
        return target;
    }

    return eventifier;

});
