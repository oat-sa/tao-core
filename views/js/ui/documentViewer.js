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
    'ui/component',
    'ui/documentViewer/viewerFactory',
    'tpl!ui/documentViewer/documentViewer'
], function (_, component, viewerFactory, documentViewerTpl) {
    'use strict';

    /**
     * Some defaults config
     * @type {Object}
     */
    var defaults = {
        width: 'auto',
        height: 'auto',
        fitToWidth: false,
        allowSearch: false,
        caseSensitiveSearch: false,
        highlightAllMatches: false
    };

    /**
     * Creates a document viewer
     * @param {Object} config - The config set
     * @param {Number|String} [config.width] - The width in pixels, or 'auto' to use the container's width
     * @param {Number|String} [config.height] - The height in pixels, or 'auto' to use the container's height
     * @param {Boolean} [config.fitToWidth] - The document will be displayed using the full available width instead of fitting the height
     * @param {Boolean} [config.allowSearch] - Allow to search within the displayed document
     * @param {Boolean} [config.caseSensitiveSearch] - Use a case sensitive search when the search feature is available
     * @param {Boolean} [config.highlightAllMatches] - Highlight all matches to see all of them at a glance
     * @returns {Object}
     */
    function documentViewerFactory(config) {
        var documentType, documentUrl, viewer;

        /**
         * The document viewer API
         * @type {Object}
         */
        var documentViewer = {
            /**
             * Gets the type of the loaded document
             * @returns {String}
             */
            getType: function getType() {
                return documentType || null;
            },

            /**
             * Gets the url of the loaded document
             * @returns {String}
             */
            getUrl: function getUrl() {
                return documentUrl || null;
            },

            /**
             * Gets the current viewer
             * @returns {Object}
             */
            getViewer: function getViewer() {
                return viewer || null;
            },

            /**
             * Loads a document
             * @param {String} url - The URL of the document to load
             * @param {String} type - The MIME type of the document to load
             * @returns {documentViewer}
             * @throws TypeError if one of the url or the type is missing
             * @throws Error if the document type is unknown
             * @fires load
             * @fires loaded
             * @fires unloaded
             */
            load: function load(url, type) {
                var self = this;

                if (_.isEmpty(url) || !_.isString(url)) {
                    throw new TypeError('You must provide the URL of the document!');
                }

                if (_.isEmpty(type) || !_.isString(type)) {
                    throw new TypeError('You must provide a document type!');
                }

                // destroy existing viewer before setting a new one
                if (viewer) {
                    viewer.destroy();
                }

                documentType = type;
                documentUrl = url;

                viewer = viewerFactory(documentType, _.merge({
                    type: documentType, // provide the type in case of hybrid/multi-type implementation
                    url: documentUrl,
                    replace: true       // always replace existing viewer
                }, _.pick(this.config, _.keys(defaults)))).on('loaded', function () {
                    /**
                     * @event documentViewer#loaded
                     * @param {String} url - The URL of the document to load
                     * @param {String} type - The MIME type of the document to load
                     */
                    self.trigger('loaded', documentUrl, documentType);
                }).on('unloaded', function () {
                    /**
                     * @event documentViewer#unloaded
                     * @param {String} url - The URL of the document to load
                     * @param {String} type - The MIME type of the document to load
                     */
                    self.trigger('unloaded', documentUrl, documentType);
                }).on('resized', function (width, height) {
                    /**
                     * @event documentViewer#resized
                     * @param {Number} width
                     * @param {Number} height
                     */
                    self.trigger('resized', width, height);
                }).on('error', function (err) {
                    /**
                     * @event documentViewer#error
                     * @param error
                     */
                    self.trigger('error', err);
                });

                /**
                 * @event documentViewer#load
                 * @param {String} url - The URL of the document to load
                 * @param {String} type - The MIME type of the document to load
                 */
                this.trigger('load', documentUrl, documentType);

                if (this.is('rendered')) {
                    viewer.render(this.getElement());
                }

                return this;
            },

            /**
             * Unloads the current document and clears the viewer
             * @returns {documentViewer}
             * @fires unload
             */
            unload: function unload() {
                if (viewer) {
                    viewer.destroy();
                }

                /**
                 * @event documentViewer#unload
                 * @param {String} url - The URL of the document to load
                 * @param {String} type - The MIME type of the document to load
                 */
                this.trigger('unload', documentUrl, documentType);

                viewer = null;
                documentType = null;
                documentUrl = null;

                return this;
            }
        };

        return component(documentViewer, defaults)
            .setTemplate(documentViewerTpl)
            .on('init', function onInit() {
                viewer = null;
                documentType = null;
                documentUrl = null;
            })
            .on('destroy', function onDestroy() {
                this.unload();
            })
            .on('render', function onRender() {
                if (viewer) {
                    viewer.render(this.getElement());
                }
            })
            .on('setsize', function onSetSize(width, height) {
                if (viewer) {
                    viewer.setSize(width, height);
                }
            })
            .init(config);
    }

    /**
     * Registers a viewer for a particular document type
     * @param {String} type - The of document the viewer can handle
     * @param {Object} provider - The document viewer implementation
     * @returns {documentViewerFactory}
     */
    documentViewerFactory.registerProvider = function registerProvider(type, provider) {
        viewerFactory.registerProvider(type, provider);
        return this;
    };

    /**
     * Clears the registered viewers
     * @returns {documentViewerFactory}
     */
    documentViewerFactory.clearProviders = function clearProviders() {
        viewerFactory.clearProviders();
        return this;
    };

    return documentViewerFactory;
});
