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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 *
 */

/**
 * Simple encoder for XML/HTML entities
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(function() {
    'use strict';

    /**
     * The list of chars to be encoded
     * @type {String[]}
     */
    var guiltyChars = ['&', '<', '>', '"'];

    return {

        /**
         * Encode a string with guilty chars to the matching html entity codes
         * @param {String} input
         * @returns {String} encoded input
         */
        encode: function encode(input) {
            input = input + '';

            return input.split('').map(function(character){
                return guiltyChars.indexOf(character) > -1 ? '&#' + character.charCodeAt() + ';' : character;
            }).join('');
        },

        /**
         * Decode a string
         * @param {String} input - with html entity chars
         * @returns {String} decoded
         */
        decode: function decode(input) {
            input = input + '';

            return input.replace(/&#(\d+);/g, function(matches, code) {
                return String.fromCharCode(code);
            });
        }
    };
});
