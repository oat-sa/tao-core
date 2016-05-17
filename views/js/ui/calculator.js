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
    'ui/component',
    'ui/calculator/build.amd',
    'tpl!ui/calculator/calculator'
], function ($, _, __, component, calculatorBuild, calculatorTpl){
    'use strict';

    /**
     * Defines a calculator component
     * @type {Object}
     */
    var calculator = {
        reset : function reset(){

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

                console.log($element);
            })
            .init(config);
    };

    return calculatorFactory;
});
