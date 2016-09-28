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
    'core/promise'
], function ($, Promise) {
    'use strict';

    /**
     * The signature of Base64 content string
     * @type {string}
     */
    var BASE64_MARKER = ';base64,';

    /**
     * The default scale factor
     * @type {Number}
     */
    var DEFAULT_SCALE = 1.0;

    /**
     * The minimum scale factor that allows a good experience
     * @type {Number}
     */
    var MIN_SCALE = 0.25;

    /**
     * The maximum scale factor that allows a good experience
     * @type {Number}
     */
    var MAX_SCALE = 10.0;

    /**
     * A conversion factor for CSS units
     * @type {Number}
     */
    var CSS_UNITS = 96.0 / 72.0;


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
     * Returns scale factor for the canvas.
     * @return {Number}
     */
    function getOutputScale(ctx) {
        var devicePixelRatio = window.devicePixelRatio || 1;
        var backingStoreRatio = ctx.backingStorePixelRatio ||
            ctx.webkitBackingStorePixelRatio ||
            ctx.mozBackingStorePixelRatio ||
            ctx.msBackingStorePixelRatio ||
            ctx.oBackingStorePixelRatio || 1;
        return devicePixelRatio / backingStoreRatio;
    }


    /**
     * Creates a wrapper for PDF.js to render a document
     * @param PDFJS
     * @param $canvas
     * @param config
     * @returns {Object}
     */
    function pdfjsWrapperFactory(PDFJS, $canvas, config) {
        var pdfDoc = null;
        var pageNum = 1;
        var pageCount = 1;
        var pageNumPending = null;
        var pageRendering = null;
        var canvas = $canvas.get(0);
        var ctx = canvas.getContext('2d');
        var scale = Math.min(Math.max(MIN_SCALE, getOutputScale(ctx) * DEFAULT_SCALE), MAX_SCALE);
        var pixelWidth = 1;
        var pixelHeight = 1;
        var $container = $canvas.parent();
        var states = {};

        /**
         * Sets a state
         * @param {String} name The name of the state to set
         * @param {Boolean} state The value of the state
         */
        function setState(name, state) {
            states[name] = !!state;
        }

        /**
         * Unloads the document and resets the context
         */
        function unload() {
            if (pdfDoc) {
                pdfDoc.destroy();
            }
            pdfDoc = null;
            states = {};
        }

        /**
         * Resize the viewer according to the document dimensions
         * @param {Object} viewport
         */
        function resizeCanvas(viewport) {
            var ratio = (viewport.width / (viewport.height || 1)) || 1;
            var parentWidth = $container.width();
            var parentOffset = $container.offset();
            var width, height;

            if (config.fitToWidth) {
                width = pixelWidth;
                height = width / ratio;

                if (height > pixelHeight) {
                    $canvas.width(Math.max(1, pixelWidth / 2)).height(height);
                    parentWidth = $container.prop('scrollWidth');
                    width = parentWidth;
                    height = width / ratio;
                }
            } else {
                if (ratio >= 1) {
                    height = Math.min(pixelHeight, pixelWidth / ratio);
                    width = Math.min(pixelWidth, height * ratio);
                } else {
                    width = Math.min(pixelWidth, pixelHeight * ratio);
                    height = Math.min(pixelHeight, width / ratio);
                }
            }

            $canvas
                .width(width)
                .height(height)
                .offset({
                    left: parentOffset.left + Math.max(0, (parentWidth - width) / 2)
                });

            canvas.width = viewport.width;
            canvas.height = viewport.height;
        }

        /**
         * Renders a page
         * @param num
         * @returns {Promise}
         */
        function renderPage(num) {
            if (pdfDoc) {
                setState('rendered', false);
                setState('rendering', true);
                if (!pageRendering) {
                    pageRendering = pdfDoc.getPage(num)
                        .then(function (page) {
                            var viewport = page.getViewport(scale * CSS_UNITS);
                            var renderContext = {
                                canvasContext: ctx,
                                viewport: viewport
                            };

                            resizeCanvas(viewport);

                            return page.render(renderContext).promise.then(function () {
                                var nextPage = pageNumPending;
                                pageNumPending = null;
                                pageRendering = null;
                                setState('rendered', true);
                                setState('rendering', false);
                                if (nextPage !== null) {
                                    return renderPage(nextPage);
                                }
                            });
                        });
                } else {
                    pageNumPending = num;
                }
                return pageRendering;
            } else {
                return Promise.resolve(num);
            }
        }

        return {
            /**
             * Loads a PDF document using PDF.js
             * @param {String} url
             * @returns {Promise}
             */
            load: function load(url) {
                unload();
                return PDFJS.getDocument(processUri(url)).then(function (pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    pageNum = 1;
                    pageCount = pdfDoc.numPages;
                    setState('loaded', true);
                    return renderPage(pageNum);
                });
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
            getPdfDoc: function getPdfDoc() {
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
                page = Math.min(Math.max(1, page || 0), pageCount);
                if (page !== pageNum) {
                    pageNum = page;
                    return renderPage(pageNum);
                }
                return Promise.resolve(pageNum);
            },

            /**
             * Resize the viewport
             * @param {Number} width
             * @param {Number} height
             * @returns {Promise}
             */
            setSize: function setSize(width, height) {
                if (width !== pixelWidth || height !== pixelHeight) {
                    pixelWidth = width;
                    pixelHeight = height;
                    return renderPage(pageNum);
                }
                return Promise.resolve(pageNum);
            },

            /**
             * Refresh the current page
             * @returns {Promise}
             */
            refresh: function refresh() {
                return renderPage(pageNum);
            },

            /**
             * Liberates the resources
             */
            destroy: function destroy() {
                unload();
            }
        };
    }

    return pdfjsWrapperFactory;
});
