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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define([
    'ui/toggler',
    'ui/disabler',
    'ui/adder',
    'ui/deleter',
    'ui/incrementer',
    'ui/inplacer',
    'ui/btngrouper',
    'ui/durationer',
    'ui/selecter',
    'ui/modal',
    'ui/tooltip',
    'ui/form',
    'ui/validator',
    'ui/groupvalidator'
], function(toggler, disabler, adder, deleter, incrementer, inplacer, btngrouper, durationer, selecter, modal, tooltip, form) {
    'use strict';

    /**
     * svg4everybody is only required by certain legacy browsers to enable the use of external SVG sprites.
     * The functionality below is based on lib/polyfill/svg4everybody/svg4everybody.js
     */
    function initCrossBrowserSvg() {

        // code taken from svg4everybody
        var newerIEUA = /\bTrident\/[567]\b|\bMSIE (?:9|10)\.0\b/;
        var webkitUA = /\bAppleWebKit\/(\d+)\b/;
        var olderEdgeUA = /\bEdge\/12\.(\d+)\b/;

        if(newerIEUA.test(navigator.userAgent) ||
            (navigator.userAgent.match(olderEdgeUA) || [])[1] < 10547 ||
            (navigator.userAgent.match(webkitUA) || [])[1] < 537){

            require(['lib/polyfill/svg4everybody/svg4everybody'], function(svg4everybody){
                svg4everybody();
            });
        }
    }

    /**
     * @author Bertrand Chevrier <bertrand@taotesting.com>
     * @exports ui
     */
    return {

        /**
         * Start up the components lookup and data-attr listening
         * @param {jQueryElement} $container - to lookup within
         */
        startEventComponents : function($container){
            adder($container);
            btngrouper($container);
            deleter($container);
            disabler($container);
            toggler($container);
            inplacer($container);
            modal($container);
            form($container);
            this.startDomComponent($container);
        },

        startDomComponent : function($container){
            incrementer($container);
            durationer($container);
            selecter($container);
            initCrossBrowserSvg();
            tooltip.lookup($container);
        }
    };
});
