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
    'core/promise'
], function (_, Promise) {
    'use strict';

    /**
     * Creates a component that will provide access to the text contained by a PDF
     * @param {Object} config
     * @param {Object} config.PDFJS - The PDFJS entry point
     * @returns {Object}
     */
    function textManagerFactory(config) {
        var pdfDoc = null;
        var PDFJS = null;
        var pageContents = null;
        var textRenderTasks = null;

        /**
         * Stops the rendering task for a particular page, if any
         */
        function cancelRenderingTask(pageIndex) {
            if (textRenderTasks && textRenderTasks[pageIndex]) {
                textRenderTasks[pageIndex].cancel();
                textRenderTasks[pageIndex] = null;
            }
        }

        /**
         * Stops all the rendering tasks, if any
         */
        function cancelAllRenderingTasks() {
            var pageIndex;
            if (textRenderTasks) {
                pageIndex = textRenderTasks.length;
                while (pageIndex--) {
                    cancelRenderingTask(pageIndex);
                }
            }
        }

        /**
         * Extracts the text of a particular page of the PDF
         * @param {Number} pageNum
         * @returns {Promise}
         */
        function getPageTextContent(pageNum) {
            return pdfDoc.getPage(pageNum).then(function (page) {
                return page.getTextContent({
                    normalizeWhitespace: true
                });
            });
        }

        /**
         * Extracts the full text of the PDF
         * @returns {Promise}
         */
        function getTextContent() {
            var numPages = pdfDoc.numPages;
            var promises = [];

            _.times(numPages, function (pageIndex) {
                promises.push(getPageTextContent(pageIndex + 1).then(function (textContent) {
                    var strings = _.map(textContent.items, 'str');
                    return {
                        content: textContent,
                        strings: strings,
                        text: strings.join(''),
                        nodes: []
                    };
                }));
            });

            return Promise.all(promises);
        }

        /**
         * Gets the PDF content
         * @returns {Promise}
         */
        function getPageContents() {
            var numPages = pdfDoc.numPages;
            var contentPromise;

            if (!pageContents) {
                contentPromise = getTextContent().then(function (content) {
                    textRenderTasks = new Array(numPages);
                    pageContents = content;
                    return pageContents;
                });
            } else {
                contentPromise = Promise.resolve(pageContents);
            }

            return contentPromise;
        }

        config = config || {};
        PDFJS = config.PDFJS;

        if (!_.isPlainObject(PDFJS)) {
            throw new TypeError('You must provide the entry point to the PDF.js library! [config.PDFJS is missing]');
        }

        return {
            /**
             * Assign the PDF document from which extract the text
             * @returns {Object}
             */
            setDocument: function setDocument(doc) {
                cancelAllRenderingTasks();
                pdfDoc = doc;
                pageContents = null;
            },

            /**
             * Gets the PDF document
             * @returns {Object}
             */
            getDocument: function getDocument() {
                return pdfDoc;
            },

            /**
             * Gets the content of the PDF
             * @returns {Promise}
             */
            getContents: function getContents() {
                if (pdfDoc) {
                    return getPageContents();
                }
                return Promise.reject(new Error('You must assign a document to get the content from!'));
            },

            /**
             * Gets the text of the document, grouped by page
             * @returns {Promise}
             */
            getText: function getText() {
                return this.getContents().then(function (content) {
                    return _.map(content, 'text');
                });
            },

            /**
             * Gets the full text of the document in a single string
             * @returns {Promise}
             */
            getFullText: function getText() {
                return this.getContents().then(function (content) {
                    return _.map(content, 'text').join(' ');
                });
            },

            /**
             * Gets the content of a particular page
             * @param {Number} pageNum
             * @returns {Promise}
             */
            getPageContent: function getPageContent(pageNum) {
                return this.getContents().then(function (content) {
                    var pageIndex = Math.min(Math.max(0, pageNum - 1), content.length - 1);
                    return content[pageIndex];
                });
            },

            /**
             * Gets the full text of a particular page
             * @param {Number} pageNum
             * @returns {Promise}
             */
            getPageText: function getPageText(pageNum) {
                return this.getContents().then(function (content) {
                    var pageIndex = Math.min(Math.max(0, pageNum - 1), content.length - 1);
                    return content[pageIndex].text;
                });
            },

            /**
             * Renders the text of a page into a layer using the provided viewport settings.
             * The promise will return the rendered layer.
             * @param {Number} pageNum
             * @param {Object} viewport
             * @param {Number} [timeout]
             * @returns {Promise}
             */
            renderPage: function renderPage(pageNum, viewport, timeout) {
                return this.getContents().then(function (content) {
                    var pageIndex = Math.min(Math.max(0, pageNum - 1), content.length - 1);
                    var pageContent = content[pageIndex];
                    var textLayerFrag = document.createDocumentFragment();

                    cancelRenderingTask(pageIndex);

                    pageContent.nodes = [];

                    textRenderTasks[pageIndex] = PDFJS.renderTextLayer({
                        textContent: pageContent.content,
                        textDivs: pageContent.nodes,
                        container: textLayerFrag,
                        viewport: viewport,
                        timeout: timeout
                    });

                    return textRenderTasks[pageIndex].promise.then(function () {
                        textRenderTasks[pageIndex] = null;
                        return textLayerFrag;
                    }, function () {
                        // silently catch any error
                        textRenderTasks[pageIndex] = null;
                    });
                });
            },

            /**
             * Destroys the text manager and frees the resources
             */
            destroy: function destroy() {
                cancelAllRenderingTasks();

                pdfDoc = null;
                PDFJS = null;
                pageContents = null;
                config = null;
            }
        };
    }

    return textManagerFactory;
});
