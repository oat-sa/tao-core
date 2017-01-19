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
 *
 * Logger API, highly inspired from https://github.com/trentm/node-bunyan
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/format'
], function(_, format){
    'use strict';

    var defaultLevel = 'info';

    var levels = {
        fatal : 60, // The service/app is going to stop or become unusable now. An operator should definitely look into this soon.
        error : 50, // Fatal for a particular request, but the service/app continues servicing other requests. An operator should look at this soon(ish).
        warn  : 40, // A note on something that should probably be looked at by an operator eventually.
        info  : 30, // Detail on regular operation.
        debug : 20, // Anything else, i.e. too verbose to be included in "info" level.
        trace : 10  // Logging from external libraries used by your app or very detailed application logging.
    };

    /**
     * Major version of the node-bunyan package (for compat)
     */
    var bunyanVersion = 0;

    var logQueue = [];

    var getLevel = function getLevel(level){
        if(_.isString(level) && !_.has(levels, level)){
            return defaultLevel;
        }
        if(_.isNumber(level)){
            return _.findKey(levels, function(l){
                return l === level;
            }) || defaultLevel;
        }
        return level;
    };

    var getLevelNum = function getLevelNum(level){
        if(_.isString(level) && _.has(levels, level)){
            return levels[level];
        }
        if(_.isNumber(level) && _.contains(levels, level)){
            return level;
        }
        return levels[defaultLevel];
    };

    var checkMinLevel = function checkMinLevel(minLevel, level) {
        return getLevelNum(level) >= getLevelNum(minLevel);
    };

    /**
     * Creates a logger instance
     *
     *
     * @returns {logger} a new logger instance
     */
    var loggerFactory = function loggerFactory(name, minLevel, fields){

        var baseRecord;
        var logger;
        var hasMinLevel;

        if(!_.isString(name) || _.isEmpty(name)){
            throw new TypeError('A logger need a name');
        }

        baseRecord = _.defaults(fields || {}, {
            name     : name,
            pid      : 1,    // only for compatk
            hostname : navigator.userAgent
        });

        hasMinLevel = _.partial(checkMinLevel, getLevelNum(minLevel));

        /**
         * Exposes a log method and one by log level, like logger.trace()
         *
         * @typedef logger
         */
        logger = {


            /**
             * Log messages by delegating to the provider
             *
             * @param {String|Number} level - the log level
             * @param {...String} messages - the messages
             * @returns {logger} chains
             */
            log : function log(level, recordFields, message){

                var record;
                var err;
                var rest = [];
                var time = new Date().toISOString();

                if(!hasMinLevel(level)){
                    return;
                }

                if(_.isString(recordFields) || recordFields instanceof Error){
                    message = recordFields;
                    recordFields = {};
                    rest = [].slice.call(arguments, 2);
                } else {
                    rest = [].slice.call(arguments, 3);
                }

                record = {
                    level : getLevel(level),
                    v     : bunyanVersion,
                    time  : time
                };

                if(checkMinLevel(levels.error, level) || message instanceof Error){
                    err = message instanceof Error ? message : new Error(message);

                    record.msg = err.message;
                    record.err = {
                        name : err.name,
                        message : err.message,
                        stack : err.stack
                    };

                } else {
                    record.msg = format.apply(null, [message].concat(rest));
                }

                _.merge(record, recordFields, baseRecord);

                logQueue.push(record);

                this.flush();

                return this;
            },

            /**
             * Get/set the default level of the logger
             * @param {String|Number} [level] - set the default level
             * @returns {Number|logger} the default level as a getter or chains as a setter
             */
            level : function(value){
                if(typeof value === 'undefined'){
                    //update the partial function
                    minLevel = getLevelNum(value);
                    hasMinLevel = _.partial(checkMinLevel, minLevel);
                    return this;
                }
                return minLevel;
            },

            child : function child(fields){

            },



            /**
             * Flush the message queue if there's at least on provider
             * @returns {logger} chains
             */
            flush : function flush(){
                if(loggerFactory.providers && loggerFactory.providers.length){
                    _.forEach(logQueue, function(message){
                        //forward to the providers
                        _.forEach(loggerFactory.providers, function(provider){
                            provider.log.call(provider, message);
                        });
                    });
                    //clear the queue
                    logQueue = [];
                }
                return this;
            }
        };

        //augment the logger by each level
        return _.reduce(levels, function reduceLogLevel(target, level, levelName){
            target[levelName] = _.partial(logger.log, level);
            return target;
        }, logger);
    };

    /**
     * A logger provider provides with a way to log
     * @typedef {Object} loggerProvider
     * @property {Function} log - called with the message in parameter
     * @throws TypeError
     */
    loggerFactory.register = function register(provider){

        if(!_.isPlainObject(provider) || !_.isFunction(provider.log)){
            throw new TypeError('A log provider is an object with a log method');
        }
        this.providers = this.providers || [];
        this.providers.push(provider);
    };

    /**
     * Exposes the levels
     * @type {Object}
     */
    loggerFactory.levels = levels;

    return loggerFactory;
});
