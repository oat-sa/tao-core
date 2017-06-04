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
    'ui/generis/form/widgets/textBox/textBox',
    'tpl!tao/ui/generis/form/form',
    'css!tao/ui/generis/form/form'
], function(
    $,
    _,
    __,
    request,
    componentFactory,
    textBoxFactory,
    tpl
) {
    'use strict';


    /**
     * The factory
     * @param {String} options.class.uri - Resource class identifier
     * @param {String} [options.class.label = ''] - Resource class label
     * @param {String} [options.submit.text = 'Save'] - Submit button text
     * @param {String} options.request.url - Resource url
     * @param {String} [options.uri = null] - Resource identifier
     * @event submit - Fires on form submission
     * @returns {ui/component}
     */
    function formFactory(options) {
        options = options || {};

        return componentFactory({
            /**
             * Gets a field value on the form
             * @param {String} uri
             * @param {Function} callback
             * @returns {String|this}
             */
            get: function get(uri, callback) {
                return this;
            },

            /**
             * Sets a field value on the form
             * @param {String} uri
             * @param {String} value
             * @param {Function} callback
             * @returns {String|this}
             */
            set: function set(uri, value, callback) {
                return this;
            },

            /**
             * Validates form
             * @param {Function} callback
             * @returns {Boolean}
             */
            validate: function validate(callback) {
                var isValid = _.reduce(this.config.fields, function(acc, field) {
                    return field.validate() && acc;
                }, true);

                return this;
            }

        }, {
            fields: {},
            class: {
                label: ''
            },
            submit: {
                text: __('Save')
            },
            uri: null
        })

        .setTemplate(tpl)

        .on('render', function () {
            var $form = this.getElement();
            var self = this;

            // Render fields
            request(this.config.request.url, {
                uri: this.config.uri
            })
            .then(function (data) {
                var $fieldset = $form.find('fieldset');
                _.each(data.properties, function (property) {
                    if (property.widget === 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox') {
                        self.config.fields[property.uri] = textBoxFactory(property).render($fieldset);
                    }
                });
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

        .init(options);
    }

    return formFactory;
});