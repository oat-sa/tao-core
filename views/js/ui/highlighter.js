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
 * xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 * xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 * xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 * xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery'
], function (
    $
) {
    'use strict';

    /**
     * @param {Object} options
     * @param {Object} options.selector
     * @param {String} option.className
     */
    return function(options) {
        var selector = options.selector; // fixme: pass an array of ranges to highlightRanges instead ? yes for keyboard selection

        var isWrapping = false;

        function toggleWrapping() {
            isWrapping = !isWrapping;
        }

        function wrapAllTextNodes(range, rootNode, startNode, startOffset, endNode, endOffset) {
            var childNodes = rootNode.childNodes;
            var node, i, right;

            for (i = 0; i < childNodes.length; i++) {
                node = childNodes[i];

                if (node.isSameNode(startNode)) {
                    if (range.startContainer.nodeType === window.Node.TEXT_NODE
                        && startOffset !== 0) {
                        right = node.splitText(startOffset);
                        // if needed, we correct the end offset
                        if (endOffset !== 0 && node.isSameNode(endNode)) {
                            endOffset -= startOffset;
                        }
                        // we defer the highlight to the newly created node
                        startOffset = 0;
                        startNode = right;
                        if (node.isSameNode(endNode)) {
                            endNode = right;
                        }
                    } else {
                        toggleWrapping();
                    }
                }

                if (node.isSameNode(endNode)) {
                    if (range.endContainer.nodeType === window.Node.TEXT_NODE
                        && endOffset !== 0
                    ) {
                        node.splitText(endOffset);

                    }
                }

                switch(node.nodeType) {
                    case window.Node.ELEMENT_NODE: {
                        wrapAllTextNodes(range, node, startNode, startOffset, endNode, endOffset); // recursive call
                        break;
                    }
                    case window.Node.TEXT_NODE: {
                        if (isWrapping) {
                            wrapTextNode(node, 0, node.textContent.length);
                            // todo: check reverse selection !
                        }
                        break;
                    }
                }

                if (node.isSameNode(endNode)) {
                    toggleWrapping();
                }
                // todo: break loop ?
            }
        }

        function wrapTextNode(node, startOffset, endOffset) {
            // $(node).wrap($('<span>', {
            //     class: options.className
            // }));

            var content = node.textContent;
            var before = content.slice(0, startOffset);
            var toWrap = content.slice(startOffset, endOffset);
            var after = content.slice(endOffset);
            var $wrapper = $('<span>', {
                class: options.className
            });
            node.textContent = toWrap;
            $(node).wrap($wrapper);

            if (before.length) {
                console.log('adding before: ' + before);
                $wrapper.before(before);
            }
            // node.appendChild(toWrap);
            if (after.length) {
                $wrapper.after(after);
            }
            // node.innerHTML = $wrapped.html();
            // var $node = $(node);
            //
            // if (before.length) {
            //     $node.append(document.createTextNode(before));
            // }
            // $node.append($wrapped);
            //
            // if (after.length) {
            //     $node.append(document.createTextNode(after));
            // }
/*
            var wrapped =
                content.slice(0, startOffset)
                + '<span class="' + options.className + '">'
                + content.slice(startOffset, endOffset)
                + '</span>'
                + content.slice(endOffset);
                */
            console.log('===============');
            console.log('textContent = ' + node.textContent);
            console.log('startOffset = ' + startOffset);
            console.log('endOffset = ' + endOffset);
            console.log('toWrap = ' + toWrap);
            /*
            // $(node).html(wrapped);
            // debugger;
            // $(node).replaceWith($(wrapped));
            node.innerHTML = wrapped;
            */
        }

        return {
            highlightRanges: function highlightRanges() {
                var ranges = selector.getRanges();
                var startNode, endNode;
                // var rangeContent;
                // console.log('=============================================');

                ranges.forEach(function(range) {

                    var highlightContainer = document.createElement('span');
                    highlightContainer.className = options.className;

                    // deal with the easiest case first: highlight a plain text without any dom nodes
                    if (range.commonAncestorContainer.nodeType === window.Node.TEXT_NODE) {
                        range.surroundContents(highlightContainer);

                    // now the fun stuff: highlighting content with mixed text and dom nodes
                    } else {

                        if (range.startContainer.nodeType === window.Node.ELEMENT_NODE) {
                            startNode = range.startContainer.childNodes[range.startOffset];
                        } else {
                            startNode = range.startContainer;
                        }
                        if (range.endContainer.nodeType === window.Node.ELEMENT_NODE) {
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

                    // var rangeContent = range.extractContents();
                    // console.dir(range.commonAncestorContainer);
                    // console.dir(range.startContainer);
                    // console.dir(range.endContainer);
                    // console.dir(getStartNode(range));
                    // console.dir(getEndNode(range));
                    // wrapAllTextNodes(
                    //     range.commonAncestorContainer,
                    //     getStartNode(range),
                    //     getEndNode(range)
                    // );
                    // range.insertNode(rangeContent);
                    // console.dir(rangeContent);

                    // console.dir(rangeContent);
                    // var highlightContainer = document.createElement('span');
                    // highlightContainer.className = options.className;
                    //
                    // highlightContainer.appendChild(rangeContent);
                    // range.insertNode(highlightContainer);
                    // rangeContent = range.extractContents();
                    // wrapTextNode(rangeContent);
                });

            }
        };
    };
});
