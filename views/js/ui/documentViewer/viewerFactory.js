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
    'core/promise',
    'core/providerRegistry',
    'core/delegator',
    'ui/component'
], function (_, Promise, providerRegistry, delegator, component) {
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
     * A list of methods any provider must have
     * @type {Array}
     */
    var mandatory = [
        'load'          // loads the document to view
    ];

    /**
     * Creates a document viewer
     * @param {Object} config - The config set
     * @param {String} config.url - The URL of the document to load
     * @param {String} config.type - The MIME type of the document to load
     * @param {Number|String} [config.width] - The width in pixels, or 'auto' to use the container's width
     * @param {Number|String} [config.height] - The height in pixels, or 'auto' to use the container's height
     * @param {Boolean} [config.fitToWidth] - The document will be displayed using the full available width instead of fitting the height
     * @param {Boolean} [config.allowSearch] - Allow to search within the displayed document
     * @param {Boolean} [config.caseSensitiveSearch] - Use a case sensitive search when the search feature is available
     * @param {Boolean} [config.highlightAllMatches] - Highlight all matches to see all of them at a glance
     * @returns {Object}
     */
    function viewerFactory(documentType, config) {

        /**
         * The selected document viewer
         * @type {Object}
         */
        var viewer = viewerFactory.getProvider(documentType);

        /**
         * The document viewer API
         * @type {Object}
         */
        var documentViewer = component({
            /**
             * Gets the type of the loaded document
             * @returns {String}
             */
            getType: function getType() {
                return this.config.type;
            },

            /**
             * Gets the url of the loaded document
             * @returns {String}
             */
            getUrl: function getUrl() {
                return this.config.url;
            }
        }, defaults);

        /**
         * The function used to delegate the calls from the API to the provider.
         * @type {Function}
         */
        var delegate = delegator(documentViewer, viewer, {
            name: documentType + 'Viewer',
            eventifier: false,
            wrapper: function viewerWrapper(response) {
                return Promise.resolve(response);
            }
        });

        if (_.isFunction(viewer.getTemplate)) {
            documentViewer.setTemplate(viewer.getTemplate());
        }

        return documentViewer
            .on('init', function onInit() {
                var self = this;
                delegate('init').then(function () {
                    /**
                     * @event viewer#initialized
                     */
                    self.trigger('initialized');
                }).catch(function (err) {
                    /**
                     * @event viewer#error
                     * @param err
                     */
                    self.trigger('error', err);
                });
            })
            .on('destroy', function onDestroy() {
                var self = this;
                delegate('unload').then(function () {
                    /**
                     * @event viewer#unloaded
                     */
                    self.trigger('unloaded');
                }).catch(function (err) {
                    /**
                     * @event viewer#error
                     * @param err
                     */
                    self.trigger('error', err);
                });
            })
            .on('render', function onRender() {
                var self = this;
                delegate('load').then(function () {
                    /**
                     * @event viewer#loaded
                     */
                    self.trigger('loaded');
                }).catch(function (err) {
                    /**
                     * @event viewer#error
                     * @param err
                     */
                    self.trigger('error', err);
                });
            })
            .on('setsize', function onSetSize(width, height) {
                var self = this;
                delegate('setSize', width, height).then(function () {
                    /**
                     * @event viewer#resized
                     * @param {Number} width
                     * @param {Number} height
                     */
                    self.trigger('resized', width, height);
                }).catch(function (err) {
                    /**
                     * @event viewer#error
                     * @param err
                     */
                    self.trigger('error', err);
                });
            })
            .init(config);
    }

    return providerRegistry(viewerFactory, function (provider) {
        //mandatory methods
        _.each(mandatory, function (name) {
            if (!_.isFunction(provider[name])) {
                throw new TypeError('The viewer provider MUST implement the ' + name + '() method!');
            }
        });
        return true;
    });
});
