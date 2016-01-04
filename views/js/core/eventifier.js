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
 * @example simple usage
 * var emitter = eventifier({});
 * emitter.on('hello', function(who){
 *      console.log('Hello ' + who);
 * });
 * emitter.trigger('hello', 'world');
 *
 * @example using before
 * emitter.before('hello', function(e, who){
 *      if(!who || who === 'nobody'){
 *          console.log('I am not saying Hello to nobody');
 *          return false;
 *      }
 * });
 *
 * @example using before asynchronously
 * emitter.before('hello', function(e, who){
 *
 *      //I am in an asynchronous context
 *      var done = e.done();
 *
 *      //ajax call
 *      fetch('do/I/know?who='+who).then(function(yes){
 *          if(yes){
 *              console.log('I know', who);
 *              e.done();
 *          }else{
 *              console.log('I don't talk to stranger');
 *              e.prevent();
 *          }
 *      })).catch(function(){
 *          console.log('System failure, I should quit now');
 *          e.preventNow();
 *      });
 * });
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'async',
    'core/collections'
], function(_, async, collections){
    'use strict';

    var globalNs = '*';

    /**
     * Create an async callstack
     * @param {array} handlers - array of handlers to create the async callstack from
     * @param {object} context - the object that each handler will be applied on
     * @param {array} args - the arguments passed to each handler
     * @param {function} success - the success callback
     * @returns {array} array of aync call stack
     */
    function createAsyncCallstack(handlers, context, args, success){

        var callstack =  _.map(handlers, function(handler){

            //return an async call
            return function(cb){

                var result;
                var asyncMode = false;
                var _args = _.clone(args);
                var event = {
                    done : function asyncDone(){
                        asyncMode = true;
                        //returns the done function and wait until it is called to continue the async queue processing
                        return done;
                    },
                    prevent : function asyncPrevent(){
                        asyncMode = true;
                        //immediately call prevent()
                        prevent();
                    },
                    preventNow : function asyncPreventNow(){
                        asyncMode = true;
                        //immediately call preventNow()
                        preventNow();
                    }
                };

                /**
                 * Call success
                 * @private
                 */
                function done(){
                    //allow passing to next
                    cb(null, {success:true});
                }

                /**
                 * Call fail but can continue to next loop
                 * @private
                 */
                function prevent(){
                    cb(null, {success:false});
                }

                /**
                 * Call fail and must stop the execution of the stack right now
                 * @returns {undefined}
                 */
                function preventNow(){
                    //stop async processing queue right now
                    cb(new Error('prevent now'), {success:false, immediate:true});
                }

                //set the event object as the first argument
                _args.unshift(event);
                result = handler.apply(context, _args);

                if(!asyncMode){
                    if(result === false){
                        //if the call
                        prevent();
                    }else{
                        done();
                    }
                }
            };
        });

        async.series(callstack, function(err, results){
            var successes = _.pluck(results, 'success');
            if(_.indexOf(successes, false) === -1){
                success();
            }
        });
    }

    function getName(name){
        if(name.indexOf('.') > -1){
            return name.substr(0, name.indexOf('.'));
        }
        return name;
    }

    function getNamespace(name){
        if(name.indexOf('.') > -1){
            return name.substr(name.indexOf('.'));
        }
        return globalNs;
    }

    /**
     * Makes the target an event emitter by delegating calls to the event API.
     * @param {Object} [target = {}] - the target object, a new plain object is created when omited.
     * @param {logger} [logger] - a logger to trace events
     * @returns {Object} the target for conveniance
     */
    function eventifier(target, logger){

        var eventHandlers  = {};

        var getHandlers = function getHandlers(name, ns, type){
            type = type || 'between';
            eventHandlers[ns] = eventHandlers[ns] || {};
            eventHandlers[ns][name] = eventHandlers[ns][name] || { after : [], before : [], between : [] };
            return eventHandlers[ns][name][type];
        };

        target = target || {};

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
            * @param {String} eventName - the name of the event to listen
            * @param {Function} handler - the callback to run once the event is triggered
            * @returns {Object} the target object
            */
            on : function on(eventName, handler){
                var ns = getNamespace(eventName);
                var name = getName(eventName);

                if(typeof handler === 'function'){
                    getHandlers(name, ns).push(handler);
                }

                return this;
            },

           /**
            * Remove ALL handlers for an event.
            *
            * @example target.off('foo');
            *
            * @this the target
            * @param {String} eventName - the name of the event
            * @returns {Object} the target object
            */
            off : function off(eventName){
                var ns = getNamespace(eventName);
                var name = getName(eventName);

                eventHandlers[ns] = eventHandlers[ns] || {};
                eventHandlers[ns][name] = { after : [], before : [], between : [] };

                return this;
            },

            /**
            * Trigger an event.
            *
            * @example target.trigger('foo', 'Awesome');
            *
            * @this the target
            * @param {String} eventName - the name of the event to trigger
            * @returns {Object} the target object
            */
            trigger : function trigger(eventName){
                var self = this;
                var args = [].slice.call(arguments, 1);
                var ns = getNamespace(eventName);
                var name = getName(eventName);

                //TODO group the handlers for all ns : ALL before -> ALL between -> ALL after
                //
                //check which ns needs to be executed
                _.forEach(eventHandlers, function(nsHandlers, namespace){
                    if(nsHandlers[name] && (ns === globalNs || ns === namespace)){

                        //if there is something in before we delay the execution
                        if(nsHandlers[name] && nsHandlers[name].before.length){
                            createAsyncCallstack(nsHandlers[name].before, self, args, _.partial(triggerEvent, nsHandlers[name]));
                        } else {
                            triggerEvent(nsHandlers[name]);
                        }
                    }
                });

                /**
                * Call the actual registered event handlers
                * @private
                */
                function triggerEvent(handlers){
                    //trigger the event handlers
                    _.forEach(handlers.between, function(handler){
                        handler.apply(self, args);
                    });

                    //trigger the after event handlers if applicable
                    _.forEach(handlers.after, function(handler){
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
            * @param {String} eventName
            * @returns {Object} the target object
            */
            before : function before(eventName, handler){
                var ns = getNamespace(eventName);
                var name = getName(eventName);

                if(typeof handler === 'function'){
                    getHandlers(name, ns, 'before').push(handler);
                }
                return this;
            },

            /**
            * Register a callback that is executed after the given event name
            * The handlers will all be executed, no matter what
            *
            * @this the target
            * @param {String} eventName
            * @returns {Object} the target object
            */
            after : function after(eventName, handler){
                var ns = getNamespace(eventName);
                var name = getName(eventName);

                if(typeof handler === 'function'){
                    getHandlers(name, ns, 'after').push(handler);
                }
                return this;
            }
        };

        _(eventApi).functions().forEach(function(method){
            target[method] = function delegate(){
                var args =  [].slice.call(arguments);
                if(logger && logger.trace){
                    logger.trace.apply(logger, ['event', method].concat(args));
                }
                return eventApi[method].apply(target, args);
            };
        });

        return target;
    }

    return eventifier;
});
