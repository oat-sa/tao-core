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
 * Copyright (c) 2018 Open Assessment Technologies SA
 *
 */

/**
 * Limit a string by either word or character count
 *
 * @author dieter <dieter@taotesting.com>
 */
define([], function () {
    'use strict';

    return {

        /**
         * Limit a string by word count
         *
         * @param {string} str
         * @param {integer} maxWordCount
         * @returns {string}
         */
        limitByWordCount : function limitByWordCount(str, maxWordCount) {
            // contains alternating a word and whitespace
            // to make sure the original whitespace is retained
            var textArr  = str.match(/(([\S]+)|([\s]+))/g);
            var newText  = /\s+/.test(textArr[0]) ? textArr.shift() : '';
            while(maxWordCount && textArr.length) {
                newText += textArr.shift(); // word
                if(textArr.length){
                    newText += textArr.shift(); // white space
                }
                maxWordCount--;
            }
            newText = newText.replace(/\s+$/,''); // remove trailing space
            return newText;
        },

        /**
         * Limit a string by character count
         *
         * @param {string} str
         * @param {integer} maxCharCount
         * @returns {string|*}
         */
        limitByCharCount : function limitByCharCount(str, maxCharCount) {
            return str.substr(0, maxCharCount);
        }
    };
});
