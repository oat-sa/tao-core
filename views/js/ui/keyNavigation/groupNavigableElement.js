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

    var _ns = '.group-navigator';

    var groupNavigableElement = function groupNavigableElement(navigableElement) {

        var $group;

        if(!navigableElement){
            throw new TypeError('the navigation group does not exist');
        }

        $group = navigableElement.getGroup();
        if(!$group.length || !$.contains(document, $group[0])){
            throw new TypeError('the group dom element does not exist');
        }

        return eventifier({
            init: function init() {

                //add the focusin and focus out class for group highlighting
                $group.on('focusin'+_ns, function(){
                    $group.addClass('focusin');
                }).on('focusout'+_ns, function(){
                    $group.removeClass('focusin');
                });

                return this;
            },
            destroy : function destroy(){

                $group
                    .removeClass('focusin')
                    .off(_ns);

                //navigableElement.destroy();
                return this;
            },
            getElement: function getElement() {
                return $group;
            },
            isVisible: function isVisible() {
                return $group.is(':visible');
            },
            exists: function exists() {
                return $group.length;
            },
            focus: function focus() {
                navigableElement.focus();
                return this;
            }
        });
    };

    groupNavigableElement.createFromNavigableDoms =  function createFromNavigableDoms(navigableDomElements){
        var list = [];
        _.each(navigableDomElements, function(el){
            list.push(groupNavigableElement(el));
        });
        return list;
    };

    return groupNavigableElement;
});