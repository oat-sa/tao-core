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
    'core/promise',
    'ui/hider',
    'tpl!ui/documentViewer/providers/pdfViewer/pdfjs/page'
], function ($, Promise, hider, pageTpl) {
    'use strict';

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
     * The maximum allowed scale factor
     * @type {Number}
     */
    var MAX_SCALE = 10.0;

    /**
     * A conversion factor from printed to displayed
     * @type {Number}
     */
    var CSS_UNITS = 96.0 / 72.0;

    /**
     * Returns scale factor for the canvas.
     * @param {CanvasRenderingContext2D} context
     * @returns {Number}
     */
    function getOutputScale(context) {
        var devicePixelRatio = window.devicePixelRatio || 1;
        var backingStoreRatio = context.backingStorePixelRatio ||
            context.webkitBackingStorePixelRatio ||
            context.mozBackingStorePixelRatio ||
            context.msBackingStorePixelRatio ||
            context.oBackingStorePixelRatio || 1;
        return devicePixelRatio / backingStoreRatio;
    }

    /**
     * Normalize a scale factor
     * @param {Number} scale
     * @returns {Number}
     */
    function normalizeScale(scale) {
        return Math.min(Math.max(MIN_SCALE, parseInt(scale, 10) || DEFAULT_SCALE), MAX_SCALE);
    }

    /**
     * Creates a page view
     * @param {jQuery} $container
     * @param {Object} config
     * @param {Number} config.pageNum - The number of the page the view is displaying
     * @param {Object} [config.textManager] - The textManager component that gives access to the text content
     * @returns {Object}
     */
    function pageViewFactory($container, config) {
        var $pageView, $textLayer, $drawLayer;
        var canvas, context, scale;
        var textManager = null;
        var rendered = false;
        var pageNum;

        var view = {
            /**
             * The page number that is attached to this view
             * @type {Number}
             */
            get pageNum() {
                return pageNum;
            },

            set pageNum(num) {
                pageNum = parseInt(num, 10);
                $pageView.attr('data-page', num);
            },

            /**
             * Whether the view has been rendered or not
             * @returns {Boolean}
             */
            isRendered: function isRendered() {
                return rendered;
            },

            /**
             * Gets the page container
             * @returns {jQuery}
             */
            getContainer: function getContainer() {
                return $container;
            },

            /**
             * Gets the page view element
             * @returns {jQuery}
             */
            getElement: function getElement() {
                return $pageView;
            },

            /**
             * Gets the draw layer element
             * @returns {jQuery}
             */
            getDrawLayerElement: function getDrawLayerElement() {
                return $drawLayer;
            },

            /**
             * Gets the text layer container
             * @returns {jQuery}
             */
            getTextLayerElement: function getTextLayerElement() {
                return $textLayer;
            },

            /**
             * Gets the canvas element
             * @returns {HTMLElement}
             */
            getCanvas: function getCanvas() {
                return canvas;
            },

            /**
             * Gets the drawing context
             * @returns {CanvasRenderingContext2D}
             */
            getRenderingContext: function getRenderingContext() {
                return context;
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
             * Renders a page into the view
             * @param {Object} page - The PDF page definition
             * @param {Boolean} [fitToWidth] - Force the page view to fit its container width, without respect of the container height
             * @returns {Promise}
             */
            render: function render(page, fitToWidth) {
                var viewport, textViewport, renderContext;
                var promises = [];
                var renderScale = scale * CSS_UNITS;

                rendered = false;
                viewport = page.getViewport(renderScale);
                renderContext = {
                    canvasContext: view.getRenderingContext(),
                    viewport: viewport
                };

                adjustSize(viewport, fitToWidth);

                promises.push(
                    page.render(renderContext).promise
                );

                if (textManager) {
                    $textLayer.empty();
                    textViewport = viewport.clone({
                        scale: renderScale * $textLayer.width() / viewport.width
                    });

                    promises.push(
                        textManager.renderPage(page.pageIndex + 1, textViewport).then(function(renderedContent) {
                            if ($textLayer) {
                                $textLayer.append(renderedContent);
                            }
                        })
                    );
                }

                return Promise.all(promises).then(function () {
                    rendered = true;
                });
            },

            /**
             * Shows the page
             */
            show: function show() {
                hider.show($pageView);
            },

            /**
             * Hides the page
             */
            hide: function hide() {
                hider.hide($pageView);
            },

            /**
             * Removes and destroys the page view
             */
            destroy: function destroy() {
                $pageView.remove();

                $container = null;
                $pageView = null;
                $textLayer = null;
                $drawLayer = null;
                canvas = null;
                context = null;
                textManager = null;
                config = null;
            }
        };

        /**
         * Adjust the size of the page view to fit its container with respect to the provided viewport
         * @param {Object} viewport - The PDF page viewport
         * @param {Boolean} fitToWidth - Force the page view to fit its container width, without respect of the container height
         */
        function adjustSize(viewport, fitToWidth) {
            var ratio = (viewport.width / (viewport.height || 1)) || 1;
            var parentWidth = $container.width();
            var parentHeight = $container.height();
            var parentOffset = $container.offset();
            var width, height;

            function setSize(w, h) {
                $pageView
                    .width(w)
                    .height(h)
                    .offset({
                        left: parentOffset.left + Math.max(0, (parentWidth - w) / 2)
                    });

                $drawLayer
                    .width(w)
                    .height(h);

                $textLayer
                    .width(w)
                    .height(h);
            }

            if (fitToWidth) {
                width = parentWidth;
                height = width / ratio;

                if (height > parentHeight) {
                    setSize(Math.max(1, parentWidth / 2), height);
                    parentWidth = $container.prop('scrollWidth');
                    width = parentWidth;
                    height = width / ratio;
                }
            } else {
                if (ratio >= 1) {
                    height = Math.min(parentHeight, parentWidth / ratio);
                    width = Math.min(parentWidth, height * ratio);
                } else {
                    width = Math.min(parentWidth, parentHeight * ratio);
                    height = Math.min(parentHeight, width / ratio);
                }
            }

            setSize(width, height);

            canvas.width = viewport.width;
            canvas.height = viewport.height;
        }

        config = config || {};
        textManager = config.textManager;
        pageNum = config.pageNum;

        $pageView = $(pageTpl({page: pageNum}));
        $textLayer = $pageView.find('.pdf-text');
        $drawLayer = $pageView.find('canvas');
        canvas = $drawLayer.get(0);
        context = canvas.getContext('2d');
        scale = normalizeScale(getOutputScale(context) * DEFAULT_SCALE);

        // the page view is automatically added to its container
        $container.append($pageView);

        return view;
    }

    return pageViewFactory;
});
