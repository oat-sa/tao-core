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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'core/promise'
], function ($, _, Promise) {
    'use strict';

    /**
     * Map of special chars to normalize
     * @type {Object}
     */
    var charactersToNormalize = {
        '\u2018': '\'',  // Left single quotation mark
        '\u2019': '\'',  // Right single quotation mark
        '\u201A': '\'',  // Single low-9 quotation mark
        '\u201B': '\'',  // Single high-reversed-9 quotation mark
        '\u201C': '"',   // Left double quotation mark
        '\u201D': '"',   // Right double quotation mark
        '\u201E': '"',   // Double low-9 quotation mark
        '\u201F': '"',   // Double high-reversed-9 quotation mark
        '\u00BC': '1/4', // Vulgar fraction one quarter
        '\u00BD': '1/2', // Vulgar fraction one half
        '\u00BE': '3/4'  // Vulgar fraction three quarters
    };

    /**
     * RegExp that will match special chars to normalize
     * @type {RegExp}
     */
    var normalizationRegex = new RegExp('[' + Object.keys(charactersToNormalize).join('') + ']', 'g');

    /**
     * Replaces special chars by normalized equivalent
     * @param {String} text
     * @returns {String}
     */
    function normalizeSpecialChars(text) {
        return text.replace(normalizationRegex, function (ch) {
            return charactersToNormalize[ch];
        });
    }

    /**
     * Find terms in a text
     * @param {String} query
     * @param {String} text
     * @returns {Array}
     */
    function findInText(query, text) {
        var queryLen = query.length;
        var index, end = 0;
        var matches = [];

        if (queryLen) {
            do {
                index = text.indexOf(query, end);
                end = index + queryLen;
                if (index !== -1) {
                    matches.push([index, end]);
                }
            } while (index !== -1);
        }

        return matches;
    }

    /**
     * Find the query in the whole document
     * @param {String} query
     * @param {Array} contentText
     * @param {Object} config
     * @returns {Array}
     */
    function findInDocument(query, contentText, config) {
        var normalizedQuery = normalizeSpecialChars(query);

        if (!config.caseSensitive) {
            normalizedQuery = normalizedQuery.toLowerCase();
        }

        return _.times(contentText.length, function (pageIndex) {
            var pageContent = normalizeSpecialChars(contentText[pageIndex]);

            if (!config.caseSensitive) {
                pageContent = pageContent.toLowerCase();
            }

            return findInText(normalizedQuery, pageContent);
        });
    }

    /**
     * Wraps a text into a highlighting span
     * @param {String} text
     * @param {String} [cls]
     * @returns {String}
     */
    function highlight(text, cls) {
        cls = 'highlight' + (cls ? ' ' + cls : '');
        return '<span class="' + cls + '">' + text + '</span>';
    }

    /**
     * Refines the array of matches to provide positions inside the text layer
     * @param {Array} matches
     * @param {Object} pageContent
     * @returns {Array}
     */
    function refineMatches(matches, pageContent) {
        var refinedMatches = [];
        var textItems = pageContent.content.items;
        var count = textItems.length;
        var cursor = 0;
        var strPos = 0;

        _.forEach(matches, function (match, matchIndex) {
            var matchStart = match[0];
            var matchEnd = match[1];
            var position = {
                matchIndex: matchIndex,
                matchStart: matchStart,
                matchEnd: matchEnd
            };

            while (cursor < count && matchStart >= strPos + textItems[cursor].str.length) {
                strPos += textItems[cursor].str.length;
                cursor++;
            }

            position.begin = {
                node: cursor,
                offset: matchStart - strPos
            };

            while (cursor < count && matchEnd > strPos + textItems[cursor].str.length) {
                strPos += textItems[cursor].str.length;
                cursor++;
            }

            position.end = {
                node: cursor,
                offset: matchEnd - strPos
            };

            refinedMatches.push(position);
        });

        return refinedMatches;
    }

    /**
     * Renders the matches into the text layer
     * @param {Array} matches
     * @param {Object} pageContent
     */
    function renderMatches(matches, pageContent) {
        var positions = refineMatches(matches, pageContent);
        var matchIndex = positions.length - 1;
        var nodes = pageContent.nodes;

        _.forEachRight(nodes, function (node, nodeIndex) {
            var $node = $(node);
            var nodeText = pageContent.content.items[nodeIndex].str;
            var match, startInNode, endInNode, nodeInMatch;

            while (matchIndex >= 0) {
                match = positions[matchIndex];
                startInNode = match.begin.node === nodeIndex;
                endInNode = match.end.node === nodeIndex;
                nodeInMatch = nodeIndex > match.begin.node && nodeIndex < match.end.node;

                if (startInNode && endInNode) {
                    nodeText = nodeText.substr(0, match.begin.offset) +
                        highlight(nodeText.substring(match.begin.offset, match.end.offset)) +
                        nodeText.substr(match.end.offset);
                    matchIndex--;
                } else if (startInNode) {
                    nodeText = nodeText.substr(0, match.begin.offset) +
                        highlight(nodeText.substr(match.begin.offset), 'begin');
                    matchIndex--;
                } else if (endInNode) {
                    nodeText = highlight(nodeText.substring(0, match.end.offset), 'end') +
                        nodeText.substr(match.end.offset);
                    break;
                } else if (nodeInMatch) {
                    nodeText = highlight(nodeText, 'middle');
                    break;
                } else {
                    break;
                }
            }

            $node.html(nodeText);
        });
    }

    /**
     * Embeds the search engine for the PDF viewer
     * @param {Object} config A config set
     * @param {Object} config.textManager - The textManager component that gives access to the text content
     * @param {Boolean} [config.caseSensitive] - Use a case sensitive search when the search feature is available
     * @returns {Object} Returns the search engine instance
     */
    function pdfjsSearchFactory(config) {
        var textManager = null;
        var matches = null;

        config = config || {};
        textManager = config.textManager;

        if ('object' !== typeof textManager) {
            throw new TypeError('You must provide a textManager to give access to the PDF text content! [config.textManager is missing]');
        }

        return {
            /**
             * Gets the search matches
             * @returns {Array}
             */
            getMatches: function getMatches() {
                return matches;
            },

            /**
             * Clears the search matches
             */
            clearMatches: function clearMatches() {
                matches = [];
            },

            /**
             * Sets the text manager
             * @param {Object} manager
             */
            setTextManager: function setTextManager(manager) {
                textManager = manager;
            },

            /**
             * Gets the text manager
             * @returns {Object}
             */
            getTextManager: function getTextManager() {
                return textManager;
            },

            /**
             * Searches for the requested query.
             * The promise will return the page number of the first match, that could be 0 if no result has been found.
             * @param {String} query
             * @returns {Promise}
             */
            search: function search(query) {
                matches = null;
                return textManager.getContents().then(function (pageContents) {
                    var contentText = _.map(pageContents, 'text');
                    matches = findInDocument(query, contentText, config);

                    // the promise will return the page number of the first match, or 0 if none
                    return 1 + _.findIndex(matches, function (pageMatches) {
                        return pageMatches.length > 0;
                    });
                });
            },

            /**
             * Displays the search matches on the rendered page
             * @param {Number} pageNum
             * @returns {Promise}
             */
            updateMatches: function updateMatches(pageNum) {
                if (matches) {
                    return textManager.getPageContent(pageNum).then(function (pageContent) {
                        if (pageContent) {
                            renderMatches(matches[pageNum - 1] || [], pageContent);
                        }
                    });
                }
                return Promise.resolve();
            },

            /**
             * Destroys the search engine and frees the resources
             */
            destroy: function destroy() {
                textManager = null;
                matches = null;
                config = null;
            }
        };
    }

    return pdfjsSearchFactory;
});
