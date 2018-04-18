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
 *
 * Logger facade
 *
 * Load the logger providers based on the module configuration
 * and exposes the logger api
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'module',
    'core/logger/api'
], function(_, module, loggerFactory) {
    'use strict';

    /**
     * The default configuration if nothing
     * is found on the module config
     */
    var defaultConfig = {
        level : loggerFactory.levels.warn,
        loggers : {
            'core/logger/console' : {
                'level' : 'warn'
            }
        }
    };

    //the logger providers are configured through the AMD module config
    var config = _.defaults(module.config() || {}, defaultConfig);
    var logger = loggerFactory('core/logger');

    loggerFactory.setDefaultLevel(config.level);
    loggerFactory.load(config.loggers);

    /**
      * Catch uncaught errors
      * @param msg - error message
      * @param url - current url
      * @param line - line number
      * @param col - column number
      * @param error - error object (not all browsers support).
      * @return {boolean}
      */
    window.onerror = function (msg, url, line, col, error) {
        logger.error("Caught[via window.onerror]: '" + msg + "' from " + url + ":" + line + ":" + col);
    };

    /**
     * Expose explicitely an direct way to activate log levels
     * @param {String|Number} level - the new log level
     * @returns {String} the defined level
     */
    window.setTaoLogLevel = function setTaoLogLevel(level){
        return loggerFactory.setDefaultLevel(level);
    };

    //exposes the API
    return loggerFactory;
});
