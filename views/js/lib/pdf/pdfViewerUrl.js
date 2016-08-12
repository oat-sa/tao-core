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
    'core/promise'
], function ($, context, Promise) {
    'use strict';

    /**
     * The URL of the custom viewer.
     * Need to be installed in order to be used, otherwise the native browser feature will be used.
     * @type {String}
     */
    var viewerUrl = context.taobase_www + 'js/lib/pdf/pdfjs/web/viewer.html';

    /**
     * Will refer to the promise that is used to resolve the viewer
     * @type {Promise}
     */
    var viewerCheck = null;

    /**
     * Gets the URL of the PDF viewer in order to render a particular document
     * @param {String} documentUrl
     * @returns {Promise}
     */
    function getViewerUrl(documentUrl) {
        if (!viewerCheck) {
            viewerCheck = new Promise(function (resolve) {
                $.ajax({
                    type: 'HEAD',
                    async: true,
                    url: viewerUrl,
                    success: function onSuccess() {
                        resolve(true);
                    },
                    error: function onError() {
                        resolve(false);
                    }
                });
            });
        }

        return viewerCheck.then(function(customViewerInstalled) {
            if (customViewerInstalled) {
                return viewerUrl + '?file=' + encodeURIComponent(documentUrl);
            } else {
                return documentUrl;
            }
        });
    }

    return getViewerUrl;
});
