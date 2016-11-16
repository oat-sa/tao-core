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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */
/**
 * This is just a tiny wrapper around window.getSelection() to allow mocking in unit tests
 * This has no legacy support for IE10 deprecated API: document.selection.createRange()
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([], function () {
    'use strict';



    /**
     * @returns {Object} The selector helper
     */
    return {
        /**
         * Get the current selected ranges
         * @returns {Range[]}
         */
        getAllRanges: function getRanges() {
            var selection;
            var ranges = [];
            var i;

            if (window.getSelection) {
                selection = window.getSelection();

                for (i = 0; i < selection.rangeCount; i++) {
                    ranges.push(selection.getRangeAt(0));
                }
            }
            return ranges;
        },

        removeAllRanges: function removeAllRanges() {
            if (window.getSelection) {
                window.getSelection().removeAllRanges();
            }
        },

        rangeCount: function rangeCount() {
            if (window.getSelection) {
                return window.getSelection().rangeCount;
            }
            return 0;
        }
    };
});
