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
 * Create a navigator to navigate between navigation group
 * @see core/keyNavigator
 *
 * @example
 * groupKeyNavigator({
 *       id : 'test-runner',
 *       groups : ['top-toolbar', 'middle-panel', 'bottom-toolbar']
 * });
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/eventifier',
    'core/keyNavigator',
], function($, _, eventifier, keyNavigator){
    'use strict';

    var _groupNavigators = {};

    var _ns = '.group-navigator';

    var _defaults = {
        replace : false,
        loop : true
    };

    /**
     * Create a navigationGroup
     *
     * @param config
     * @param {String} config.id - global unique id to define this group
     * @param {Array} config.groups - array of navigation groups to manage
     * @param {Boolean} [config.replace=false] - define if the navigation group can be reinitialized, hence replacing the existing one
     * @param {Boolean} [config.loop=false] - define if the navigation should loop after reaching the last or the first element
     * @returns {groupNavigator}
     */
    var groupNavigatorFactory = function groupNavigatorFactory(config){

        config = _.defaults(config, _defaults);

        var id = config.id;
        var groups = config.groups || [];
        var navigationGroups = [];
        var _cursor = {
            position : 0
        };

        /**
         * Get the current focused element within the key navigation group
         *
         * @returns {Object} the cursor
         */
        var getCursor = function getCursor(){
            var isFocused = false;
            if (document.activeElement) {
                _.forEach(navigationGroups, function(navigationGroup, index) {
                    var groupElement = navigationGroup.$dom.get(0);
                    if (navigationGroup.$dom.is(':visible') && (document.activeElement === groupElement || $.contains(groupElement, document.activeElement))) {
                        _cursor.position = index;
                        isFocused = true;
                        return false;
                    }
                });
            }

            if (isFocused) {
                return _cursor;
            }

            return null;
        };

        /**
         * Get the closest allowed position in the right
         *
         * @param {Number} fromPosition - the starting position
         * @returns {Number}
         */
        var getClosestPositionRight = function getClosestPositionRight(fromPosition){
            var pos;
            for(pos = fromPosition; pos < navigationGroups.length; pos++){
                if(navigationGroups[pos] && navigationGroups[pos].$dom.is(':visible')){
                    return pos;
                }
            }
            return -1;
        }

        /**
         * Get the closest allowed position in the left
         *
         * @param {Number} fromPosition - the starting position
         * @returns {Number}
         */
        var getClosestPositionLeft = function getClosestPositionLeft(fromPosition){
            var pos;
            for(pos = fromPosition; pos >= 0; pos--){
                if(navigationGroups[pos] && navigationGroups[pos].$dom.is(':visible')){
                    return pos;
                }
            }
            return -1;
        }

        if(_groupNavigators[id]){
            if(config.replace){
                _groupNavigators[id].destroy();
            }else{
                throw new Error('the navigation group id is already in use : '+id);
            }
        }

        if(!_.isArray(groups) || !groups.length){
            throw new Error('the groups must be configured with an no empty array of navigationGroup ids');
        }

        _.each(groups, function(groupId){
            var $group;
            var navigationGroup = keyNavigator.get(groupId);
            if(!navigationGroup){
                throw new TypeError('the navigation group does not exist');
            }

            $group = navigationGroup.getGroup();
            if(!$group.length || !$.contains(document, $group[0])){
                throw new TypeError('the group dom element does not exist');
            }

            //add the focusin and focus out class for group highlighting
            $group.on('focusin'+_ns, function(){
                $group.addClass('focusin');
            }).on('focusout'+_ns, function(){
                $group.removeClass('focusin');
            });

            navigationGroups.push({
                group : navigationGroup,
                $dom : $group
            });

        });


        var initialCursor = getCursor();
        if(initialCursor){
            //if the focus is on the element before the refresh, refocus again
            navigationGroups[getClosestPositionRight(initialCursor.position)].$dom.trigger('focus');
        }

        /**
         * The group navigator object
         *
         * @typedef groupNavigator
         */
        var groupNavigator = eventifier({

            /**
             * Get the navigation group id
             * @returns {String}
             */
            getId : function getId(){
                return id;
            },

            /**
             * Move cursor to next position
             *
             * @returns {groupNavigator}
             * @fires groupNavigator#upperbound when we cannot move further
             * @fires groupNavigator#next when the cursor successfully moved to the next position
             */
            next : function next(){
                var cursor = getCursor();
                var pos;
                if(cursor){
                    pos = getClosestPositionRight(cursor.position + 1);
                    if(pos >= 0){
                        this.focusPosition(pos);
                    }else if(config.loop){
                        //loop allowed, so returns to the first element
                        this.focusPosition(getClosestPositionRight(0));
                    }else{
                        //reaching the end of the list
                        this.trigger('upperbound');
                    }
                    this.trigger('next', getCursor());
                }else{
                    this.focusPosition(getClosestPositionRight(0));
                }
            },

            /**
             * Move cursor to previous position
             *
             * @returns {groupNavigator}
             * @fires groupNavigator#lowerbound when we cannot move lower
             * @fires groupNavigator#previous when the cursor successfully moved to the previous position
             */
            previous : function previous(){
                var cursor = getCursor();
                var pos;
                if(cursor){
                    pos = getClosestPositionLeft(cursor.position - 1);
                    if(pos >= 0){
                        this.focusPosition(pos);
                    }else if(config.loop){
                        //loop allowed, so returns to the last element
                        this.focusPosition(getClosestPositionLeft(navigationGroups.length - 1));
                    }else{
                        //reaching the end of the list
                        this.trigger('lowerbound');
                    }
                    this.trigger('previous', getCursor());
                }else{
                    this.focusPosition(getClosestPositionRight(0));
                }
            },

            /**
             * Focus to a position defined by its index
             *
             * @param {Integer} position
             * @returns {groupNavigator}
             * @fires groupNavigator#focus on the new cursor
             */
            focusPosition : function focusPosition(position){
                if(navigationGroups[position]){
                    _cursor.position = position;
                    navigationGroups[position].group.focus();
                    this.trigger('focus', navigationGroups[position]);
                }
                return this;
            },

            /**
             * Destroy and cleanup
             * @returns {groupNavigator}
             */
            destroy : function destroy(){
                _.each(navigationGroups, function(group){
                    group.$dom
                        .removeClass('focusin')
                        .off(_ns);
                });
                delete _groupNavigators[id];
                return this;
            }
        });

        //store the navigator for external reference
        _groupNavigators[id] = groupNavigator;

        return groupNavigator;
    }

    /**
     * Get a group navigator by its id
     *
     * @param {String} id
     * @returns {groupNavigator}
     */
    groupNavigatorFactory.get = function get(id){
        if(_groupNavigators[id]){
            return _groupNavigators[id];
        }
    };

    return groupNavigatorFactory;
});