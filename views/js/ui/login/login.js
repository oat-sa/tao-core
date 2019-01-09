/*
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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Ilya Yarkavets <ilya.yarkavets@1pt.com>
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'lib/uuid',
    'tpl!ui/login/tpl/login',
    'css!ui/switch/css/switch.css'
], function($, _, __, component, uuid, loginTpl){
    'use strict';

    var _defaultConfig = {
        disableAutocomplete : false,
        enablePasswordReveal : false,
        message : {
            error : '',
            info: null
        }
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
     * @returns {loginComponent} the component
     */
    return function loginFactory($container, config){


        /**
         * The component API
         */
        var api = {

            /**
             *
             * @returns {boolean}
             */
            isAutcompleteDisabled : function isAutocompleteDisabled() {
                return this.config.disableAutocomplete;
            },

            /**
             *
             * @returns {boolean}
             */
            isPasswordRevealEnabled : function isPasswordRevealEnabled() {
                return this.config.enablePasswordReveal;
            },

            /**
             *
             * @param $loginForm
             * @returns {*}
             */
            createFakeForm : function createFakeForm($loginForm) {
                var fakeFormDom = $loginForm.clone();

                return fakeFormDom.replaceWith('<div class="form loginForm fakeForm">' + fakeFormDom.innerHTML + '</div>');
            }
        };

        var loginComponent = component(api, _defaultConfig)
            .setTemplate(loginTpl)
            .on('init', function(){
                this.render($container);
            })
            .on('render', function(){
            });

        _.defer(function(){
            loginComponent.init(config);
        });
        return loginComponent;
    };
});

