/*
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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * From a dom element, create a navigable element compatible with ui/KeyNavigator/navigator
 */
define([
    'jquery',
    'lodash',
    'core/eventifier'
], function ($, _, eventifier) {
    'use strict';

    /**
     * From a dom element, create a navigable element compatible with ui/KeyNavigator/navigator
     * @param {JQuery} $element
     * @returns {navigableDomElement}
     */
    var navigableDomElement = function navigableDomElement($element) {

        $element = $($element);

        /**
         * @typedef navigableDomElement
         */
        return eventifier({
            /**
             * Init the navigableDomElement instance
             * @returns {navigableDomElement}
             */
            init : function init() {
                if (!$element.length) {
                    throw new TypeError('dom element does not exist');
                }
                $element.attr('tabindex', -1);//add simply a tabindex to enable focusing, this tabindex is not actually used in tabbing order
                $element.addClass('key-navigation-highlight');
                return this;
            },

            /**
             * Destroy the navigableDomElement instance
             * @returns {navigableDomElement}
             */
            destroy : function destroy(){
                $element.removeClass('key-navigation-highlight');
                return this;
            },

            /**
             * Get the dom element
             * @returns {JQuery}
             */
            getElement : function getElement() {
                return $element;
            },

            /**
             * Check if the navigable element is visible
             * @returns {boolean}
             */
            isVisible : function isVisible() {
                return $element.is(':visible');
            },

            /**
             * Check if the navigable element is not disabled
             * @returns {boolean}
             */
            isEnabled : function isEnabled() {
                return !$element.is(':disabled');
            },

            /**
             * Set focus on the navigable element
             * @returns {navigableGroupElement}
             */
            focus : function focus() {
                $element.focus();
                return this;
            }
        });
    };

    /**
     * From a JQuery container, returns an array of navigableDomElement
     * @param {JQuery} $elements
     * @returns {Array}
     */
    navigableDomElement.createFromDoms =  function createFromDoms($elements){
        var list = [];
        $elements.each(function(){
            list.push(navigableDomElement($(this)));
        });
        return list;
    };

    return navigableDomElement;
});