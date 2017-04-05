
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
 * Copyright (c) 2017 Open Assessment Technologies SA
 */

/**
 * A promise queue to fil the gap... (to run them in series for example)
 *
 * @example Ensure b starts once a is finished
 * var a = function a (){
 *   return new Promise(function(resolve){
 *      setTimeout(resolve, 150);
 *   });
 * };
 * var b = function b (){
 *   return new Promise(function(resolve){
 *      setTimeout(resolve, 25);
 *   });
 * };
 *
 * var queue = promiseQueueFactory();
 * queue.serie(a);
 * queue.serie(b);
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'core/eventifier',
    'lib/uuid'
], function(_, Promise, eventifier, uuid){
    'use strict';

    /**
     * Creates a new promise queue
     * @returns {promiseQueue}
     */
    return function promiseQueueFactory(){

        //where we keep the pending promises
        var queue = {};

        var getId = function getId(){
            var id =  'promise-' + uuid(6);
            if(typeof queue[id] === 'undefined'){
                return id;
            }
            return getId();
        };

        /**
         * @typedef {promiseQueue}
         */
        return {

            /**
             * Just add another promise to the queue
             * @param {Promise} promise
             * @return {promiseQueue} chains
             */
            add : function add(promise){
                queue[getId()] = promise;
                return this;
            },

            /**
             * Get the queue values
             * @returns {Promise[]} the array of promises in the queue
             */
            getValues : function getValues(){
                return _.values(queue);
            },

            /**
             * Empty the queue
             * @return {promiseQueue} chains
             */
            clear : function clear(){
                queue = {};
                return this;
            },

            /**
             * Run the given promise at the end of the queue,
             * @param {Function} promiseFn - a function that returns a promise
             * @returns {Promise}
             */
            serie : function serie(promiseFn){
                var id = getId();

                //the actual queue to execute before running the given promise
                var currentQueue = this.getValues();

                //use an emitter to notify the promise fulfillment, internally.
                var emitter = eventifier();

                //add a waiting promise into the queue (for others who are calling the queue)
                queue[id] = new Promise(function(resolve){
                    emitter.on('fulfilled', resolve);
                });

                //wait for the queue,
                //then run the given promise
                //and resolve the waiting promise (for others)
                return Promise
                    .all(currentQueue)
                    .then(function(){
                        if(_.isFunction(promiseFn)){
                            return promiseFn();
                        }
                    })
                    .then(function(data){
                        emitter.trigger('fulfilled');
                        delete queue[id];
                        return data;
                    })
                    .catch(function(err){
                        queue = {};
                        throw err;
                    });
            }
        };
    };
});
