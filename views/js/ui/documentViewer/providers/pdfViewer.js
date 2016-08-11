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
    'core/promise',
    'tpl!ui/documentViewer/providers/pdf/fallback'
], function (Promise, fallbackTpl) {
    'use strict';

    return {
        /**
         * Gets the template used to render the viewer
         * @returns {Function}
         */
        getTemplate: function getTemplate() {
            return fallbackTpl;
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
            var $element = this.getElement();
            var url = this.getUrl();

            return new Promise(function (resolve, reject) {
                if ($element) {
                    $element
                        .off('load.provider')
                        .on('load.provider', resolve)
                        .attr('src', url);
                } else {
                    // unfortunately this is the only kind of errors we can grab
                    // as the iframe does not allow to check for load errors
                    reject(new Error('The component is not properly rendered'));
                }
            });
        }
    };
});
