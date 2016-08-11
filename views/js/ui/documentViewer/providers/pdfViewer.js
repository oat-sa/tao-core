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
    'context',
    'core/promise',
    'tpl!ui/documentViewer/providers/pdfViewer'
], function ($, context, Promise, viewerTpl) {
    'use strict';

    var viewerInstalled = false;

    /**
     * Gets the URL of the installed PDF viewer
     * @param {String} [documentUrl]
     * @returns {String}
     */
    function getViewerUrl(documentUrl) {
        var viewerUrl = context.root_url + 'tao/views/js/lib/pdf/pdfjs/web/viewer.html';

        if (documentUrl) {
            viewerUrl += '?file=' + encodeURIComponent(documentUrl);
        }

        return viewerUrl;
    }

    /**
     * Checks if a PDF viewer has been installed
     * @returns {Promise}
     */
    function checkForInstalledViewer() {
        if (viewerInstalled) {
            return Promise.resolve(true);
        }

        return new Promise(function (resolve) {
            $.ajax({
                type: 'HEAD',
                async: true,
                url: getViewerUrl(),
                success: function onSuccess() {
                    viewerInstalled = true;
                    resolve(true);
                },

                error: function onError() {
                    viewerInstalled = false;
                    resolve(false);
                }
            });
        });
    }

    /**
     * Loads the PDF using either a custom viewer or the native browser feature
     * @param {jQuery} $iframe
     * @param {String} documentUrl
     * @returns {Promise}
     */
    function applyViewer($iframe, documentUrl) {
        return checkForInstalledViewer().then(function() {
            return new Promise(function (resolve, reject) {
                if ($iframe) {
                    $iframe
                        .off('load.provider')
                        .on('load.provider', resolve)
                        .attr('src', viewerInstalled ? getViewerUrl(documentUrl) : documentUrl);
                } else {
                    // unfortunately this is the only kind of errors we can grab
                    // as the iframe does not allow to check for load errors
                    reject(new Error('The component is not properly rendered'));
                }
            });
        });
    }

    return {
        /**
         * Gets the template used to render the viewer
         * @returns {Function}
         */
        getTemplate: function getTemplate() {
            return viewerTpl;
        },

        /**
         * Initializes the component
         */
        init: function init() {
            // needed by the providers registry to validate the provider
        },

        /**
         * Loads and displays the document
         */
        load: function load() {
            return applyViewer(this.getElement(), this.getUrl());
        }
    };
});
