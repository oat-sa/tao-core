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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Logger API, highly inspired and mostly compatible from https://github.com/trentm/node-bunyan
 *
 * @example
 * var logger = loggerFactory('component');
 * logger.info('Message');
 * logger.debug('Formated %s', 'message');
 * logger.trace({ anotherField : true}, 'hello');
 * logger.error(new Error('Something went wrong'));
 *
 * var childLogger = logger.child({ type : 'sub-component'});
 * childLogger.warn('oops');
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/format',
    'core/promise'
], function(_, format, Promise){
    'use strict';

    /**
     * The default level
     */
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

    /**
     * Where messages dwells
     */
    var logQueue = [];

    /**
     * Get the actual level as a string,
     * fallback to the default level.
     * @param {String|Number} [level] - the level
     * @returns {String} the level
     */
    var getLevel = function getLevel(level){
        if(typeof level === 'undefined' || (_.isString(level) && !_.has(levels, level)) ){
            return defaultLevel;
        }
        if(_.isNumber(level)){
            return _.findKey(levels, function(l){
                return l === level;
            }) || defaultLevel;
        }
        return level;
    };

    /**
     * Get the actual level as a number,
     * fallback to the default level.
     * @param {String|Number} [level] - the level
     * @returns {Number} the level
     */
    var getLevelNum = function getLevelNum(level){
        if(_.isString(level) && _.has(levels, level)){
            return levels[level];
        }
        if(_.isNumber(level) && _.contains(levels, level)){
            return level;
        }
        return levels[defaultLevel];
    };

    /**
     * Check whether the given level is above the minimum level threshold
     * @param {String|Number} minlevel- the minimum level
     * @param {String|Number} [level] - the level to check
     * @returns {Boolean}
     */
    var checkMinLevel = function checkMinLevel(minLevel, level) {
        return getLevelNum(level) >= getLevelNum(minLevel);
    };

    /**
     * Creates a logger instance
     *
     * @param {String} name - each logger instance MUST have a name
     * @param {String|Number} [minLevel] - the minimum logging level
     * @param {Object} [fields] - fields to add to all records
     *
     * @returns {logger} a new logger instance
     */
    var loggerFactory = function loggerFactory(name, minLevel, fields){

        var baseRecord;
        var logger;

        if(!_.isString(name) || _.isEmpty(name)){
            throw new TypeError('A logger needs a name');
        }

        if(_.isPlainObject(minLevel) && typeof field === 'undefined'){
            fields = minLevel;
            minLevel = defaultLevel;
        }

        baseRecord = _.defaults(fields || {}, {
            name     : name,
            pid      : 1,    // only for compat
            hostname : navigator.userAgent
        });

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
             * @param {Object} [recordFields] - fields to add to the log record
             * @param {String|Error} message - the message to log
             * @param {...String} [rest] - rest parameters if the message is formatted
             * @returns {logger} chains
             */
            log : function log(level, recordFields, message){

                var record;
                var err;
                var rest = [];
                var time = new Date().toISOString();

                //without providers or not the level, we don't log.
                if(loggerFactory.providers === false || !checkMinLevel(minLevel || defaultLevel, level)){
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
                    record.err = err;

                } else {
                    record.msg = format.apply(null, [message].concat(rest));
                }

                _.merge(record, baseRecord, recordFields);

                logQueue.push(record);

                loggerFactory.flush();

                return this;
            },

            /**
             * Get/set the default level of the logger
             * @param {String|Number} [level] - set the default level
             * @returns {String|logger} the default level as a getter or chains as a setter
             */
            level : function(value){
                if(typeof value !== 'undefined'){
                    //update the partial function
                    minLevel = getLevelNum(value);
                    return this;
                }
                return getLevel(minLevel);
            },

            /**
             * Fork the current logger to create a child logger :
             * same config + child fields
             *
             * @param {Object} [childFields] - specialized child fields
             * @return {logger} the child logger
             */
            child : function child(childFields){
                return loggerFactory(name, minLevel, _.defaults(childFields, baseRecord));
            }
        };

        //augment the logger by each level
        return _.reduce(levels, function reduceLogLevel(target, level, levelName){
            target[levelName] = _.partial(logger.log, level);
            return target;
        }, logger);
    };

    /**
     * Exposes the levels
     * @type {Object}
     */
    loggerFactory.levels = levels;

    /**
     * The list of providers bound to the logger.
     * @type {Boolean|Array} false means we don't log, array even empty we keep the logs
     */
    loggerFactory.providers = false;

    /**
     * Load providers from AMD modules
     * @param {Object} providerConfigs - provider's modules to load and register
     * @returns {Promise} resolves once modules are registered
     */
    loggerFactory.load = function load(providerConfigs){
        var self = this;
        var modules = [];
        this.providers = [];

        return new Promise( function(resolve, reject) {
            //we can load the loggers dynamically
            _.forEach(providerConfigs, function (providerConfig, providerName) {
                modules.push(providerName);
            });
            require(modules, function(){
                var loadedProviders = [].slice.call(arguments);
                _.forEach(loadedProviders, function (provider, moduleKey){
                    try {
                        self.register(provider, providerConfigs[modules[moduleKey]]);
                    } catch(err){
                        reject(err);
                    }
                });

                //flush messages that arrived before the providers are there
                self.flush();

                resolve();

            }, reject);
        });
    };

    /**
     * A logger provider provides with a way to log
     * @typedef {Object} loggerProvider
     * @property {Function} log - called with the message in parameter
     * @param {Object} providerConfig - provider's config
     * @throws TypeError
     */
    loggerFactory.register = function register(provider, providerConfig){

        if(!_.isPlainObject(provider) || !_.isFunction(provider.log)){
            throw new TypeError('A log provider is an object with a log method');
        }
        //propogate checkMinLevel function
        provider.checkMinLevel = checkMinLevel;
        if (_.isFunction(provider.setConfig)) {
            provider.setConfig(providerConfig);
        }
        this.providers = this.providers || [];
        this.providers.push(provider);
    };


    /**
     * Flush the messages queue into the providers
     */
    loggerFactory.flush = function flush(){
        if(_.isArray(this.providers) && this.providers.length > 0){
            _.forEach(logQueue, function(message){
                //forward to the providers
                _.forEach(loggerFactory.providers, function(provider){
                    provider.log.call(provider, message);
                });
            });
            //clear the queue
            logQueue = [];
        }
    };

    /**
     * Change the default level for all loggers
     * @param {String|Number} [level] - set the default level
     * @returns {String} the defined level
     */
    loggerFactory.setDefaultLevel = function setDefaultLevel(level){
        defaultLevel = getLevel(level);
        return defaultLevel;
    };

    return loggerFactory;
});
