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
 * @example
 * taskQueueModelFactory({
 *        url : {
 *            get: urlHelper.route('get', 'TaskQueueWebApi', 'taoTaskQueue'),
 *            archive: urlHelper.route('archive', 'TaskQueueWebApi', 'taoTaskQueue'),
 *            all : urlHelper.route('getAll', 'TaskQueueWebApi', 'taoTaskQueue'),
 *            download : urlHelper.route('download', 'TaskQueueWebApi', 'taoTaskQueue')
 *        },
 *        pollSingleIntervals : [
 *            {iteration: 4, interval:1000},
 *        ],
 *        pollAllIntervals : [
 *            {iteration: 0, interval:5000},
 *        ]
 *    }).pollAll()
 */
define([
    'jquery',
    'lodash',
    'core/promise',
    'core/eventifier',
    'core/polling',
    'core/dataProvider/request',
    'ui/feedback',
    'jquery.fileDownload'
], function ($, _, Promise, eventifier, polling, request, feedback) {
    'use strict';

    var _defaults = {
        url : {
            get: '',
            archive: '',
            all : '',
            download : ''
        },
        pollSingleIntervals : [
            {iteration: 4, interval:1000},
        ],
        pollAllIntervals : [
            {iteration: 10, interval:5000},
            {iteration: 0, interval:10000}//infinite
        ]
    };

    /**
     * Check if two tasks have equivalent task status
     * @param {Object} task1 - a task object to be compared
     * @param {Object} task2 - another task object to be compared
     * @returns {Boolean}
     */
    function hasSameState(task1, task2){
        if(task1.status === task2.status){
            return true;
        }else if(task1.status === 'created' || task1.status === 'in_progress'){
            return  (task2.status === 'created' || task2.status === 'in_progress');
        }
        return false;
    }

    /**
     * Create a task queue model to communicates with the server REST API
     *
     * @param {Object} config
     * @param {Object} config.url - the list of server endpoints
     * @param {String} config.url.get - the url to get the status log for a single task
     * @param {String} config.url.archive - the url to archive a task
     * @param {String} config.url.all - the url to get the status for all tasks for the current user
     * @param {String} config.url.download - the url to download a file created by the task
     * @param {Array} config.pollSingleIntervals - the array of poll intervals that will be used to regulate the polling speed for a simple task
     *         e.g. {iteration: 4, interval:1000} means that it will poll up to four times every 1000ms.
     * @param {Array} config.pollAllIntervals - the array of poll intervals that will be used to regulate the main polling speed.
     *         e.g. {iteration: 10, interval:1000} means that it will poll up to 10 times every 5000ms.
     *         e.g. {iteration: 0, interval:10000} means that it will poll up to 10000ms indefinitely
     *
     * @return {taskQueueModel}
     */
    return function taskQueueModel(config) {

        var model;

        /**
         * cached array of task data
         * @type {Object}
         */
        var _cache;

        /**
         * store instance of single polling
         * @type {Object}
         */
        var singlePollings = {};

        var getPollSingleIntervals = function getPollSingleIntervals(){
            if(config.pollSingleIntervals && _.isArray(config.pollSingleIntervals)){
                return _.cloneDeep(config.pollSingleIntervals);
            }
        };

        var getPollAllIntervals = function getPollAllIntervals(){
            if(config.pollAllIntervals && _.isArray(config.pollAllIntervals)){
                return _.cloneDeep(config.pollAllIntervals);
            }
        };

        config = _.defaults(config || {}, _defaults);

        /**
         * @typedef taskQueueModel - central model to query the backend's REST API for task queue
         */
        model = eventifier({

            /**
             * Modify the task queue REST API endpoints
             * @param urls - the new endpoints
             * @returns {taskQueueModel}
             */
            setEndpoints : function setEndpoints(urls){
                _.assign(config.url, urls || {});
                return this;
            },

            /**
             * Get the status of a task identified by its unique task id
             *
             * @param {String} taskId - unique task identifier
             * @returns {Promise}
             */
            get : function get(taskId){
                var status;

                if(!config.url || !config.url.get){
                    throw new TypeError('config.url.get is not configured while get() is being called');
                }

                status = request(config.url.get, {taskId: taskId}, 'GET', {}, true)
                    .then(function(taskData){
                        //check taskData
                        if(taskData && taskData.status){
                            if(_cache){
                                //detect change
                                if(!_cache[taskData.id]){
                                    model.trigger('singletaskadded', taskData);
                                }else if(!hasSameState(_cache[taskData.id], taskData)){
                                    //check if the status has changed
                                    model.trigger('singletaskstatuschange', taskData);
                                }
                            }else{
                                _cache = {};
                            }
                            _cache[taskData.id] = taskData;
                            return taskData;
                        }
                        return Promise.reject(new Error('failed to get task data'));
                    });

                status.catch(function(err){
                    model.trigger('error', err);
                });

                return status;
            },

            /**
             * Get the task data, but try the cache first!
             * @returns {Promise}
             */
            getCached : function getCached(taskId) {
                if (_cache && _cache[taskId]) {
                    return Promise.resolve(_cache[taskId]);
                }
                return this.get(taskId);
            },

            /**
             * Get the status of all task identified by their unique task id
             *
             * @returns {Promise} - resolved when the server response has been received
             */
            getAll : function getAll(){
                var status;

                if(!config.url || !config.url.all){
                    throw new TypeError('config.url.all is not configured while getAll() is being called');
                }

                status = request(config.url.all, {limit: 100}, 'GET', {}, true)
                    .then(function(taskData){
                        var newCache = {};
                        //check taskData
                        if(taskData){
                            if(_cache){
                                //detect change
                                _.forEach(taskData, function(task){
                                    var id = task.id;
                                    if(!_cache[id]){
                                        model.trigger('multitaskadded', task);
                                    }else if(!hasSameState(_cache[id], task)){
                                        //check if the status has changed
                                        model.trigger('multitaskstatuschange', task);
                                    }
                                    newCache[id] = task;
                                });
                                _.forEach(_.difference(_.keys(_cache), _.keys(newCache)), function(id){
                                    model.trigger('taskremoved', _cache[id]);
                                });
                            }else{
                                _.forEach(taskData, function(task){
                                    newCache[task.id] = task;
                                });
                            }
                            //update local cache
                            _cache = newCache;

                            return taskData;
                        }
                        return Promise.reject(new Error('failed to get all task data'));
                    });

                status.catch(function(err){
                    model.trigger('error', err);
                });

                return status;
            },

            /**
             * Remove a task identified by its unique task id
             *
             * @param {String} taskId - the task id
             * @returns {Promise} - resolved when achive action done
             */
            archive : function archive(taskId){

                var status;

                if(!config.url || !config.url.archive){
                    throw new TypeError('config.url.archive is not configured while archive() is being called');
                }

                status = request(config.url.archive, {taskId : taskId}, 'GET', {}, true);

                status.catch(function(res){
                    model.trigger('error', res);
                });

                return status;
            },

            /**
             * Poll status for all tasks
             * @param {Boolean} [immediate] - tells if the polling should immediately start (otherwise, will wait until the next iteration)
             * @returns {taskQueueModel}
             */
            pollAll : function pollAll(immediate){

                var self = this;
                var loop = 0;
                var pollingIntervals = getPollAllIntervals();

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
                        if(pollingInterval && typeof pollingInterval.iteration !== 'undefined' && pollingInterval.interval){
                            loop = pollingInterval.iteration;
                            pollingInstance.setInterval(pollingInterval.interval);
                        }
                    }
                };

                if(!config.url || !config.url.all){
                    throw new TypeError('config.url.all is not configured while pollAll() is being called');
                }

                if(!this.globalPolling){
                    //no global polling yet, create one
                    this.globalPolling = polling({
                        action: function action() {
                            // get into asynchronous mode
                            var done = this.async();
                            var statusArr;
                            model.getAll().then(function(taskDataArray){
                                model.trigger('pollAll', taskDataArray);

                                //smart polling: stop polling when there is no task in progress
                                statusArr = _.map(taskDataArray, 'status');
                                if(statusArr.indexOf('in_progress') === -1 && statusArr.indexOf('created') === -1){
                                    return done.reject();
                                }

                                _updateInterval(self.globalPolling);
                                done.resolve();
                            }).catch(function(){
                                done.reject();
                            });
                        }
                    });
                    _updateInterval(this.globalPolling);
                    this.globalPolling.start();
                    this.trigger('pollAllStart');
                }else{
                    this.globalPolling.start();
                    this.trigger('pollAllStart');
                }

                if(immediate){
                    //if it is request to immediate start polling, start it now
                    this.globalPolling.next();
                }

                return model;
            },

            /**
             * Stop the main polling action
             * @returns {taskQueueModel}
             */
            pollAllStop : function pollAllStop(){
                if(this.globalPolling){
                    this.globalPolling.stop();
                    this.trigger('pollAllStop');
                }
                return this;
            },

            /**
             * Start a single fast polling for a single task id
             * @param {String} taskId - the task id
             * @returns {Promise} resolved when the single polling action finishes
             */
            pollSingle : function pollSingle(taskId){

                var self = this;
                var loop = 0;

                var pollingIntervals = getPollSingleIntervals();

                /**
                 * gradually increase the polling interval to ease server load
                 * @private
                 * @param {Object} pollingInstance - a poll object
                 */
                var _updateInterval = function _updateInterval(pollingInstance){
                    var pollingInterval;
                    if(loop){
                        loop --;
                        return true;//continue polling
                    }else{
                        pollingInterval = pollingIntervals.shift();
                        if(pollingInterval && pollingInterval.iteration && pollingInterval.interval){
                            loop = pollingInterval.iteration;
                            pollingInstance.setInterval(pollingInterval.interval);
                            return true;//continue polling
                        }else{
                            //stop polling
                            return false;
                        }
                    }
                };

                if(!config.url || !config.url.get){
                    throw new TypeError('config.url.get is not configured while pollSingle() is being called');
                }

                if(singlePollings[taskId]){
                    singlePollings[taskId].stop();
                }

                return new Promise(function(resolve){
                    var poll = polling({
                        action: function action() {
                            // get into asynchronous mode
                            var done = this.async();
                            self.get(taskId).then(function(taskData){
                                if(taskData.status === 'completed' || taskData.status === 'failed'){
                                    //the status status could be either "completed" or "failed"
                                    poll.stop();
                                    self.trigger('pollSingleFinished', taskId, taskData);
                                    resolve({finished: true, task: taskData});
                                }else if(!_updateInterval(poll)){
                                    //if we have reached the end of the total polling config
                                    self.trigger('pollSingleFinished', taskId, taskData);
                                    resolve({finished: false, task: taskData});
                                }else{
                                    self.trigger('pollSingle', taskId, taskData);
                                    done.resolve();//go to next poll iteration
                                }

                            }).catch(function(){
                                done.reject();
                            });
                        }
                    });
                    _updateInterval(poll);
                    singlePollings[taskId] = poll.start();
                    self.trigger('pollSingleStart', taskId);
                });
            },

            /**
             * Interrupt a single polling action
             * @param {String} taskId - the task id
             * @returns {model}
             */
            pollSingleStop : function pollSingleStop(taskId){
                if(singlePollings && singlePollings[taskId]){
                    singlePollings[taskId].stop();
                    this.trigger('pollSingleStop', taskId);
                }
                return this;
            },

            /**
             * Call a task creation url
             * @param {String} url - the server side task queue creation service
             * @param {Object} [data] - request data
             * @returns {promise} - resolved when task creation response is sent back by the server
             */
            create : function create(url, data){
                var taskCreate, self = this;
                taskCreate = request(url, data, 'POST', {}, true)
                    .then(function(creationResult){
                        //poll short result:
                        if(creationResult && creationResult.task && creationResult.task.id){
                            self.trigger('created', creationResult);
                            return self.pollSingle(creationResult.task.id).then(function(result){
                                if(creationResult.extra){
                                    result.extra = creationResult.extra;
                                }
                                if(result.finished){
                                    //send to queue
                                    self.trigger('fastFinished', result);
                                }else{
                                    //send to queue
                                    self.trigger('enqueued', result);
                                }
                                return result;
                            });
                        }
                        return Promise.reject(new Error('failed to get task data'));
                    });

                taskCreate.catch(function(err){
                    model.trigger('error', err);
                });

                return taskCreate;
            },

            /**
             * Call the task result file download endpoint
             * @param {String} taskId - the task id
             * @returns {promise} - resolved when the download popup is shown
             */
            download : function download(taskId){

                if(!config.url || !config.url.download){
                    throw new TypeError('config.url.download is not configured while download() is being called');
                }

                return new Promise(function(resolve, reject){
                    $.fileDownload(config.url.download, {
                        httpMethod: 'POST',
                        data: {taskId : taskId},
                        successCallback : function(result){
                            resolve(result);
                        },
                        failCallback: function (err) {
                            reject(err);
                        }
                    });
                });
            },

            /**
             * Call the task result redirection endpoint
             * @param {String} taskId - the task id
             * @returns {Promise}
             */
            redirect : function redirect(taskId){
                return this.getCached(taskId).then(function(taskData) {
                    var redirectUrl = (taskData || {}).redirectUrl;
                    if(!redirectUrl){
                        throw new TypeError('config.redirectUrl is not configured while redirect() is being called');
                    }

                    if(redirectUrl.indexOf('http') !== 0) {
                        throw new TypeError('redirectUrl does not look like a proper url: ' + redirectUrl);
                    }

                    return request(taskData.redirectUrl);
                })
                .then(function(response){
                    if(!_.isEmpty(response)){
                        window.location.href = response;
                    }
                })
                .catch(function(err){
                    //202 -> resource deleted, handle it has a user error
                    if(err && err.code === 202 && err.response && err.response.errorMessage){
                        feedback().error(err.response.errorMessage);
                    }
                    throw err;
                });
            }
        });

        return model;
    };
});
