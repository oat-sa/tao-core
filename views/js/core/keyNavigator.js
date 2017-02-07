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
 *
 * Create a navigator group to enable keyboard navigation between elements
 *
 * @example
 * var $navigationBar = $('#navigation-bar');
 * var $buttons = $navigationBar.find('li');
 * keyNavigator({
 *       id : 'navigation-toolbar',
 *       replace : true,
 *       group : $navigationBar,
 *       elements : $buttons,
 *       default : 0
 *   }).on('right down', function(){
 *       this.next();
 *   }).on('left up', function(){
 *       this.previous();
 *   }).on('activate', function(cursor){
 *       cursor.$dom.click();
 *   });
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/eventifier',
], function($, _, eventifier){
    'use strict';

    var _navigationGroups = {};

    var _ns = '.navigation-group';

    var _defaults = {
        default : 0,
        keepState : false,
        replace : false,
        loop : false
    };

    var KEY_CODE_SPACE = 32;
    var KEY_CODE_ENTER = 13;
    var KEY_CODE_LEFT  = 37;
    var KEY_CODE_UP    = 38;
    var KEY_CODE_RIGHT = 39;
    var KEY_CODE_DOWN  = 40;

    /**
     * Get the list of keys that should be mapped to the directional keys
     *
     * @returns {Object}
     */
    var getArrowKeyMap = function getArrowKeyMap(){
        var map = {};
        map[KEY_CODE_UP] = 'up';
        map[KEY_CODE_LEFT] = 'left';
        map[KEY_CODE_DOWN] = 'down';
        map[KEY_CODE_RIGHT] = 'right';
        return map;
    }

    /**
     * Get the list of keys that should be mapped to activation action
     * @returns {Array}
     */
    var getActivateKey = function getActivateKey(){
        return [KEY_CODE_SPACE, KEY_CODE_ENTER];
    }

    /**
     * Create a navigationGroup
     *
     * @param config - the config
     * @param {String} config.id - global unique id to define this group
     * @param {JQuery} config.elements - the group of element to be keyboard-navigated
     * @param {JQuery} [config.group] - the container the group of elements belong to
     * @param {Number} [config.default=0] - the default position the group should set the focus on
     * @param {Boolean} [config.keepState=false] - define if the position should be saved in memory after the group blurs and re-focuses
     * @param {Boolean} [config.replace=false] - define if the navigation group can be reinitialized, hence replacing the existing one
     * @param {Boolean} [config.loop=false] - define if the navigation should loop after reaching the last or the first element
     * @returns {navigationGroup}
     */
    var navigationGroupFactory = function navigationGroupFactory(config){

        config = _.defaults(config, _defaults);

        var id = config.id;
        var $navigables = $(config.elements);
        var arrowKeyMap = getArrowKeyMap();
        var activationKeys = getActivateKey();
        var $group;
        var _cursor = {
            position : -1,
            $dom : null
        };

        /**
         * Get the current focused element within the key navigation group
         *
         * @returns {Object} the cursor
         */
        var getCursor = function getCursor(){

            var isFocused = false;

            if (document.activeElement) {
                // try to find the focused element within the known list of focusable elements
                _.forEach($navigables, function(focusable, index) {
                    if (document.activeElement === focusable) {
                        _cursor.position = index;
                        _cursor.$dom = $(focusable);
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
            for(pos = fromPosition; pos < $navigables.length; pos++){
                if($navigables[pos] && $($navigables[pos]).is(':visible')){
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
                if($navigables[pos] && $($navigables[pos]).is(':visible')){
                    return pos;
                }
            }
            return -1;
        }

        if(_navigationGroups[id]){
            if(config.replace){
                _navigationGroups[id].destroy();
            }else{
                throw new TypeError('the navigation group id is already in use : '+id);
            }
        }

        if(!$navigables.length){
            throw new TypeError('no navigation element');
        }

        $navigables.each(function(){
            var $navigable = $(this);
            if(!$navigable.length){
                throw new TypeError('dom element does not exist');
            }
            $navigable.attr('tabindex', -1);//add simply a tabindex to enable focusing, this tabindex is not actually used in tabbing order
            $navigable.addClass('key-navigation-highlight');
        });

        if(config.group){
            $group = $(config.group);
            if($group.length){
                $group
                    .addClass('key-navigation-group')
                    .attr('data-navigation-id', id);
            }else{
                throw new TypeError('group element does not exist');
            }
        }

        /**
         * The navigation group object
         *
         * @typedef navigationGroup
         */
        var navigationGroup = eventifier({

            /**
             * Get the navigation group id
             * @returns {String}
             */
            getId : function(){
                return id;
            },

            /**
             * Get the defined group the navigator group belongs to
             * @returns {JQuery}
             */
            getGroup : function(){
                return $group;
            },

            /**
             * Move cursor to next position
             *
             * @returns {navigationGroup}
             * @fires navigationGroup#upperbound when we cannot move further
             * @fires navigationGroup#next when the cursor successfully moved to the next position
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
                    //no cursor, might be blurred, so attempt resuming navigation from cursor in memory
                    this.focusPosition(getClosestPositionRight(0));
                }
                return this;
            },

            /**
             * Move cursor to previous position
             *
             * @returns {navigationGroup}
             * @fires navigationGroup#lowerbound when we cannot move lower
             * @fires navigationGroup#previous when the cursor successfully moved to the previous position
             */
            previous : function previous(){
                var cursor = getCursor();
                var pos;
                if(cursor){
                    pos = getClosestPositionLeft(cursor.position - 1);
                    if(pos >= 0){
                        this.focusPosition(pos);
                    }else if(config.loop){
                        //loop allowed, so returns to the first element
                        this.focusPosition(getClosestPositionLeft($navigables.length - 1));
                    }else{
                        //reaching the end of the list
                        this.trigger('lowerbound');
                    }
                    this.trigger('previous', getCursor());
                }else{
                    //no cursor, might be blurred, so attempt resuming navigation from cursor in memory
                    this.focusPosition(getClosestPositionRight(0));
                }
                return this;
            },

            /**
             * Focus to a position defined by its index
             *
             * @param {Integer} position
             * @returns {navigationGroup}
             * @fires navigationGroup#blur on the previous cursor
             * @fires navigationGroup#focus on the new cursor
             */
            activate : function activate(target){
                var cursor = getCursor();
                if(cursor){
                    this.trigger('activate', cursor, target);
                }
                return this;
            },

            /**
             * Go to another navigation group, defined by its id
             *
             * @param {String} groupId
             * @returns {navigationGroup}
             * @fires navigationGroup#error is the target group does not exists
             */
            goto : function goto(groupId){
                if(_navigationGroups[groupId]){
                    _navigationGroups[groupId].focus();
                }else{
                    this.trigger('error', new Error('goto an unknown navigation group'))
                }
                return this;
            },

            /**
             * Focus the cursor position in memory is keepState is activated, or the default position otherwise
             * @returns {navigationGroup}
             */
            focus : function focus(){
                if(config.keepState && _cursor && _cursor.position >= 0){
                    this.focusPosition(getClosestPositionRight(_cursor.position));
                }else{
                    this.focusPosition(getClosestPositionRight(config.default));
                }
                return this;
            },

            /**
             * Focus to a position defined by its index
             *
             * @param {Integer} position
             * @returns {navigationGroup}
             * @fires blur on the previous cursor
             * @fires focus on the new cursor
             */
            focusPosition : function focusPosition(position){
                if($navigables[position]){
                    if(_cursor.$dom){
                        this.trigger('blur', _cursor);
                    }
                    _cursor.position = position;
                    $navigables[_cursor.position].focus();
                    _cursor.$dom = $($navigables[_cursor.position]);
                    this.trigger('focus', _cursor);
                }
                return this;
            },

            /**
             * Destroy and cleanup
             * @returns {navigationGroup}
             */
            destroy : function destroy(){
                $navigables.off(_ns);
                $navigables.removeClass('navigation-highlight');
                delete _navigationGroups[id];
                return this;
            },

            /**
             * Blur the current cursor
             * @returns {navigationGroup}
             */
            blur : function blur(){
                if(_cursor && _cursor.$dom){
                    this.trigger('blur', _cursor);
                }
                return this;
            }
        });

        //internal key bindings
        //to save useless event bindings, the events are attached only if the there are more than one focusable element
        // or no group or with the group identical to the single element
        if($navigables.length > 1
            || !$group
            || $group && $navigables.get(0) !== $group.get(0)){

            $navigables.on('keydown'+_ns, function(e){
                var keyCode = e.keyCode ? e.keyCode : e.charCode;
                if(arrowKeyMap[keyCode]){
                    if(e.target.tagName === 'INPUT'){
                        //prevent scrolling of parent element
                        e.preventDefault();
                    }
                    e.stopPropagation();
                    navigationGroup.trigger(arrowKeyMap[keyCode]);
                }
            }).on('keyup'+_ns, function(e){
                var keyCode = e.keyCode ? e.keyCode : e.charCode;
                if(activationKeys.indexOf(keyCode) >= 0){
                    e.preventDefault();
                    navigationGroup.activate(e.target);
                }
            });
        }

        $navigables.on('blur', function(){
            navigationGroup.blur();
        });

        //store the navigator for external reference
        _navigationGroups[id] = navigationGroup;

        return navigationGroup;
    }

    /**
     * Get a group navigation by its id
     *
     * @param {String} id
     * @returns {navigationGroup}
     */
    navigationGroupFactory.get = function get(id){
        if(_navigationGroups[id]){
            return _navigationGroups[id];
        }
    };

    return navigationGroupFactory;
});
