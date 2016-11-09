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

        function wrapAllTextNodes(rootNode, wrapStartNode, wrapEndNode) {
            var childNodes = rootNode.childNodes;
            var node, i;

            for (i = 0; i < childNodes.length; i++) {
                node = childNodes[i];

                if (node.isSameNode(wrapStartNode)) {
                    toggleWrapping();
                } else if (node.isSameNode(wrapEndNode)) {
                    toggleWrapping();
                }

                switch(node.nodeType) {
                    case window.Node.ELEMENT_NODE: {
                        wrapAllTextNodes(node); // recursive call
                        break;
                    }
                    case window.Node.TEXT_NODE: {
                        if (isWrapping) {
                            wrapTextNode(node);
                        }
                        break;
                    }
                }
            }
        }

        function wrapTextNode(node) {
            console.log('wrapping ' + node.textContent);
            $(node).wrap($('<span>', {
                class: options.className
            }));
            // node.textContent="yeah!!!";
        }

        function getStartNode(range) {
            if (range.startContainer.firstChild.nodeType === window.Node.ELEMENT_NODE) {
                return range.startContainer.childNodes[range.startOffset];
            }
        }

        function getEndNode(range) {
            if (range.endContainer.firstChild.nodeType === window.Node.ELEMENT_NODE) {
                return range.endContainer.childNodes[range.endOffset];
            }
        }

        function isLiteralNode(node) {
            var literalNodeTypes = [
                window.Node.CDATA_SECTION_NODE,
                window.Node.COMMENT_NODE,
                window.Node.TEXT_NODE
            ];
            return literalNodeTypes.indexOf(node.nodeType) !== -1;
        }

        return {
            highlightRanges: function highlightRanges() {
                var ranges = selector.getRanges();
                // var rangeContent;
                // console.log('=============================================');

                ranges.forEach(function(range) {

                    // deal with the simplest case first : select part of a text within another text
                    if (isLiteralNode(range.commonAncestorContainer)) {
                        //
                    }

                    // var rangeContent = range.extractContents();
                    console.dir(range.commonAncestorContainer);
                    console.dir(range.startContainer);
                    console.dir(range.endContainer);
                    console.dir(getStartNode(range));
                    console.dir(getEndNode(range));
                    wrapAllTextNodes(
                        range.commonAncestorContainer,
                        getStartNode(range),
                        getEndNode(range)
                    );
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
