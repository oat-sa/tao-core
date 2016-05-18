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
    'i18n',
    'interact',
    'ui/component',
    'ui/calculator/build.amd',
    'tpl!ui/calculator/calculator'
], function ($, _, __, interact, component, calculatorBuild, calculatorTpl){
    'use strict';

    /**
     * Defines a calculator component
     * @type {Object}
     */
    var calculator = {
        reset : function reset(){
            //clear calculator
            this.calc.press('C');
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
            var $content = $element.find('.widget-content');
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

        var minWidth = 150;
        var maxWidth = 600;
        var $target = $(e.target),
            $title = $target.find('.widget-title-bar'),
            $content = $target.find('.widget-content'),
            x = (parseFloat($target.attr('data-x')) || 0) + e.deltaRect.left,
            y = (parseFloat($target.attr('data-y')) || 0) + e.deltaRect.top,
            transform = 'translate(' + x + 'px, ' + y + 'px)';
        
        if(e.rect.width <= minWidth || e.rect.width >= maxWidth){
            return;
        }else if(e.rect.width <= 200){
            $target.addClass('small').removeClass('large');
        }else if(e.rect.width >= 380){
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

    /**
     * Builds an instance of the calculator component
     * @param {Object} config
     * @param {Array} [config.calculator] - The list of entries to display
     * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
     * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
     * @returns {calculator}
     */
    var calculatorFactory = function calculatorFactory(config){

        config = _.defaults(config || {}, {
            title : __('Calculator'),
            resizeable : true,
            draggable : true,
            width : 240,
            height : 360,
            draggableContainer : 'parent',
            top : 0,//position top absolute in the window
            left : 0//position left absolute in the window
        });

        return component(calculator)
            .setTemplate(calculatorTpl)
            .on('render', function (){

                var self = this;
                var $element = this.getElement();
                var $content = $element.find('.widget-content');

                //set size + position
                this.resetPosition();
                this.resetSize();

                //init closer
                $element.find('.widget-title-bar .closer').click(function (e){
                    e.preventDefault();
                    self.hide();
                });

                //init the calculator
                this.calc = calculatorBuild.init($content);

                //make the widget draggable + resizable
                interact($element[0])
                    .draggable({
                        inertia : false,
                        autoScroll : true,
                        restrict : {
                            restriction : config.draggableContainer,
                            endOnly : false,
                            elementRect : {top : 0, left : 0, bottom : 1, right : 1}
                        },
                        onmove : _moveItem
                    }).resizable({
                        preserveAspectRatio : true,
                        edges : {left : true, right : true, bottom : true, top : true},
                        onmove: _resizeItem
                    });
            })
            .after('show', function(){
                var self = this;
                _.defer(function(){
                    //need defer to ensure that element show callbacks are all executed
                    self.calc.focus();
                });
            })
            .on('destroy', function(){
                if(this.calc){
                    this.calc.remove();
                }
            })
            .init(config);
    };

    return calculatorFactory;
});
