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
    'ui/component',
    'ui/generis/widget/loader',
    'ui/generis/widget/checkBox/checkBox',
    'ui/generis/widget/comboBox/comboBox',
    'ui/generis/widget/hiddenBox/hiddenBox',
    'ui/generis/widget/textBox/textBox',
    'tpl!ui/generis/form/form',
    'css!ui/generis/form/form'
], function (
    $,
    _,
    __,
    componentFactory,
    widgetLoader,
    checkBoxFactory,
    comboBoxFactory,
    hiddenBoxFactory,
    textBoxFactory,
    tpl
) {
    'use strict';

    /**
     * The factory
     * @param {Object} [options]
     * @param {String} [config.form.action = '#']
     * @param {String} [config.form.method = 'get']
     * @param {String} [config.submit.text = 'Save']
     * @param {String} [config.title = 'Generis Form']
     */
    function factory(options, config) {
        var form;

        options = options || {};

        config = config || {};
        config.form = config.form || {};
        config.submit = config.submit || {};

        form = componentFactory({

            /**
             * Add a widget/field to form
             * @param {Object} widgetOptions
             * @param {String} widgetOptions.uri - the property URI
             * @param {String} [widgetOptions.widget] - the widget URI
             * @param {String|String[]} [widgetOptions.value] - the default value
             * @param {Boolean} [widgetOptions.required = false] - is the field required
             * @returns {this}
             */
            addWidget: function addWidget(widgetOptions) {
                var widget = widgetLoader(widgetOptions.widget)({}, widgetOptions);

                this.widgets.push(widget);

                if (this.is('rendered')) {
                    widget.render(this.getElement().find('form > fieldset'));
                } else {
                    this.on('render.' + widget.config.uri, function () {
                        widget.render(this.getElement().find('form > fieldset'));
                        this.off('render.' + this.config.uri);
                    });
                }

                return this;
            },

            /**
             * Get a widget
             * @param {String} uri - the property URI
             * @returns {Object} the widget
             */
            getWidget : function getWidget(uri){
                return _.find(this.widgets, function(widget){
                    return widget.config.uri === uri;
                });
            },

            /**
             * Remove a widget/field from form
             * @param {String} uri - the property URI
             * @returns {this}
             */
            removeWidget: function removeWidget(uri) {
                _.remove(this.widgets, function (widget) {
                    if (widget.config.uri === uri) {
                        widget.destroy();
                        return true;
                    }
                });

                return this;
            },

            /**
             * Validates form and populates errors array
             * @returns {this}
             */
            validate: function validate() {
                this.errors = _(this.widgets)
                .map(function (widget) {
                    widget.validate();
                    return {
                        uri: widget.config.uri,
                        errors: widget.validator.errors
                    };
                })
                .reject(function (data) {
                    return data.errors ? data.errors.length === 0 : true;
                })
                .value();

                return this;
            },

            /**
             * Serializes form values to an array of name/value objects
             * @returns {Object[]}
             */
            serializeArray: function serializeArray() {
                return _.map(this.widgets, function (widget) {
                    return widget.serialize();
                });
            },

            /**
             * Convenience method to retrieve the form values
             * as name : value
             * @returns {Object} the values object
             */
            getValues : function getValues(){
                return _.reduce(this.serializeArray(), function(acc, field){
                    if( _.isString(field.name) && !_.isEmpty(field.name) &&
                       (_.isString(field.value) && !_.isEmpty(field.value)) ||
                       (_.isArray(field.value) && field.value.length > 0) ){

                        acc[field.name] = field.value;
                    }
                    return acc;
                }, {});
            },

            /**
             * Toggles loading state
             * @param {Boolean} [isLoading = undefined]
             * @returns {this}
             * @fires loading
             * @fires loaded
             */
            toggleLoading: function toggleLoading(isLoading) {
                if (typeof isLoading === 'undefined') {
                    isLoading = !this.is('loading');
                }

                if (isLoading) {
                    /**
                     * @event form#loading
                     */
                    this.trigger('loading');
                    this.disable();
                } else {
                    /**
                     * @event form#loaded
                     */
                    this.trigger('loaded');
                    this.enable();
                }

                this.setState('loading', isLoading);

                return this;
            }
        }, {
            formAction: '#',
            formMethod: 'get',
            submitText: __('Save'),
            title: __('Generis Form'),
            reset: true,
            resetText: __('Reset')
        })
        .setTemplate(tpl)
        .init(config)
        .on('render', function () {
            var $form = this.getElement().find('form');
            var self = this;

            $form.on('submit', function (e) {
                e.preventDefault();

                /**
                 * @event form#submit
                 * @param {Object[]} formData
                 */
                self.trigger('submit', self.serializeArray());

                return false;
            });

            $form.on('reset', function(){

                /**
                 * @event form#reset
                 * @param {Object[]} formData
                 */
                self.trigger('reset', self.serializeArray());
            });

        });

        form.data = options;
        form.errors = [];
        form.widgets = [];

        // Add widgets to form
        _.each(options.properties || [], function (property) {
            if (property.range && typeof property.range === 'string') {
                property.range = options.values[property.range];
            }
            form.addWidget(property);
        });

        return form;
    }

    return factory;
});
