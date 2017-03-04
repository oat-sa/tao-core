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
    'ui/dynamicComponent',
    'lib/calculator/index'
], function ($, _, __, dynamicComponent, calculatorBuild){
    'use strict';

    var _defaults = {
        title : __('Calculator'),
        preserveAspectRatio : false,
        width : 240,
        height : 360,
        minWidth : 150,
        minHeight : 220,
        alternativeTemplate : null
    };

    /**
     * Calculate the new font size according to the width and height ratio during component resizing.
     * It has been calculated to match a reference font-size of 10px when the calculator content is 240px wide and 330 height.
     * @param {Number} width
     * @param {Number} height
     * @returns {Number}
     */
    var computeFontSize = function computeFontSize(width, height){
        var _fontSizeHeightRatio = 10/340;
        var _fontSizeWidthRatio = 10/240;
        return (width * _fontSizeWidthRatio + height * _fontSizeHeightRatio)/2;
    };

    var calculator = {
        press : function press(key){
            this.calc.press(key);
            return this;
        }
    };

    /**
     * Computes the ratio between width and height of the applied font family.
     * @param {jQuery} $element
     * @returns {Number}
     */
    function getFontRatio($element) {
        var $sample = $('<div />').text('0').css({
            'font-family': $element.css('font-family'),
            'font-size': '10px',
            'line-height': '10px',
            'position': 'absolute',
            'padding': '0',
            'top': -1000,
            'left': -1000
        }).appendTo('body');

        var fontRatio = $sample.height() / $sample.width();

        $sample.remove();

        return fontRatio;
    }

    /**
     * Adjust the font size of the parent element will automatically scale the font-size of the children proportionally
     * @param {jQuery} $text
     * @param {Number} fontRatio
     * @param {Number} fontSize
     */
    function adjustFontSize($text, fontRatio, fontSize) {
        var width = $text.width();
        var height = $text.height();
        var charWidth = fontSize / fontRatio;
        var len;

        if ($text.is(':input')) {
            len = $text.val().length;

            if (len * charWidth >= width) {
                fontSize = Math.max(height / 4, Math.min(width / len * (fontRatio || 1.6), fontSize));
            }
        } else {
            fontSize = computeFontSize(width, height);
        }

        $text.css('fontSize', fontSize);
    }

    /**
     * Builds an instance of the calculator component
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
     * @param {Function} [config.alternativeTemplate] - allow defining an alternative template (a handlebar cimpiled template function)
     * @param {String} [config.stackingScope] - The scope in which to stack the component
     * @returns {calculator}
     */
    function calculatorFactory(config){

        config = _.defaults(config || {}, _defaults);

        return dynamicComponent(calculator)
            .on('rendercontent', function ($content){
                var $input, self = this, calcConfig = {};

                if(_.isFunction(config.alternativeTemplate)){
                    calcConfig.template = config.alternativeTemplate;
                }

                //init the calculator
                this.calc = calculatorBuild.init($content, calcConfig);

                $input = $content.find('input.calcDisplay').on('change', function(){
                    adjustFontSize($input, self.fontRatio, self.fontSize);
                });

                this.fontSize = parseFloat($input.css('font-size'));
                this.fontRatio = getFontRatio($input);
            })
            .after('show', function (){
                var self = this;
                _.defer(function (){
                    //need defer to ensure that element show callbacks are all executed
                    var $display = self.getElement().find('.calcDisplay');
                    var strLength = $display.val().length + 1;
                    $display.focus();
                    $display[0].setSelectionRange(strLength, strLength);
                });
            })
            .on('reset', function(){
                //reset the calculator input
                this.calc.press('C');
            })
            .on('resize', function(){
                var $element = this.getElement();
                if($element){
                    adjustFontSize($element.find('form'), this.fontRatio, this.fontSize);
                    adjustFontSize($element.find('input.calcDisplay'), this.fontRatio, this.fontSize);
                }
            })
            .on('destroy', function (){
                if(this.calc){
                    this.calc.remove();
                }
            }).init(config);
    }

    return calculatorFactory;
});
