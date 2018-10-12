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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * @author Ivan Klimchuk <klimchuk@1pt.com>
 */
define(['module', 'moment'], function (module, moment) {
    'use strict';

    var configuration = module.config();

    /**
     * Util object for manipulate locale dependent data
     * @exports util/locale
     */
    return {

        /**
         * Returns config of component
         * @returns {*}
         */
        getConfig: function getConfig() {
            return configuration;
        },

        /**
         * Sets config of component
         * @param config
         */
        setConfig: function setConfig(config) {
            configuration = config || {};
        },

        /**
         * Returns current system decimal separator
         * @returns {string}
         */
        getDecimalSeparator: function getDecimalSeparator() {
            return this.getConfig() && this.getConfig().decimalSeparator ? this.getConfig().decimalSeparator : '.';
        },

        /**
         * Returns current system thousands separator
         * @returns {string}
         */
        getThousandsSeparator: function getThousandsSeparator() {
            return this.getConfig() && this.getConfig().thousandsSeparator ? this.getConfig().thousandsSeparator : '';
        },

        /**
         * Returns datetime format
         * @return {string}
         */
        getDateTimeFormat : function getDateTimeFormat() {
            return this.getConfig() && this.getConfig().dateTimeFormat ? this.getConfig().dateTimeFormat : 'DD/MM/YYYY HH:mm:ss';
        },

        /**
         * Parse float values with process locale features
         * @param numStr
         * @returns {Number}
         */
        parseFloat: function (numStr) {
            var thousandsSeparator = this.getThousandsSeparator(),
                decimalSeparator = this.getDecimalSeparator();

            // discard all thousand separators:
            if (thousandsSeparator.length) {
                numStr = numStr.replace(new RegExp('\\' + thousandsSeparator, 'g'), '');
            }
        
            // standardise the decimal separator as '.':
            if (decimalSeparator !== '.') {
                numStr = numStr.replace(new RegExp('\\' + '.', 'g'), '_')
                               .replace(new RegExp('\\' + decimalSeparator, 'g'), '.');
            }
        
            // now the numeric string can be correctly parsed with the native parseFloat:
            return parseFloat(numStr);
        },

        /**
         * Parse integer values with process locale features
         * @param number
         * @param numericBase
         * @returns {Number}
         */
        parseInt: function (number, numericBase) {
            var thousandsSeparator = this.getThousandsSeparator();

            if (thousandsSeparator.length) {
                number = number.replace(new RegExp('\\' + thousandsSeparator, 'g'), '');
            }

            return parseInt(number, numericBase);
        },

        /**
         * Parse unix timestamp
         * Note that user's (browser's) timezone will be used.
         * @param {Number} timestamp
         * @return string
         */
        formatDateTime: function (timestamp) {
            return moment(timestamp, 'X').format(this.getDateTimeFormat());
        }
    };

});
