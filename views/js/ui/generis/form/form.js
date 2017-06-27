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
     * @param {Object} [options.data]
     * @param {String} [config.form.action = '#']
     * @param {String} [config.form.method = 'get']
     * @param {String} [config.submit.text = 'Save']
     * @param {String} [config.title = 'Generis Form']
     */
    function factory(options, config) {
        var form;

        options = options || {};
        options.data = options.data || {};

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
                var $fieldset = this.getElement().find('form > fieldset');
                var widget = _widgetFactories[widgetOptions.widget]({}, widgetOptions);

                this.widgets.push(widget);

                if (this.is('rendered')) {
                    widget.render($fieldset);
                } else {
                    this.on('render.' + widget.config.uri, function () {
                        widget.render($fieldset);
                        this.off('render.' + widget.config.uri);
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
                this.errors = _.map(this.widgets, function (widget) {
                    widget.validate();
                    return {
                        uri: widget.config.uri,
                        errors: widget.errors
                    };
                });

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
            form: {
                action: '#',
                method: 'get'
            },
            submit: {
                text: 'Save'
            },
            title: 'Generis Form'
        })
        .setTemplate(tpl)
        .init(config);

        form.class = options.class || {};
        form.errors = [];
        form.widgets = [];

        // Add widgets to form
        _.each(options.data.properties || [], function (property) {
            if (property.range && typeof property.range === 'string') {
                property.range = options.data.values[property.range];
            }
            form.addWidget(property);
        });

        return form;
    }

    return factory;
});