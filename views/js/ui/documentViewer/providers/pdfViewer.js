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
    'core/requireIfExists',
    'ui/documentViewer/providers/pdfViewer/fallback/viewer',
    'ui/documentViewer/providers/pdfViewer/pdfjs/viewer',
    'tpl!ui/documentViewer/providers/pdfViewer/viewer'
], function (_, requireIfExists, fallbackFactory, pdfjsFactory, viewerTpl) {
    'use strict';

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
            this.pdf = null;
        },

        /**
         * Loads and displays the document
         */
        load: function load() {
            var self = this;
            var $element = this.getElement();

            // try to load the  PDF.js lib, otherwise fallback to the browser native handling
            return requireIfExists('pdfjs-dist/build/pdf')
                .then(function (pdfjs) {
                    var config = _.clone(self.config);
                    if (pdfjs) {
                        config.PDFJS = pdfjs;
                        self.pdf = pdfjsFactory($element, config);
                    } else {
                        self.pdf = fallbackFactory($element, config);
                    }

                    return self.pdf.load(self.getUrl());
                })
                .then(function () {
                    self.setSize($element.width(), $element.height());
                });
        },

        /**
         * Destroys the component
         */
        unload: function unload() {
            if (this.pdf) {
                this.pdf.unload();
            }

            if (this.is('rendered')) {
                this.getElement().empty();
            }

            this.pdf = null;
        },

        /**
         * Sets the size of the component
         * @param {Number} width
         * @param {Number} height
         */
        setSize: function setSize(width, height) {
            if (this.pdf) {
                this.pdf.setSize(width, height);
            }
        }
    };
});
