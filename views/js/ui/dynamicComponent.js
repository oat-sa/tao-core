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
    'ui/component/stackable',
    'ui/transformer',
    'ui/interactUtils',
    'util/position',
    'lib/uuid',
    'tpl!ui/dynamicComponent/layout'
], function ($, _, interact, componentFactory, makeStackable, transformer, interactUtils, position, uuid, layoutTpl){
    'use strict';

    var _defaults = {
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
        top : 0,
        left : 0,
        proportionalResize: false,
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
            var $element = this.getElement();

            if(this.is('rendered') && !this.is('disabled')){
                interactUtils.restoreOriginalPosition($element);

                this.setCoords();

                $element.css({
                    left: this.config.left,
                    top: this.config.top
                });

                /**
                 * @event dynamicComponent#move
                 * @param {Object} position - the new positions
                 */
                this.trigger('move', this.position);
            }
            return this;
        },

        /**
         * compute x/y coords of the component according to the start position and the dragged offset
         */
        setCoords : function setCoords() {
            var $element = this.getElement();

            // fixme: attributes data-x and data-y are added by interactUtils.
            // If the position is really needed, it should be computed differently
            this.position.x = parseFloat($element.attr('data-x')) + this.config.left;
            this.position.y = parseFloat($element.attr('data-y')) + this.config.top;
        },

        /**
         * Sets the size of the content, and adapts the component's size accordingly.
         * @param {Number} width - The width of the content, the full width of the component will be adjusted.
         * @param {Number} height - The height of the content, the full height of the component will be adjusted.
         * @returns {dynamicComponent} chains
         * @fires dynamicComponent#resize
         */
        setContentSize: function setContentSize(width, height) {
            var $element, $titleBar;
            if (this.is('rendered') && !this.is('disabled')) {
                $element = this.getElement();
                $titleBar = $('.dynamic-component-title-bar', $element);

                this.config.width = width + $element.outerWidth() - $element.width();
                this.config.height = height + $element.outerHeight() - $element.height() + $titleBar.outerHeight();
                this.resetSize();
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
     * @param {Object} specs - extra functions to extend the component
     * @param {Object} defaults
     * @param {jQuery|HTMLElement|String} [defaults.renderTo] - An optional container in which renders the component
     * @param {Boolean} [defaults.replace] - When the component is appended to its container, clears the place before
     * @param {String} [defaults.title] - title to be displayed in the title bar
     * @param {Boolean} [defaults.resizable] - allow the component to be resizable
     * @param {Boolean} [defaults.draggable] - allow the component to be draggable
     * @param {Number} [defaults.width] - the initial width of the component content
     * @param {Number} [defaults.height] - the intial height of the component content
     * @param {Number} [defaults.minWidth] - the min width for resize
     * @param {Number} [defaults.minHeight] - the min height for resize
     * @param {Number} [defaults.maxWidth] - the max width for resize
     * @param {Number} [defaults.maxHeight] - the max height for resize
     * @param {Number} [defaults.largeWidthThreshold] - the width below which the container will get the class "small"
     * @param {Number} [defaults.smallWidthThreshold] - the width above which the container will get the class "large"
     * @param {Boolean} [defaults.preserveAspectRatio] - preserve ratio on resize
     * @param {jQuery|HTMLElement|String} [defaults.draggableContainer] - the DOMElement the draggable/resizable component will be constraint in
     * @param {Number} [defaults.top] - the initial position top absolute to the relative positioned container
     * @param {Number} [defaults.left] - the initial position left absolute to the relative positioned container
     * @param {Number} [defaults.stackingScope] - in which scope to stack the component
     * @param {Boolean} [defaults.proportionalResize] - resize proportionally in both dimensions
     * @returns {component}
     */
    var dynComponentFactory = function dynComponentFactory(specs, defaults){

        var component;

        defaults = _.defaults(defaults || {}, _defaults);
        specs = _.defaults(specs || {}, dynamicComponent);

        component = componentFactory(specs, defaults)
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
                var $resizeControll = $('.dynamic-component-resize-wrapper', $element);
                var pixelRatio      = window.devicePixelRatio;
                var interactElement;

                //keeps moving/resizing positions data
                self.position = {
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
                    .on('click touchstart', '.closer', function(e) {
                        e.preventDefault();
                        self.hide();
                    })
                    .on('click touchstart', '.reset', function(e) {
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
                        onmove : function(event) {
                            interactUtils.moveElement($element, event.dx, event.dy);
                            self.setCoords();
                            self.trigger('move', self.position);
                        },
                        onend : function() {
                            self.setCoords();
                        }
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
                        edges : {left : false, right : '.dynamic-component-resize-wrapper', bottom : '.dynamic-component-resize-wrapper', top : false},
                        onmove : _resizeItem
                    });
                }

                interactElement
                    .on('dragstart resizeinertiastart', function() {
                        $contentOverlay.addClass('dragging-active');
                        $content.addClass('moving');
                        $titleBar.addClass('moving');
                    })
                    .on('dragend', function(){
                        $contentOverlay.removeClass('dragging-active');
                        $content.removeClass('moving');
                        $titleBar.removeClass('moving');
                    })
                    .on('resizestart', function() {
                        $contentOverlay.addClass('dragging-active');
                        $resizeControll.addClass('resizing');
                        $content.addClass('sizing');
                    }).on('resizeend', function() {
                        $contentOverlay.removeClass('dragging-active');
                        $resizeControll.removeClass('resizing');
                        $content.removeClass('sizing');
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
                            endOnly : false
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
                 * Callback for on resize event
                 * @param {Object} e - the interact event object
                 */
                function _resizeItem(e){
                    var width = e.rect.width;
                    var height = e.rect.height;
                    var $parent = config.draggableContainer || $element.parent();
                    var elementOffset = $element.offset();
                    var parentOffset = $parent.offset();

                    // if proportional resize enabled calculate scale rate
                    // and apply it to width and height

                    var dimensions  = calculateSize(width, height);
                    width = calculateOverlap(dimensions.width, elementOffset.left, parentOffset.left, $parent.width());
                    height = calculateOverlap(dimensions.height, elementOffset.top, parentOffset.top, $parent.height());

                    if(height !== null && width !== null){

                        if (width <= config.smallWidthThreshold) {
                            $element.addClass('small').removeClass('large');
                        } else if(width >= config.largeWidthThreshold) {
                            $element.addClass('large').removeClass('small');
                        } else {
                            $element.removeClass('small').removeClass('large');
                        }

                        interactUtils.moveElement(
                            $element,
                            (width > config.minWidth && width < config.maxWidth) ? e.deltaRect.left : 0,
                            (height > config.minHeight && height < config.maxHeight) ? e.deltaRect.top : 0
                        );

                        self.position.width   = width;
                        self.position.height  = height;
                        self.setCoords();

                        $element.css({
                            width  : width + 'px',
                            height : height + 'px'
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
                }

                /**
                 * check if given side of dynamic component is overlapping the container and calculate size of that side
                 * @param {Number} side - side value of the component to check and calculate, cold be height or width
                 * @param {Number} elOffset - offset value towards child to parent container
                 * @param {Number} parentOffset - offset value towards parent container to its ancestor
                 * @returns {Number|null} - new width or height values for the side of the component or null if there is no overlap between it and container
                 */
                function calculateOverlap(side, elOffset, parentOffset, parentValue) {
                    var result = side;
                    var fullSizeSide = elOffset + side;
                    var fullSizeParent = parentOffset + parentValue;
                    if (fullSizeSide > fullSizeParent) {
                        if (config.proportionalResize) {
                            result = null;
                        } else{
                            result -= fullSizeSide - fullSizeParent;
                        }
                    }
                    return result;
                }

                /**
                 * calculates size of the dynamic component compared to  configured max/min values and scale rate coefficient applied
                 * @param {Number} width - width of the component at the moment of resizing
                 * @param {Number} height -  height of the component at the moment of resizing
                 * @returns {width,height} - object with adjusted weight and height
                 */
                function calculateSize(width, height) {
                    var scaleRate;
                    if (config.proportionalResize) {
                        scaleRate = Math.max(width / config.minWidth, height / config.minHeight);
                        width = config.minWidth * scaleRate;
                        height = config.minHeight * scaleRate;
                    }

                    if (width < config.minWidth) {
                        width = config.minWidth;
                    } else if (width > config.maxWidth) {
                        width = config.maxWidth;
                    }


                    if (height < config.minHeight) {
                        height = config.minHeight;
                    } else if (height > config.maxHeight) {
                        height = config.maxHeight;
                    }

                    return {
                        width: width,
                        height: height
                    };
                }


            })
            .on('destroy', function(){
                $(window).off('resize.dynamic-component-' + this.id);
            });

        return makeStackable(component, { stackingScope: defaults.stackingScope });
    };

    return dynComponentFactory;
});
