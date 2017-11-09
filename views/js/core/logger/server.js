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
        url : url.route('log', 'log', 'tao')
    };
    var config = _.defaults(module.config() || {}, defaultConfig);
    var queue = [];

    /**
     * send log message to the server side
     * @param {Object} record - See core/logger/api::log() method
     */
    function send(record) {
        $.ajax({
            url : config.url,
            type : 'POST',
            cache : false,
            data : record,
            dataType : 'json',
        }).fail(function(jqXHR, textStatus, errorThrown) {
            queue.push(record);
        });
    }

    /**
     * Initialize the logger API with the console provider
     * @returns {logger} the logger
     */
    return {
        /**
         * log message
         * @param {Object} record - See core/logger/api::log() method
         */
        log : function log(record) {
            queue.push(record);
            _.forEach(queue, function (message) {
                var messageIndex = queue.indexOf(message);
                queue.splice(messageIndex, 1);
                send(message);
            });
        }
    };
});
