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
    'core/promise',
    'ui/documentViewer/providers/pdfViewer/pdfjs/pageView'
], function ($, _, Promise, pageViewFactory) {
    'use strict';

    /**
     * A conversion factor for CSS units
     * @type {Number}
     */
    var CSS_UNITS = 96.0 / 72.0;

    /**
     * Creates a pages manager that will handle the PDF views
     * @param {Number} pageCount
     * @param {jQuery} $pageContainer
     * @param {Object} config
     * @param {Boolean} [config.fitToWidth] - Fit the page to the available width, a scroll bar may appear
     * @returns {Object}
     */
    function pagesManagerFactory(pageCount, $pageContainer, config) {
        var activeView = null;
        var views;

        var pagesManager = {
            /**
             * Gets the pages container
             * @returns {jQuery}
             */
            getContainer: function getContainer() {
                return $pageContainer;
            },

            /**
             * Gets the number of managed views
             * @returns {Number}
             */
            getViewsCount: function getViewsCount() {
                return pageCount;
            },

            /**
             * Gets the view related to a particular page
             * @param {Number} page
             * @returns {Object}
             */
            getView: function getView(page) {
                var index, view;

                page = Math.min(Math.max(1, page), pageCount);
                index = page - 1;

                view = views[index];
                if (!view) {
                    views[index] = view = pageViewFactory(page);
                    $pageContainer.append(view.getContainer());
                }

                return view;
            },

            /**
             * Gets the active page view
             * @returns {Object}
             */
            getActiveView: function getActiveView() {
                return activeView;
            },

            /**
             * Sets the active page view
             * @param {Number} page
             */
            setActiveView: function setActiveView(page) {
                var oldActiveView = activeView;

                activeView = pagesManager.getView(page);

                if (oldActiveView && oldActiveView !== activeView) {
                    oldActiveView.hide();
                }

                if (activeView) {
                    activeView.show();
                }
            },

            /**
             * Resize a page view
             * @param {Object} view
             * @param {Object} viewport
             */
            resizeView: function resizeView(view, viewport) {
                var ratio = (viewport.width / (viewport.height || 1)) || 1;
                var parentWidth = $pageContainer.width();
                var parentHeight = $pageContainer.height();
                var parentOffset = $pageContainer.offset();
                var $page = view.getContainer();
                var width, height;

                if (config.fitToWidth) {
                    width = parentWidth;
                    height = width / ratio;

                    if (height > parentHeight) {
                        view.setSize(Math.max(1, parentWidth / 2), height);
                        parentWidth = $pageContainer.prop('scrollWidth');
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

                view.setSize(width, height, viewport);

                $page
                    .offset({
                        left: parentOffset.left + Math.max(0, (parentWidth - width) / 2)
                    });
            },

            /**
             * Renders a page into the active view
             * @param {Object} page
             * @returns {Promise}
             */
            renderPage: function renderPage(page) {
                var viewport, renderContext;

                if (activeView) {
                    activeView.rendered = false;
                    viewport = page.getViewport(activeView.scale * CSS_UNITS);
                    renderContext = {
                        canvasContext: activeView.getRenderingContext(),
                        viewport: viewport
                    };

                    pagesManager.resizeView(activeView, viewport);

                    return page.render(renderContext).promise.then(function () {
                        activeView.rendered = true;
                    });
                }

                return Promise.resolve();
            },

            /**
             * Destroys the pages manager
             */
            destroy: function destroy() {
                _.forEach(views, function (view) {
                    if (view) {
                        view.destroy();
                    }
                });

                activeView = null;
                views = null;
            }
        };

        pageCount = Math.max(1, pageCount || 0);
        views = new Array(pageCount);

        return pagesManager;
    }

    return pagesManagerFactory;
});
