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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'tpl!ui/loadingButton/tpl/button',
    'css!ui/loadingButton/css/button'
], function ($, _, __, component, buttonTpl) {
    'use strict';

    var _defaults = {
        type : 'info',
        icon : 'property-advanced',
        title : '',
        label : 'OK',
        terminatedLabel : 'STOPPED'
    };

    var buttonApi = {
        start : function start(){
            this.setState('started', true);
            this.trigger('started');
        },
        terminate : function terminate(){
            if(this.is('started')){
                this.setState('started', false);
                this.setState('terminated', true);
                this.trigger('terminated');
            }
            return this;
        },
        reset : function reset(){
            if(this.is('terminated')){
                this.setState('terminated', false);
                this.trigger('reset');
            }
            return this;
        }
    };

    /**
     * Create a button with the lifecycle : render -> started -> terminated [-> reset]
     */
    return function loadingButtonFactory(config) {
        var initConfig = _.defaults(config || {}, _defaults);
        return component(buttonApi)
            .setTemplate(buttonTpl)
            .on('render', function() {
                var self = this;
                this.getElement().on('click', function(){
                    if(!self.is('started') && !self.is('terminated')){
                        self.start();
                    }
                });

            })
            .init(initConfig);
    };

});