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
    'util/url',
    'core/store'
], function(_, $, module, url, store){
    'use strict';

    var storeName = 'logger';
    var storeKey  = 'queue';
    var defaultConfig = {
        url : url.route('log', 'log', 'tao'),
        id : 'taolog'
    };
    var config = _.defaults(module.config() || {}, defaultConfig);
    var storeId = storeName + '-' + config.id;
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
        return store(storeId).then(function(actionStore) {
            return actionStore.setItem(storeKey, logQueue);
        });
    }

    /**
     * Flush the log messages store and retrieve the data
     * @returns {Promise} resolves with the flushed data
     */
    function flush() {
        logQueue = [];
        return store(storeId).then(function(actionStore) {
            return actionStore.getItem(storeKey).then(function(queue){
                return actionStore.setItem(storeKey, logQueue).then(function(){
                    return queue;
                });
            });
        });
    }

    /**
     * Send log messages from the queue
     * @param {Object} messages - log messages array
     */
    function send(messages) {
        return $.ajax({
            url : config.url,
            type : 'POST',
            cache : false,
            data : {messages: messages},
            dataType : 'json'
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
            flush().then(function (messages) {
                send(messages).fail(function() {
                    //in case of connectivity issue messages return back to the storage to be sent with next call
                    _.forEach(messages, function (message) {
                        push(message);
                    });
                });
            });
        }
    };
});
