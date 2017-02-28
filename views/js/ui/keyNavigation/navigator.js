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
 * var navigables = domNavigableElement.createFromDoms($buttons);
 * keyNavigator({
 *       id : 'navigation-toolbar',
 *       replace : true,
 *       group : $navigationBar,
 *       elements : navigables,
 *       defaultPosition : 0
 *   }).on('right down', function(){
 *       this.next();
 *   }).on('left up', function(){
 *       this.previous();
 *   }).on('activate', function(cursor){
 *       cursor.navigable.getElement().click();
 *   });
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/eventifier'
], function($, _, eventifier){
    'use strict';

    var _navigationGroups = {};

    var _ns = '.ui-key-navigator';

    var _defaults = {
        defaultPosition : 0,
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
    };

    /**
     * Get the list of keys that should be mapped to activation action
     * @returns {Array}
     */
    var getActivateKey = function getActivateKey(){
        return [KEY_CODE_SPACE, KEY_CODE_ENTER];
    };

    /**
     * Check if the object is argument is a valid navigable element
     *
     * @param {Object} navElement
     * @returns {boolean}
     */
    var isNavigableElement = function isNavigableElement(navElement){
        return (
            navElement
            && _.isFunction(navElement.init)
            && _.isFunction(navElement.destroy)
            && _.isFunction(navElement.getElement)
            && _.isFunction(navElement.isVisible)
            && _.isFunction(navElement.isEnabled)
            && _.isFunction(navElement.focus)
        );
    };

    /**
     * Create a keyNavigator
     *
     * @param config - the config
     * @param {String} config.id - global unique id to define this group
     * @param {JQuery} config.elements - the group of element to be keyboard-navigated
     * @param {JQuery} [config.group] - the container the group of elements belong to
     * @param {Number|Function} [config.defaultPosition=0] - the default position the group should set the focus on (could be a function to compute the position)
     * @param {Boolean} [config.keepState=false] - define if the position should be saved in memory after the group blurs and re-focuses
     * @param {Boolean} [config.replace=false] - define if the navigation group can be reinitialized, hence replacing the existing one
     * @param {Boolean} [config.loop=false] - define if the navigation should loop after reaching the last or the first element
     * @returns {keyNavigator}
     */
    var keyNavigatorFactory = function keyNavigatorFactory(config){

        var id, navigables, keyNavigator, $group;
        var arrowKeyMap = getArrowKeyMap();
        var activationKeys = getActivateKey();
        var _cursor = {
            position : -1,
            navigable : null
        };

        /**
         * Get the current focused element within the key navigation group
         *
         * @returns {Object} the cursor
         */
        var getCurrentCursor = function getCurrentCursor(){

            var isFocused = false;

            if (document.activeElement) {
                // try to find the focused element within the known list of focusable elements
                _.forEach(navigables, function(navigable, index) {
                    if (navigable.isVisible() && navigable.isEnabled()
                        && (document.activeElement === navigable.getElement().get(0) || $.contains(navigable.getElement().get(0), document.activeElement))) {
                        _cursor.position = index;
                        _cursor.navigable = navigable;
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
            for(pos = fromPosition; pos < navigables.length; pos++){
                if(navigables[pos] && navigables[pos].isVisible() && navigables[pos].isEnabled()){
                    return pos;
                }
            }
            return -1;
        };

        /**
         * Get the closest allowed position in the left
         *
         * @param {Number} fromPosition - the starting position
         * @returns {Number}
         */
        var getClosestPositionLeft = function getClosestPositionLeft(fromPosition){
            var pos;
            for(pos = fromPosition; pos >= 0; pos--){
                if(navigables[pos] && navigables[pos].isVisible() && navigables[pos].isEnabled()){
                    return pos;
                }
            }
            return -1;
        };

        config = _.defaults(config || {}, _defaults);

        id = config.id || _.uniqueId('navigator_');
        navigables = config.elements || [];

        if(_navigationGroups[id]){
            if(config.replace){
                _navigationGroups[id].destroy();
            }else{
                throw new TypeError('the navigation group id is already in use : '+id);
            }
        }

         _.each(navigables, function(navigable){
                //check if it is a valid navigable element
                navigable.init();
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
         * @typedef keyNavigator
         */
        keyNavigator = eventifier({

            /**
             * Get the navigation group id
             * @returns {String}
             */
            getId : function getId(){
                return id;
            },

            /**
             * Get the defined group the navigator group belongs to
             * @returns {JQuery}
             */
            getGroup : function getGroup(){
                return $group;
            },

            /**
             * Move cursor to next position
             *
             * @returns {keyNavigator}
             * @fires keyNavigator#upperbound when we cannot move further
             * @fires keyNavigator#next when the cursor successfully moved to the next position
             */
            next : function next(){
                var cursor = getCurrentCursor();
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
                    this.trigger('next', getCurrentCursor());
                }else{
                    //no cursor, might be blurred, so attempt resuming navigation from cursor in memory
                    this.focusPosition(getClosestPositionRight(0));
                }
                return this;
            },

            /**
             * Move cursor to previous position
             *
             * @returns {keyNavigator}
             * @fires keyNavigator#lowerbound when we cannot move lower
             * @fires keyNavigator#previous when the cursor successfully moved to the previous position
             */
            previous : function previous(){
                var cursor = getCurrentCursor();
                var pos;
                if(cursor){
                    pos = getClosestPositionLeft(cursor.position - 1);
                    if(pos >= 0){
                        this.focusPosition(pos);
                    }else if(config.loop){
                        //loop allowed, so returns to the first element
                        this.focusPosition(getClosestPositionLeft(navigables.length - 1));
                    }else{
                        //reaching the end of the list
                        this.trigger('lowerbound');
                    }
                    this.trigger('previous', getCurrentCursor());
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
             * @returns {keyNavigator}
             * @fires keyNavigator#blur on the previous cursor
             * @fires keyNavigator#focus on the new cursor
             */
            activate : function activate(target){
                var cursor = getCurrentCursor();
                if(cursor){
                    this.trigger('activate', cursor, target);
                }
                return this;
            },

            /**
             * Go to another navigation group, defined by its id
             *
             * @param {String} groupId
             * @returns {keyNavigator}
             * @fires keyNavigator#error is the target group does not exists
             */
            goto : function goto(groupId){
                if(_navigationGroups[groupId]){
                    _navigationGroups[groupId].focus();
                }else{
                    this.trigger('error', new Error('goto an unknown navigation group'));
                }
                return this;
            },

            /**
             * Focus the cursor position in memory is keepState is activated, or the default position otherwise
             * @param {keyNavigator} [originNavigator] -  optionally indicates where the previous focus is on
             * @returns {keyNavigator}
             */
            focus : function focus(originNavigator){
                var pos;
                if(config.keepState && _cursor && _cursor.position >= 0){
                    pos = _cursor.position;
                }else if(_.isFunction(config.defaultPosition)){
                    pos = config.defaultPosition(navigables);
                }else{
                    pos = config.defaultPosition;
                }
                this.focusPosition(getClosestPositionRight(pos), originNavigator);
                return this;
            },

            /**
             * Focus to a position defined by its index
             *
             * @param {Integer} position
             * @returns {keyNavigator}
             * @fires blur on the previous cursor
             * @fires focus on the new cursor
             */
            focusPosition : function focusPosition(position, originNavigator){
                if(navigables[position]){
                    if(_cursor.navigable){
                        this.trigger('blur', this.getCursor(), originNavigator);
                    }
                    _cursor.position = position;
                    navigables[_cursor.position].focus();
                    _cursor.navigable = navigables[_cursor.position];
                    this.trigger('focus', this.getCursor(), originNavigator);
                }
                return this;
            },

            /**
             * Set focus on the first available focusable element
             * @returns {keyNavigator}
             */
            first : function first(){
                this.focusPosition(getClosestPositionRight(0));
                return this;
            },

            /**
             * Set focus on the last available focusable element
             * @returns {keyNavigator}
             */
            last : function last(){
                this.focusPosition(getClosestPositionLeft(navigables.length -1));
                return this;
            },

            /**
             * Destroy and cleanup
             * @returns {keyNavigator}
             */
            destroy : function destroy(){
                _.each(navigables, function(navigable){
                    navigable.getElement().off(_ns);
                    navigable.destroy();
                });

                //$navigables.removeClass('navigation-highlight');
                delete _navigationGroups[id];
                return this;
            },

            /**
             * Blur the current cursor
             * @returns {keyNavigator}
             */
            blur : function blur(){
                var cursor = this.getCursor();
                if(cursor && cursor.navigable){
                    this.trigger('blur', cursor);
                }
                return this;
            },

            /**
             * Return the current cursor of the navigator
             * @returns {Object}
             */
            getCursor : function getCursor(){
                //clone the return cursor to protect this private variable
                return _.clone(_cursor);
            }
        });

        _.each(navigables, function(navigable){

            if(!isNavigableElement(navigable)){
                throw new TypeError('not a valid navigable element');
            }

            if(navigables.length > 1
                || !$group
                || $group && navigable.getElement().get(0) !== $group.get(0)){

                //internal key bindings
                //to save useless event bindings, the events are attached only if the there are more than one focusable element
                // or no group or with the group identical to the single element
                navigable.getElement().on('keydown'+_ns, function(e){
                    var keyCode = e.keyCode ? e.keyCode : e.charCode;
                    if(arrowKeyMap[keyCode]){
                        if(e.target.tagName !== 'IMG' && !$(e.target).hasClass('key-navigation-scrollable')){
                            //prevent scrolling of parent element
                            e.preventDefault();
                        }
                        e.stopPropagation();
                        keyNavigator.trigger(arrowKeyMap[keyCode]);
                    }
                }).on('keyup'+_ns, function(e){
                    var keyCode = e.keyCode ? e.keyCode : e.charCode;
                    if(activationKeys.indexOf(keyCode) >= 0){
                        e.preventDefault();
                        keyNavigator.activate(e.target);
                    }
                });
            }

            navigable.getElement().on('blur', function(){
                keyNavigator.blur();
            });

        });

        //store the navigator for external reference
        _navigationGroups[id] = keyNavigator;

        return keyNavigator;
    };

    /**
     * Get a group navigation by its id
     *
     * @param {String} id
     * @returns {keyNavigator}
     */
    keyNavigatorFactory.get = function get(id){
        if(_navigationGroups[id]){
            return _navigationGroups[id];
        }
    };

    return keyNavigatorFactory;
});
