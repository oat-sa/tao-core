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
    'lodash',
    'i18n',
    'ui/dynamicComponent',
    'lib/calculator/index'
], function (_, __, dynamicComponent, calculatorBuild){
    'use strict';

    var _defaults = {
        title : __('Calculator')
    };
    
    /**
     * Builds an instance of the calculator component
     * @param {Object} config
     * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
     * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
     * @returns {calculator}
     */
    var calculatorFactory = function calculatorFactory(config){

        config = _.defaults(config || {}, _defaults);

        return dynamicComponent()
            .on('rendercontent', function ($content){
                //init the calculator
                this.calc = calculatorBuild.init($content);
            })
            .after('show', function (){
                var self = this;
                _.defer(function (){
                    //need defer to ensure that element show callbacks are all executed
                    self.calc.focus();
                });
            })
            .on('reset', function(){
                //reset the calculator input
                this.calc.press('C');
            })
            .on('destroy', function (){
                if(this.calc){
                    this.calc.remove();
                }
            }).init(config);
    };

    return calculatorFactory;
});
