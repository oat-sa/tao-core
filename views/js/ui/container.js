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
     * Defines a container manager
     * @param {String} [containerSelector] - The CSS selector of the container (default: .container)
     * @returns {containerManager}
     */
    function containerFactory(containerSelector) {
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
                containerSelector = cssScope;

                $container = $(containerSelector);
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
             * Find an element that belongs to the container.
             * @param {String} selector
             * @returns {jQuery}
             */
            find: function find(selector) {
                return $container.find(selector);
            },

            /**
             * Writes content into the container.
             * @param content
             * @returns {containerManager}
             */
            write: function write(content) {
                $container.html(content);
                return this;
            },

            /**
             * Gets the data encoded into the DOM.
             * @returns {Object}
             */
            getData: function getData() {
                return $container.data();
            },

            /**
             * Sets the data encoded into the DOM.
             * @param {Object} data
             * @returns {containerManager}
             */
            setData: function setData(data) {
                $container.removeData().data(data);
                return this;
            },

            /**
             * Remove the data encoded into the DOM.
             * @returns {containerManager}
             */
            removeData: function removeData() {
                $container.removeData();
                return this;
            },

            /**
             * Checks whether a value has been encoded into the DOM.
             * @param {String} name
             * @returns {Boolean}
             */
            hasValue: function hasValue(name) {
                var data = this.getData();
                return 'undefined' !== typeof (data && data[name]);
            },

            /**
             * Gets a value encoded into the DOM.
             * @param {String} name
             * @returns {*}
             */
            getValue: function getValue(name) {
                var data = this.getData();
                return data && data[name];
            },

            /**
             * Encodes into the DOM.
             * @param {String} name
             * @param {Object} value
             * @returns {containerManager}
             */
            setValue: function setValue(name, value) {
                $container.data(name, value);
                return this;
            },

            /**
             * Gets access to the container element
             * @returns {jQuery}
             */
            getElement: function getElement() {
                return $container;
            },

            /**
             * Gets the container's selector
             * @returns {String}
             */
            getSelector: function getSelector() {
                return containerSelector;
            }
        };

        return containerManager.init(containerSelector || '.container');
    }

    return containerFactory;
});
