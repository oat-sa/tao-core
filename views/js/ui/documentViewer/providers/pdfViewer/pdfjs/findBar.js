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
    'ui/autoscroll',
    'ui/hider',
    'ui/documentViewer/providers/pdfViewer/pdfjs/searchEngine',
    'tpl!ui/documentViewer/providers/pdfViewer/pdfjs/findBar'
], function ($, _, autoscroll, hider, searchEngineFactory, findBarTpl) {
    'use strict';

    /**
     * The keypress delay before performing a search
     * @type {Number}
     */
    var searchThrottle = 250;

    /**
     * Enable/disable an element
     * @param {jQuery} $element
     * @param {Boolean} enabled
     */
    function toggleState($element, enabled) {
        if (enabled) {
            $element.removeAttr('disabled');
        } else {
            $element.attr('disabled', true);
        }
    }

    /**
     * Creates a find bar to search and display terms in the PDF document
     * @param {Object} config
     * @param {Object} config.events - The events hub
     * @param {Object} config.areaBroker - The areaBroker that gives access to the UI area
     * @param {Object} config.textManager - The textManager component that gives access to the text content
     * @param {Boolean} [config.caseSensitive] - Use a case sensitive search when the search feature is available
     * @param {Boolean} [config.highlightAll] - Highlight all matches to see all of them at a glance
     */
    function pdfjsFindBarFactory(config) {
        var throttledSearchStart = _.throttle(checkQuery, searchThrottle);
        var events = null;
        var broker = null;
        var searchEngine = null;
        var query = null;
        var pageNum = 0;
        var enabled = true;
        var loopBegin = false;
        var loopEnd = false;
        var navigating = false;
        var controls;

        /**
         * Search for the provided query, then trigger an update request
         */
        function doSearch() {
            loopBegin = false;
            loopEnd = false;

            if (query) {
                if (searchEngine) {
                    /**
                     * Notifies a search is processing
                     * @event searching
                     * @param {String} query
                     */
                    events.trigger('searching', query);

                    searchEngine.search(query, pageNum).then(function (page) {
                        /**
                         * Notifies the search has ended
                         * @event searchdone
                         * @param {String} query
                         * @param {Number} page
                         */
                        events.trigger('searchdone', query, page);

                        if (page) {
                            jumpTo(page);
                        } else {
                            refresh();
                        }
                    }).catch(function(err) {
                        /**
                         * Notifies the search error
                         * @event error
                         * @param {Object} err
                         */
                        events.trigger('error', err);
                    });
                }
            } else {
                if (searchEngine) {
                    searchEngine.clearMatches();
                }
                refresh();
            }

            updateControls();
        }

        /**
         * Displays the search matches on the rendered page
         * @param {Number} page - The page number of the rendered page
         * @returns {Promise}
         */
        function updateMatches(page) {
            var textManager = searchEngine && searchEngine.getTextManager();
            if (textManager && textManager.getDocument()) {
                /**
                 * Notifies the matches are updating
                 * @event matchesupdating
                 * @param {Number} page
                 */
                events.trigger('matchesupdating', page);

                return searchEngine.updateMatches(page).then(matchUpdated);
            }
        }

        /**
         * Requests a page change
         * @param {Number} page
         */
        function jumpTo(page) {
            /**
             * @event setpage
             * @param {Number} page
             */
            events.trigger('setpage', page);
        }

        /**
         * Requests a page refresh
         */
        function refresh() {
            /**
             * @event refresh
             */
            events.trigger('refresh');
        }

        /**
         * Finalizes the matches update
         * @param {Number} page
         */
        function matchUpdated(page) {
            if (navigating) {
                navigating = false;
                focusOnMatch();
            }

            updateControls();

            /**
             * Notifies the matches have been displayed
             * @event matchesupdated
             * @param {Number} page
             */
            events.trigger('matchesupdated', page);
        }

        /**
         * Select the current match and displays the page that contains it
         */
        function jumpToMatch() {
            var match;
            if (searchEngine) {
                match = searchEngine.getCurrentMatch();
                if (match) {
                    navigating = true;
                    if (pageNum !== match.page) {
                        jumpTo(match.page);
                    } else {
                        updateSelection();
                    }
                }
            }
        }

        /**
         * Moves between matches
         * @param {Number} direction
         */
        function moveBy(direction) {
            loopBegin = false;
            loopEnd = false;

            if (searchEngine) {
                if (direction < 0) {
                    loopEnd = searchEngine.previousMatch();
                } else {
                    loopBegin = searchEngine.nextMatch();
                }

                jumpToMatch();
            }

            updateControls();
            focusOnInput();
        }

        /**
         * Gets the selected match element
         * @returns {jQuery}
         */
        function getSelectectMatchElement() {
            return broker.getContentArea().find('.highlight.selected');
        }

        /**
         * Gets the current match element
         * @returns {jQuery}
         */
        function getCurrentMatchElement() {
            var match = searchEngine.getCurrentMatch();
            return broker.getContentArea().find('[data-match="' + match.index + '"]');
        }

        /**
         * Updates the selection to reflect the current match
         */
        function updateSelection() {
            if (broker && searchEngine) {
                getSelectectMatchElement().removeClass('selected');
                getCurrentMatchElement().addClass('selected');
                matchUpdated(pageNum);
            }
        }

        /**
         * Gives the focus to the selected match
         */
        function focusOnMatch() {
            if (broker && searchEngine) {
                autoscroll(getSelectectMatchElement(), broker.getContentArea());
            }
        }

        /**
         * Gives the focus to the query input
         */
        function focusOnInput() {
            controls.$searchQuery.focus();
        }

        /**
         * Reads the caseSensitive option and update the config
         */
        function readCaseSensitiveOption() {
            config.caseSensitive = controls.$caseSensitive.is(':checked');
        }

        /**
         * Reads the highlightAll option and apply the state
         */
        function applyHighlightAllOption() {
            if (broker) {
                broker.getContentArea().toggleClass('highlight-all', controls.$highlightAll.is(':checked'));
            }
        }

        /**
         * Checks if a change has been made in the current query, then performs a new search if needed
         */
        function checkQuery() {
            var typedQuery = controls.$searchQuery.val();
            if (typedQuery !== query) {
                query = typedQuery;
                navigating = true;
                doSearch();
            }
        }

        /**
         * Fetches the required controls
         * @param {jQuery} $container
         * @returns {Object}
         */
        function fetchControls($container) {
            return {
                $searchBar: $('.pdf-find-bar', $container),
                $searchButton: $('[data-control="pdf-search"]', $container),
                $searchQuery: $('[data-control="pdf-search-query"]', $container),
                $prevMatch: $('[data-control="pdf-search-prev"]', $container),
                $nextMatch: $('[data-control="pdf-search-next"]', $container),
                $caseSensitive: $('[data-control="case-sensitive-search"]', $container),
                $highlightAll: $('[data-control="highlight-all"]', $container),
                $matchIndex: $('[data-control="pdf-search-index"]', $container),
                $matchCount: $('[data-control="pdf-search-count"]', $container),
                $searchPosition: $('[data-control="pdf-search-position"]', $container),
                $searchLoopBegin: $('[data-control="pdf-search-loop-begin"]', $container),
                $searchLoopEnd: $('[data-control="pdf-search-loop-end"]', $container)
            };
        }

        /**
         * Will update the displayed controls
         */
        function updateControls() {
            var matchCount = 0;
            var matchIndex = 0;
            var match;

            if (searchEngine) {
                matchCount = searchEngine.getMatchCount();
                match = searchEngine.getCurrentMatch();
                matchIndex = match && match.overall;
            }

            if (!enabled && !hider.isHidden(controls.$searchBar)) {
                hider.hide(controls.$searchBar);
            }

            toggleState(controls.$searchButton, enabled);
            toggleState(controls.$searchQuery, enabled);
            toggleState(controls.$caseSensitive, enabled);
            toggleState(controls.$highlightAll, enabled);
            toggleState(controls.$prevMatch, enabled && matchCount > 1);
            toggleState(controls.$nextMatch, enabled && matchCount > 1);

            controls.$matchIndex.text(matchIndex);
            controls.$matchCount.text(matchCount);
            hider.toggle(controls.$searchPosition, enabled && matchCount > 0);
            hider.toggle(controls.$searchLoopBegin, enabled && loopBegin);
            hider.toggle(controls.$searchLoopEnd, enabled && loopEnd);
        }

        config = config || {};

        if (!_.isPlainObject(config.events)) {
            throw new TypeError('You must provide an events hub! [config.events is missing]');
        }
        if (!_.isPlainObject(config.areaBroker)) {
            throw new TypeError('You must provide an areaBroker to give access to the UI! [config.areaBroker is missing]');
        }
        if (!_.isPlainObject(config.textManager)) {
            throw new TypeError('You must provide a textManager to give access to the PDF text content! [config.textManager is missing]');
        }

        searchEngine = searchEngineFactory(config);
        broker = config.areaBroker;
        events = config.events;

        broker.getActionsArea().prepend(findBarTpl(config));
        controls = fetchControls(broker.getBarArea());

        readCaseSensitiveOption();
        applyHighlightAllOption();

        controls.$searchButton.on('click', function () {
            hider.toggle(controls.$searchBar);

            if (!hider.isHidden(controls.$searchBar)) {
                focusOnInput();
            }
        });

        controls.$prevMatch.on('click', function () {
            moveBy(-1);
        });

        controls.$nextMatch.on('click', function () {
            moveBy(1);
        });

        controls.$caseSensitive.on('change', function () {
            navigating = true;
            readCaseSensitiveOption();
            doSearch();
            focusOnInput();
        });

        controls.$highlightAll.on('change', function () {
            applyHighlightAllOption();
            focusOnInput();
        });

        controls.$searchQuery.on('keypress', function (event) {
            switch (event.keyCode) {
                case 27:
                    hider.hide(controls.$searchBar);
                    break;

                default:
                    throttledSearchStart();
            }
        });

        events
            .on('enable.findbar', function () {
                enabled = true;
                updateControls();
            })
            .on('disable.findbar', function () {
                enabled = false;
                updateControls();
            })
            .on('pagechange.findbar', function (page) {
                pageNum = page;
                updateControls();
            })
            .on('allrendered.findbar', function (page) {
                pageNum = page;
                updateMatches(page);
                updateControls();
            })
            /**
             * Notifies the component is initialized
             * @event init.findbar
             */
            .trigger('init.findbar');

        return {
            /**
             * Gets the search engine instance
             * @returns {Object}
             */
            getSearchEngine: function getSearchEngine() {
                return searchEngine;
            },

            /**
             * Destroys the search engine and frees the resources
             */
            destroy: function destroy() {
                if (searchEngine) {
                    searchEngine.destroy();
                }

                if (controls.$searchButton) {
                    controls.$searchButton.remove();
                }
                if (controls.$searchBar) {
                    controls.$searchBar.remove();
                }

                controls = {};
                config = null;
                searchEngine = null;
                broker = null;

                /**
                 * Notifies the component is destroying
                 * @event destroy.findbar
                 */
                events.trigger('destroy.findbar');
                events.off('.findbar');
                events = null;
            }
        };
    }

    return pdfjsFindBarFactory;
});
