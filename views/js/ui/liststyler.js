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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 *
 * @author dieter <dieter@taotesting.com>
 * @requires jquery
 * @requires lodash
 * @requires core/pluginifier
 * @requires util/strPad
 * @requires util/ucfirst
 */
define([
    'jquery',
    'lodash',
    'core/pluginifier',
    'util/strPad',
    'util/ucfirst'
], function ($, _, Pluginifier, strPad, ucfirst) {
    'use strict';

    var ns = 'liststyler';

    var defaults = {
    };

    /**
     * list styles - at the time of writing this is the list of cross browser compatible
     * styles.
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/list-style-type
     */
    var listStyles = {
        none:'',
        disc:   '\u25cf',
        circle: '\u25cb',
        square: '\uffed',
        decimal: '1.',
        'decimal-leading-zero': '01',
        'lower-alpha': 'a.',
        'upper-alpha': 'A.',
        'lower-roman': 'i.',
        'upper-roman': 'I.',
        'lower-greek': '\u03b1',
        'armenian': '\u0531',
        'georgian': '\u10d0'
    };


    /**
     * Populate selectBox with options
     * @param selectBox
     */
    function populate(selectBox) {
        _.forOwn(listStyles, function(symbol, style) {
            selectBox.appendChild(new Option(style, style));
        });
    }

    /**
     * Prepare select2 formatting
     *
     * @param option
     * @returns {*}
     */
    function formatOption (option) {
        var symbol = listStyles[option.value];
        var styleArr = option.value.split('-'),
            l = styleArr.length,
            i;
        var text    = '';
        for(i = 0; i < l; i++){
            text += (i ? '\u00A0' : '') + ucfirst(styleArr[i]);
        }
        return $('<span/>',{ text: text }).data('data-list-symbol', symbol);
    }


    /**
     * Hint: to get a proper two-column design of the select box you should a fixed font
     *
     * @type {{init: init}}
     */
    var ListStyler = {


        /**
         * Initialize the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.

         * @example $('selector').liststyler({target : $('target') });
         * @public
         *
         * @constructor
         * @returns {*}
         */
        init: function (options) {


            //get options using default
            options = $.extend(true, {}, defaults, options);

            return this.each(function () {
                var $elt = $(this),
                    $target = options.target || $($elt.data('target'));

                populate(this);

                var currClass = '';

                $elt.on('change', function() {
                    if(currClass) {
                        $target.removeClass(currClass);
                    }
                    currClass = 'ls-' + this.value;
                    $target.addClass(currClass);
                });

                $elt.select2({
                    templateResult: formatOption
                });

                /**
                 * The plugin has been created
                 * @event ListStyler#create.toggler
                 */
                $elt.trigger('create.' + ns);
            });
        },


        /**
         * Destroy the plugin completely.
         * Called the jQuery way once registered by the Pluginifier.
         *
         * @example $('selector').toggler('destroy');
         * @public
         */
        destroy: function () {
            this.each(function () {
                var $elt = $(this);

                /**
                 * The plugin have been destroyed.
                 * @event ListStyler#destroy.toggler
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
    };

    //Register the toggler to behave as a jQuery plugin.
    Pluginifier.register(ns, ListStyler);
});
