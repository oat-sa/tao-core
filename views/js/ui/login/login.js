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
    'tpl!ui/login/tpl/login'
], function($, _, __, component, uuid, loginTpl){
    'use strict';

    var _defaultConfig = {
        disableAutocomplete : false,
        enablePasswordReveal : false,
        message : {
            error : '',
            info: null
        },
        name: 'loginForm'
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
            isAutocompleteDisabled : function isAutocompleteDisabled() {
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
             * @returns {*}
             */
            createFakeForm : function createFakeForm() {
                var $fakeFormDom = this.getElement().clone();

                var $form = $fakeFormDom.find('form').clone();

                var $fakeForm = $form.replaceWith('<div class="form loginForm fakeForm">' + $form.html() + '</div>');

                return $fakeFormDom.html($fakeForm);
            },

            getFakeForm : function getFakeForm() {
                return this.getContainer().find('div.fakeForm');
            },

            manipulateFormDom : function manipulateFormDom() {
                var $form, $pwdInput, $pwdLabel;

                if (this.isAutocompleteDisabled()) {
                    $form = this.getFakeForm();
                } else {
                    $form = this.getElement().find('form');
                }

                $pwdInput = $form.find('input[type=password]');
                $pwdLabel = $form.find('label[for=' + $pwdInput.attr('name') + ']');

                $pwdInput.replaceWith('<span class="viewable-hiddenbox">'
                    + $pwdLabel[0].outerHTML
                    + $pwdInput[0].outerHTML
                    + '<span class="viewable-hiddenbox-toggle" tabindex="0"><span class="icon-preview"></span><span class="icon-eye-slash" style="display: none;"></span></span></span>'
                );
                $pwdLabel.remove();
            },

            attachPasswordRevealEvents : function attachPasswordRevealEvents() {
                var $form, $pwdInput, $inputToggle, $viewIcon, $hideIcon, show, hide, autoHide;

                var self = this;

                if (this.isAutocompleteDisabled()) {
                    $form = this.getFakeForm();
                } else {
                    $form = this.getElement().find('form');
                }

                $pwdInput = $form.find('input[type=password]')[0];
                $inputToggle = $form.find('.viewable-hiddenbox-toggle');
                $viewIcon = $form.find('span.icon-preview');
                $hideIcon = $form.find('span.icon-eye-slash');

                show = function() {
                    $viewIcon.hide();
                    $hideIcon.show();

                    $pwdInput.type = 'text';
                    $pwdInput.autocomplete = 'off';

                    window.addEventListener('mousedown', autoHide);

                    $pwdInput.focus();
                };

                hide = function() {
                    $hideIcon.hide();
                    $viewIcon.show();

                    $pwdInput.type = 'password';
                    $pwdInput.autocomplete = self.isAutocompleteDisabled() ? 'off' : 'on';

                    window.removeEventListener('mousedown', autoHide);
                };

                autoHide = function(event) {
                    if (!event.target.isSameNode($pwdInput) && !event.target.isSameNode($hideIcon[0]) && !event.target.isSameNode($inputToggle[0])) {
                        hide();
                    }
                };

                hide();

                $inputToggle.on('click', function() {
                    if ($pwdInput.type === 'password') {
                        show();
                    } else {
                        hide();
                    }
                });

                $inputToggle.on('keyup', function(e) {
                    if (e.key === ' ') {
                        if ($pwdInput.type === 'password') {
                            show();
                        } else {
                            hide();
                        }
                    }
                });
            }
        };

        var loginComponent = component(api, _defaultConfig)
            .setTemplate(loginTpl)
            .on('init', function(){

                this.render($container);
            })
            .on('render', function(){
                var $fakeForm;

                if (this.isAutocompleteDisabled()) {
                    $fakeForm = this.createFakeForm();

                    this.hide();
                    this.getElement().find('form').attr('id', 'loginForm');
                    this.getContainer().prepend($fakeForm);
                }

                if (this.isPasswordRevealEnabled()) {
                    this.manipulateFormDom();
                    this.attachPasswordRevealEvents();
                }
            });

        _.defer(function(){
            loginComponent.init(config);
        });
        return loginComponent;
    };
});

