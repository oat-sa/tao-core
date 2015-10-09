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
    'qtip'
], function ($, _, __, tooltipster) {
    'use strict';

    /**
     * Error field highlighter
     * @param {Object} options
     * @param {string} [options.errorClass] - field error class
     * @see {Object} [options.qtip] - {@link here: http://qtip2.com} - more qtip plugin options
     * @constructor
     */
    function Highlighter() {
        var self = this;

        self.options = $.extend(true, {
            qtip : {
                position: {
                    my: 'bottom center',
                    at: 'top center'//, // at the bottom right of...
                    //target: $('.selector') // my target
                },
                show: { ready: true },
                hide: {
                    event: false
                },
                style : {
                    classes : 'qtip-rounded qtip-red'
                }
            }
        }, self.options);

        /**
         * Highlight field by class defined in <i>self.options.errorClass</i> and add error message after it.
         * @param {jQuery} $field - field element to be highlighted
         * @param {string} message - message text.
         */
        this.highlight = function highlight($field, message) {
            var options =  $.extend(true, self.options.qtip, {
                content: {
                    text: message
                }
            });
            $field.qtip(options);
            $field.addClass(self.options.errorClass);
        };

        /**
         * Unhighlight field (remove error class and error message).
         * @param {jQuery} $field
         */
        this.unhighlight = function unhighlight($field) {
            $field.removeClass(self.options.errorClass);
            $field.qtip('destroy', true);
        };

        this.destroy = function ($field) {
            $field.qtip('destroy', true);
        };
    }

    return Highlighter;
});