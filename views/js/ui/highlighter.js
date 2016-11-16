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
 * Highlighter helper: wraps every text node found within a Range object.
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'jquery'
], function (_, $) {
    'use strict';

    /**
     * Data attribute used to logically group the wrapping nodes into a single selection
     * @type {string}
     */
    var GROUP_ATTR = 'data-hl-group';

    /**
     * Children of those nodes cannot be highlighted
     * @type {string[]}
     */
    var containersBlackList = [
        'textarea',
        'math',
        'script'
    ];

    /**
     * @param {Object} options
     * @param {Object} options.className - name of the class that will be used by the wrappers tags to highlight text
     * @param {Object} options.containerSelector - allows to select the root Node in which highlighting is allowed
     */
    return function(options) {
        var className = options.className;
        var containerSelector = options.containerSelector;

        /**
         * used in recursive loops to decide if we should wrap or not the current node
         * @type {boolean}
         */
        var isWrapping = false;

        /**
         * performance improvement to break out of a potentially big recursive loop once the wrapping has ended
         * @type {boolean}
         */
        var hasWrapped = false;

        /**
         * used in recursive loops to assign a group Id to the current wrapped node
         * @type {number}
         */
        var currentGroupId;

        /**
         * used in recursive loops to build the index of text nodes
         * @type {number}
         */
        var textNodesIndex;

        /**
         * Returns the node in which highlighting is allowed
         * @returns {Element}
         */
        function getContainer() {
            return $(containerSelector).get(0);
        }

        /**
         * Highlight all text nodes within each given range
         * @param {Range[]} ranges - array of ranges to highlight, may be given by the helper selector.getAllRanges()
         */
        function highlightRanges(ranges) {
            ranges.forEach(function(range) {
                var rangeInfos;

                if (isRangeValid(range)) {
                    currentGroupId = getAvailableGroupId();

                    // easy peasy: highlighting a plain text without any DOM nodes
                    if (isWrappable(range.commonAncestorContainer)
                        && !isWrappingNode(range.commonAncestorContainer.parentNode)
                    ) {
                        range.surroundContents(getWrapper(currentGroupId));

                    // now the fun stuff: highlighting a mix of text and DOM nodes
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

                        isWrapping = false;
                        hasWrapped = false;
                        wrapTextNodesInRange(range.commonAncestorContainer, rangeInfos);
                    }
                }

                // clean up the markup after wrapping...
                range.commonAncestorContainer.normalize();
                currentGroupId = 0;
                isWrapping = false;
                reindexGroups(getContainer());
                mergeAdjacentWrappingNodes(range.commonAncestorContainer);
            });
        }

        /**
         * Check if a range is valid
         * @param {Range} range
         * @returns {boolean}
         */
        function isRangeValid(range) {
            var rangeInContainer =
                $.contains(getContainer(), range.commonAncestorContainer)
                || getContainer().isSameNode(range.commonAncestorContainer);

            var emptyRange =
                range.startContainer.isSameNode(range.endContainer)
                && range.startOffset === range.endOffset;

            return (rangeInContainer && !emptyRange);
        }

        /**
         * Core wrapping function. Traverse the DOM tree and highlight (= wraps) all text nodes within the given range.
         * Recursive.
         *
         * @param {Node} rootNode - top of the node hierarchy in which text nodes will be searched
         * @param {Object} rangeInfos
         * @param {Node} rangeInfos.startNode - node on which the selection starts
         * @param {Node} rangeInfos.startNodeContainer - container of the startNode, or the start node itself in case of text nodes
         * @param {number} rangeInfos.startOffset - same as range.startOffset, but not read-only to allow override
         * @param {Node} rangeInfos.endNode - node on which the selection ends
         * @param {Node} rangeInfos.endNodeContainer - container of the endNode, or the end node itself in case of text nodes
         * @param {number} rangeInfos.endOffset - same as range.endOffset, but not read-only to allow override
         */
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
                        isWrapping = true;
                    }
                }

                if (currentNode.isSameNode(rangeInfos.endNode) && isText(rangeInfos.endNodeContainer)) {
                    if (rangeInfos.endOffset !== 0) {
                        currentNode.splitText(rangeInfos.endOffset);
                    } else {
                        isWrapping = false;
                    }
                }

                // wrap the current node...
                if (isText(currentNode))  {
                    wrapTextNode(currentNode, currentGroupId);

                // ... or continue deeper in the node tree
                } else if (isElement(currentNode)) {
                    wrapTextNodesInRange(currentNode, rangeInfos);
                }

                // end wrapping ?
                if (currentNode.isSameNode(rangeInfos.endNode)) {
                    isWrapping = false;
                    hasWrapped = true;
                    break;
                }
            }
        }

        /**
         * wraps a text node into the highlight span
         * @param {Node} node - the node to wrap
         * @param {number} groupId - the highlight group
         */
        function wrapTextNode(node, groupId) {
            if (isWrapping
                && !isWrappingNode(node.parentNode)
                && isWrappable(node)
            ) {
                $(node).wrap($(getWrapper(groupId)));
            }
        }

        /**
         * We need to reindex the groups after a user highlight: either to merge groups or to resolve inconsistencies
         * Recursive.
         *
         * @param {Node} rootNode
         */
        function reindexGroups(rootNode) {
            var childNodes = rootNode.childNodes;
            var i, currentNode, parent;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                if (isWrappable(currentNode)) {
                    parent = currentNode.parentNode;
                    if (isWrappingNode(parent)) {
                        if (isWrapping === false) {
                            currentGroupId++;
                        }
                        isWrapping = true;
                        parent.setAttribute(GROUP_ATTR, currentGroupId); // set the new group Id
                    } else {
                        isWrapping = false;
                    }
                } else if (isElement(currentNode)) {
                    reindexGroups(currentNode);
                }
            }
        }

        /**
         * Some highlights may result in having adjacent wrapping nodes. We remove them here to get a cleaner markup.
         *
         * @param {Node} rootNode
         */
        function mergeAdjacentWrappingNodes(rootNode) {
            var childNodes = rootNode.childNodes;
            var i, currentNode;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                if (isWrappingNode(currentNode)) {
                    while (isWrappingNode(currentNode.nextSibling)) {
                        currentNode.firstChild.textContent += currentNode.nextSibling.firstChild.textContent;
                        currentNode.parentNode.removeChild(currentNode.nextSibling);
                    }
                } else if (isElement(currentNode)) {
                    mergeAdjacentWrappingNodes(currentNode);
                }
            }
        }

        /**
         * Remove all wrapping nodes from markup
         */
        function clearHighlights() {
            $(getContainer()).find('.' + className).each(function() {
                var $wrapped = $(this);
                $wrapped.replaceWith($wrapped.text());
            });
        }

        /**
         * Index-related functions:
         * ========================
         * To allow saving and restoring highlights on an equivalent, but different, DOM tree
         * (for example if the markup is deleted and re-created)
         * we build an index containing the status of each text node:
         * - not highlighted
         * - fully highlighted
         * - partially highlighted (= with inline ranges)
         *
         * This index will be used to restore a selection on the new DOM tree
         */

        /**
         * Bootstrap the recursive function that will traverse the DOM tree to index text Nodes
         * @returns {Object[]}
         */
        function getHighlightIndex() {
            var highlightIndex = [];
            var rootNode = getContainer();
            rootNode.normalize();

            textNodesIndex = 0;

            buildHighlightIndex(rootNode, highlightIndex); //todo: make this a pure function ?
            return highlightIndex;
        }

        /**
         *
         * @param rootNode
         * @param highlightIndex
         * @returns {*}
         */
        function buildHighlightIndex(rootNode, highlightIndex) {
            var childNodes = rootNode.childNodes;
            // var highlightIndex = [];
            var i, currentNode, entry, newInlineRange, offset;
            var skippedNodes;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                // A simple node not highlighted and isolated (= not followed by an wrapped text)
                if (isWrappable(currentNode) && !isWrappingNode(currentNode.nextSibling)) {
                    highlightIndex[textNodesIndex] = { highlighted: false };
                    textNodesIndex++;

                // an isolated node (= not followed by a highlightable text) with its whole content highlighted
                } else if (isWrappingNode(currentNode) && !isWrappable(currentNode.nextSibling)) {
                    highlightIndex[textNodesIndex] = {
                        highlighted: true,
                        groupId: currentNode.getAttribute(GROUP_ATTR)
                    };
                    textNodesIndex++;

                // less straightforward: a succession of (at least) 1 wrapping node with 1 wrappable text node, in either order, and possibly more
                // the trick is to create a unique text node on which we will be able to re-apply multiple partial highlights
                // for this, we use 'inlineRanges'
                } else if (isHotNode(currentNode) && isHotNode(currentNode.nextSibling)) {
                    skippedNodes = -1;
                    entry = {
                        highlighted: true,
                        inlineRanges: []
                    };

                    offset = 0;

                    while(currentNode) {
                        if (isWrappingNode(currentNode)) {
                            newInlineRange = {
                                groupId: currentNode.getAttribute(GROUP_ATTR)
                            };
                            if (isText(currentNode.previousSibling)) {
                                newInlineRange.startOffset = offset ;
                            }
                            if (isText(currentNode.nextSibling)) {
                                newInlineRange.endOffset = offset + currentNode.textContent.length;
                            }
                            entry.inlineRanges.push(newInlineRange);
                        }

                        offset += currentNode.textContent.length;
                        currentNode = (isHotNode(currentNode.nextSibling)) ? currentNode.nextSibling : null;
                        skippedNodes++;
                    }
                    i += skippedNodes;

                    highlightIndex[textNodesIndex] = entry;
                    textNodesIndex++;

                // go deeper in the node tree...
                } else if (isElement(currentNode)) {
                    highlightIndex.concat(buildHighlightIndex(currentNode, highlightIndex));
                }
            }
            return highlightIndex;
        }

        function highlightFromIndex(highlightIndex) {
            var rootNode = getContainer();
            rootNode.normalize();

            textNodesIndex = 0;

            restoreHighlight(rootNode, highlightIndex);
        }

        function restoreHighlight(rootNode, highlightIndex) {
            var childNodes = rootNode.childNodes;
            var i, j, currentNode, entry, skippedNodes, parent, childCount;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                skippedNodes = 0;

                if (isWrappable(currentNode)) {
                    parent = currentNode.parentNode;
                    childCount = parent.childNodes.length;

                    entry = highlightIndex[textNodesIndex];

                    if (entry.highlighted === true) {
                        var range = document.createRange();
                        if (_.isArray(entry.inlineRanges)) {
                            for (j = entry.inlineRanges.length; j > 0; j -= 1) {
                                var inlineRange = entry.inlineRanges[j - 1];

                                range.setStart(currentNode, inlineRange.startOffset || 0);
                                range.setEnd(currentNode, inlineRange.endOffset || currentNode.textContent.length);
                                range.surroundContents(getWrapper(inlineRange.groupId));
                            }

                        } else {
                            range.selectNodeContents(currentNode);
                            range.surroundContents(getWrapper(entry.groupId));
                        }
                        // we do want to loop over the nodes created by the wrapping operation
                        skippedNodes = parent.childNodes.length - childCount;
                        i += skippedNodes;
                    }
                    textNodesIndex++;

                } else if (isElement(currentNode)) {
                    restoreHighlight(currentNode, highlightIndex);
                }
            }
        }


        /**
         * Helpers
         */

        /**
         * Check if the given node is a wrapper
         * @param {Node} node
         * @returns {boolean}
         */
        function isWrappingNode(node) {
            return isElement(node)
                && node.tagName.toLowerCase() === 'span'
                && node.className === className;
        }

        /**
         * Check if the given node can be wrapped
         * @param {Node} node
         * @returns {boolean}
         */
        function isWrappable(node) {
            return isText(node)
                && $(node).closest(containersBlackList.join(',')).length === 0
                && node.textContent.trim().length > 0;
        }

        /**
         * Create a wrapping node
         * @param {number} groupId
         * @returns {Element}
         */
        function getWrapper(groupId) {
            var wrapper = document.createElement('span');
            wrapper.className = className;
            wrapper.setAttribute(GROUP_ATTR, groupId);
            return wrapper;
        }

        /**
         * Returns the first unused group Id
         * @returns {number}
         */
        function getAvailableGroupId() {
            var id = currentGroupId || 1;
            while ($(getContainer()).find('[' + GROUP_ATTR + '=' + id + ']').length !== 0) {
                id++;
            }
            return id;
        }

        /**
         * Check if the given node is an element
         * @param {Node} node
         * @returns {boolean}
         */
        function isElement(node) {
            return node && typeof node === 'object' && node.nodeType === window.Node.ELEMENT_NODE;
        }

        /**
         * Check if the given node is of type text
         * @param {Node} node
         * @returns {boolean}
         */
        function isText(node) {
            return node && typeof node === 'object' && node.nodeType === window.Node.TEXT_NODE;
        }

        /**
         * a "Hot Node" is either wrappable text node or a wrapper
         * @param {Node} node
         * @returns {boolean}
         */
        function isHotNode(node) {
            return isWrappingNode(node) || isWrappable(node);
        }

        /**
         * Public API of the highlighter helper
         */
        return {
            highlightRanges:    highlightRanges,
            highlightFromIndex: highlightFromIndex,
            getHighlightIndex:  getHighlightIndex,
            clearHighlights:    clearHighlights
        };
    };
});
