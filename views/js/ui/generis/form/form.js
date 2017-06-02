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
    'core/dataProvider/request',
    'ui/component',
    'ui/form/field',
    'tpl!tao/ui/form/form',
    'css!tao/ui/form/form'
], function(
    $,
    _,
    __,
    request,
    componentFactory,
    fieldFactory,
    tpl
) {
    'use strict';

    var _fieldContainer = '.field-container';

    /**
     * The form factory
     * @param {String} options.action - Url to submit form
     * @param {JSON} [options.json] - Json data to initialize form (overrides options.request)
     * @param {String} [options.method] - Http method to submit form
     * @param {String} [options.name] - Form or resource name
     * @param {String} [options.request.method] - Request method to load form data
     * @param {Object} [options.request.parameters] - Request parameters for form data
     * @param {String} [options.reques.url] - Request url to load form data
     * @param {Object} [options.templateVars.submit.value] - Submit button value
     * @event error - Fires on unsuccessful form submission
     * @event load - Fires on successful loading of form data
     * @event success - Fires on successful form submission
     * @returns {ui/component}
     */
    function formFactory(options) {
        var _fields = {};

        options = options || {};
        options.name = options.name || __('Form');

        return componentFactory({
            /**
             * Retrieves a field on the form
             * @param {String} name - Name of the field
             * @returns {ui/form/field}
             */
            getField: function getField(name) {
                return _fields[name];
            },

            /**
             * Add a field to the form
             * @param {Object} fieldOptions
             * @returns {ui/form}
             */
            addField: function addField(name, fieldOptions) {
                // Create ui/form/field
                var field = fieldFactory(fieldOptions);

                // Add field to fields
                _fields[name] = field;

                // Render field (if appropriate)
                if (this.is('rendered')) {
                    field.render(_fieldContainer);
                }

                return this;
            },

            /**
             * Remove a field from to the form
             * @param {String} name - Name of the field
             * @returns {ui/form}
             */
            removeField: function removeField(name) {
                if (_fields.hasOwnProperty(name)) {
                    _fields[name].destroy();
                    delete _fields[name];
                }

                return this;
            },

            /**
             * Checks form validity
             * @returns {Boolean}
             */
            validate: function validate() {
                return _.reduce(_fields, function(acc, field) {
                    return field.validate() && acc;
                }, true);
            }

        }, {
            submit: {
                value: __('Save')
            }
        })

        .setTemplate(tpl)

        .on('init', function () {
            var self = this;

            if (options.json) {
                setTimeout(function () { // force async with setTimeout (because this triggers an event)
                    _.each(options.json, function (field) {
                        self.addField(field.uri, field);
                    });
                    self.trigger('load', options.json);
                });
            } else if (options.request) {
                request(options.request.url, options.request.parameters, options.request.method)
                .then(function (data) {
                    _.each(data, function (field) {
                        self.addField(field.uri, field);
                    });
                    self.trigger('load', data);
                })
                .catch(function (error) {
                    self.trigger('error', error);
                });
            }
        })

        .on('render', function () {
            var $form = this.getElement().find('form');
            var self = this;

            // Render fields
            _.each(_fields, function (field) {
                if (!field.is('rendered')) {
                    field.render(_fieldContainer);
                }
            });

            // Handle submit
            $form.on('submit', function (e) {
                var $this = $(this);

                e.preventDefault();

                if (self.validate()) {
                    request(options.action, $this.serializeArray(), options.method)
                    .then(function (data) {
                        self.trigger('success', data);
                    })
                    .catch(function (error) {
                        self.trigger('error', error);
                    });
                }

                return false;
            });
        })

        .init(options.templateVars);
    }

    return formFactory;
});