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

    var KEY_CODE_SPACE = 32;
    var KEY_CODE_ENTER = 13;
    var KEY_CODE_LEFT  = 37;
    var KEY_CODE_UP    = 38;
    var KEY_CODE_RIGHT = 39;
    var KEY_CODE_DOWN  = 40;

    var _navigationGroups = {};

    var _defaults = {
        default : 0,
        keepState : false,
        replace : false,
        loop : false
    };

    var navigationGroupFactory = function navigationGroupFactory(config){

        config = _.defaults(config, _defaults);

        var id = config.id;
        var $navigables = $(config.elements);
        var $group;
        var _cursor = {
            position : -1,
            $dom : null
        };

        var i = 0;
        $navigables.each(function(){
            var $navigable = $(this);
            if(!$navigable.length){
                throw new Error('dom element does not exist');
            }
            $navigable.attr('data-navigation-order', i);
            $navigable.attr('tabindex', -1);
            $navigable.addClass('key-navigation-highlight');
            i++;
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
                $navigables.off('.navigation-group');
                delete _navigationGroups[id];
            }
        });

        //replace this by events
        var map = {};
        map[KEY_CODE_UP] = 'up';
        map[KEY_CODE_LEFT] = 'left';
        map[KEY_CODE_DOWN] = 'down';
        map[KEY_CODE_RIGHT] = 'right';
        var activateKeys = [KEY_CODE_SPACE, KEY_CODE_ENTER];

        $navigables.on('keydown.navigation-group', function(e){
            var keyCode = e.keyCode ? e.keyCode : e.charCode;
            if(map[keyCode]){
                //e.preventDefault();
                //e.stopPropagation();
                navigationGroup.trigger(map[keyCode]);
            }
        }).on('keyup.navigation-group', function(e){
            var keyCode = e.keyCode ? e.keyCode : e.charCode;
            if(activateKeys.indexOf(keyCode) >= 0){
                e.preventDefault();
                //e.stopPropagation();
                navigationGroup.activate();
            }
        });

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
