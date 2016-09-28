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
    'ui/documentViewer/providers/pdfViewer/pdfjs/wrapper',
    'tpl!ui/documentViewer/providers/pdfViewer/pdfjs/viewer'
], function ($, Promise, wrapperFactory, viewerTpl) {
    'use strict';

    /**
     * Wraps the component that use the PDF.js lib to render a PDF.
     * @param {jQuery} $container
     * @param {Object} pdfjs
     * @param {Object} config
     * @returns {Object}
     */
    function pdfjsViewerFactory($container, pdfjs, config) {
        var template = viewerTpl(config);
        var controls = {};
        var pdf = null;
        var enabled = true;

        /**
         * Will update the displayed page number, and toggle the input enabling
         */
        function updatePageNumber() {
            var page = pdf.getPage();
            if (page !== Number(controls.$pageNum.val())) {
                controls.$pageNum.val(page);
            }

            if (enabled && pdf.getPageCount() > 1) {
                controls.$pageNum.removeAttr('disabled');
            } else {
                controls.$pageNum.attr('disabled', true);
            }
        }

        /**
         * Will toggle the input enabling of the "Previous" button
         */
        function updatePrevBtn() {
            if (enabled && pdf.getPage() > 1) {
                controls.$pagePrev.removeAttr('disabled');
            } else {
                controls.$pagePrev.attr('disabled', true);
            }
        }

        /**
         * Will toggle the input enabling of the "Next" button
         */
        function updateNextBtn() {
            if (enabled && pdf.getPage() < pdf.getPageCount()) {
                controls.$pageNext.removeAttr('disabled');
            } else {
                controls.$pageNext.attr('disabled', true);
            }
        }

        /**
         * Will update the displayed controls according to the current PDF
         */
        function updateControls() {
            updatePrevBtn();
            updateNextBtn();
            updatePageNumber();
        }

        /**
         * Enable the controls
         */
        function enable() {
            enabled = true;
            updateControls();
        }

        /**
         * Disable the controls
         */
        function disable() {
            enabled = false;
            controls.$navigation.attr('disabled', true);
            controls.$pageNum.attr('disabled', true);
        }

        /**
         * Go to a particular page
         * @param page
         */
        function jumpPage(page) {
            pdf.setPage(page).then(updateControls);
            updateControls();
        }

        /**
         * Move the current page by step
         * @param step
         */
        function movePage(step) {
            jumpPage(pdf.getPage() + step);
        }

        config = config || {};

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
                pdfjs.PDFJS.disableRange = true;

                controls = {
                    $bar: $container.find('.pdf-bar'),
                    $navigation: $container.find('.navigation'),
                    $container: $container.find('.pdf-container'),
                    $pagePrev: $container.find('[data-control="pdf-page-prev"]'),
                    $pageNext: $container.find('[data-control="pdf-page-next"]'),
                    $pageNum: $container.find('[data-control="pdf-page-num"]'),
                    $pageCount: $container.find('[data-control="pdf-page-count"]'),
                    $fitToWidth: $container.find('[data-control="fit-to-width"]'),
                    $content: $container.find('[data-control="pdf-content"]')
                };

                pdf = wrapperFactory(pdfjs, controls.$content, config);

                this.setSize($container.width(), $container.height());

                disable();

                controls.$fitToWidth.on('change', function () {
                    config.fitToWidth = controls.$fitToWidth.is(':checked');
                    pdf.refresh();
                });

                controls.$navigation.on('click', function () {
                    movePage(Number($(this).data('direction')) || 1);
                });

                controls.$pageNum
                    .on('change', function () {
                        jumpPage(Number(controls.$pageNum.val()) || pdf.getPage());
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

                return pdf.load(url).then(function () {
                    controls.$pageCount.html(pdf.getPageCount());
                    enable();
                });
            },

            /**
             * Destroys the component
             */
            unload: function unload() {
                if (pdf) {
                    pdf.destroy();
                }

                $container.empty();
                controls = {};
                pdf = null;
            },

            /**
             * Sets the size of the component
             * @param {Number} width
             * @param {Number} height
             */
            setSize: function setSize(width, height) {
                var contentHeight;

                // only adjust the action bar width, and let the PDF viewer manage its size with the remaining space
                if (pdf) {
                    contentHeight = height - controls.$bar.outerHeight();
                    controls.$bar.width(width);
                    controls.$container.width(width).height(contentHeight);
                    return pdf.setSize(width, contentHeight);
                }
            }
        };
    }

    return pdfjsViewerFactory;
});
