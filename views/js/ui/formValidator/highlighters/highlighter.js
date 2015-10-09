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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * @author Aleh Hutnikau
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/formValidator/highlighters/message',
    'ui/formValidator/highlighters/tooltip',
    'ui/formValidator/highlighters/qtip'
], function ($, _, __, messageHighlighter, tooltipHighlighter, qtipHighlighter) {
    'use strict';

    /**
     * Error field highlighter
     * @param {Object} options
     * @param {string} [options.type] -
     * @param {string} [options.errorClass] -
     * @param {string} [options.errorMessageClass] -
     * @constructor
     */
    function Highlighter(options) {
        var self = this;

        this.options = {
            type : 'message'
        };

        this.init = function init() {
            self.options = $.extend(true, self.options, options);

            switch(options.type) {
                case 'message':
                    messageHighlighter.apply(this, Array.prototype.slice.call(arguments));
                    break;
                case 'tooltip':
                    tooltipHighlighter.apply(this, Array.prototype.slice.call(arguments));
                    break;
                case 'qtip':
                    qtipHighlighter.apply(this, Array.prototype.slice.call(arguments));
                    break;
                default:
                    throw new TypeError('Highlighter not found');
            }
        };

        this.highlight = function ($field, message) {
            console.log('should be overwritten');
        };

        this.unhighlight = function ($field) {
            console.log('should be overwritten');
        };

        this.destroy = function ($field) {
            console.log('should be overwritten');
        };

        this.init();
    };

    return Highlighter;
});