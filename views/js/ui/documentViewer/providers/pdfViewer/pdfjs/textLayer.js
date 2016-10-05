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
     * Creates a textLayer object. It will have in charge to give access to the PDF text content.
     * Without this component, there is no way to get the text content, either for selecting or searching.
     * @param {jQuery} $container - The place where render the text layer
     * @param {Object} config
     * @param {Object} config.PDFJS - The PDFJS entry point
     * @returns {Object}
     */
    function textLayerFactory($container, config) {

        var textRenderTask;
        var textDivs = [];
        var textContent = null;
        var fullText = null;
        var PDFJS = null;

        /**
         * Stops the rendering task, if any
         */
        function cancel() {
            if (textRenderTask) {
                textRenderTask.cancel();
                textRenderTask = null;
            }
        }

        /**
         * Extracts the full text from the text content collection
         * @returns {String}
         */
        function extractFullText() {
            return textContent && textContent.items && _.map(textContent.items, 'str').join(' ');
        }

        config = config || {};
        PDFJS = config.PDFJS;

        if ('object' !== typeof PDFJS) {
            throw new TypeError('You must provide the entry point to the PDS.js library! [config.PDFJS is missing]');
        }

        return {
            /**
             * Gets the page container
             * @returns {jQuery}
             */
            getContainer: function getContainer() {
                return $container;
            },

            /**
             * Gets the full text contained by the layer
             * @returns {String}
             */
            getFullText: function getFullText() {
                if (fullText === null) {
                    fullText = extractFullText();
                }
                return fullText;
            },

            /**
             * Gets the text content collection
             * @returns {Object}
             */
            getTextContent: function getTextContent() {
                return textContent;
            },

            /**
             * Sets the text content collection. Reset the full text property.
             * @param {Object} text
             */
            setTextContent: function setTextContent(text) {
                cancel();

                textContent = text;
                fullText = null;
            },

            /**
             * Sets the text content collection from a PDF page.
             * @param {Object} page
             * @returns {Promise}
             */
            setTextContentFromPage: function setTextContentFromPage(page) {
                var self = this;
                return page.getTextContent({normalizeWhitespace: true}).then(function (text) {
                    self.setTextContent(text);
                    return text;
                });
            },

            /**
             * Renders the layer using the provided viewport settings
             * @param {Object} viewport
             * @param {Number} [timeout]
             * @returns {Promise}
             */
            render: function render(viewport, timeout) {
                var textLayerFrag = document.createDocumentFragment();

                cancel();
                $container.empty();

                textDivs = [];
                textRenderTask = PDFJS.renderTextLayer({
                    textContent: textContent,
                    container: textLayerFrag,
                    viewport: viewport,
                    textDivs: textDivs,
                    timeout: timeout
                });

                return textRenderTask.promise.then(function () {
                    textRenderTask = null;
                    $container.append(textLayerFrag);
                    return textLayerFrag;
                }, function () {
                    // silently catch any error
                    textRenderTask = null;
                });
            },

            /**
             * Destroys the text layer and frees the resources
             */
            destroy: function destroy() {
                cancel();
                $container.empty();

                textDivs = null;
                textContent = null;
                fullText = null;
                $container = null;
                PDFJS = null;
            }
        };
    }

    return textLayerFactory;
});
