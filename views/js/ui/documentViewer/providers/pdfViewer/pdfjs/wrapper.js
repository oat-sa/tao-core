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
    'lodash',
    'core/promise',
    'ui/documentViewer/providers/pdfViewer/pdfjs/pagesManager',
    'ui/documentViewer/providers/pdfViewer/pdfjs/textManager'
], function (_, Promise, pagesManagerFactory, textManagerFactory) {
    'use strict';

    /**
     * The signature of Base64 content string
     * @type {string}
     */
    var BASE64_MARKER = ';base64,';

    /**
     * Converts a Base64 string to an array of bytes
     * @param {String} data
     * @returns {Uint8Array}
     */
    function base64toBytes(data) {
        var raw = window.atob(data);
        var rawLength = raw.length;
        var array = new Uint8Array(new ArrayBuffer(rawLength));

        while (rawLength--) {
            array[rawLength] = raw.charCodeAt(rawLength);
        }

        return array;
    }

    /**
     * Checks if an URI contains a Base64 content, then decode it and return an array. Otherwise return the URL.
     * @param {String} uri
     * @returns {String|Uint8Array}
     */
    function processUri(uri) {
        var base64Index;

        uri = String(uri);
        base64Index = uri.indexOf(BASE64_MARKER);

        if (base64Index >= 0) {
            return base64toBytes(uri.substring(base64Index + BASE64_MARKER.length));
        }

        return uri;
    }

    /**
     * Creates a wrapper for PDF.js to render a document
     * @param {jQuery} $container
     * @param {Object} config
     * @param {Object} config.events - The events hub
     * @param {Object} config.PDFJS - The PDFJS entry point
     * @param {Boolean} [config.fitToWidth] - Fit the page to the available width, a scroll bar may appear
     * @returns {Object}
     */
    function pdfjsWrapperFactory($container, config) {
        var pdfDoc = null;
        var pageNum = 1;
        var pageCount = 1;
        var pageNumPending = null;
        var pageRendering = null;
        var pagesManager = null;
        var textManager = null;
        var states = {};
        var PDFJS = null;
        var events = null;

        /**
         * Wraps the PDF.js API
         * @type {Object}
         */
        var wrapper = {
            /**
             * The wrapped API (i.e.: the PDF.js library)
             * @type {Object}
             */
            get wrapped() {
                return PDFJS;
            },

            /**
             * Loads a PDF document using PDF.js
             * @param {String} url
             * @returns {Promise}
             */
            load: function load(url) {
                pdfDoc = null;
                states = {};

                /**
                 * Notifies a document is loading
                 * @event loading
                 * @param {String} url
                 */
                events.trigger('loading', url);

                return PDFJS.getDocument(processUri(url)).then(function (doc) {
                    if (!states.destroyed) {
                        pdfDoc = doc;
                        pageNum = 1;
                        pageCount = pdfDoc.numPages;
                        textManager.setDocument(pdfDoc);
                        states.loaded = true;

                        /**
                         * Notifies a document has been loaded
                         * @event loaded
                         * @param {String} url
                         */
                        events.trigger('loaded', url);
                    }
                });
            },

            /**
             * Renders a page
             * @param {Number} num
             * @returns {Promise}
             */
            renderPage: function renderPage(num) {
                if (pdfDoc) {
                    if (!pageRendering) {
                        /**
                         * Notifies a page is rendering
                         * @event rendering
                         * @param {Number} pageNum
                         */
                        events.trigger('rendering', num);

                        states.rendered = false;
                        states.rendering = true;
                        pageRendering = pdfDoc.getPage(num).then(function (page) {
                            if (pagesManager) {
                                return pagesManager.renderPage(page, config.fitToWidth).then(function () {
                                    var nextPage = pageNumPending;
                                    pageNumPending = null;
                                    pageRendering = null;

                                    if (!states.destroyed) {
                                        states.rendered = true;
                                        states.rendering = false;

                                        /**
                                         * Notifies a page has been rendered
                                         * @event rendered
                                         * @param {Number} pageNum
                                         */
                                        events.trigger('rendered', num);

                                        if (nextPage !== null) {
                                            return wrapper.renderPage(nextPage);
                                        }

                                        /**
                                         * Notifies the last requested page has been rendered
                                         * @event allrendered
                                         * @param {Number} pageNum
                                         */
                                        events.trigger('allrendered', num);
                                    }
                                });
                            }
                        });
                    } else {
                        pageNumPending = num;
                    }

                    return pageRendering;
                } else {
                    return Promise.resolve(num);
                }
            },

            /**
             * Gets a state
             * @param {String} name The name of the state to get
             * @returns {Boolean} The value of the state
             */
            getState: function getState(name) {
                return !!states[name];
            },

            /**
             * Gets the PDF document
             * @returns {Object}
             */
            getDocument: function getDocument() {
                return pdfDoc;
            },

            /**
             * Gets the pages count of the current PDF
             * @returns {Number}
             */
            getPageCount: function getPageCount() {
                return pageCount;
            },

            /**
             * Gets the current page number
             * @returns {Number}
             */
            getPage: function getPage() {
                return pageNum;
            },

            /**
             * Changes the current page
             * @param {Number} page
             * @returns {Promise}
             */
            setPage: function setPage(page) {
                pageNum = Math.min(Math.max(1, page || 0), pageCount);
                pagesManager.setActiveView(pageNum);

                /**
                 * Notifies a page change
                 * @event pagechange
                 * @param {Number} pageNum
                 */
                events.trigger('pagechange', pageNum);

                return wrapper.renderPage(pageNum);
            },

            /**
             * Gets the text manager
             * @returns {Object}
             */
            getTextManager: function getTextManager() {
                return textManager;
            },

            /**
             * Gets the pages manager
             * @returns {Object}
             */
            getPagesManager: function getPagesManager() {
                return pagesManager;
            },

            /**
             * Refresh the current page
             * @returns {Promise}
             */
            refresh: function refresh() {
                /**
                 * Notifies a page is refreshing
                 * @event refreshing
                 * @param {Number} pageNum
                 */
                events.trigger('refreshing', pageNum);

                pagesManager.setActiveView(pageNum);
                return wrapper.renderPage(pageNum);
            },

            /**
             * Liberates the resources
             */
            destroy: function destroy() {
                if (pagesManager) {
                    pagesManager.destroy();
                }

                if (textManager) {
                    textManager.destroy();
                }

                if (pdfDoc) {
                    pdfDoc.destroy();
                }

                states = {
                    destroyed: true
                };
                pdfDoc = null;
                pageNumPending = null;
                pageRendering = null;
                pagesManager = null;
                $container = null;
                PDFJS = null;
                config = null;

                /**
                 * Notifies the component is destroying
                 * @event destroy.wrapper
                 */
                events.trigger('destroy.wrapper');
                events = null;
            }
        };

        config = config || {};
        PDFJS = config.PDFJS;
        events = config.events;

        if (!_.isPlainObject(PDFJS)) {
            throw new TypeError('You must provide the entry point to the PDF.js library! [config.PDFJS is missing]');
        }
        if (!_.isPlainObject(events)) {
            throw new TypeError('You must provide an events hub! [config.events is missing]');
        }

        textManager = textManagerFactory({
            PDFJS: PDFJS
        });

        // todo: accept option to use a view per page instead of a single view for all pages
        pagesManager = pagesManagerFactory($container, {
            pageCount: 1,
            textManager: textManager
        });

        events
            .on('setpage.wrapper', function (page) {
                wrapper.setPage(page);
            })
            .on('refresh.wrapper', function () {
                wrapper.refresh();
            })
            /**
             * Notifies the component is initialized
             * @event init.wrapper
             */
            .trigger('init.wrapper');

        return wrapper;
    }

    return pdfjsWrapperFactory;
});
