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
    'core/eventifier',
    'core/promise'
], function (_, eventifier, Promise) {
    'use strict';

    /**
     * Fakes the PDF.js API
     * @returns {Object}
     */
    var mockPDFJS = {
        /*
         * some configurable entries to setup the mock
         */
        pageCount: 10,
        viewportWidth: 256,
        viewportHeight: 128,
        textContent: 'This is a test',

        PDFJS: {},

        getDocument: function getDocument(uri) {
            return Promise.resolve(pdfDocumentFactory(uri));
        },

        renderTextLayer: function renderTextLayer(config) {
            var rejectPromise;
            var promiseTo = null;
            var cancelTo = null;
            var promise = new Promise(function (resolve, reject) {
                promiseTo = setTimeout(function () {
                    _.forEach(config.textContent.items, function (item) {
                        var textDiv = document.createElement('div');
                        config.textDivs.push(textDiv);
                        textDiv.textContent = item.str;
                        config.container.appendChild(textDiv);
                    });

                    clearTimeout(cancelTo);
                    promiseTo = null;
                    resolve();
                }, 100);
                rejectPromise = reject;
            });

            function cancel() {
                if (promiseTo !== null) {
                    clearTimeout(promiseTo);
                    rejectPromise('canceled');
                }
                promiseTo = null;
            }

            if (config.timeout) {
                cancelTo = setTimeout(cancel, config.timeout);
            }

            mockPDFJS.trigger('textLayer');
            return {
                promise: promise,
                cancel: cancel
            };
        }
    };

    /**
     * Fakes a PDF.js document object
     * @param {String} uri
     * @returns {Object}
     */
    function pdfDocumentFactory(uri) {
        return {
            numPages: mockPDFJS.pageCount,

            getPage: function getPage() {
                return Promise.resolve(pdfPageFactory());
            },

            destroy: function destroy() {

            }
        };
    }

    /**
     * Fakes a PDF.js page object
     * @returns {Object}
     */
    function pdfPageFactory() {
        return {
            getViewport: function getViewport() {
                return {
                    width: mockPDFJS.viewportWidth,
                    height: mockPDFJS.viewportHeight,

                    clone: function() {
                        return _.clone(this);
                    }
                };
            },

            getTextContent: function getTextContent() {
                return Promise.resolve({
                    items: _.map(mockPDFJS.textContent.split(' '), function (term) {
                        return {str: term};
                    })
                });
            },

            render: function render() {
                mockPDFJS.trigger('pageRender');
                return {
                    promise: new Promise(function (resolve) {
                        setTimeout(resolve, 100);
                    })
                };
            }
        };
    }

    return eventifier(mockPDFJS);
});
