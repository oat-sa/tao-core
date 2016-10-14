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
    'core/eventifier',
    'ui/documentViewer/providers/pdfViewer/pdfjs/areaBroker',
    'ui/documentViewer/providers/pdfViewer/pdfjs/findBar',
    'ui/documentViewer/providers/pdfViewer/pdfjs/wrapper',
    'tpl!ui/documentViewer/providers/pdfViewer/pdfjs/viewer'
], function ($, _, eventifier, areaBroker, findBarFactory, wrapperFactory, viewerTpl) {
    'use strict';

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
     * Wraps the component that use the PDF.js lib to render a PDF.
     * @param {jQuery} $container
     * @param {Object} config
     * @param {Object} config.PDFJS - The PDFJS entry point
     * @param {Boolean} [config.fitToWidth] - Fit the page to the available width, a scroll bar may appear
     * @param {Boolean} [config.allowSearch] - Allow to search within the displayed PDF
     * @param {Boolean} [config.caseSensitiveSearch] - Use a case sensitive search when the search feature is available
     * @param {Boolean} [config.highlightAllMatches] - Highlight all matches to see all of them at a glance
     * @returns {Object}
     */
    function pdfjsViewerFactory($container, config) {
        var template = viewerTpl(config);
        var events = eventifier();
        var controls = {};
        var broker = null;
        var findBar = null;
        var pdfConfig = null;
        var pdf = null;
        var PDFJS = null;
        var enabled = true;

        /**
         * Will update the displayed controls according to the current PDF
         */
        function updateControls() {
            var page = pdf.getPage();
            var pageCount = pdf.getPageCount();
            if (page !== parseInt(controls.$pageNum.val(), 10)) {
                controls.$pageNum.val(page);
            }

            toggleState(controls.$pagePrev, enabled && page > 1);
            toggleState(controls.$pageNext, enabled && page < pageCount);
            toggleState(controls.$pageNum, enabled && pageCount > 1);
        }

        /**
         * Enables the controls
         */
        function enable() {
            /**
             * Requests an enabling
             * @event enable
             */
            events.trigger('enable');
        }

        /**
         * Disable the controls
         */
        function disable() {
            /**
             * Requests a disabling
             * @event disable
             */
            events.trigger('disable');
        }

        /**
         * Will refresh the page
         */
        function refresh() {
            /**
             * Requests a page refresh
             * @event refresh
             */
            events.trigger('refresh');
        }

        /**
         * Go to a particular page
         * @param page
         */
        function jumpPage(page) {
            /**
             * Requests a page change
             * @event setpage
             * @param {Number} pageNum
             */
            events.trigger('setpage', page);
        }

        /**
         * Move the current page by step
         * @param step
         */
        function movePage(step) {
            jumpPage(pdf.getPage() + step);
        }

        config = config || {};
        PDFJS = config.PDFJS;

        pdfConfig = _.merge({
            events: events
        }, _.pick(config, ['PDFJS', 'fitToWidth']));

        if (!_.isPlainObject(PDFJS)) {
            throw new TypeError('You must provide the entry point to the PDF.js library! [config.PDFJS is missing]');
        }

        return {
            /**
             * Loads and displays the document
             * @param {String} url
             * @returns {Promise}
             */
            load: function load(url) {
                // PDF.js installed
                $container.html(template);

                // Disable the streaming mode: the file needs to be fully loaded before display.
                // This will prevent "Bad offset" error under Chrome and IE, but will slow down the first display.
                // Other approach would be to provide a range loader callback, but need a lot of work.
                PDFJS.PDFJS.disableRange = true;

                events
                    .on('enable', function () {
                        enabled = true;
                        updateControls();
                    })
                    .on('disable', function () {
                        enabled = false;
                        updateControls();
                    })
                    .on('loaded', function () {
                        controls.$pageCount.html(pdf.getPageCount());
                        enable();
                    })
                    .on('pagechange rendered', function () {
                        updateControls();
                    });

                broker = areaBroker($container, {
                    bar: $('.pdf-bar', $container),
                    actions: $('.pdf-actions', $container),
                    info: $('.pdf-info', $container),
                    content: $('.pdf-container', $container)
                });

                controls = {
                    $navigation: $container.find('.navigation'),
                    $pagePrev: $container.find('[data-control="pdf-page-prev"]'),
                    $pageNext: $container.find('[data-control="pdf-page-next"]'),
                    $pageNum: $container.find('[data-control="pdf-page-num"]'),
                    $pageCount: $container.find('[data-control="pdf-page-count"]'),
                    $fitToWidth: $container.find('[data-control="fit-to-width"]')
                };

                pdf = wrapperFactory(broker.getContentArea(), pdfConfig);

                if (config.allowSearch) {
                    findBar = findBarFactory({
                        events: events,
                        areaBroker: broker,
                        textManager: pdf.getTextManager(),
                        caseSensitive: config.caseSensitiveSearch,
                        highlightAll: config.highlightAllMatches
                    });
                }

                this.setSize($container.width(), $container.height());

                controls.$fitToWidth.on('change', function () {
                    pdfConfig.fitToWidth = controls.$fitToWidth.is(':checked');
                    refresh();
                });

                controls.$navigation.on('click', function () {
                    movePage(parseInt($(this).data('direction'), 10) || 1);
                });

                controls.$pageNum
                    .on('change', function () {
                        jumpPage(parseInt(controls.$pageNum.val(), 10) || pdf.getPage());
                    })
                    .on('keydown', function (event) {
                        switch (event.keyCode) {
                            case 38:
                                movePage(1);
                                event.stopPropagation();
                                event.preventDefault();
                                break;

                            case 40:
                                movePage(-1);
                                event.stopPropagation();
                                event.preventDefault();
                                break;
                        }
                    });

                disable();
                return pdf.load(url);
            },

            /**
             * Destroys the component
             */
            unload: function unload() {
                disable();

                if (findBar) {
                    findBar.destroy();
                }

                if (pdf) {
                    pdf.destroy();
                }

                events.removeAllListeners();
                $container.empty();
                controls = {};
                pdfConfig = null;
                pdf = null;
                findBar = null;
                broker = null;
            },

            /**
             * Sets the size of the component
             * @param {Number} width
             * @param {Number} height
             */
            setSize: function setSize(width, height) {
                var contentHeight, $bar, $content;

                // only adjust the action bar width, and let the PDF viewer manage its size with the remaining space
                if (pdf) {
                    $bar = broker.getBarArea();
                    $content = broker.getContentArea();

                    contentHeight = height - $bar.outerHeight();

                    $bar.width(width);
                    $content.width(width).height(contentHeight);

                    /**
                     * Notifies a resize
                     * @event resized
                     * @param {Number} width
                     * @param {Number} height
                     * @param {Number} contentHeight
                     */
                    events.trigger('resized', width, height, contentHeight);

                    // force the repaint of the current page, the PDF wrapper will take care of its container's size
                    return pdf.refresh();
                }
            }
        };
    }

    return pdfjsViewerFactory;
});
