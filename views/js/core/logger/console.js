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
 * A console based logger.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'module'
], function(_, module){
    'use strict';

    var defaultConfig = {
        level: 'info'
    };
    var config = _.defaults(module.config() || {}, defaultConfig);
    var mapping = {
        debug : 'debug',
        info  : 'info',
        notice  : 'notice',
        warning  : 'warning',
        error : 'error',
        critical : 'error',
        alert : 'error',
        emergency : 'error'
    };

    /**
     * Initialize the logger API with the console provider
     * @returns {logger} the logger
     */
    return {
        log : function log(record){
            var level = record.level;
            if (this.checkMinLevel(config.level, level)) {
                if(_.isFunction(window.console[mapping[level]])){
                    if(record.err){
                        window.console[mapping[level]].call(window.console, record.name, record.msg, record.err, record);
                    } else {
                        window.console[mapping[level]].call(window.console, record.name, record.msg, record);
                    }
                } else {
                    window.console.log('['+ level.toUpperCase() + ']', record.name, record.msg, record);
                }
            }
        }
    };
});
