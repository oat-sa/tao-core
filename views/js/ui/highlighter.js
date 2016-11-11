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
 * Highlighter helper: wraps every text node within a Range object.
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

    var containersBlackList = [
        'textarea',
        'math',
        'script'
    ];

    /**
     * @param {Object} options
     */
    return function(options) {
        var $wrapper = options.$wrapper;

        var isWrapping = false;
        var hasWrapped = false;

        function toggleWrapping() {
            isWrapping = !isWrapping;
        }

        function wrapTextNodesInRange(rootNode, rangeInfos) {
            var childNodes = rootNode.childNodes;
            var currentNode, i;

            for (i = 0; i < childNodes.length; i++) {
                if (hasWrapped) {
                    break;
                }
                currentNode = childNodes[i];

                // split current node in case the wrapping start/ends on a partially selected text node
                if (currentNode.isSameNode(rangeInfos.startNode)) {
                    if (isText(rangeInfos.startNodeContainer) && rangeInfos.startOffset !== 0) {
                        // we defer the wrapping to the next iteration of the loop
                        rangeInfos.startNode = currentNode.splitText(rangeInfos.startOffset);
                        rangeInfos.startOffset = 0;
                    } else {
                        toggleWrapping();
                    }
                }

                if (currentNode.isSameNode(rangeInfos.endNode)) {
                    if (isText(rangeInfos.endNodeContainer) && rangeInfos.endOffset !== 0) {
                        currentNode.splitText(rangeInfos.endOffset);
                    }
                }

                // wrap
                if (isElement(currentNode)) {
                    wrapTextNodesInRange(currentNode, rangeInfos);

                } else if (isText(currentNode)) {
                    wrapTextNode(currentNode);
                }

                // end wrapping ?
                if (currentNode.isSameNode(rangeInfos.endNode)) {
                    toggleWrapping();
                    hasWrapped = true;
                    break;
                }
            }
        }

        function wrapTextNode(node) {
            if (isWrapping && canBeHighlighted(node)) {
                $(node).wrap($wrapper.clone());
            }
        }

        function canBeHighlighted(textNode) {
            return $(textNode).closest(containersBlackList.join(',')).length === 0;
        }

        function isElement(node) {
            return node.nodeType === ELEMENT_NODE;
        }

        function isText(node) {
            return node.nodeType === TEXT_NODE;
        }

        return {
            highlightRanges: function highlightRanges(ranges) {
                ranges.forEach(function(range) {
                    var rangeInfos;

                    // deal with the easiest case first: highlight a plain text without any nested DOM nodes
                    if (isText(range.commonAncestorContainer) && canBeHighlighted(range.commonAncestorContainer)) {
                        range.surroundContents($wrapper.clone().get(0));

                    // now the fun stuff: highlighting content with mixed text and DOM nodes
                    } else {
                        rangeInfos = {
                            startNode: (isElement(range.startContainer))
                                ? range.startContainer.childNodes[range.startOffset]
                                : range.startContainer,
                            startNodeContainer: range.startContainer,
                            startOffset: range.startOffset,

                            endNode: (isElement(range.endContainer))
                                ? range.endContainer.childNodes[range.endOffset - 1]
                                : range.endContainer,
                            endNodeContainer: range.endContainer,
                            endOffset: range.endOffset
                        };

                        wrapTextNodesInRange(range.commonAncestorContainer, rangeInfos);
                    }
                });

            }
        };
    };
});
