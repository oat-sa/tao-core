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
 *      taskQueueManager = taskQueue({url:{status: 'serviceUrl'}})
            .on('running', function (taskData) {
                console.log('running');
            }).on('finished', function (taskData) {
                console.log('finished !');
            }).on('error', function (err) {
                self.trigger('error', err);
            }).pollStatus('task123xyz');
 *
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
            getStatus : function getStatus(taskId){
                var status;
                var error;
                if(!config.url || !config.url.status){
                    error = new Error('config.url.status is not defined');
                    api.trigger('error', error);
                    return Promise.reject(error);
                }

                status = request(config.url.status, {taskId: taskId})
                    .then(function(taskData){
                        //check taskData
                        if(taskData && taskData.status){
                            return Promise.resolve(taskData);
                        }
                        console.log('error', taskData);
                        return Promise.reject(new Error('failed to get task data'));
                    });

                status.catch(function(err){
                    api.trigger('error', err);
                });

                return status;
            },
            pollStatus : function pollStatus(taskId){

                var loop = 0;

                /**
                 * gradually increase the polling interval to ease server load
                 * @param pollingInstance - a poll object
                 */
                var updateInterval = function updateInterval(pollingInstance){
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
                                updateInterval(poll);
                                done.resolve();
                            }
                        }).catch(function(){
                            done.reject();
                        });
                    }
                });
                updateInterval(poll);
                poll.start();
                api.trigger('pollStart');

                return api;
            },
            pollStop : function pollStop(){
                if(poll){
                    poll.stop();
                    api.trigger('pollStop');
                }
                return api;
            },
            remove : function remove(taskId){

                var status;
                var error;

                if(!config.url || !config.url.remove){
                    error = new Error('config.url.remove is not defined');
                    api.trigger('error', error);
                    return Promise.reject(error);
                }

                status = request(config.url.remove, {taskId : taskId})
                    .then(function(taskData){
                        if(taskData.status === 'archived'){
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
