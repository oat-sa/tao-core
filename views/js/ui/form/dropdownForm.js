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
    'ui/component',
    'ui/button',
    'ui/form/simpleForm',
    'tpl!ui/form/tpl/dropdownForm',
    'css!ui/form/css/dropdownForm.css'
], function ($, _, __, componentFactory, buttonFactory, formFactory, dropdownFormTpl) {
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
     * @property {Object} [ranges] - An optional list of ranges for the widgets (@see widgetConfig.range)
     */

    /**
     * Some default config
     * @type {Object}
     */
    const defaults = {
        triggerIcon: null,
        triggerText: __('Form'),
        submitIcon: null,
        submitText: __('Submit'),
    };

    /**
     * Builds a dropdown form component.
     *
     * @example
     *  const container = $('.my-container', $container);
     *
     *  const config = {
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
     *  const form = dropdownFormFactory(container, config)
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
     * @param {Object} [config.ranges] - An optional list of ranges for the widgets (@see widgetConfig.range)
     * @returns {dropdownForm}
     * @fires ready - When the component is ready to work
     */
    function dropdownFormFactory(container, config) {
        let form = null;
        let button = null;
        let controls = null;

        const api = {
            /**
             * Gets access to the form
             * @returns {form}
             */
            getForm() {
                return form;
            },

            /**
             * Update the form with a new list of widgets
             * @param {widgetConfig[]} widgets
             * @returns {Promise}
             * @throws {Error} if the form is not yet rendered
             */
            setFormWidgets(widgets) {
                if (!this.is('rendered') || !form) {
                    return Promise.reject(new Error('The form is not rendered'));
                }

                return form.setWidgets(widgets);
            },

            /**
             * Gets the values from the form widgets
             * @returns {Object}
             */
            getFormValues() {
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
            setFormValues(values) {
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
            openForm() {
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
            closeForm() {
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
        const dropdownForm = componentFactory(api, defaults)
            // set the component's layout
            .setTemplate(dropdownFormTpl)

            // auto render on init
            .on('init', function onDropdownFormInit() {
                // auto render on init
                _.defer(() => this.render(container));
            })

            // renders the component
            .on('render', function onDropdownFormRender() {
                const initConfig = this.getConfig();
                const formConfig = _.defaults({reset: false}, initConfig);
                const buttonConfig = {
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
                    .on('click', () => {
                        if (this.is('open')) {
                            this.closeForm();
                        } else {
                            this.openForm();
                        }
                    })
                    .render(controls.$trigger);

                /**
                 * @event ready
                 */
                form = formFactory(controls.$form, formConfig)
                    .spread(this, 'ready change submit invalid error');
            })

            .on('ready', function onDropdownFormReady() {
                // init state
                if (this.is('open')) {
                    this.openForm();
                }
            })

            .on('submit', function onDropdownFormSubmit() {
                this.closeForm();
            })

            // take care of the disable state
            .on('disable', function onDropdownFormDisable() {
                if (this.is('open')) {
                    this.closeForm();
                }
                if (this.is('rendered')) {
                    button.disable();
                    form.disable();
                }
            })
            .on('enable', function onDropdownFormEnable() {
                if (this.is('rendered')) {
                    button.enable();
                    form.enable();
                }
            })

            // cleanup the place
            .on('destroy', function onDropdownFormDestroy() {
                button.destroy();
                form.destroy();
                button = null;
                form = null;
                controls = null;
            });

        // initialize the component with the provided config
        // defer the call to allow to listen to the init event
        _.defer(() => dropdownForm.init(config));

        return dropdownForm;
    }

    return dropdownFormFactory;
});
