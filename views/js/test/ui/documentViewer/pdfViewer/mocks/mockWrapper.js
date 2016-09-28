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
    'core/promise'
], function (Promise) {
    'use strict';

    function mockWrapper() {
        var pdfDoc = null;
        var pageNum = 1;
        var pageCount = 1;
        return {
            /**
             * Loads a PDF document using PDF.js
             * @param {String} url
             * @returns {Promise}
             */
            load: function load(url) {
                pdfDoc = {};
                pageNum = 1;
                pageCount = mockWrapper.pageCount;
                return Promise.resolve();
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
                page = Math.min(Math.max(1, page || 0), this.getPageCount());
                if (page !== pageNum) {
                    pageNum = page;
                }
                return Promise.resolve();
            },

            /**
             * Resize the viewport
             * @param {Number} width
             * @param {Number} height
             * @returns {Promise}
             */
            setSize: function setSize(width, height) {
                return Promise.resolve();
            },

            /**
             * Refresh the current page
             * @returns {Promise}
             */
            refresh: function refresh() {
                return Promise.resolve();
            },

            /**
             * Liberates the resources
             */
            destroy: function destroy() {
                pdfDoc = null;
            }
        };
    }

    mockWrapper.pageCount = 10;

    return mockWrapper;
});
