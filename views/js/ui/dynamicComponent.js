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
    'ui/transformer',
    'util/position',
    'lib/uuid',
    'tpl!ui/dynamicComponent/layout'
], function ($, _, interact, component, transformer, position, uuid, layoutTpl){
    'use strict';

    var _defaults = {
        title : '',
        resizable : true,
        draggable : true,
        width : 240,
        height : 360,
        minWidth : 150,
        maxWidth : 600,
        minHeight: 100,
        maxHeight: 900,
        largeWidthThreshold : 380,
        smallWidthThreshold : 200,
        draggableContainer : 'parent',
        preserveAspectRatio : true,
        top : 0, //position top absolute in the window
        left : 0//position left absolute in the window
    };

    /**
     * Defines a dynamicComponent
     * @typedef {Object} dynamicComponent
     */
    var dynamicComponent = {

        /**
         * Reset the position and the size
         * @returns {dynamicComponent} chains
         * @fires dynamicComponent#reset
         */
        reset : function reset(){
            if(this.is('rendered') && !this.is('disabled')){
                this.resetPosition();
                this.resetSize();

                /**
                 * @event dynamicComponent#reset
                 */
                this.trigger('reset');
            }
            return this;
        },

        /**
         * Reset the component position to it's original value
         * @returns {dynamicComponent} chains
         * @fires dynamicComponent#move
         */
        resetPosition : function resetPosition(){
            if(this.is('rendered') && !this.is('disabled')){
                this.getElement().css({
                    top : this.config.top,
                    left : this.config.left,
                    transform : 'none'
                });

                this.position.x = this.config.left;
                this.position.y = this.config.top;

                /**
                 * @event dynamicComponent#move
                 * @param {Object} position - the new positions
                 */
                this.trigger('move', this.position);
            }
            return this;
        },

        /**
         * Reset the component size to it's original value
         * @returns {dynamicComponent} chains
         * @fires dynamicComponent#resize
         */
        resetSize : function resetSize(){
            var self = this;
            var $element;
            var $content;
            var $titleBar;
            if(this.is('rendered') && !this.is('disabled')){
                $element  = this.getElement();
                $content  = $('.dynamic-component-content', $element);
                $titleBar = $('.dynamic-component-title-bar', $element);

                $element.css({
                    width:  this.config.width + 'px',
                    height: this.config.height + 'px'
                });

                //defer to ensure the next reflow occurs before calculating the content size
                _.defer(function(){
                    self.position.width         = self.config.width;
                    self.position.height        = self.config.height;
                    self.position.contentWidth  = $titleBar.width();
                    self.position.contentHeight = $element.height() - $titleBar.outerHeight();

                    $content.css({
                        width : self.position.contentWidth + 'px',
                        height : self.position.contentHeight + 'px'
                    });

                    /**
                     * @event dynamicComponent#resize
                     * @param {Object} position - the new positions
                     */
                    self.trigger('resize', self.position);
                });
            }
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
     * @param {Number} [config.minHeight] - the min height for resize
     * @param {Number} [config.maxWidth] - the max width for resize
     * @param {Number} [config.maxHeight] - the max height for resize
     * @param {Number} [config.largeWidthThreshold] - the width below which the container will get the class "small"
     * @param {Number} [config.smallWidthThreshold] - the width above which the container will get the class "large"
     * @param {Boolean} [config.preserveAspectRatio] - preserve ratio on resize
     * @param {jQuery|HTMLElement|String} [config.draggableContainer] - the DOMElement the draggable/resizable component will be constraint in
     * @param {Number} [config.top] - the initial position top absolute to the windows
     * @param {Number} [config.left] - the initial position left absolute to the windows
     * @returns {component}
     */
    var dynComponentFactory = function dynComponentFactory(specs, defaults){

        defaults = _.defaults(defaults || {}, _defaults);
        specs = _.defaults(specs || {}, dynamicComponent);

        return component(specs, defaults)
            .setTemplate(layoutTpl)
            .on('init', function(){
                this.id = uuid();
            })
            .on('render', function (){

                var self            = this;
                var $element        = this.getElement();
                var config          = this.config;
                var $content        = $('.dynamic-component-content', $element);
                var $titleBar       = $('.dynamic-component-title-bar', $element);
                var $contentOverlay = $('.dynamic-component-layer', $element);
                var pixelRatio      = window.devicePixelRatio;
                var interactElement;

                //keeps moving/resizing positions data
                this.position = {
                    x:      this.config.left,
                    y:      this.config.top,
                    width:  this.config.width,
                    height: this.config.height
                };

                //set size + position
                this.resetPosition();
                this.resetSize();

                //init controls
                $titleBar
                    .on('click', '.closer', function(e) {
                        e.preventDefault();
                        self.hide();
                    })
                    .on('click', '.reset', function(e) {
                        e.preventDefault();
                        self.resetSize();
                    });

                /**
                 * Init the component content
                 * @event dynamicComponent#rendercontent
                 * @param {jQueryElement} $content - the rendered content
                 */
                this.trigger('rendercontent', $content);

                //make the dynamic-component draggable + resizable
                interactElement = interact($element[0]);
                if(config.draggable){

                    interactElement.draggable({
                        inertia : false,
                        autoScroll : true,
                        manualStart: true,
                        restrict : _.merge(getRestriction(), {
                            elementRect: { left: 0, right: 1, top: 0, bottom: 1 }
                        }),
                        onmove : _moveItem
                    });

                    //manually start interactjs draggable on the handle
                    interact($titleBar[0]).on('down', function (event){

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

                    $(window).on('resize.dynamic-component-' + self.id, function(){
                        var container;

                        //on browser zoom, reset the position to prevent having
                        //the component pushed outside it's container
                        if(window.devicePixelRatio !== pixelRatio ) {
                            pixelRatio = window.devicePixelRatio;

                            container = getDraggableContainer();
                            if( position.isInside(container, $element[0]) === false ){
                                self.resetPosition();
                            }
                        }
                    });
                }
                if(config.resizable){

                    interactElement.resizable({
                        preserveAspectRatio : config.preserveAspectRatio,
                        autoScroll : true,
                        restrict : getRestriction(),
                        edges : {left : true, right : true, bottom : true, top : true},
                        onmove : _resizeItem
                    });
                }

                interactElement
                    .on('dragstart resizeinertiastart resizestart', function() {
                        $contentOverlay.addClass('dragging-active');
                    })
                    .on('dragend resizeend', function(){
                        $contentOverlay.removeClass('dragging-active');
                    });

                //interact sometimes doesn't trigger the start event if the move is quick and ends over an iframe...
                $element.on('mousedown', function(){
                    if(/\-resize/.test($('html').css('cursor')) && ! $contentOverlay.hasClass('dragging-active')){
                        $contentOverlay.addClass('dragging-active');
                    }
                });

                function getRestriction(){
                    var draggableContainer = getDraggableContainer();
                    if(!draggableContainer) {
                        return {
                            restriction : 'parent',
                            endOnly : false,
                        };
                    }
                    return {
                        restriction : draggableContainer,
                        endOnly : false
                    };
                }

                function getDraggableContainer(){
                    var draggableContainer = config.draggableContainer;
                    if(draggableContainer instanceof $ && draggableContainer.length){
                        draggableContainer = draggableContainer[0];
                    }
                    return draggableContainer;
                }

                /**
                 * Callback for on move event
                 * @param {Object} e - the interact event object
                 */
                function _moveItem(event){

                    self.position.x = (parseFloat(self.position.x) || 0) + event.dx;
                    self.position.y = (parseFloat(self.position.y) || 0) + event.dy;

                    transformer.translate($element, self.position.x, self.position.y);

                    self.trigger('move', self.position);
                }

                /**
                 * Callback for on resize event
                 * @param {Object} e - the interact event object
                 */
                function _resizeItem(e){

                    var width  = e.rect.width < config.minWidth ? config.minWidth : e.rect.width > config.maxWidth ? config.maxWidth : e.rect.width;
                    var height = e.rect.height < config.minHeight ? config.minHeight : e.rect.height > config.maxHeight ? config.maxHeight : e.rect.height;

                    if(width <= config.smallWidthThreshold) {
                        $element.addClass('small').removeClass('large');
                    } else if(width >= config.largeWidthThreshold) {
                        $element.addClass('large').removeClass('small');
                    } else {
                        $element.removeClass('small').removeClass('large');
                    }

                    self.position.x              = (parseFloat(self.position.x) || 0) + e.deltaRect.left;
                    self.position.y              = (parseFloat(self.position.y) || 0) + e.deltaRect.top;
                    self.position.width          = width;
                    self.position.height         = height;


                    transformer.translate($element, self.position.x, self.position.y);

                    $element.css({
                        width  : width + 'px',
                        height : height + 'px',
                    });

                    _.defer(function(){

                        self.position.contentWidth   = $titleBar.width();
                        self.position.contentHeight  = $element.height() - $titleBar.outerHeight();
                        $content.css({
                            width  :  self.position.contentWidth + 'px',
                            height : self.position.contentHeight + 'px'
                        });

                        self.trigger('resize', self.position);
                    });
                }
            })
            .on('destroy', function(){
                $(window).off('resize.dynamic-component-' + this.id);
            });
    };

    return dynComponentFactory;
});
