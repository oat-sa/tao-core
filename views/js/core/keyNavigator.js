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

    var getArrowKeyMap = function getArrowKeyMap(){
        var map = {};
        map[KEY_CODE_UP] = 'up';
        map[KEY_CODE_LEFT] = 'left';
        map[KEY_CODE_DOWN] = 'down';
        map[KEY_CODE_RIGHT] = 'right';
        return map;
    }

    var getActivateKey = function getActivateKey(){
        return [KEY_CODE_SPACE, KEY_CODE_ENTER];
    }

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

        $navigables.each(function(){
            var $navigable = $(this);
            if(!$navigable.length){
                throw new Error('dom element does not exist');
            }
            $navigable.attr('tabindex', -1);//add simply a tabindex to enable focusing, this tabindex is not actually used in tabbing order
            $navigable.addClass('key-navigation-highlight');
        });

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
         * Set cursor to initial position
         */
        var resetCursor = function resetCursor(){
            var position = getClosestPositionRight(0);
            _cursor.position = position;
            _cursor.$dom = $navigables[position];
        }

        var getClosestPositionRight = function getClosestPositionRight(fromPosition){
            var pos;
            for(pos = fromPosition; pos < $navigables.length; pos++){
                if($navigables[pos] && $($navigables[pos]).is(':visible')){
                    return pos;
                }
            }
            return -1;
        }

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
                throw new Error('the navigation group id is already in use : '+id);
            }
        }

        if(config.group){
            $group = $(config.group);
            if($group.length){
                $group
                    .addClass('key-navigation-group')
                    .attr('data-navigation-id', id);
            }
        }

        /**
         * Create a navigation group object
         */
        var navigationGroup = eventifier({
            getId : function(){
                return id;
            },
            getGroup : function(){
                return $group;
            },
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
                    this.focusPosition(getClosestPositionRight(0));
                }
            },
            activate : function activate(){
                var cursor = getCursor();
                if(cursor){
                    this.trigger('activate', cursor);
                }
            },
            goto : function goto(groupId){
                if(_navigationGroups[groupId]){
                    _navigationGroups[groupId].focus();
                }
            },
            focus : function focus(){
                if(config.keepState && _cursor && _cursor.position >= 0){
                    this.focusPosition(getClosestPositionRight(_cursor.position));
                }else{
                    this.focusPosition(getClosestPositionRight(config.default));
                }
            },
            focusPosition : function focusPosition(position){
                if($navigables[position]){
                    _cursor.position = position;
                    $navigables[_cursor.position].focus();
                    _cursor.$dom = $navigables[_cursor.position];
                    this.trigger('focus', _cursor);
                }
            },
            destroy : function destroy(){
                $navigables.off(_ns);
                delete _navigationGroups[id];
            }
        });

        //internal key bindings
        $navigables.on('keydown'+_ns, function(e){
            var keyCode = e.keyCode ? e.keyCode : e.charCode;
            if(arrowKeyMap[keyCode]){
                navigationGroup.trigger(arrowKeyMap[keyCode]);
            }
        }).on('keyup'+_ns, function(e){
            var keyCode = e.keyCode ? e.keyCode : e.charCode;
            if(activationKeys.indexOf(keyCode) >= 0){
                e.preventDefault();
                navigationGroup.activate();
            }
        });

        //store the navigator for external reference
        _navigationGroups[id] = navigationGroup;

        return navigationGroup;
    }

    navigationGroupFactory.get = function get(id){
        if(_navigationGroups[id]){
            return _navigationGroups[id];
        }
    };

    navigationGroupFactory.getAll = function getAll(id){
        //return object references only
        return _.clone(_navigationGroups[id]);
    };

    return navigationGroupFactory;
});
