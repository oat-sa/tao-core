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
    'ui/generis/form/widgets/checkBox/checkBox',
    'ui/generis/form/widgets/comboBox/comboBox',
    'ui/generis/form/widgets/hiddenBox/hiddenBox',
    'ui/generis/form/widgets/textBox/textBox',
    'util/url',
    'tpl!tao/ui/generis/form/form',
    'css!tao/ui/generis/form/form'
], function(
    $,
    _,
    __,
    request,
    componentFactory,
    checkBoxFactory,
    comboBoxFactory,
    hiddenBoxFactory,
    textBoxFactory,
    url,
    tpl
) {
    'use strict';


    var _widgetFactories = {
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox': checkBoxFactory,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox': comboBoxFactory,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox': hiddenBoxFactory,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox': textBoxFactory
    };


    /**
     * The factory
     * @param {String} [options.class.uri = ''] - Resource class identifier
     * @param {String} [options.class.label = ''] - Resource class label
     * @param {JSON} [options.resource.json] - Resource data as JSON (overrides options.data.url)
     * @param {String} options.resource.url - Resource url
     * @param {String} [options.resource.uri = null] - Resource identifier
     * @returns {ui/component}
     */
    function formFactory(options) {
        options = options || {};
        options.class = options.class || {};
        options.resource = options.resource || {};

        return componentFactory({
            /**
             * Gets a field value on the form
             * @param {String} uri
             * @param {Function} [callback]
             * @returns {String|this}
             */
            get: function get(uri, callback) {
                var field = this.fields[uri];
                var ret;

                if (this.is('loaded')) {
                    ret = field.get();
                }

                if (typeof callback === 'function') {
                    callback.apply(this, [ret]);
                    return this;
                }

                return ret;
            },

            /**
             * Serialize form data into array of objects
             * @param {Function} [callback]
             * @returns {Array|this}
             */
            serialize: function serialize(callback) {
                var ret = _.map(_.values(this.fields), function (field) {
                    return field.serialize();
                });

                if (typeof callback === 'function') {
                    callback.apply(this, [ret]);
                    return this;
                }

                return ret;
            },

            /**
             * Sets a field value on the form
             * @param {String} uri
             * @param {String} value
             * @param {Function} [callback]
             * @returns {String|this}
             */
            set: function set(uri, value, callback) {
                var field = this.fields[uri];
                var ret;

                if (this.is('loaded')) {
                    ret = field.set(value);
                }

                if (typeof callback === 'function') {
                    callback.apply(this, [ret]);
                    return this;
                }

                return field.set(ret);
            },

            /**
             * Submit form
             * @returns {Promise}
             */
            submit: function submit(callback) {
                var promise = request(
                    this.config.form.action,
                    this.serialize(),
                    this.config.form.method
                );

                if (typeof callback === 'function') {
                    callback.apply(this, [promise]);
                }

                return promise;
            },

            /**
             * Validate form
             * @param {Function} [callback]
             * @returns {Boolean}
             */
            validate: function validate(callback) {
                var isValid = _.reduce(
                    this.fields,
                    function (acc, field) {
                        return (field.validate().length === 0) && acc;
                    },
                    true
                );

                if (typeof callback === 'function') {
                    callback.apply(this, [isValid]);
                    return this;
                }

                return isValid;
            },

            /**
             * Load data into form
             * @param {Object} data
             * @param {Function} [callback]
             * @returns {Boolean|this}
             */
            _load: function load(data, callback) {
                var success = _.reduce(
                    data.properties,
                    function (acc, property) {
                        var factory = _widgetFactories[property.widget];

                        if (property.range) {
                            property.range = data.values[property.range];
                        }

                        this.fields[property.uri] = factory ?
                            factory().init(property) :
                            null;
                        return acc && !!this.fields[property.uri];
                    },
                    true,
                    this
                );

                this.trigger('load', this.setState('loaded', success));

                if (typeof callback === 'function') {
                    callback.apply(this, [success]);
                }

                return success;
            }
        }, {
            form: {
                action: '#',
                method: 'GET'
            },
            submit: {
                text: __('Save')
            },
            uri: null
        })

        .setTemplate(tpl)

        .on('init', function () {
            var self = this;

            // Initialize options
            this.fields = {};
            this.class = {
                uri: options.class.uri || '',
                label: options.class.label || ''
            };
            this.resource = {
                json: options.resource.json || null,
                url: options.resource.url,
                uri: options.resource.uri
            };

            // Give form action uri parameter if applicable
            if (this.resource.uri) {
                this.config.form.action = url.build(this.config.form.action, {
                    uri: this.resource.uri
                });
            }

            // Load field data
            if (this.resource.json) {
                this._load(this.resource.json, renderFields);
            } else {
                request(this.resource.url, {
                    uri: this.resource.uri
                })
                .then(function (data) {
                    self._load(data, renderFields);
                });
            }

            function renderFields() {
                if (this.is('rendered')) {
                    _.each(this.fields, function (field) {
                        if (!field.is('rendered')) {
                            field.render(this.getElement().find('fieldset'));
                        }
                    }, this);
                }
            }
        })

        .on('render', function () {
            var $form = this.getElement();
            var self = this;

            // Render fields
            _.each(this.fields, function (field) {
                field.render($form.find('fieldset'));
            });

            // Handle submit
            $form.on('submit', function (e) {
                e.preventDefault();

                if (self.validate()) {
                    self.submit()
                    .then(function (data) {
                        console.log('success', data);
                    })
                    .catch(function (error) {
                        console.log('error', error);
                    });
                }

                return false;
            });
        });
    }

    return formFactory;
});