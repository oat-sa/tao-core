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
        keepState : false,
        replace : false,
        loop : false
    };

    var navigationGroupFactory = function navigationGroupFactory(config){

        config = _.defaults(config, _defaults);

        var id = config.id;
        var $navigables = $(config.elements);

        var _cursor = {
            position : -1,
            $dom : null
        };

        var i = 0;
        $navigables.each(function(){
            var $navigable = $(this);
            $navigable.attr('data-navigation-order', i);
            $(this).attr('tabindex', -1);
            $(this).addClass('key-navigation-highlight');
            i++;
        });

        var getCursor = function getCursor(){

            var isFocused = false;

            if (document.activeElement) {
                // try to find the focused element within the known list of focusable elements
                _.forEach($navigables, function(focusable, index) {
                    if (document.activeElement === focusable
                        || $.contains(focusable, document.activeElement)
                    ) {
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
            fromPosition = fromPosition||0;
            for(pos = fromPosition; pos < $navigables.length; pos++){
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

        var navigationGroup = eventifier({
            getId : function(){
                return id;
            },
            next : function next(){
                var cursor = getCursor();
                var i;
                var pos;
                if(cursor){
                    for(i = cursor.position + 1; i < $navigables.length; i++){
                        if($navigables[i] && $($navigables[i]).is(':visible')){
                            pos = i;
                            break;
                        }
                    }
                    if(pos >= 0){
                        this.focusPosition(pos);
                    }else if(config.loop){
                        //loop allowed, so returns to the first element
                        this.focusPosition(0);
                    }else{
                        //reaching the end of the list
                        this.trigger('upperbound', cursor);
                    }
                    this.trigger('next', getCursor());
                }else{
                    this.focusPosition(getClosestPositionRight(0));
                }
            },
            previous : function previous(){
                var cursor = getCursor();
                var i;
                var pos;
                if(cursor){
                    for(i = cursor.position -1; i >= 0; i--){
                        if($navigables[i] && $($navigables[i]).is(':visible')){
                            pos = i;
                            break;
                        }
                    }
                    if(pos >= 0){
                        this.focusPosition(pos);
                    }else if(config.loop){
                        //loop allowed, so returns to the first element
                        this.focusPosition($navigables.length - 1);
                    }else{
                        //reaching the end of the list
                        this.trigger('lowerbound', cursor);
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
                    this.focusPosition(_cursor.position);
                }else{
                    this.focusPosition(0);
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
                //e.preventDefault();
                //e.stopPropagation();
                navigationGroup.activate();
            }
        });

        _navigationGroups[id] = navigationGroup;

        return navigationGroup;
    }

    return navigationGroupFactory;
});
