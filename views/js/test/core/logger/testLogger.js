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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
/**
 * This logger keeps all logged messages into an object that the consumer can retrieve and query.
 * It can be used in unit tests to assert that specific messages have been logged.
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash'
], function(_){
    'use strict';

    var messages;

    function resetMessages() {
        messages = {
            trace : [],
            debug : [],
            info  : [],
            warn  : [],
            error : [],
            fatal : []
        };
    }

    resetMessages();

    /**
     * Initialize the logger API
     * @returns {logger} the logger
     */
    return {
        log: function log(record){
            if (_.isArray(messages[record.level])) {
                messages[record.level].push(record);
            }
        },

        getMessages: function getMessages() {
            return messages;
        },

        reset: function reset() {
            resetMessages();
        }
    };
});
