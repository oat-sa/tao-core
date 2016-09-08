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
            this.resetPosition();
            this.resetSize();
            this.trigger('reset');
            return this;
        },
        resetPosition : function resetPosition(){
            this.getElement().css({
                top : this.config.top,
                left : this.config.left,
                transform : 'none'
            });
            return this;
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
            return this;
        }
    };

    /**
     * Builds an instance of the dynamic component
     * @param {Object} config
     * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
     * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
     * @param {String} [config.title] - title to be displayed in the title bar
     * @param {Boolean} [config.resizable] - allow the component to be resizable
     * @param {Boolean} [config.draggable] - allow the component to be draggable
     * @param {Number} [config.width] - the initial width of the component content
     * @param {Number} [config.height] - the intial height of the component content
     * @param {Number} [config.minWidth] - the min width for resize
     * @param {Number} [config.maxWidth] - the max width for resize
     * @param {Number} [config.largeWidthThreshold] - the width below which the container will get the class "small"
     * @param {Number} [config.smallWidthThreshold] - the width above which the container will get the class "large"
     * @param {jQuery|HTMLElement|String} [config.draggableContainer] - the DOMElement the draggable component will be constraint in
     * @param {Number} [config.top] - the initial position top absolute to the windows
     * @param {Number} [config.left] - the initial position left absolute to the windows
     * @returns {component}
     */
    var dynComponentFactory = function dynComponentFactory(specs, defaults){

        defaults = _.defaults(defaults || {}, _defaults);
        specs = _.defaults(specs || {}, dynamicComponent);

        return component(specs, defaults)
            .setTemplate(layoutTpl)
            .on('render', function (){

                var self = this;
                var $element = this.getElement();
                var $content = $element.find('.dynamic-component-content');
                var interactElement;
                var config = this.config;
                var draggableContainer;
                var $draggingLayer;

                //set size + position
                this.resetPosition();
                this.resetSize();

                //init closer
                $('.dynamic-component-title-bar .closer', $element).on('click', function() {
                    self.hide();
                });

                //init the component content
                this.trigger('rendercontent', $content);

                //make the dynamic-component draggable + resizable
                interactElement = interact($element[0]);
                if(config.draggable){
                    draggableContainer = config.draggableContainer;
                    if(draggableContainer instanceof $ && draggableContainer.length){
                        draggableContainer = draggableContainer[0];
                    }
                    if(_.isElement(draggableContainer) || _.isString(draggableContainer)){
                        //the dragging layer enable issue while dragging content with iframes
                        $draggingLayer = $content.find('.dynamic-component-layer');

                        interactElement.draggable({
                            inertia : false,
                            autoScroll : true,
                            manualStart: true,
                            restrict : {
                                restriction : draggableContainer,
                                endOnly : false,
                                elementRect : {top : 0, left : 0, bottom : 1, right : 1}
                            },
                            onmove : _moveItem,
                            onstart: function () {
                                $draggingLayer.addClass('dragging-active');
                            },
                            onend: function () {
                                $draggingLayer.removeClass('dragging-active');
                            }
                        });

                        //manually start interactjs draggable on the handle
                        interact($element.find('.dynamic-component-title-bar')[0]).on('down', function (event){

                            var interaction = event.interaction,
                                handle = event.currentTarget;

                            interaction.start({
                                name : 'drag',
                                edges : {
                                    top : handle.dataset.top,
                                    left : handle.dataset.left,
                                    bottom : handle.dataset.bottom,
                                    right : handle.dataset.right
                                }
                            },
                            interactElement,
                            $element[0]);
                        });
                    }else{
                        self.trigger('error', new Error('invalid draggableContainer type'));
                    }
                }
                if(config.resizable){
                    interactElement.resizable({
                        preserveAspectRatio : true,
                        edges : {left : true, right : true, bottom : true, top : true},
                        onmove : _resizeItem
                    });
                }

                /**
                 * Callback for on move event
                 * @param {Object} e - the interact event object
                 */
                function _moveItem(e){

                    var $target = $(e.target),
                        x = (parseFloat($target.attr('data-x')) || 0) + e.dx,
                        y = (parseFloat($target.attr('data-y')) || 0) + e.dy,
                        transform = 'translate(' + x + 'px, ' + y + 'px) translateZ(0)';

                    $target.css({
                        webkitTransform : transform,
                        transform : transform
                    });

                    $target.attr('data-x', x);
                    $target.attr('data-y', y);
                }

                /**
                 * Callback for on resize event
                 * @param {Object} e - the interact event object
                 */
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
            });
    };

    return dynComponentFactory;
});
