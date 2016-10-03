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
    'ui/hider',
    'tpl!ui/documentViewer/providers/pdfViewer/pdfjs/page'
], function ($, hider, pageTpl) {
    'use strict';

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
     * Creates a page view
     * @param {Number} pageNum
     * @returns {Object}
     */
    function pageViewFactory(pageNum) {
        var $container = $(pageTpl({page: pageNum}));
        var $textLayer = $container.find('.pdf-text');
        var $page = $container.find('canvas');
        var canvas = $page.get(0);
        var context = canvas.getContext('2d');
        var scale = getOutputScale(context);

        return {
            /**
             * The page number that is attached to this view
             * @type {Number}
             */
            pageNum: pageNum,

            /**
             * Whether the view has been rendered or not
             * @type {Boolean}
             */
            rendered: false,

            /**
             * The scale factor that is used to render the view
             * @type {Number}
             */
            scale: pageViewFactory.normalizeScale(scale * pageViewFactory.DEFAULT_SCALE),

            /**
             * Gets the page container
             * @returns {jQuery}
             */
            getContainer: function getContainer() {
                return $container;
            },

            /**
             * Gets the page panel
             * @returns {jQuery}
             */
            getPage: function getPage() {
                return $page;
            },

            /**
             * Gets the text layer container
             * @returns {jQuery}
             */
            getTextLayer: function getTextLayer() {
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
             * Resize the view according to the provided dimensions
             * @param {Number} width
             * @param {Number} height
             * @param {Object} [viewport]
             */
            setSize: function setSize(width, height, viewport) {
                $container
                    .width(width)
                    .height(height);

                $page
                    .width(width)
                    .height(height);

                $textLayer
                    .width(width)
                    .height(height);

                if (viewport) {
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                }
            },

            /**
             * Shows the page
             */
            show: function show() {
                hider.show($container);
            },

            /**
             * Hides the page
             */
            hide: function hide() {
                hider.hide($container);
            },

            /**
             * Remove and destroys the page view
             */
            destroy: function destroy() {
                $container.remove();

                $container = null;
                $textLayer = null;
                $page = null;
                canvas = null;
                context = null;
            }
        };
    }

    /**
     * The default scale factor
     * @type {Number}
     */
    pageViewFactory.DEFAULT_SCALE = 1.0;

    /**
     * The minimum scale factor that allows a good experience
     * @type {Number}
     */
    pageViewFactory.MIN_SCALE = 0.25;

    /**
     * The maximum scale factor that allows a good experience
     * @type {Number}
     */
    pageViewFactory.MAX_SCALE = 10.0;

    /**
     * Normalize a scale factor
     * @param {Number} scale
     * @returns {Number}
     */
    pageViewFactory.normalizeScale = function normalizeScale(scale) {
        return Math.min(Math.max(pageViewFactory.MIN_SCALE, parseInt(scale, 10) || pageViewFactory.DEFAULT_SCALE), pageViewFactory.MAX_SCALE);
    };


    return pageViewFactory;
});
