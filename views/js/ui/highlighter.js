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
 * Highlighter helper.
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery'
], function (
    $
) {
    'use strict';

    var ELEMENT_NODE = window.Node.ELEMENT_NODE;
    var TEXT_NODE = window.Node.TEXT_NODE;

    /**
     * @param {Object} options
     * @param {Object} options.selector
     * @param {String} option.className
     */
    return function(options) {
        // fixme: pass an array of ranges to highlightRanges instead ? yes for keyboard selection or restoring selection
        var selector = options.selector;

        var isWrapping = false;
        var hasWrapped = false;

        function toggleWrapping() {
            isWrapping = !isWrapping;
        }

        function wrapAllTextNodes(range, rootNode, startNode, startOffset, endNode, endOffset) {
            var childNodes = rootNode.childNodes;
            var currentNode, i, right;

            for (i = 0; i < childNodes.length; i++) {
                if (hasWrapped) {
                    break;
                }
                currentNode = childNodes[i];

                if (currentNode.isSameNode(startNode)) {
                    if (range.startContainer.nodeType === TEXT_NODE
                        && startOffset !== 0) {
                        right = currentNode.splitText(startOffset);
                        // if needed, we correct the end offset
                        if (endOffset !== 0 && currentNode.isSameNode(endNode)) {
                            endOffset -= startOffset;
                        }
                        // we defer the highlight to the newly created node
                        startOffset = 0;
                        startNode = right;
                        if (currentNode.isSameNode(endNode)) {
                            endNode = right;
                        }
                    } else {
                        toggleWrapping();
                    }
                }

                if (currentNode.isSameNode(endNode)) {
                    if (range.endContainer.nodeType === TEXT_NODE
                        && endOffset !== 0
                    ) {
                        currentNode.splitText(endOffset);

                    }
                }

                switch(currentNode.nodeType) {
                    case ELEMENT_NODE:
                        wrapAllTextNodes(range, currentNode, startNode, startOffset, endNode, endOffset);
                        break;
                    case TEXT_NODE:
                        wrapTextNode(currentNode);
                        break;
                }

                if (currentNode.isSameNode(endNode)) {
                    toggleWrapping();
                    break;
                }
            }
        }

        function wrapTextNode(node) {
            if (isWrapping) {
                $(node).wrap($('<span>', {
                    class: options.className
                }));
            }
        }

        return {
            highlightRanges: function highlightRanges() {
                var ranges = selector.getRanges();
                var startNode, endNode;

                ranges.forEach(function(range) {

                    var highlightContainer = document.createElement('span');
                    highlightContainer.className = options.className;

                    // deal with the easiest case first: highlight a plain text without any nested dom nodes
                    if (range.commonAncestorContainer.nodeType === TEXT_NODE) {
                        range.surroundContents(highlightContainer);

                    // now the fun stuff: highlighting content with mixed text and dom nodes
                    } else {
                        // todo: check reverse selection !

                        if (range.startContainer.nodeType === ELEMENT_NODE) {
                            startNode = range.startContainer.childNodes[range.startOffset];
                        } else {
                            startNode = range.startContainer;
                        }
                        if (range.endContainer.nodeType === ELEMENT_NODE) {
                            endNode = range.endContainer.childNodes[range.endOffset - 1];
                        } else {
                            endNode = range.endContainer;
                        }


                        wrapAllTextNodes(
                            range,
                            range.commonAncestorContainer,
                            startNode,
                            range.startOffset,
                            endNode,
                            range.endOffset
                        );
                    }
                });

            }
        };
    };
});
