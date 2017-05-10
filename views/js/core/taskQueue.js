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
 * Task queue management API
 *
 * @example
 * //instantiate
 * var taskQueue = taskQueueApi({
 *       url:{
 *           status : 'http://my.server/taskQueue/status',
 *           remove : 'http://my.server/taskQueue/remove'
 *       }
 *   });
 *
 * @example
 * //get status
 * taskQueue.getStatus('task#65480abc').then(function(taskData){
 *     console.log('the task status is ', taskData.status);
 * });
 *
 * @example
 * //poll status
 * taskQueue.
 *     on('finished', function(){
 *           console.log('task task#65480abc is finished');
 *     }).pollStatus('task#65480abc');
 *
 * @example
 * //remove status
 * taskQueue.remove('task#65480abc').then(function(taskData){
 *     console.log('task task#65480abc is removed');
 * });
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'core/eventifier',
    'core/dataProvider/request',
    'core/polling',
], function(_, Promise, eventifier, request, polling){
    'use strict';

    var _defaults= {
        url : {}
    };

    /**
     * Builds a task queue management API
     *
     * @param {Object} config - the API config
     * @param {Object} [config.url] - The list of task queue endpoints
     * @param {String} [config.url.status] - the get status endpoint
     * @param {String} [config.url.remove] - the remove task endpoint
     * @returns {taskQueueApi}
     */
    return function taskQueueApi(config){

        config = _.defaults(config||{}, _defaults);

        var pollingIntervals = [
            {iteration: 10, interval:1000},
            {iteration: 10, interval:10000},
            {iteration: 10, interval:30000},
            {iteration: 0, interval:60000}
        ];

        var poll;

        var api = eventifier({
            /**
             * Get the status of a task identified by its unique task id
             *
             * @param {String} taskId - unique task identifier
             * @returns {Promise}
             */
            getStatus : function getStatus(taskId){
                var status;
                var error;

                if(!config.url || !config.url.status){
                    throw new TypeError('config.url.status is not configured while getStatus() is being called');
                }

                status = request(config.url.status, {taskId: taskId})
                    .then(function(taskData){
                        //check taskData
                        if(taskData && taskData.status){
                            return Promise.resolve(taskData);
                        }
                        return Promise.reject(new Error('failed to get task data'));
                    });

                status.catch(function(err){
                    api.trigger('error', err);
                });

                return status;
            },

            /**
             * Poll the status of a task
             *
             * @param {String} taskId - unique task identifier
             * @returns {taskQueueApi}
             */
            pollStatus : function pollStatus(taskId){

                var loop = 0;

                if(!config.url || !config.url.status){
                    throw new TypeError('config.url.status is not configured while getStatus() is being called');
                }

                /**
                 * gradually increase the polling interval to ease server load
                 * @private
                 * @param {Object} pollingInstance - a poll object
                 */
                var _updateInterval = function _updateInterval(pollingInstance){
                    var pollingInterval;
                    if(loop){
                        loop --;
                    }else{
                        pollingInterval = pollingIntervals.shift();
                        if(pollingInterval && pollingInterval.iteration && pollingInterval.interval){
                            loop = pollingInterval.iteration;
                            pollingInstance.setInterval(pollingInterval.interval);
                        }
                    }
                }

                api.pollStop();
                poll = polling({
                    action: function action() {
                        // get into asynchronous mode
                        var done = this.async();
                        api.getStatus(taskId).then(function(taskData){
                            if(taskData.status === 'finished'){
                                api.trigger('finished', taskData);
                                poll.stop();
                            }else{
                                api.trigger('running', taskData);
                                _updateInterval(poll);
                                done.resolve();
                            }
                        }).catch(function(){
                            done.reject();
                        });
                    }
                });
                _updateInterval(poll);
                poll.start();
                api.trigger('pollStart');

                return api;
            },

            /**
             * Stop the current polling
             *
             * @returns {taskQueueApi}
             */
            pollStop : function pollStop(){
                if(poll){
                    poll.stop();
                    api.trigger('pollStop');
                }
                return api;
            },

            /**
             * Remove a task identified by its unique task id
             *
             * @param {String} taskId - unique task identifier
             * @returns {Promise}
             */
            remove : function remove(taskId){

                var status;
                var error;

                if(!config.url || !config.url.remove){
                    throw new TypeError('config.url.remove is not configured while remove is being called');
                }

                status = request(config.url.remove, {taskId : taskId})
                    .then(function(taskData){
                        if(taskData && taskData.status === 'archived'){
                            return Promise.resolve(taskData);
                        }else{
                            return Promise.reject(new Error('removed task status should be archived'));
                        }
                    });

                status.catch(function(res){
                    api.trigger('error', res);
                });

                return status;
            }
        });

        return api;
    }
});
