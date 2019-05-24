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
 * Defines a form component factory.
 * @see formFactory
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/collections',
    'core/promise',
    'ui/component',
    'ui/button',
    'ui/hider',
    'ui/form/widget/definitions',
    'ui/form/widget/loader',
    'tpl!ui/form/tpl/form',
    'css!ui/form/css/form.css'
], function (
    $,
    _,
    __,
    collections,
    Promise,
    componentFactory,
    buttonFactory,
    hider,
    widgetDefinitions,
    widgetFactory,
    formTpl
) {
    'use strict';

    /**
     * @typedef {Object} formConfig Defines the config entries available to setup a form
     * @property {String} [title] - An optional title for the form (default none)
     * @property {String} [formAction] - The url the form is targeting (default '#')
     * @property {String} [formMethod] - The HTTP method the form should use (default 'get')
     * @property {widgetConfig[]} [widgets] - The list of widgets to set in the form (default none)
     * @property {buttonConfig[]} [buttons] - The list of buttons to set in the form (default none)
     * @property {Object} [values] - Initial values for the widgets
     * @property {Object} [ranges] - An optional list of ranges for the widgets (generis related, default none)
     */

    /**
     * Some default config
     * @type {formConfig}
     */
    var defaults = {
        formAction: '#',
        formMethod: 'get'
    };

    /**
     * Enables all components from the list
     * @param {Map} list
     */
    function enableComponents(list) {
        list.forEach(function enableComponent(component) {
            component.enable();
        });
    }

    /**
     * Disables all components from the list
     * @param {Map} list
     */
    function disableComponents(list) {
        list.forEach(function disableComponent(component) {
            component.disable();
        });
    }

    /**
     * Wait for a component to be ready
     * @param {component} component - The target component
     * @returns {Promise<component>}
     */
    function waitForRender(component) {
        return new Promise(function renderPromise(resolve) {
            function resolveRender() {
                resolve(component);
            }

            if (component.is('rendered')) {
                resolveRender();
            } else {
                component.on('ready', resolveRender);
            }
        });
    }

    /**
     * Validates a widget/button definition, then returns a promise
     * @param {component} component - The target component
     * @param {Object} definition - The definition to check
     * @param {String} key - The key name for the identifier within the definition object
     * @returns {Promise<component>}
     */
    function validateDefinition(component, definition, key) {
        return new Promise(function definitionValidator(resolve, reject) {
            if (!_.isPlainObject(definition)) {
                return reject(new TypeError('The definition must be an object'));
            }
            if (!definition[key] || !_.isString(definition[key])) {
                return reject(new TypeError('The definition must contain an identifier'));
            }

            waitForRender(component)
                .then(resolve)
                .catch(reject);
        });
    }

    /**
     * Builds a form component.
     *
     * @example
     *  var container = $('.my-container', $container);
     *
     *  var config = {
     *      title: 'My fancy form',
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
     *      }],
     *      buttons = [{
     *          id: 'publish',
     *          label: 'Publish',
     *          icon: 'save
     *      }]
     *  };
     *
     *  var form = formFactory(container, config)
     *      .on('button-publish', function() {
     *          this.submit()
     *              .then(function(values) {
     *                  dataProvider('comment').send(values);
     *              })
     *              .catch(function(reason) {
     *                  feedback().error('Invalid input!');
     *              })
     *      });
     *
     * @param {HTMLElement|String} container
     * @param {formConfig} config
     * @param {String} [config.title] - An optional title for the form (default none)
     * @param {String} [config.formAction] - The url the form is targeting (default '#')
     * @param {String} [config.formMethod] - The HTTP method the form should use (default 'get')
     * @param {widgetConfig[]} [config.widgets] - The list of widgets to set in the form (default none)
     * @param {buttonConfig[]} [config.buttons] - The list of buttons to set in the form (default none)
     * @param {Object} [config.values] - Initial values for the widgets
     * @param {Object} [config.ranges] - An optional list of ranges for the widgets (generis related, default none)
     * @returns {form}
     * @fires ready - When the component is ready to work
     */
    function formFactory(container, config) {
        var widgets = new collections.Map();
        var buttons = new collections.Map();
        var controls = null;

        var api = {
            /**
             * Gets the url the form is targeting.
             * @returns {String}
             */
            getFormAction: function getFormAction() {
                return this.getConfig().formAction;
            },
            /**
             * Gets the HTTP method the form should use.
             * @returns {String}
             */
            getFormMethod: function getFormMethod() {
                return this.getConfig().formMethod;
            },

            /**
             * Gets access to the ranges set for the widgets (generis related)
             * @returns {Object}
             */
            getRanges: function getRanges() {
                return this.getConfig().ranges || {};
            },

            /**
             * Gets the title set to the form.
             * @returns {String}
             */
            getTitle: function getTitle() {
                return this.getConfig().title;
            },

            /**
             * Change the title of the form
             * @param {String} title
             * @returns {form}
             * @fires titlechange after the title has been changed
             */
            setTitle: function setTitle(title) {
                this.getConfig().title = title;

                if (this.is('rendered')) {
                    controls.$title.text(title);
                    hider.toggle(controls.$title, !!title);
                }

                /**
                 * @event titlechange
                 * @param {String} title
                 */
                this.trigger('titlechange', title);

                return this;
            },

            /**
             * Gets a widget by its uri
             * @param {String} uri
             * @returns {formWidget}
             */
            getWidget: function getWidget(uri) {
                if (widgets.has(uri)) {
                    return widgets.get(uri);
                }
                return null;
            },

            /**
             * Adds a widget to the form
             * @param {widgetConfig} definition
             * @returns {Promise<formWidget>}
             * @throws TypeError if the widget definition is invalid
             * @fires change when the widget's value changes
             * @fires change-<uri> when the widget's value changes
             * @fires widgetadd after the widget has been added
             */
            addWidget: function addWidget(definition) {
                var self = this;
                return validateDefinition(this, definition, 'uri')
                    .then(function() {
                        var ranges = self.getRanges();
                        if (definition.range && 'string' === typeof definition.range) {
                            definition.range = ranges[definition.range];
                        }

                        if (!definition.widget) {
                            definition.widget = widgetDefinitions.DEFAULT;
                        }

                        return new Promise(function (resolve) {
                            var widget = widgetFactory(controls.$widgets, definition);
                            widgets.set(definition.uri, widget);
                            widget
                                .on('change.form', function (value) {
                                    /**
                                     * @event change
                                     * @param {String} uri
                                     * @param {String} value
                                     */
                                    self.trigger('change', definition.uri, value);

                                    /**
                                     * @event change-<uri>
                                     * @param {String} value
                                     */
                                    self.trigger('change-' + definition.uri, value);
                                })
                                .on('ready.form', function () {
                                    /**
                                     * @event widgetadd
                                     * @param {String} uri
                                     * @param {formWidget} widget
                                     */
                                    self.trigger('widgetadd', definition.uri, this);

                                    resolve(this);
                                });
                        });
                    });
            },

            /**
             * Removes a widget
             * @param {String} uri
             * @returns {form}
             * @fires widgetremove after the widget has been removed
             */
            removeWidget: function removeWidget(uri) {
                if (widgets.has(uri)) {
                    widgets.get(uri)
                        .off('.form')
                        .destroy();
                    widgets.delete(uri);

                    /**
                     * @event widgetremove
                     * @param {String} uri
                     */
                    this.trigger('widgetremove', uri);
                }
                return this;
            },

            /**
             * Gets the list of widgets.
             * @returns {Object}
             */
            getWidgets: function getWidgets() {
                var list = {};
                widgets.forEach(function (widget, uri) {
                    list[uri] = widget;
                });
                return list;
            },

            /**
             * Replace the widgets
             * @param {widgetConfig[]} definitions
             * @returns {Promise<formWidget[]>}
             */
            setWidgets: function setWidgets(definitions) {
                var self = this;
                this.removeWidgets();
                return Promise.all(_.map(definitions, function (definition) {
                    return self.addWidget(definition);
                }));
            },

            /**
             * Removes all widgets
             * @returns {form}
             */
            removeWidgets: function removeWidgets() {
                var self = this;
                widgets.forEach(function (widget, uri) {
                    self.removeWidget(uri);
                });
                widgets.clear();
                return this;
            },

            /**
             * Gets a button by its identifier
             * @param {String} id
             * @returns {button}
             */
            getButton: function getButton(id) {
                if (buttons.has(id)) {
                    return buttons.get(id);
                }
                return null;
            },

            /**
             * Adds a button to the form
             * @param {buttonConfig} definition
             * @returns {Promise<button>}
             * @throws TypeError if the button definition is invalid
             * @fires button when the button is triggered
             * @fires button-<id> when the button is triggered
             * @fires buttonadd after the button has been added
             */
            addButton: function addButton(definition) {
                var self = this;
                return validateDefinition(this, definition, 'id')
                    .then(function() {
                        return new Promise(function (resolve) {
                            var button = buttonFactory(definition);
                            buttons.set(definition.id, button);
                            button
                                .on('click.form', function () {
                                    /**
                                     * @event button
                                     * @param {String} id
                                     */
                                    self.trigger('button', definition.id);

                                    /**
                                     * @event button-<id>
                                     */
                                    self.trigger('button-' + definition.id);
                                })
                                .on('ready.form', function () {
                                    /**
                                     * @event buttonadd
                                     * @param {String} id
                                     * @param {button} button
                                     */
                                    self.trigger('buttonadd', definition.id, this);

                                    resolve(this);
                                })
                                .render(controls.$buttons);
                        });
                    });
            },

            /**
             * Removes a button
             * @param {String} id
             * @returns {form}
             * @fires buttonremove after the button has been removed
             */
            removeButton: function removeButton(id) {
                if (buttons.has(id)) {
                    buttons.get(id)
                        .off('.form')
                        .destroy();
                    buttons.delete(id);

                    /**
                     * @event buttonremove
                     * @param {String} id
                     */
                    this.trigger('buttonremove', id);
                }
                return this;
            },

            /**
             * Gets the list of buttons.
             * @returns {Object}
             */
            getButtons: function getButtons() {
                var list = {};
                buttons.forEach(function (button, id) {
                    list[id] = button;
                });
                return list;
            },

            /**
             * Replace the buttons
             * @param {buttonConfig[]} definitions
             * @returns {Promise<button[]>}
             */
            setButtons: function setButtons(definitions) {
                var self = this;
                this.removeButtons();
                return Promise.all(_.map(definitions, function (definition) {
                    return self.addButton(definition);
                }));
            },

            /**
             * Removes all buttons
             * @returns {form}
             */
            removeButtons: function removeButtons() {
                var self = this;
                buttons.forEach(function (button, id) {
                    self.removeButton(id);
                });
                buttons.clear();
                return this;
            },

            /**
             * Gets the value of a widget
             * @param {String} uri
             * @returns {String}
             */
            getValue: function getValue(uri) {
                if (widgets.has(uri)) {
                    return widgets.get(uri).getValue();
                }
                return '';
            },

            /**
             * Sets the value of a widget
             * @param {String} uri
             * @param {String} value
             * @returns {form}
             */
            setValue: function setValue(uri, value) {
                if (widgets.has(uri)) {
                    widgets.get(uri).setValue(value);
                }
                return this;
            },

            /**
             * Gets the values from all the form widgets
             * @returns {Object}
             */
            getValues: function getValues() {
                var values = {};
                widgets.forEach(function (widget, uri) {
                    values[uri] = widget.getValue();
                });
                return values;
            },

            /**
             * Sets the values for the form widgets
             * @param {Object} values
             * @returns {form}
             */
            setValues: function setValues(values) {
                _.forEach(values, function (value, uri) {
                    if (widgets.has(uri)) {
                        widgets.get(uri).setValue(value);
                    }
                });
                return this;
            },

            /**
             * Serializes form values to an array of name/value objects
             * @returns {widgetValue[]}
             */
            serialize: function serialize() {
                var values = [];
                widgets.forEach(function (widget) {
                    values.push(widget.serialize());
                });
                return values;
            },

            /**
             * Validate the form widgets
             * @returns {Promise}
             */
            validate: function validate() {
                var self = this;
                var promises = [];
                widgets.forEach(function (widget) {
                    promises.push(
                        widget.validate()
                            .catch(function(messages) {
                                return Promise.resolve({
                                    name: widget.getUri(),
                                    messages: messages
                                });
                            })
                    );
                });
                return Promise.all(promises)
                    .then(function(result) {
                        var invalid = false;

                        result = _.compact(result);

                        if (result.length) {
                            result = Promise.reject(result);
                            invalid = true;
                        }

                        self.setState('invalid', invalid);
                        return result;
                    });
            },

            /**
             * Submits the form
             * @returns {form}
             * @fires submit
             */
            submit: function submit() {
                var self = this;
                this.validate()
                    .then(function() {
                        /**
                         * @event submit
                         * @param {widgetValue[]} values
                         */
                        self.trigger('submit', self.serialize());
                    })
                    .catch(function(reason) {
                        /**
                         * @event invalid
                         * @param {Object} reason
                         */
                        self.trigger('invalid', reason);
                    });

                return this;
            },

            /**
             * Resets the form
             * @returns {form}
             * @fires reset
             */
            reset: function reset() {
                widgets.forEach(function (widget) {
                    widget.reset();
                });

                /**
                 * @event reset
                 */
                this.trigger('reset');

                return this;
            }
        };

        var form = componentFactory(api, defaults)
            // set the component's layout
            .setTemplate(formTpl)

            // auto render on init
            .on('init', function () {
                // auto render on init
                _.defer(function () {
                    form.render(container);
                });
            })

            // renders the component
            .on('render', function () {
                var self = this;
                var $element = this.getElement();
                var initConfig = this.getConfig();
                var initPromises = [];

                controls = {
                    $title: $element.find('h2'),
                    $form: $element.find('form'),
                    $widgets: $element.find('fieldset'),
                    $buttons: $element.find('.form-actions')
                };

                // prevent the default behavior of the form for submitting
                controls.$form.on('submit', function doSubmit(e) {
                    e.preventDefault();
                    self.submit();
                });
                controls.$form.on('reset', function doReset(e) {
                    e.preventDefault();
                    self.reset();
                });

                // hide the title if empty
                hider.toggle(controls.$title, !!initConfig.title);

                // initial widgets and buttons
                if (_.size(initConfig.widgets)) {
                    initPromises.push(this.setWidgets(initConfig.widgets));
                }
                if (_.size(initConfig.buttons)) {
                    initPromises.push(this.setButtons(initConfig.buttons));
                }

                Promise.all(initPromises)
                    .then(function () {
                        if (_.size(initConfig.values)) {
                            self.setValues(initConfig.values);
                        }
                    })
                    .catch(function (err) {
                        self.trigger('error', err);
                    })
                    .then(function () {
                        /**
                         * @event ready
                         */
                        self.trigger('ready');
                    });
            })

            // take care of the disable state
            .on('disable', function () {
                disableComponents(widgets);
                disableComponents(buttons);
            })
            .on('enable', function () {
                enableComponents(widgets);
                enableComponents(buttons);
            })

            // cleanup the place
            .on('destroy', function () {
                this.removeButtons();
                this.removeWidgets();
                widgets = null;
                buttons = null;
                controls = null;
                form = null;
            });

        // initialize the component with the provided config
        // defer the call to allow to listen to the init event
        _.defer(function () {
            form.init(config);
        });

        return form;
    }

    return formFactory;
});
