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
    'lodash'
], function (_) {
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
     * @param {String} text - The text to highlight
     * @param {Number} index - The match index
     * @param {String} [cls] - An additional CSS class to set
     * @returns {String}
     */
    function highlight(text, index, cls) {
        cls = 'highlight' + (cls ? ' ' + cls : '');
        return '<span class="' + cls + '" data-match="' + index + '">' + text + '</span>';
    }

    /**
     * Highlights a substring in a text
     * @param {String} text - The text in which highlight the substring
     * @param {Number} start - The start position of the substring in the text
     * @param {Number} end - The end position of the substring in the text
     * @param {Number} index - The match index
     * @param {String} [cls] - An additional CSS class to set
     * @returns {String}
     */
    function highlightInText(text, start, end, index, cls) {
        return text.substring(0, start) +
            highlight(text.substring(start, end), index, cls) +
            text.substring(end);
    }

    /**
     * Refines the array of matches to provide positions inside the text layer per nodes basis
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

        _.forEach(matches, function (match, index) {
            var matchStart = match[0];
            var matchEnd = match[1];
            var position = {
                index: index
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
            var nodeText = pageContent.content.items[nodeIndex].str;
            var match, startInNode, endInNode, nodeInMatch;

            while (matchIndex >= 0) {
                match = positions[matchIndex];
                startInNode = match.begin.node === nodeIndex;
                endInNode = match.end.node === nodeIndex;
                nodeInMatch = nodeIndex > match.begin.node && nodeIndex < match.end.node;

                if (startInNode && endInNode) {
                    nodeText = highlightInText(nodeText, match.begin.offset, match.end.offset, match.index);
                    matchIndex--;
                } else if (startInNode) {
                    nodeText = highlightInText(nodeText, match.begin.offset, nodeText.length, match.index, 'begin');
                    matchIndex--;
                } else if (endInNode) {
                    nodeText = highlightInText(nodeText, 0, match.end.offset, match.index, 'end');
                    break;
                } else if (nodeInMatch) {
                    nodeText = highlight(nodeText, match.index, 'middle');
                    break;
                } else {
                    break;
                }
            }

            node.innerHTML = nodeText;
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
        var currentQuery = null;
        var currentMatch = null;
        var matches = [];
        var pages = [];

        config = config || {};
        textManager = config.textManager;

        if ('object' !== typeof textManager) {
            throw new TypeError('You must provide a textManager to give access to the PDF text content! [config.textManager is missing]');
        }

        return {
            /**
             * Gets the list of page numbers that lead to search matches
             * @returns {Array}
             */
            getPages: function getPages() {
                return pages;
            },

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
                currentMatch = null;
                matches = [];
                pages = [];
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
             * Gets the currently matched query
             * @returns {String}
             */
            getQuery: function getQuery() {
                return currentQuery;
            },

            /**
             * Gets the current match data
             * @returns {Object}
             */
            getCurrentMatch: function getCurrentMatch() {
                return currentMatch;
            },

            /**
             * Go to the previous match and returns the match data
             * @returns {Object}
             */
            previousMatch: function previousMatch() {
                var pageIndex;
                if (currentMatch) {
                    if (currentMatch.index) {
                        currentMatch.index--;
                    } else {
                        pageIndex = (_.indexOf(pages, currentMatch.page) + pages.length - 1) % pages.length;
                        currentMatch.page = pages[pageIndex];
                        currentMatch.index = matches[currentMatch.page - 1].length - 1;
                    }
                }
                return currentMatch;
            },

            /**
             * Go to the next match and returns the match data
             * @returns {Object}
             */
            nextMatch: function nextMatch() {
                var pageIndex;
                if (currentMatch) {
                    if (currentMatch.index + 1 < matches[currentMatch.page - 1].length) {
                        currentMatch.index++;
                    } else {
                        pageIndex = (_.indexOf(pages, currentMatch.page) + 1) % pages.length;
                        currentMatch.page = pages[pageIndex];
                        currentMatch.index = 0;
                    }
                }
                return currentMatch;
            },

            /**
             * Searches for the requested query.
             * The promise will return the page number of the first match, that could be 0 if no result has been found.
             * @param {String} query - The terms to search for
             * @param {Number} [pageNum] - An optional page number from which start the search
             * @returns {Promise}
             */
            search: function search(query, pageNum) {
                matches = null;
                return textManager.getContents().then(function (pageContents) {
                    var contentText = _.map(pageContents, 'text');
                    var firstPage = 0;
                    matches = findInDocument(query, contentText, config);

                    currentQuery = query;
                    currentMatch = null;
                    pages = [];
                    _.forEach(matches, function (pageMatches, pageIndex) {
                        var page = pageIndex + 1;

                        if (pageMatches.length > 0) {
                            pages.push(page);

                            if (!firstPage && page >= pageNum) {
                                firstPage = page;
                            }
                        }
                    });

                    if (!firstPage) {
                        firstPage = pages[0] || 0;
                    }

                    if (firstPage) {
                        currentMatch = {
                            page: firstPage,
                            index: 0
                        };
                    }

                    return firstPage;
                });
            },

            /**
             * Displays the search matches on the rendered page
             * @param {Number} pageNum - The page number of the rendered page
             * @returns {Promise}
             */
            updateMatches: function updateMatches(pageNum) {
                return textManager.getPageContent(pageNum).then(function (pageContent) {
                    if (pageContent) {
                        renderMatches(matches[pageNum - 1], pageContent);
                    }
                });
            },

            /**
             * Destroys the search engine and frees the resources
             */
            destroy: function destroy() {
                textManager = null;
                currentQuery = null;
                currentMatch = null;
                matches = null;
                pages = null;
                config = null;
            }
        };
    }

    return pdfjsSearchFactory;
});
