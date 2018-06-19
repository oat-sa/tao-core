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

/**
 * A button component used to trigger lengthy action.
 * It has its own lifecycle: render -> started -> terminated [-> reset]
 *
 * @example
 * loadingButtonFactory({
 *          type : 'info',
 *          icon : 'property-advanced',
 *          title : 'Execute my script',
 *          label : 'Run',
 *          terminatedLabel : 'Terminated'
 *     });
 *
 * @author Sam <sam@taotesting.com>
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
        terminatedLabel : 'FINISHED'
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
                this.disable();
                this.trigger('terminated');
            }
            return this;
        },
        reset : function reset(){
            if(this.is('terminated')){
                this.setState('terminated', false);
                this.enable();
                this.trigger('reset');
            }
            return this;
        }
    };

    /**
     * Create a button with the lifecycle : render -> started -> terminated [-> reset]
     * @param {Object} config - the component config
     * @param {String} config.type - the icon type (info, success, error)
     * @param {String} config.icon - the button icon
     * @param {String} config.label - the button's label
     * @param {String} [config.title] - the button's title
     * @param {String} [config.terminatedLabel] - the button's label when terminated
     * @return {loadingButton} the component
     *
     * @event started - Emitted when the button is clicked and the triggered action supposed to be started
     * @event terminated - Emitted when the button action is stopped, interrupted
     * @event reset - Emitted when the button revert from the terminated stated to the initial one
     */
    return function loadingButtonFactory(config) {
        var initConfig = _.defaults(config || {}, _defaults);

        /**
         * @typedef {loadingButton} the component
         */
        return component(buttonApi)
            .setTemplate(buttonTpl)
            .on('enable', function(){
                this.getElement().removeProp('disabled');
            })
            .on('disable', function(){
                this.getElement().prop('disabled', true);
            })
            .on('render', function() {
                var self = this;
                this.getElement().on('click', function(e){
                    e.preventDefault();
                    if(!self.is('disabled') && !self.is('started') && !self.is('terminated')){
                        self.start();
                    }
                });
            })
            .init(initConfig);
    };

});