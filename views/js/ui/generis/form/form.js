define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
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
    checkBoxFactory,
    comboBoxFactory,
    hiddenBoxFactory,
    textBoxFactory,
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
             * @returns {this}
             */
            addWidget: function (widgetOptions) {
                var widget = _widgetFactories[widgetOptions.widget]({}, widgetOptions);

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
             * Remove a widget/field from form
             * @param {String} widgetUri
             * @returns {this}
             */
            removeWidget: function (widgetUri) {
                _.remove(this.widgets, function (widget) {
                    if (widget.config.uri === widgetUri) {
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
            validate: function () {
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
            serializeArray: function () {
                return _.map(this.widgets, function (widget) {
                    return widget.serialize();
                });
            }
        }, {
            formAction: '#',
            formMethod: 'get',
            submitText: 'Save',
            title: 'Generis Form'
        })
        .setTemplate(tpl)
        .init(config)
        .on('render', function () {
            var $form = this.getElement().find('form');
            var self = this;

            $form.on('submit', function (e) {
                e.preventDefault();
                self.trigger('submit');
                return false;
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