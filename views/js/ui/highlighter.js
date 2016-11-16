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
], function (
    _,
    $
) {
    'use strict';

    var ELEMENT_NODE = window.Node.ELEMENT_NODE;
    var TEXT_NODE = window.Node.TEXT_NODE;

    var GROUP_DATA_ATTR = 'data-hl-group';

    var containersBlackList = [
        'textarea',
        'math',
        'script'
    ];

    /**
     * @param {Object} options
     */
    return function(options) {
        var className = options.className;
        var containerSelector = options.containerSelector;

        var isWrapping = false;
        var hasWrapped = false;

        var currentGroupId;
        var textNodesIndex;
        var skipNext;
        var allRanges;
        var newRange;


        function getContainer() {
            return $(containerSelector).get(0);
        }

        function highlightRanges(ranges) {
            ranges.forEach(function(range) {
                var rangeInfos;

                if (isRangeValid(range)) {
                    console.log('highlighting range');
                    currentGroupId = getAvailableGroupId();

                    // easy peasy: highlighting a plain text without any nested DOM nodes
                    if (canBeHighlighted(range.commonAncestorContainer)
                        && !isWrappingNode(range.commonAncestorContainer.parentNode)
                    ) {
                        range.surroundContents(getWrapper(currentGroupId).get(0));

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

                        wrapTextNodesInRange(range.commonAncestorContainer, rangeInfos);
                    }
                }

                // clean up markup after wrapping...
                range.commonAncestorContainer.normalize();
                currentGroupId = 0;
                isWrapping = false;
                reindexGroups(getContainer());
                mergeAdjacentWrappingNodes(range.commonAncestorContainer);
            });
        }

        function isRangeValid(range) {
            var rangeInContainer =
                $.contains(getContainer(), range.commonAncestorContainer)
                || getContainer().isSameNode(range.commonAncestorContainer);
            var emptyRange =
                isText(range.startContainer)
                && range.startContainer.isSameNode(range.endContainer)
                && range.startOffset === range.endOffset;
            if (emptyRange) {
                console.log('Empty range !!!');
            }

            return (rangeInContainer && !emptyRange);
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

                if (currentNode.isSameNode(rangeInfos.endNode) && isText(rangeInfos.endNodeContainer)) {
                    if (rangeInfos.endOffset !== 0) {
                        currentNode.splitText(rangeInfos.endOffset);
                    } else {
                        isWrapping = false;
                    }
                }
                // console.dir(rangeInfos);
                // debugger;

                // do the wrapping...
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

        function toggleWrapping() {
            isWrapping = !isWrapping;
        }

        function wrapTextNode(node, groupId) {
            if (isWrapping
                && node.textContent.length > 0
                && !isWrappingNode(node.parentNode)
                && canBeHighlighted(node)
            ) {
                $(node).wrap(getWrapper(groupId));
            }
        }

        // todo: change to hasWrappingNode ?
        function isWrappingNode(node) {
            return isElement(node)
                && node.tagName.toLowerCase() === 'span'
                && node.className === className;
        }

        function changeGroupId(node, groupId) {
            node.setAttribute(GROUP_DATA_ATTR, groupId);
        }

        // todo: change to isWrappable ?
        function canBeHighlighted(node) {
            return isText(node)
                && $(node).closest(containersBlackList.join(',')).length === 0;
        }

        function getWrapper(groupId) {
            var $wrapper = $('<span>', {
                class: className
            });
            $wrapper.attr(GROUP_DATA_ATTR, groupId);
            return $wrapper;
        }

        function getAvailableGroupId() {
            var id = currentGroupId || 1;
            while ($(getContainer()).find('[' + GROUP_DATA_ATTR + '=' + id + ']').length !== 0) {
                id++;
            }
            return id;
        }

        function isElement(node) {
            return node && typeof node === 'object' && node.nodeType === ELEMENT_NODE;
        }

        function isText(node) {
            return node && typeof node === 'object' && node.nodeType === TEXT_NODE;
        }

        // todo: rename
        function mergeAdjacentWrappingNodes(rootNode) {
            var childNodes = rootNode.childNodes;
            var i, currentNode;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                if (isWrappingNode(currentNode)) {
                    // console.log('considering ' + currentNode.textContent);
                    // console.log('with sibling ' + currentNode.nextSibling.textContent);

                    while (isElement(currentNode.nextSibling)
                        && isWrappingNode(currentNode.nextSibling)
                        ) {
                        currentNode.firstChild.textContent += currentNode.nextSibling.firstChild.textContent;
                        currentNode.parentNode.removeChild(currentNode.nextSibling);
                    }
                } else if (isElement(currentNode)) {
                    mergeAdjacentWrappingNodes(currentNode);
                }
            }
        }

        function reindexGroups(rootNode) {
            var childNodes = rootNode.childNodes;
            var i, currentNode, parent;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                if (isText(currentNode) && canBeHighlighted(currentNode)) {
                    parent = currentNode.parentNode;
                    if (isWrappingNode(parent)) {
                        if (isWrapping === false) {
                            currentGroupId++;
                        }
                        isWrapping = true;
                        changeGroupId(parent, currentGroupId);
                    } else {
                        isWrapping = false;
                    }
                } else if (isElement(currentNode)) {
                    reindexGroups(currentNode);
                }
            }
        }

        //
        function getHighlightIndex() { // fixme: rename to getIndex or getHighlightIndex
            var highlightIndex = [];
            var rootNode = getContainer(); //fixme: clone node
            rootNode.normalize();

            textNodesIndex = 0;
            skipNext = false;

            return buildHighlightIndex(rootNode, highlightIndex);
        }

        // a hot node is either a highlightable text node or a highlight wrapper
        function isHotNode(node) {
            return isWrappingNode(node) || canBeHighlighted(node);
        }

        function buildHighlightIndex(rootNode, highlightIndex) {
            var childNodes = rootNode.childNodes;
            var i, currentNode, parent, entry, newInlineRange, offset;
            var skippedNodes;
            var infiniteGuard;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                parent = currentNode.parentNode;
                console.log('======== ' + currentNode.textContent);

                // A simple node text not highlighted and isolated (= not followed by an wrapped text)
                if (canBeHighlighted(currentNode) && !isWrappingNode(currentNode.nextSibling)) {
                    console.log('zeroCase with ' + currentNode.textContent);
                    highlightIndex[textNodesIndex] = { highlighted: false };
                    textNodesIndex++;

                // an isolated node (= not followed by a highlightable text) with its whole content highlighted
                } else if (isWrappingNode(currentNode) && !canBeHighlighted(currentNode.nextSibling)) {
                    console.log('firstCase with ' + currentNode.textContent);
                    highlightIndex[textNodesIndex] = {
                        highlighted: true,
                        groupId: currentNode.getAttribute(GROUP_DATA_ATTR)
                    };
                    textNodesIndex++;

                // less straightforward: at least a succession of a wrapping node with a wrappable text node, in either order, and possibly more
                } else if (isHotNode(currentNode) && isHotNode(currentNode.nextSibling)) {
                    console.log('secondCase with ' + currentNode.textContent);
                    skippedNodes = -1;
                    entry = {
                        highlighted: true,
                        inlineRanges: []
                    };

                    offset = 0;

                    while(currentNode) {
                        console.log('subnode ' + currentNode.textContent);

                        if (isWrappingNode(currentNode)) {
                            newInlineRange = {
                                groupId: currentNode.getAttribute(GROUP_DATA_ATTR)
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
                    console.log('thirdCase with ' + currentNode.textContent);
                    highlightIndex.concat(buildHighlightIndex(currentNode, highlightIndex));
                } else {
                    console.log('wtf am I doing here ?!');
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

                if (canBeHighlighted(currentNode)) {
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
                                range.surroundContents(getWrapper(inlineRange.groupId).get(0));
                            }

                        } else {
                            range.selectNodeContents(currentNode);
                            range.surroundContents(getWrapper(entry.groupId).get(0));
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

        function clearHighlights($containerToClean) {
            $containerToClean.find('.' + className).each(function() {
                var $wrapped = $(this);
                $wrapped.replaceWith($wrapped.text());
            });
        }

        return {
            highlightRanges:        highlightRanges,
            highlightFromIndex:     highlightFromIndex,
            getHighlightIndex:      getHighlightIndex,
            clearHighlights:        clearHighlights
        };
    };
});
