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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/promise',
    'ui/component',
    'ui/button',
    'ui/form/simpleForm',
    'tpl!ui/form/tpl/dropdownForm',
    'css!ui/form/css/dropdownForm.css'
], function ($, _, __, Promise, componentFactory, buttonFactory, formFactory, dropdownFormTpl) {
    'use strict';

    /**
     * @typedef {Object} dropdownFormConfig Defines the config entries available to setup a dropdown form
     * @property {String} [triggerText] - The caption of the trigger button
     * @property {String} [triggerIcon] - The icon of the trigger button
     * @property {String} [submitText] - The caption of the submit button
     * @property {String} [submitIcon] - The icon of the submit button
     * @property {widgetConfig[]} [widgets] - The list of widgets to set in the form (default none)
     * @property {buttonConfig[]} [buttons] - The list of buttons to set in the form (default none)
     * @property {Object} [values] - Initial values for the widgets
     * @property {Object} [ranges] - An optional list of ranges for the widgets (generis related, default none)
     */

    /**
     * Some default config
     * @type {Object}
     */
    var defaults = {
        triggerIcon: null,
        triggerText: __('Form'),
        submitIcon: null,
        submitText: __('Submit'),
    };

    /**
     * Builds a dropdown form component.
     *
     * @example
     *  var container = $('.my-container', $container);
     *
     *  var config = {
     *      title: 'My fancy form',
     *      triggerText: 'Comment',
     *      submitText: 'Publish',
     *      widgets: [{
     *          widget: widgetDefinitions.TEXTBOX
     *          uri: 'nickname',
     *          label: 'Name',
     *          required: true
     *      }, {
     *          widget: widgetDefinitions.TEXTAREA
     *          uri: 'comment',
     *          label: 'Comment',
     *          required: true
     *      }]
     *  };
     *
     *  var form = dropdownFormFactory(container, config)
     *      .on('submit', function(values) {
     *          // ...
     *      });
     *
     * @param {HTMLElement|String} container
     * @param {dropdownFormConfig} config
     * @param {String} [config.triggerText] - The caption of the trigger button
     * @param {String} [config.triggerIcon] - The icon of the trigger button
     * @param {String} [config.submitText] - The caption of the submit button
     * @param {String} [config.submitIcon] - The icon of the submit button
     * @param {widgetConfig[]} [config.widgets] - The list of widgets to set in the form (default none)
     * @param {buttonConfig[]} [config.buttons] - The list of buttons to set in the form (default none)
     * @param {Object} [config.values] - Initial values for the widgets
     * @param {Object} [config.ranges] - An optional list of ranges for the widgets (generis related, default none)
     * @returns {dropdownForm}
     * @fires ready - When the component is ready to work
     */
    function dropdownFormFactory(container, config) {
        var form = null;
        var button = null;
        var controls = null;

        var api = {
            /**
             * Gets access to the form
             * @returns {form}
             */
            getForm: function getForm() {
                return form;
            },

            /**
             * Update the form with a new list of widgets
             * @param {widgetConfig[]} widgets
             * @returns {Promise}
             * @throws {Error} if the form is not yet rendered
             */
            setFormWidgets: function setFormWidgets(widgets) {
                if (!this.is('rendered') || !form) {
                    return Promise.reject(new Error('The form is not rendered'));
                }

                return form.setWidgets(widgets);
            },

            /**
             * Gets the values from the form widgets
             * @returns {Object}
             */
            getFormValues: function getFormValues() {
                if (this.is('rendered')) {
                    return form.getValues();
                }

                return {};
            },

            /**
             * Sets the values to the form widgets
             * @param {Object} values
             * @returns {dropdownForm}
             */
            setFormValues: function setFormValues(values) {
                if (this.is('rendered')) {
                    form.setValues(values);
                }
                return this;
            },

            /**
             * Opens the form attached to the dropdown
             * @returns {dropdownForm}
             * @fires open - When the form is open
             */
            openForm: function openForm() {
                this.setState('open', true);

                // the event is emitted only if the component is rendered.
                if (this.is('rendered')) {
                    if (this.getContainer().width() < this.getElement().position().left + controls.$form.width()) {
                        this.setState('open-on-left', false);
                        this.setState('open-on-right', true);
                    } else {
                        this.setState('open-on-left', true);
                        this.setState('open-on-right', false);
                    }

                    /**
                     * @event open
                     */
                    this.trigger('open');
                }

                return this;
            },

            /**
             * Closes the form attached to the dropdown
             * @returns {dropdownForm}
             * @fires close - When the form is closed
             */
            closeForm: function closeForm() {
                this.setState('open', false);
                this.setState('open-on-left', false);
                this.setState('open-on-right', false);

                // the event is emitted only if the component is rendered.
                if (this.is('rendered')) {
                    /**
                     * @event open
                     */
                    this.trigger('close');
                }

                return this;
            }
        };
        var dropdownForm = componentFactory(api, defaults)
            // set the component's layout
            .setTemplate(dropdownFormTpl)

            // auto render on init
            .on('init', function () {
                // auto render on init
                _.defer(function () {
                    dropdownForm.render(container);
                });
            })

            // renders the component
            .on('render', function () {
                var self = this;
                var initConfig = this.getConfig();
                var formConfig = _.defaults({reset: false}, initConfig);
                var buttonConfig = {
                    id: 'trigger',
                    type: 'info',
                    label: initConfig.triggerText,
                    icon: initConfig.triggerIcon
                };
                controls = {
                    $trigger: this.getElement().find('.trigger-button'),
                    $form: this.getElement().find('.form-panel')
                };

                // toggle the form when clicking the dropdown button
                button = buttonFactory(buttonConfig)
                    .on('click', function () {
                        if (self.is('open')) {
                            self.closeForm();
                        } else {
                            self.openForm();
                        }
                    })
                    .render(controls.$trigger);

                form = formFactory(controls.$form, formConfig)
                    .spread(this, 'change submit invalid error')
                    .on('ready', function() {
                        // init state
                        if (self.is('open')) {
                            self.openForm();
                        }

                        /**
                         * @event ready
                         */
                        self.trigger('ready');
                    });
            })

            .on('submit', function () {
                this.closeForm();
            })

            // take care of the disable state
            .on('disable', function () {
                if (this.is('open')) {
                    this.closeForm();
                }
                if (this.is('rendered')) {
                    button.disable();
                    form.disable();
                }
            })
            .on('enable', function () {
                if (this.is('rendered')) {
                    button.enable();
                    form.enable();
                }
            })

            // cleanup the place
            .on('destroy', function () {
                button.destroy();
                form.destroy();
                button = null;
                form = null;
                controls = null;
            });

        // initialize the component with the provided config
        // defer the call to allow to listen to the init event
        _.defer(function () {
            dropdownForm.init(config);
        });

        return dropdownForm;
    }

    return dropdownFormFactory;
});
