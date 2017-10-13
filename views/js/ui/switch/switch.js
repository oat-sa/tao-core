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
 * A switch component, toggles between on and off
 *
 * @example
 * switchFactory(container, config)
 *     .on('change', function(value){
 *              console.log('The light is ' + value);
 *     });
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'lib/uuid',
    'tpl!ui/switch/tpl/switch',
    'css!ui/switch/css/switch.css'
], function($, _, __, component, uuid, switchTpl){
    'use strict';

    var states = {
        on : 'on',
        off : 'off'
    };

    var defaultConfig = {
        on : {
            label : __('On'),
        },
        off : {
            label : __('Off'),
            active : true
        },
        title : ''
    };

    /**
     * The factory that creates a switch component
     *
     * @param {jQueryElement} $container - where to append the component
     * @param {Object} config - the component config
     * @param {Object} [config.on] - the on config
     * @param {String} [config.on.label] - the on button label
     * @param {Boolean} [config.on.active = false] - the default state
     * @param {Object} [config.off] - the off config
     * @param {String} [config.off.label] - the off button label
     * @param {Boolean} [config.off.active = true] - the default state
     * @param {String} [config.title] - the component title tooltip
     * @param {String} [config.name] - the component name (used by the element)
     * @returns {switchComponent} the component
     */
    return function switchFactory($container, config){
        var onElt;
        var offElt;

        /**
         * The component API
         */
        var api = {

            /**
             * Retrieve the component name
             * @returns {String} it's name
             */
            getName : function getName(){
                return this.config.name;
            },

            /**
             * Is the switch on ?
             * @returns {Boolean}
             */
            isOn : function isOn(){
                return this.is(states.on);
            },

            /**
             * Is the switch off ?
             * @returns {Boolean}
             */
            isOff : function isOff(){
                return !this.is(states.on);
            },

            /**
             * Switch On (if not yet on)
             * @returns {switchComponent}  chains
             * @fires switchComponent#change
             * @fires switchComponent#on
             */
            setOn : function setOn(){
                if(!this.isOn()){
                    this.setState(states.on, true);
                }
                if(this.is('rendered')){
                    offElt.removeClass('active');
                    onElt.addClass('active');

                    this.trigger('change', states.on)
                        .trigger(states.on);
                }
                return this;
            },

            /**
             * Switch Off (if not yet off)
             * @returns {switchComponent}  chains
             * @fires switchComponent#change
             * @fires switchComponent#off
             */
            setOff : function setOff(){
                if(!this.isOff()){
                    this.setState(states.on, false);
                }
                if(this.is('rendered')){
                    onElt.removeClass('active');
                    offElt.addClass('active');
                    this.trigger('change', states.off)
                        .trigger(states.off);
                }
                return this;
            },


            /**
             * Toggle on/off
             * @returns {switchComponent}  chains
             */
            toggle : function toggle(){
                return this.isOn() ? this.setOff() : this.setOn();
            },

            /**
             * Get the value
             * @returns {String} on/off
             */
            getValue : function getValue(){
                return this.is(states.on) ? states.on : states.off;
            }
        };

        var switchComponent = component(api, defaultConfig)
            .setTemplate(switchTpl)
            .on('init', function(){

                //generates a name if none
                if(!this.config.name){
                    this.config.name = 'switch-' + uuid();
                }

                //keeps defaults values if overridden
                this.config.on = _.defaults(this.config.on, defaultConfig.on);
                this.config.off = _.defaults(this.config.off, defaultConfig.off);

                //initial state
                if(this.config.on.active === true){
                    this.config.off.active = false;
                    this.setOn();
                } else {
                    this.setOff();
                }

                this.render($container);
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();
                onElt = $('.' + states.on, $component);
                offElt = $('.' + states.off, $component);

                //switch
                $(':checkbox', $component).on('change', function(e){
                    e.preventDefault();
                    self.toggle();
                });
            });

        _.defer(function(){
            switchComponent.init(config);
        });
        return switchComponent;
    };
});
