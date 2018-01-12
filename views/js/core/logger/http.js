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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * The logger sending data to the server.
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
define([
    'lodash',
    'jquery',
    'util/url'
], function(_, $, url){
    'use strict';

    var defaultConfig = {
        url : url.route('log', 'Log', 'tao'),
        level: 'warning'
    };
    var config;
    var logQueue = [];

    /**
     * Push log message into log queue
     * @param {Object} message - log message
     * @returns {Promise} resolves when the message is stored
     */
    function push(message) {
        logQueue.push(message);
    }

    /**
     * Flush the log messages store and retrieve the data
     * @returns {Promise} resolves with the flushed data
     */
    function flush() {
        var messages = logQueue;
        logQueue = [];
        _.forEach(messages, function (message) {
            send(message);
        });
    }

    /**
     * Send log messages from the queue
     * @param {Object} message - log message
     */
    function send(message) {
        $.ajax({
            url : config.url,
            type : 'POST',
            cache : false,
            data : {json : JSON.stringify(message)},
            dataType : 'json',
            global : false,
            error : function () {
                push(message);
            }
        });
    }

    /**
     * @returns {logger} the logger
     */
    return {
        setConfig : function setConfig(newConfig){
            config = _.defaults(newConfig || {}, defaultConfig);
            if (_.isArray(config.url)) {
                config.url = url.route.apply(url, config.url);
            }
        },
        /**
         * log message
         * @param {Object} record - See core/logger/api::log() method
         */
        log : function log(message) {
            if (this.checkMinLevel(config.level, message.level)) {
                push(message);
                flush();
            }
        }
    };
});