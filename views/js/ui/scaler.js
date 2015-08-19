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
 */
define([
], function () {
    'use strict';


    var ns = 'scaler';

    /**
     * Figure out the vendor prefix, if any
     */
    var prefix = (function() {
        var _prefixes = ['webkit', 'ms'],
        i = _prefixes.length,
        style = window.getComputedStyle(document.body);
        
        if(style.getPropertyValue('transform')) {
            return '';
        }
        while(i--) {
            if(style[_prefixes[i] + 'Transform'] !== undefined) {
                return '-' + _prefixes[i] + '-';
            }
        }
    }());


    /**
     * Scale the container with the given factor. Factors < 1 will be filtered out.
     *
     * @param $container
     * @param {number} factor
     */
    function scale($container, factor) {

        var cssObj = {};

        // defaults to 1
        factor = factor || 1;

        // avoid negative scale factors
        factor = Math.max(0, factor);

        cssObj[prefix + 'transform'] = 'scale(' + factor + ',' + factor + ')';

        $container.css(cssObj);
        $container.trigger('scale.' + ns, { factor: factor });
    }


    /**
     * @exports
     */
    return {
        scale: scale,
        reset: function($container) {
            scale($container, 1);
            $container.trigger('reset.' + ns);
        }
    };
});