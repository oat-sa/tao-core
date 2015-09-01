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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'util/capitalize'
], function ($, capitalize) {
    'use strict';

    /**
     * Adapts the size of several elements
     */
    var adaptSize = (function () {


        /**
         * The actual resize function
         *
         * @param {jQueryElements} elements
         * @param {object} dimensions
         * @private
         */
        var _resize = function ($elements, dimensions) {

            // This whole function is based on calculating the largest height/width.
            // Therefor the elements need to have style: height/width to be removed
            // since otherwise we could never track when something is actually getting smaller than before.
            $elements.each(function () {
                for (var dimension in dimensions) {
                    if (dimensions.hasOwnProperty(dimension)) {
                        $(this)[dimension]('auto');
                    }
                }
            });

            $elements.each(function () {
                for (var dimension in dimensions) {
                    if (dimensions.hasOwnProperty(dimension)) {
                        dimensions[dimension] = Math.max(dimensions[dimension], $(this)['outer' + capitalize(dimension)]());
                    }
                }
            });

            $elements.css(dimensions);
        };

        return {
            width: function ($elements) {
                _resize($elements, {width: 0});
            },
            height: function ($elements) {
                _resize($elements, {height: 0});
            },
            both: function ($elements) {
                _resize($elements, {height: 0, width: 0});
            }
        };

    })();

    return adaptSize;
});
