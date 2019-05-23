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
    'lodash',
    'i18n',
    'handlebars',
    'core/promise',
    'core/providerRegistry',
    'ui/component',
    'ui/form/validator/validator',
    'tpl!ui/form/widget/tpl/widget',
    'tpl!ui/form/widget/tpl/label',
    'css!ui/form/widget/css/widget'
], function (
    _,
    __,
    Handlebars,
    Promise,
    providerRegistry,
    componentFactory,
    validatorFactory,
    widgetTpl,
    labelTpl
) {
    'use strict';

    /**
     * @typedef {Object} widgetConfig Defines the config entries available to setup a form widget
     * @property {String} widget - The type of widget
     * @property {String} uri - The identifier of the widget
     * @property {String} [label] - The label of the widget
     * @property {String} [value] - The value of the widget
     * @property {String[]} [range] -
     * @property {Boolean} [required] - Tells if the value is required
     * @property {validationRule|validationRule[]|validator} [validation]
     */

    /**
     * @typedef {Object} widgetValue Defines the value serialized from a widget
     * @property {String} name - The identifier of the widget
     * @property {String} value - The value of the widget
     */

    /**
     * Some default config
     * @type {widgetConfig}
     */
    var defaults = {
        widgetType: 'input-box',
        required: false,
        label: __('Label'),
        value: ''
    };

    /**
     * Gets the provider with respect to the provided config
     * @param {widgetConfig} config
     * @returns {Object}
     * @throws {TypeError} if the config is not valid or if the provider does not exist
     */
    function getWidgetProvider(config) {
        if (!_.isPlainObject(config)) {
            throw new TypeError('The config must be an object');
        }
        if (!_.isString(config.uri) || !config.uri) {
            throw new TypeError('The config must contain an uri');
        }
        if (!_.isString(config.widget) || !config.widget) {
            throw new TypeError('The config must declare a type of widget');
        }

        return widgetFactory.getProvider(config.widget);
    }

    /**
     * Factory that builds a form element based on its config.
     *
     * * @example
     *  var container = $('.my-container', $container);
     *
     *  var config = {
 *          widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox'
 *          uri: 'nickname',
 *          label: 'Name',
 *          required: true
     *  };
     *
     *  var widget = widgetFactory(container, config)
     *      .on('change', function(value) {
     *          // ...
     *      });
     *
     * @param {HTMLElement|String} container
     * @param {widgetConfig} config
     * @param {String} config.widget - The type of widget
     * @param {String} config.uri - The identifier of the widget
     * @param {String} [config.label] - The label of the widget
     * @param {String} [config.value] - The value of the widget
     * @param {String[]} [config.range] -
     * @param {Boolean} [config.required] - Tells if the value is required
     * @param {validationRule|validationRule[]|validator} [config.validation]
     * @returns {widgetForm}
     * @fires ready - When the component is ready to work
     */
    function widgetFactory(container, config) {
        var validator;
        var provider = getWidgetProvider(config);

        /**
         * @typedef {component} widgetForm
         */
        var widgetApi = {
            /**
             * Gets the widget's URI
             * @returns {String}
             */
            getUri: function getUri() {
                return this.getConfig().uri;
            },

            /**
             * Gets the value of the widget
             * @returns {String}
             */
            getValue: function getValue() {
                if (_.isFunction(provider.getValue)) {
                    return provider.getValue.call(this);
                }

                if (this.is('rendered')) {
                    return this.getWidgetElement().val();
                }

                return this.getConfig().value || '';
            },

            /**
             * Sets the value of the widget
             * @param {String} value
             * @returns {widgetForm}
             * @fires change after the value has been changed
             */
            setValue: function setValue(value) {
                this.getConfig().value = value;

                if (_.isFunction(provider.setValue)) {
                    provider.setValue.call(this, value);
                } else {
                    if (this.is('rendered')) {
                        this.getWidgetElement().val(value);
                    }
                }

                this.notify();

                return this;
            },

            /**
             * Gets access to the validation engine
             * @returns {validator}
             */
            getValidator: function getValidator() {
                return validator;
            },

            /**
             * Sets the validation engine
             * @param {validationRule|validationRule[]|validator} validation
             * @returns {widgetForm}
             */
            setValidator: function setValidator(validation) {
                if (validation && _.isFunction(validation.validate)) {
                    validator = validation;
                } else {
                    if (validation && !validation.validations) {
                        if (!_.isArray(validation)) {
                            validation = [validation];
                        }
                        validation = {
                            validations: validation
                        };
                    }

                    validator = validatorFactory(validation);
                }

                return this;
            },

            /**
             * Resets the widget to its default value
             * @returns {widgetForm}
             */
            reset: function reset() {
                if (_.isFunction(provider.reset)) {
                    provider.reset.call(this);
                } else {
                    this.setValue('');
                }
                return this;
            },

            /**
             * Serializes the value of the widget
             * @returns {widgetValue}
             */
            serialize: function serialize() {
                return {
                    name: this.getUri(),
                    value: this.getValue()
                };
            },

            /**
             * Validates the widget
             * @returns {Promise}
             */
            validate: function validate() {
                var self = this;
                return this.getValidator()
                    .validate(this.getValue())
                    .then(function (res) {
                        self.setState('invalid', false);
                        return res;
                    })
                    .catch(function (err) {
                        self.setState('invalid', true);
                        return Promise.reject(err);
                    });
            },

            /**
             * Triggers the change event
             * @returns {widgetForm}
             * @fires change
             */
            notify: function notify() {
                /**
                 * @event change
                 * @param {String} value
                 * @param {String} uri
                 */
                this.trigger('change', this.getValue(), this.getUri());
                return this;
            },

            /**
             * Gets access to the actual form element
             * @returns {jQuery|null}
             */
            getWidgetElement: function getWidgetElement() {
                if (this.is('rendered')) {
                    return this.getElement()
                        .find('[name="' + this.getUri() + '"]');
                }
                return null;
            }
        };

        var widget = componentFactory(widgetApi, defaults)
            .setTemplate(provider.template || widgetTpl)
            .on('init', function () {
                if (this.getConfig().required) {
                    this.getValidator().addValidation({
                        id: 'required',
                        message: __('This field is required'),
                        predicate: function (value) {
                            return value && value.length > 0;
                        },
                        precedence: 1
                    });
                }

                _.defer(function () {
                    widget.render(container);
                });
            })
            .on('render', function () {
                // reflect the type of widget
                this.setState(this.getConfig().widgetType, true);

                // react to data change
                this.getWidgetElement().on('change blur', function () {
                    var value = widget.getValue();
                    if (value !== widget.getConfig().value) {
                        widget.getConfig().value = value;
                        widget.notify();
                    }
                });

                /**
                 * @event ready
                 */
                this.trigger('ready');
            })
            .on('disable', function () {
                if (this.is('rendered')) {
                    this.getWidgetElement().prop('disabled', true);
                }
            })
            .on('enable', function () {
                if (this.is('rendered')) {
                    this.getWidgetElement().prop('disabled', false);
                }
            });

        widget.setValidator(config && config.validator);

        _.defer(function () {
            widget.init(provider.init.call(widget, config) || config);
        });

        return widget;
    }


    // expose a partial that can be used by every form widget to inject the label markup
    Handlebars.registerPartial('ui-form-widget-label', labelTpl);

    // the widgetFactory is also a providers registry
    return providerRegistry(widgetFactory);
});
