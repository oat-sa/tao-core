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

    var selection;

    if (!window.getSelection) {
        throw new Error('Browser does not support getSelection()');
    }

    selection = window.getSelection();

    function isEmpty(range) {
        return (range.startOffset === range.endOffset
            && range.startContainer.isSameNode(range.endContainer)
        );
    }

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

        removeAllRanges: function removeAllRanges() {
            selection.removeAllRanges();
        },

        hasRanges: function hasRanges() {
            return selection.rangeCount > 0;
        },

        hasNonEmptyRanges: function hasNonEmptyRanges() {
            var hasNonEmpty = false;
            var range;
            var i;

            if (!this.hasRanges()) {
                return false;
            }

            for (i = 0; i < selection.rangeCount; i++) {
                range = selection.getRangeAt(i);
                if (!isEmpty(range)) {
                    hasNonEmpty = true;
                }
            }
            return hasNonEmpty;
        },

        rangeCount: function rangeCount() {
            if (window.getSelection) {
                return window.getSelection().rangeCount;
            }
            return 0;
        }
    };
});
