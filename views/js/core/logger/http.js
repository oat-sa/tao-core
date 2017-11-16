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
    'module',
    'util/url'
], function(_, $, module, url){
    'use strict';

    var defaultConfig = {
        url : url.route('log', 'log', 'tao'),
        id : 'taolog'
    };
    console.log(module);
    var config = _.defaults(module.config() || {}, defaultConfig);
    var logQueue = [];

    if (_.isArray(config.url)) {
        config.url = url.route.apply(url, config.url);
    }

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
        return $.ajax({
            url : config.url,
            type : 'POST',
            cache : false,
            data : {json: message},
            dataType : 'json'
        }).fail(function () {
            push(message);
        });
    }

    /**
     * @returns {logger} the logger
     */
    return {
        /**
         * log message
         * @param {Object} record - See core/logger/api::log() method
         */
        log : function log(record) {
            push(record);
            flush();
        }
    };
});
