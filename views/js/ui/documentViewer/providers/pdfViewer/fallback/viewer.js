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
    'tpl!ui/documentViewer/providers/pdfViewer/fallback/viewer'
], function ($, Promise, viewerTpl) {
    'use strict';

    /**
     * Wraps the component that use the native PDF viewer provided by the browser.
     * @param {jQuery} $container
     * @returns {Object}
     */
    function fallbackViewerFactory($container) {
        var template = viewerTpl();
        var $viewer = null;

        return {
            /**
             * Loads and displays the document
             * @param {String} url
             * @returns {Promise}
             */
            load: function load(url) {
                return new Promise(function (resolve) {
                    $viewer = $container.html(template).find('iframe');
                    $viewer
                        .one('load.provider', resolve)
                        .attr('src', url);
                });
            },

            /**
             * Destroys the component
             */
            unload: function unload() {
                $container.empty();
                $viewer = null;
            },

            /**
             * Sets the size of the component
             * @param {Number} width
             * @param {Number} height
             */
            setSize: function setSize(width, height) {
                if ($viewer) {
                    // the browser will adjust the PDF
                    $viewer.width(width).height(height);
                }
            }
        };
    }

    return fallbackViewerFactory;
});
