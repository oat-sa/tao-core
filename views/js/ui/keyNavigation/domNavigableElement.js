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

define([
    'jquery',
    'lodash',
    'core/eventifier',
], function ($, _, eventifier) {
    'use strict';

    var domNavigableElement = function domNavigableElement($element) {

        $element = $($element);

        return eventifier({
            init: function init() {
                if (!$element.length) {
                    throw new TypeError('dom element does not exist');
                }
                $element.attr('tabindex', -1);//add simply a tabindex to enable focusing, this tabindex is not actually used in tabbing order
                $element.addClass('key-navigation-highlight');
                return this;
            },
            destroy : function destroy(){
                $element.removeClass('navigation-highlight');
                return this;
            },
            getElement: function getElement() {
                return $element;
            },
            isVisible: function isVisible() {
                return $element.is(':visible');
            },
            exists: function exists() {
                return $element.length;
            },
            focus: function focus() {
                $element.focus();
                return this;
            }
        });
    };

    domNavigableElement.createFromJqueryContainer =  function createFromJqueryContainer($elements){
        var list = [];
        $elements.each(function(){
            list.push(domNavigableElement($(this)));
        });
        return list;
    };

    return domNavigableElement;
});