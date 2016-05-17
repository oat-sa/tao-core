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

        }
    };

    function _moveItem(e){

        var $target = $(e.target),
            x = (parseFloat($target.attr('data-x')) || 0) + e.dx,
            y = (parseFloat($target.attr('data-y')) || 0) + e.dy,
            transform = 'translate(' + x + 'px, ' + y + 'px)';

        $target.css({
            webkitTransform : transform,
            transform : transform,
        });

        $target.attr('data-x', x);
        $target.attr('data-y', y);
    }

    function _resizeItem(e){

        var minWidth = 140;
        var maxWidth = 640;
        var $target = $(e.target),
            $title = $target.find('.widget-title-bar'),
            $content = $target.find('.widget-content'),
            x = (parseFloat($target.attr('data-x')) || 0) + e.deltaRect.left,
            y = (parseFloat($target.attr('data-y')) || 0) + e.deltaRect.top,
            transform = 'translate(' + x + 'px, ' + y + 'px)';

        if(e.rect.width <= minWidth || e.rect.width >= maxWidth){
            return;
        }

        $target.css({
            width : e.rect.width,
            height : e.rect.height,
            webkitTransform : transform,
            transform : transform
        });

        $content.css({
            width : e.rect.width,
            height : e.rect.height - $title.height(),
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
            width : 280,
            height : 360
        });

        return component(calculator)
            .setTemplate(calculatorTpl)
            .on('render', function (){

                var self = this;
                var $element = this.getElement();
                var $content = $element.find('.widget-content');
                $content.width(config.width);
                $content.height(config.height);

                calculatorBuild.init($content);

                interact($element[0])
                    .draggable({
                        inertia : false,
                        autoScroll : true,
                        restrict : {
                            restriction : 'parent',
                            endOnly : true,
                            elementRect : {top : 0, left : 0, bottom : 1, right : 1}
                        },
                        onmove : _moveItem,
                        onend : function (){
                            console.log('end', arguments);
                        }
                    }).resizable({
                        preserveAspectRatio : true,
                        edges : {left : true, right : true, bottom : true, top : true}
                    }).on('resizemove', function (e){
                        _resizeItem(e);
                    });

                console.log($element);
            })
            .init(config);
    };

    return calculatorFactory;
});
