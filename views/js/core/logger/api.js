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
 * TODO sprintf like messages
 * TODO structured messages { timestamp, context, message, stack, etc. } in order to to pattern based printing
 * TODO
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['lodash'], function(_){
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
     * Creates a logger instance based on the given provider
     * @param {Object} provider - the logger provider
     * @param {Function} provider.log - the function the logs are delegated to
     * @param {String} [context] - add a context in all logged messages
     * @returns {logger} a new logger instance
     */
    return function loggerFactory(provider, context){

        if(!_.isPlainObject(provider) || !_.isFunction(provider.log)){
            throw new TypeError('A log provider is an object with a log method');
        }

        /**
         * Exposes a log method and one by log level, like logger.trace()
         *
         * @typedef logger
         */
        var logger = {

            /**
             * Log messages by delegating to the provider
             *
             * @param {String|Number} [level] - the log level
             * @param {...String} messages - the messages
             */
            log : function log(level){
                var messages;

                //extract arguments : optional level and messages
                if(_.isString(level) && !_.isNumber(levels[level])){
                   messages = [].slice.call(arguments);
                   level = defaultLevel;
                }
                if(_.isNumber(level)){
                    level = _.findKey(levels, function(l){
                        return l === level;
                    }) || defaultLevel;
                }

                if(!messages){
                   messages = [].slice.call(arguments, 1);
                }

                //stringify the messages
                messages = _.map(messages, function(msg){
                    if(typeof msg === 'object'){
                        return JSON.stringify(msg);
                    }
                    return msg + '';
                });

                //prepend the context
                if(context){
                    messages.unshift('[' + context + ']');
                }
                return provider.log.call(provider, level, messages);
            }
        };

        //augment the logger by each level
        return _.reduce(levels, function reduceLogLevel(target, level, levelName){
            target[levelName] = _.partial(logger.log, level);
            return target;
        }, logger);
    };

});
