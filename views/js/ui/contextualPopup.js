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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 *
 */
define([
    'jquery',
    'lodash',
    'tpl!tao/ui/contextualPopup/popup'
], function($, _, popupTpl){
    
    'use strict';
    
    var _ns = '.contextual-popup';
    
    var _defaults = {
        controls : false,
        style : {}
    };
    
    
    /**
     * Create an element selector reltive to the $anchor and contained in the $container
     * 
     * @param {JQuery} $anchor
     * @param {JQuery} $container
     * @param {Object} options
     * @param {JQuery|String} [options.content] - the inital content of the popup
     * @param {Boolean} [options.controls] - add cancel/done button
     * @returns {Object} the new selector instance
     */
    function create($anchor, $container, options){
        
        options = _.defaults(options, _defaults);
        $anchor.data('contextual-popup-options', options);
        
        //anchor must be positioned in css
        var positions = _computePosition($anchor, $container);
        var $element = $(popupTpl({
            popup : positions.popup,
            arrow : positions.arrow,
            controls : options.controls
        }));

        //only one 
        $anchor.find('.contextual-popup').remove();

        //attach the popup
        $element.css('width', options.style.popupWidth);
        $anchor.append($element);
        $element.off(_ns).on('click' + _ns, '.done', function(){
            _done($element);
        }).on('click' + _ns, '.cancel', function(){
            _cancel($element);
        });
        
        if(options.content){
            setContent(options.content);
        }
        
        /**
         * Set the popup content
         * @param {JQuery|String} content
         * @returns {undefined}
         */
        function setContent(content){
            if(content instanceof $ || _.isString(content)){
                $element.find('.popup-content').empty().append(content);
            }
        }

        return {
            /**
             * Get the popup JQuery container
             * 
             * @returns {jQuery}
             */
            getPopup : function getPopup(){
                return $element;
            },
            
            setContent : setContent,
            
            /**
             * Recalculates the position of the popup relative to the anchor
             * Useful after any changes in layout
             * 
             * @returns {undefined}
             */
            reposition : function reposition(){
                var pos = _computePosition($anchor, $container);
                $element.css({
                    top : pos.popup.top,
                    left : pos.popup.left
                });
                $element.children('.arrow').css('left', pos.arrow.left);
                $element.children('.arrow-cover').css('left', pos.arrow.leftCover);
            },
            
            /**
             * Manually triggers "done" 
             * 
             * @returns {undefined}
             */
            done : function done(){
                _done($element);
            },
            
            /**
             * Manually triggers "cancel" 
             * 
             * @returns {undefined}
             */
            cancel : function cancel(){
                _cancel($element);
            },
            
            /**
             * Manually triggers "hide" 
             * 
             * @returns {undefined}
             */
            hide : function hide(){
                _hide($element);
            },
            
            /**
             * Manually triggers "show" 
             * 
             * @returns {undefined}
             */
            show : function show(){
                $element.show();
                $element.trigger('show' + _ns);
            },
            
            /**
             * Manually triggers "destroy" 
             * 
             * @returns {undefined}
             */
            destroy : function destroy(){
                $element.remove();
                $element.trigger('destroy' + _ns);
            }
        };
    }

    /**
     * Hide
     * 
     * @fires hide.contextual-popup
     * @param {JQuery} $element
     */
    function _hide($element){
        $element.hide();
        $element.trigger('hide' + _ns);
    }
    
    /**
     * Callback when the "done" button is clicked
     * 
     * @fires done.contextual-popup
     * @param {JQuery} $element
     */
    function _done($element){
        _hide($element);
        $element.trigger('done' + _ns);
    }

    /**
     * Callback when the "cancel" button is clicked
     * 
     * @fires cancel.contextual-popup
     * @param {JQuery} $element
     */
    function _cancel($element){
        _hide($element);
        $element.trigger('cancel' + _ns);
    }
    
    var _styleDefaults = {
        popupWidth : 500,
        arrowWidth : 6,
        marginTop : 15,
        marginLeft : 15
    };
    
    /**
     * Calculate the position of the popup and arrow relative to the anchor and container elements
     * 
     * @param {JQuery} $anchor
     * @param {JQuery} $container
     * @returns {Object} - Object containing the positioning data
     */
    function _computePosition($anchor, $container){
        
        var options = $anchor.data('contextual-popup-options');
        var styleOpts = _.defaults(options.style || {}, _styleDefaults);
        var popupWidth = styleOpts.popupWidth;
        var arrowWidth = styleOpts.arrowWidth;
        var marginTop = styleOpts.marginTop;
        var marginLeft = styleOpts.marginLeft;
        var _anchor = {top : $anchor.offset().top, left : $anchor.offset().left, w : $anchor.innerWidth(), h : $anchor.innerHeight()};
        var _container = {top : $container.offset().top, left : $container.offset().left, w : $container.innerWidth()};
        var _popup = {
            top : _anchor.h + marginTop,
            left : -popupWidth / 2 + _anchor.w / 2,
            w : popupWidth
        };

        var offset = _anchor.left - _container.left;
        //do we have enough space on the left ?
        if(offset + marginLeft + _anchor.w / 2 < _popup.w / 2){
            _popup.left = -offset + marginLeft;
        }else if(_container.w - (offset + _anchor.w / 2 + marginLeft) < _popup.w / 2){
            _popup.left = -offset + _container.w - marginLeft - _popup.w;
        }

        var _arrow = {
            left : -_popup.left + _anchor.w / 2 - arrowWidth,
            leftCover : -_popup.left + _anchor.w / 2 - arrowWidth - 6
        };

        return {
            popup : _popup,
            arrow : _arrow
        };
    }

    return create;
});