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
 * Tiny wrapper around window.getSelection()
 * This has no legacy support for IE10 deprecated API: document.selection.createRange()
 * Please note that multiple ranges are not part of the official spec and only supported in a few browsers. See:
 * http://w3c.github.io/selection-api/#methods
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([], function () {
    'use strict';

    var selection;

    if (!window.getSelection) throw new Error('Browser does not support getSelection()');

    selection = window.getSelection();

    /**
     * @returns {Object} The selector helper
     */
    return {

        /**
         * Get the current selected ranges
         * @returns {Range[]}
         */
        getAllRanges: function getRanges() {
            var i, allRanges = [];

            for (i = 0; i < selection.rangeCount; i++) {
                allRanges.push(selection.getRangeAt(i));
            }
            return allRanges;
        },

        /**
         * Remove all ranges from the selection
         */
        removeAllRanges: function removeAllRanges() {
            selection.removeAllRanges();
        },

        /**
         * Check if the selection contains any range
         * @returns {boolean}
         */
        hasRanges: function hasRanges() {
            return selection.rangeCount > 0;
        },

        /**
         * Check if the selection contains any empty range
         * @returns {boolean}
         */
        hasNonEmptyRanges: function hasNonEmptyRanges() {
            var i, range;

            if (!this.hasRanges()) {
                return false;
            }

            for (i = 0; i < selection.rangeCount; i++) {
                range = selection.getRangeAt(i);
                if (range.collapsed) {
                    return false;
                }
            }
            return true;
        }
    };
});
