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
 * From an instance of keyNavigator, create a navigable element compatible with ui/KeyNavigator/navigator
 * It enables navigating within a group of keyNavigator
 */
define([
    'jquery',
    'lodash',
    'core/eventifier'
], function ($, _, eventifier) {
    'use strict';

    var _ns = '.navigable-group-element';

    /**
     * From an instance of keyNavigator, create a navigable element compatible with ui/KeyNavigator/navigator
     * @param {keyNavigator} keyNavigator
     * @returns {navigableGroupElement}
     */
    var navigableGroupElementFactory = function navigableGroupElementFactory(keyNavigator) {

        var $group;

        if(!keyNavigator){
            throw new TypeError('the navigation group does not exist');
        }

        $group = keyNavigator.getGroup();
        if(!$group.length || !$.contains(document, $group[0])){
            throw new TypeError('the group dom element does not exist');
        }

        /**
         * @typedef navigableGroupElement
         */
        return eventifier({
            /**
             * Init the navigableGroupElement instance
             * @returns {navigableGroupElement}
             */
            init : function init() {

                //add the focusin and focus out class for group highlighting
                $group.on('focusin'+_ns, function(){
                    $group.addClass('focusin');
                }).on('focusout'+_ns, function(){
                    _.defer(function(){
                        if(!document.activeElement || !$.contains($group.get(0), document.activeElement)){
                            $group.removeClass('focusin');
                        }
                    });
                });

                return this;
            },

            /**
             * Destroy the navigableGroupElement instance
             * @returns {navigableGroupElement}
             */
            destroy : function destroy(){

                $group
                    .removeClass('focusin')
                    .off(_ns);

                return this;
            },

            /**
             * Get the dom element
             * @returns {JQuery}
             */
            getElement : function getElement() {
                return $group;
            },

            /**
             * Check if the navigable element is visible
             * @returns {boolean}
             */
            isVisible: function isVisible() {
                var hasVisibleNavigable = false;
                if(!$group.is(':visible')){
                    return false;
                }
                _.forEach(keyNavigator.getNavigables(), function(nav){
                    if(nav.isVisible()){
                        hasVisibleNavigable = true;
                        return false;
                    }
                });
                return hasVisibleNavigable;
            },

            /**
             * Check if the navigable element is not disabled
             * @returns {boolean}
             */
            isEnabled : function isEnabled() {
                var hasEnabledNavigable = false;
                if($group.is(':disabled')){
                    return false;
                }
                _.forEach(keyNavigator.getNavigables(), function(nav){
                    if(nav.isEnabled()){
                        hasEnabledNavigable = true;
                        return false;
                    }
                });
                return hasEnabledNavigable;
            },

            /**
             * Set focus on the navigable element
             * @returns {navigableGroupElement}
             */
            focus : function focus() {
                keyNavigator.focus(this);
                return this;
            }
        });
    };

    /**
     *
     * @param {Array} keyNavigators - the array of navigators to be transformed into an array or navigableGroupElement
     * @returns {Array}
     */
    navigableGroupElementFactory.createFromNavigators =  function createFromNavigators(keyNavigators){
        var list = [];
        _.each(keyNavigators, function(keyNavigator){
            list.push(navigableGroupElementFactory(keyNavigator));
        });
        return list;
    };

    return navigableGroupElementFactory;
});