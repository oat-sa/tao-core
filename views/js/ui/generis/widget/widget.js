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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'handlebars',
    'ui/component',
    'ui/generis/validator/validator',
    'tpl!ui/generis/widget/widget',
    'css!ui/generis/widget/widget'
], function (
    $,
    _,
    __,
    Handlebars,
    componentFactory,
    generisValidatorFactory,
    ptl
) {
    'use strict';

    Handlebars.registerPartial('ui-generis-widget-label', ptl);

    /**
     * The factory
     * @param {Object} [options.validator]
     * @param {Object} [spec]
     * @returns {ui/component}
     */
    function factory(options, spec) {
        var widget;

        options = options || {};

        widget = componentFactory(_.assign({
            /**
             * Gets widget value
             * @returns {String}
             */
            get: function get() {
                var ret = this.config.value || '';

                if (this.is('rendered')) {
                    ret = this.getElement()
                    .find('[name="' + this.config.uri + '"]')
                    .val();
                }

                return ret;
            },

            /**
             * Sets widget value
             * @param {String} value
             * @returns {String}
             */
            set: function set(value) {
                this.config.value = value;

                if (this.is('rendered')) {
                    this.getElement()
                    .find('[name="' + this.config.uri + '"]')
                    .val(value);
                }

                return this.config.value;
            },

            /**
             * Add a validator
             * @param {ui/generis/validator/validator} validator
             * @returns {this}
             */
            setValidator: function setValidator(validator) {
                validator = validator || [];

                if (typeof validator.is === 'function') { // is a ui/component
                    this.validator = validator;
                } else {
                    this.validator = generisValidatorFactory({
                        validations: validator
                    });
                }

                if (this.is('rendered')) {
                    this.validator.render(this.getElement());
                } else {
                    this.on('render.setValidator', function () {
                        this.validator.render(this.getElement());
                        this.off('render.setValidator');
                    });
                }

                return this;
            },

            /**
             * Validates widget (if validator is not null)
             * @returns {this}
             */
            validate: function validate() {
                var input;

                if (this.validator) {
                    this.validator.run(this.get());
                    this.validator.display();

                    if (this.is('rendered')) {
                        input = this.getElement().find('.right > :input, .right > .check-box-input');
                        if (this.validator.errors.length) {
                            input.addClass('error');
                        } else {
                            input.removeClass('error');
                        }
                    }
                }

                return this;
            },

            /**
             * Serializes widget into a name/value object for form submission
             * @returns {Object}
             */
            serialize: function serialize() {
                return {
                    name: this.config.uri,
                    value: this.get()
                };
            }
        }, spec), {
            label: __('Label'),
            required: false
        })
        .on('render', function () {
            var $input = this.getElement().find('.right :input');
            var self = this;

            $input.on('change blur', function () {
                /**
                 * @event widget#change
                 * @param {Object} widgetData
                 */
                self.trigger('change', self.serialize());
            });
        });

        widget.setValidator(options.validator);

        return widget;
    }

    return factory;
});
