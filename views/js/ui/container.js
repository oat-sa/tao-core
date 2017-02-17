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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash'
], function ($, _) {
    'use strict';

    /**
     * Extract CSS class names from a selector
     * @param {String} selector
     * @returns {String}
     */
    function getCssClass(selector) {
        var parts;

        parts = [];
        _.forEach(selector.split(' '), function(elem) {
            if (elem && elem.charAt(0) === '.') {
                parts.push(elem.substr(1));
            }
        });
        return parts.join(' ');
    }

    /**
     *
     * @param {String} selector
     * @returns {containerManager}
     */
    function containerFactory(selector) {
        var $container;
        var containerCls;

        /**
         * @typedef {containerManager}
         */
        var containerManager = {
            /**
             * Initializes the component.
             * @param {String} cssScope
             */
            init: function init(cssScope) {
                if (!cssScope || !_.isString(cssScope)) {
                    throw new TypeError('You must provide a CSS scope for the container manager!');
                }

                containerCls = getCssClass(cssScope);
                selector = cssScope;

                $container = $(selector);
                return this;
            },

            /**
             * Cleans up component and release resources
             */
            destroy: function destroy() {
                $container = null;
                return this;
            },

            /**
             * Checks if the container has the wanted scope
             * @param {String} scope
             * @returns {Boolean}
             */
            hasScope: function hasScope(scope) {
                return !!($container && $container.is(scope));
            },

            /**
             * Changes the scope of the container
             * @param {String} scope
             * @returns {containerManager}
             */
            changeScope: function changeScope(scope) {
                if ($container) {
                    $container
                        .removeClass()
                        .addClass(containerCls);

                    if (scope) {
                        $container.addClass(getCssClass(scope));
                    }
                }
                return this;
            },

            /**
             * Gets access to the container
             * @returns {jQuery}
             */
            getContainer: function getContainer() {
                return $container;
            },

            /**
             * Gets the container's selector
             * @returns {String}
             */
            getSelector: function getSelector() {
                return selector;
            }
        };

        return containerManager.init(selector || '.container');
    }

    return containerFactory;
});
