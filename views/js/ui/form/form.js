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
     * @property {Object} [ranges] - An optional list of ranges for the widgets (@see widgetConfig.range)
     */

    /**
     * Some default config
     * @type {formConfig}
     */
    const defaults = {
        formAction: '#',
        formMethod: 'get'
    };

    /**
     * Enables all components from the list
     * @param {Map} collection
     */
    function enableComponents(collection) {
        for (let component of collection.values()) {
            component.enable();
        }
    }

    /**
     * Disables all components from the list
     * @param {Map} collection
     */
    function disableComponents(collection) {
        for (let component of collection.values()) {
            component.disable();
        }
    }

    /**
     * Remove and destroy a component from a collection.
     * @param {Map} collection
     * @param {String} key
     */
    function removeComponent(collection, key) {
        collection.get(key)
            .off('.form')
            .destroy();
        collection.delete(key);
    }

    /**
     * Gets indexed components from a collection.
     * @param {Map} collection
     * @returns {Object}
     */
    function getComponents(collection) {
        const components = {};
        for (let [id, component] of collection) {
            components[id] = component;
        }
        return components;
    }

    /**
     * Wait for a component to be ready
     * @param {component} component - The target component
     * @returns {Promise<component>}
     */
    function waitForRender(component) {
        return new Promise(function renderPromise(resolve) {
            const resolveRender = () => resolve(component);

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
        if (!_.isPlainObject(definition)) {
            return Promise.reject(new TypeError('The definition must be an object'));
        }
        if (!definition[key] || !_.isString(definition[key])) {
            return Promise.reject(new TypeError('The definition must contain an identifier'));
        }

        return waitForRender(component);
    }

    /**
     * Builds a form component.
     *
     * @example
     *  const container = $('.my-container', $container);
     *
     *  const config = {
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
     *  const form = formFactory(container, config)
     *      .on('button-publish', () => {
     *          this.submit()
     *              .then(values => dataProvider('comment').send(values))
     *              .catch(reason => feedback().error('Invalid input!'))
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
     * @param {Object} [config.ranges] - An optional list of ranges for the widgets (@see widgetConfig.range)
     * @returns {form}
     * @fires ready - When the component is ready to work
     */
    function formFactory(container, config) {
        const widgets = new Map();
        const buttons = new Map();
        let controls = null;

        const api = {
            /**
             * Gets the url the form is targeting.
             * @returns {String}
             */
            getFormAction() {
                return this.getConfig().formAction;
            },
            /**
             * Gets the HTTP method the form should use.
             * @returns {String}
             */
            getFormMethod() {
                return this.getConfig().formMethod;
            },

            /**
             * Gets access to the ranges set for the widgets (generis related)
             * @returns {Object}
             */
            getRanges() {
                return this.getConfig().ranges || {};
            },

            /**
             * Gets the title set to the form.
             * @returns {String}
             */
            getTitle() {
                return this.getConfig().title;
            },

            /**
             * Change the title of the form
             * @param {String} title
             * @returns {form}
             * @fires titlechange after the title has been changed
             */
            setTitle(title) {
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
             * @returns {widgetForm}
             */
            getWidget(uri) {
                if (widgets.has(uri)) {
                    return widgets.get(uri);
                }
                return null;
            },

            /**
             * Adds a widget to the form
             * @param {widgetConfig} definition
             * @returns {Promise<widgetForm>}
             * @throws TypeError if the widget definition is invalid
             * @fires change when the widget's value changes
             * @fires change-<uri> when the widget's value changes
             * @fires widgetadd after the widget has been added
             */
            addWidget(definition) {
                return validateDefinition(this, definition, 'uri')
                    .then(() => {
                        const ranges = this.getRanges();
                        if (definition.range && 'string' === typeof definition.range) {
                            definition.range = ranges[definition.range];
                        }

                        if (!definition.widget) {
                            definition.widget = widgetDefinitions.DEFAULT;
                        }

                        return new Promise(resolve => {
                            const widget = widgetFactory(controls.$widgets, definition);
                            widgets.set(definition.uri, widget);
                            widget
                                .on('change.form', value => {
                                    /**
                                     * @event change
                                     * @param {String} uri
                                     * @param {String} value
                                     */
                                    this.trigger('change', definition.uri, value);

                                    /**
                                     * @event change-<uri>
                                     * @param {String} value
                                     */
                                    this.trigger(`change-${definition.uri}`, value);
                                })
                                .on('ready.form', () => {
                                    /**
                                     * @event widgetadd
                                     * @param {String} uri
                                     * @param {widgetForm} widget
                                     */
                                    this.trigger('widgetadd', definition.uri, widget);

                                    resolve(widget);
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
            removeWidget(uri) {
                if (widgets.has(uri)) {
                    removeComponent(widgets, uri);

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
            getWidgets() {
                return getComponents(widgets);
            },

            /**
             * Replace the widgets
             * @param {widgetConfig[]} definitions
             * @returns {Promise<widgetForm[]>}
             */
            setWidgets(definitions) {
                this.removeWidgets();
                return Promise.all(_.map(definitions, definition => this.addWidget(definition)));
            },

            /**
             * Removes all widgets
             * @returns {form}
             */
            removeWidgets() {
                for (let uri of widgets.keys()) {
                    this.removeWidget(uri);
                }
                widgets.clear();
                return this;
            },

            /**
             * Gets a button by its identifier
             * @param {String} id
             * @returns {button}
             */
            getButton(id) {
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
            addButton(definition) {
                return validateDefinition(this, definition, 'id')
                    .then(() => new Promise(resolve => {
                        const button = buttonFactory(definition);
                        buttons.set(definition.id, button);
                        button
                            .on('click.form', () => {
                                /**
                                 * @event button
                                 * @param {String} id
                                 */
                                this.trigger('button', definition.id);

                                /**
                                 * @event button-<id>
                                 */
                                this.trigger(`button-${definition.id}`);
                            })
                            .on('ready.form', () => {
                                /**
                                 * @event buttonadd
                                 * @param {String} id
                                 * @param {button} button
                                 */
                                this.trigger('buttonadd', definition.id, button);

                                resolve(button);
                            });
                        button.render(controls.$buttons);
                    }));
            },

            /**
             * Removes a button
             * @param {String} id
             * @returns {form}
             * @fires buttonremove after the button has been removed
             */
            removeButton(id) {
                if (buttons.has(id)) {
                    removeComponent(buttons, id);

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
            getButtons() {
                return getComponents(buttons);
            },

            /**
             * Replace the buttons
             * @param {buttonConfig[]} definitions
             * @returns {Promise<button[]>}
             */
            setButtons(definitions) {
                this.removeButtons();
                return Promise.all(_.map(definitions, definition => this.addButton(definition)));
            },

            /**
             * Removes all buttons
             * @returns {form}
             */
            removeButtons() {
                for (let id of buttons.keys()) {
                    this.removeButton(id);
                }
                buttons.clear();
                return this;
            },

            /**
             * Gets the value of a widget
             * @param {String} uri
             * @returns {String}
             */
            getValue(uri) {
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
            setValue(uri, value) {
                if (widgets.has(uri)) {
                    widgets.get(uri).setValue(value);
                }
                return this;
            },

            /**
             * Gets the values from all the form widgets
             * @returns {Object}
             */
            getValues() {
                const values = {};
                for (let [uri, widget] of widgets) {
                    values[uri] = widget.getValue();
                }
                return values;
            },

            /**
             * Sets the values for the form widgets
             * @param {Object} values
             * @returns {form}
             */
            setValues(values) {
                _.forEach(values, (value, uri) => {
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
            serialize() {
                const values = [];
                for (let widget of widgets.values()) {
                    values.push(widget.serialize());
                }
                return values;
            },

            /**
             * Validate the form widgets
             * @returns {Promise}
             */
            validate() {
                const promises = [];
                for (let [uri, widget] of widgets) {
                    promises.push(
                        widget.validate()
                            .catch(messages => Promise.resolve({uri, messages}))
                    );
                };
                return Promise.all(promises)
                    .then(result => {
                        let invalid = false;

                        result = _.compact(result);

                        if (result.length) {
                            result = Promise.reject(result);
                            invalid = true;
                        }

                        this.setState('invalid', invalid);
                        return result;
                    });
            },

            /**
             * Submits the form
             * @returns {form}
             * @fires submit
             */
            submit() {
                this.validate()
                    .then(() => {
                        /**
                         * @event submit
                         * @param {widgetValue[]} values
                         */
                        this.trigger('submit', this.serialize());
                    })
                    .catch(reason => {
                        /**
                         * @event invalid
                         * @param {Object} reason
                         */
                        this.trigger('invalid', reason);
                    });

                return this;
            },

            /**
             * Resets the form
             * @returns {form}
             * @fires reset
             */
            reset() {
                for (let widget of widgets.values()) {
                    widget.reset();
                }

                /**
                 * @event reset
                 */
                this.trigger('reset');

                return this;
            }
        };

        const form = componentFactory(api, defaults)
        // set the component's layout
            .setTemplate(formTpl)

            // auto render on init
            .on('init', function onFormInit() {
                // auto render on init
                _.defer(() => this.render(container));
            })

            // renders the component
            .on('render', function onFormRender() {
                const $element = this.getElement();
                const initConfig = this.getConfig();
                const initPromises = [];

                controls = {
                    $title: $element.find('.form-title'),
                    $form: $element.find('form'),
                    $widgets: $element.find('fieldset'),
                    $buttons: $element.find('.form-actions')
                };

                // prevent the default behavior of the form for submitting
                controls.$form.on('submit', e => {
                    e.preventDefault();
                    this.submit();
                });
                controls.$form.on('reset', e => {
                    e.preventDefault();
                    this.reset();
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
                    .then(() => {
                        if (_.size(initConfig.values)) {
                            this.setValues(initConfig.values);
                        }
                    })
                    .catch(err => {
                        this.trigger('error', err);
                    })
                    .then(() => {
                        /**
                         * @event ready
                         */
                        this.trigger('ready');
                    });
            })

            // take care of the disable state
            .on('disable', () => {
                disableComponents(widgets);
                disableComponents(buttons);
            })
            .on('enable', () => {
                enableComponents(widgets);
                enableComponents(buttons);
            })

            // cleanup the place
            .on('destroy', function onFormDestroy() {
                this.removeButtons();
                this.removeWidgets();
                controls = null;
            });

        // initialize the component with the provided config
        // defer the call to allow to listen to the init event
        _.defer(() => form.init(config));

        return form;
    }

    return formFactory;
});
