/**
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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'interact',
    'ui/component',
    'tpl!ui/dynamicComponent/layout'
], function ($, _, interact, component, layoutTpl){
    'use strict';

    var _defaults = {
        title : '',
        resizable : true,
        draggable : true,
        width : 240,
        height : 360,
        minWidth : 150,
        maxWidth : 600,
        largeWidthThreshold : 380,
        smallWidthThreshold : 200,
        draggableContainer : 'parent',
        top : 0, //position top absolute in the window
        left : 0//position left absolute in the window
    };

    /**
     * Defines a dynamicComponent
     * @type {Object}
     */
    var dynamicComponent = {
        reset : function reset(){
            this.trigger('reset');
            this.resetPosition();
            this.resetSize();
        },
        resetPosition : function resetPosition(){
            this.getElement().css({
                top : this.config.top,
                left : this.config.left,
                transform : 'none'
            });
        },
        resetSize : function resetSize(){
            var $element = this.getElement();
            var $content = $element.find('.dynamic-component-content');
            $element.css({
                width : 'auto',
                height : 'auto'
            });
            $content.css({
                width : this.config.width,
                height : this.config.height
            });
        }
    };

    /**
     * Builds an instance of the calculator component
     * @param {Object} config
     * @param {Array} [config.calculator] - The list of entries to display
     * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
     * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
     * @returns {calculator}
     */
    var dynComponentFactory = function dynComponentFactory(config){

        config = _.defaults(config || {}, _defaults);

        function _moveItem(e){

            var $target = $(e.target),
                x = (parseFloat($target.attr('data-x')) || 0) + e.dx,
                y = (parseFloat($target.attr('data-y')) || 0) + e.dy,
                transform = 'translate(' + x + 'px, ' + y + 'px)';

            $target.css({
                webkitTransform : transform,
                transform : transform
            });

            $target.attr('data-x', x);
            $target.attr('data-y', y);
        }
        
        function _resizeItem(e){

            var $target = $(e.target),
                $title = $target.find('.dynamic-component-title-bar'),
                $content = $target.find('.dynamic-component-content'),
                x = (parseFloat($target.attr('data-x')) || 0) + e.deltaRect.left,
                y = (parseFloat($target.attr('data-y')) || 0) + e.deltaRect.top,
                transform = 'translate(' + x + 'px, ' + y + 'px)';

            if(e.rect.width <= config.minWidth || e.rect.width >= config.maxWidth){
                return;
            }else if(e.rect.width <= config.smallWidthThreshold){
                $target.addClass('small').removeClass('large');
            }else if(e.rect.width >= config.largeWidthThreshold){
                $target.addClass('large').removeClass('small');
            }else{
                $target.removeClass('small').removeClass('large');
            }

            $target.css({
                width : e.rect.width,
                height : e.rect.height,
                webkitTransform : transform,
                transform : transform
            });

            $content.css({
                width : $title.width(),
                height : $target.innerHeight() - $title.height() - parseInt($target.css('padding-top')) - parseInt($target.css('padding-bottom'))
            });

            $target.attr('data-x', x);
            $target.attr('data-y', y);
        }

        return component(dynamicComponent)
            .setTemplate(layoutTpl)
            .on('render', function (){

                var self = this;
                var $element = this.getElement();
                var $content = $element.find('.dynamic-component-content');
                var interactElement;

                //set size + position
                this.resetPosition();
                this.resetSize();

                //init closer
                $element.find('.dynamic-component-title-bar .closer').click(function (e){
                    e.preventDefault();
                    self.hide();
                });

                //init the calculator
                this.trigger('rendercontent', $content);
                
                //make the dynamic-component draggable + resizable
                interactElement = interact($element[0]);
                if(config.draggable){
                    interactElement.draggable({
                        inertia : false,
                        autoScroll : true,
                        restrict : {
                            restriction : config.draggableContainer,
                            endOnly : false,
                            elementRect : {top : 0, left : 0, bottom : 1, right : 1}
                        },
                        onmove : _moveItem
                    });
                }
                if(config.resizable){
                    interactElement.resizable({
                        preserveAspectRatio : true,
                        edges : {left : true, right : true, bottom : true, top : true},
                        onmove : _resizeItem
                    });
                }
            });
    };

    return dynComponentFactory;
});
