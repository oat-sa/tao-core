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
        var $container = options.$container;

        var isWrapping = false;
        var hasWrapped = false;

        var currentGroupId;
        var textNodesIndex;
        var skipNext;
        var allRanges;
        var newRange;


        function highlightRanges(ranges) {
            ranges.forEach(function(range) {
                var rangeInfos;

                currentGroupId = getAvailableGroupId();

                // easy peasy: highlighting a plain text without any nested DOM nodes
                if (isText(range.commonAncestorContainer)
                    && canBeHighlighted(range.commonAncestorContainer)
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
                range.commonAncestorContainer.normalize();
                currentGroupId = 0;
                isWrapping = false;
                reindexGroups($container.get(0));
                mergeAdjacentWrappingNodes(range.commonAncestorContainer);
            });
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

                if (isElement(currentNode)) {
                    wrapTextNodesInRange(currentNode, rangeInfos);

                } else if (isText(currentNode)) {
                    wrapTextNode(currentNode, currentGroupId);
                }

                // end wrapping ?
                if (currentNode.isSameNode(rangeInfos.endNode)) {
                    toggleWrapping();
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
            while ($container.find('[' + GROUP_DATA_ATTR + '=' + id + ']').length !== 0) {
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
        function getVirtualRanges() { // fixme: rename to getIndex or getHighlightIndex
            var virtualRanges = [];
            var rootNode = $container.get(0); //fixme: clone node
            rootNode.normalize();

            textNodesIndex = 0;
            skipNext = false;

            return buildVirtualRanges(rootNode, virtualRanges);
        }

        // a hot node is either a highlightable text node or a highlight wrapper
        function isHotNode(node) {
            return isWrappingNode(node) || canBeHighlighted(node);
        }

        function buildVirtualRanges(rootNode, virtualRange) {
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
                    virtualRange[textNodesIndex] = { highlighted: false };
                    textNodesIndex++;

                // an isolated node (= not followed by a highlightable text) with its whole content highlighted
                } else if (isWrappingNode(currentNode) && !canBeHighlighted(currentNode.nextSibling)) {
                    console.log('firstCase with ' + currentNode.textContent);
                    virtualRange[textNodesIndex] = {
                        highlighted: true,
                        groupId: currentNode.getAttribute(GROUP_DATA_ATTR)
                    };
                    textNodesIndex++;

                // less straightforward: at least a succession of a wrapping node with a text node, in either order, and possibly more
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

                    virtualRange[textNodesIndex] = entry;
                    textNodesIndex++;

                // continue deeper in the node hierarchy
                } else if (isElement(currentNode)) {
                    console.log('thirdCase with ' + currentNode.textContent);
                    virtualRange.concat(buildVirtualRanges(currentNode, virtualRange));
                } else {
                    console.log('wtf am I doing here ?!');
                }
            }
            return virtualRange;
        }

        function highlightVirtualRanges(virtualRanges) {
            var rootNode = $container.get(0);
            rootNode.normalize();

            textNodesIndex = 0;
console.log('================ RESTORING');
            console.dir(virtualRanges);
            restoreHighlight(rootNode, virtualRanges);
        }

        function restoreHighlight(rootNode, virtualRanges) {
            var childNodes = rootNode.childNodes;
            var i, j, currentNode, entry, skippedNodes, parent, childCount;

            for (i = 0; i < childNodes.length; i++) {
                currentNode = childNodes[i];

                skippedNodes = 0;

                if (canBeHighlighted(currentNode)) {
                    parent = currentNode.parentNode;
                    childCount = parent.childNodes.length;

                    entry = virtualRanges[textNodesIndex];
                    console.log('dealing with ' + currentNode.textContent);
                    console.dir(entry);

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
                    restoreHighlight(currentNode, virtualRanges);
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
            highlightVirtualRanges: highlightVirtualRanges,
            getVirtualRanges:       getVirtualRanges,
            clearHighlights:        clearHighlights
        };
    };
});
