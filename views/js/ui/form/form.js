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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'urlParser',
    'core/eventifier',
    'ui/form/field/field',
    'tpl!ui/form/form',
    'css!ui/form/form'
], function ($, _, __, UrlParser, eventifier, field, formTpl) {
    'use strict';


    /**
     * Default namespace/class
     * @type {String}
     */
    var _ns = 'ui-form';


    /**
     * Default properties for form
     * @type {Object}
     */
    var _defaults = {
        object : {
            name : _ns,
            submit : {
                name : 'submit-' + _ns,
                value : 'Save'
            }
        }
    };


    /**
     * Defines a form object
     * @type {Object}
     */
    var form = {

        /**
         * Element property
         * @type {HTMLElement}
         */
        element : null,


        /**
         * Fields property
         * @type {Array}
         */
        fields : [],


        /**
         * Options property
         * @type {Object}
         */
        options : {},


        /**
         * Initializes the form
         * @param {Object} [options]
         * @returns {form}
         */
        init : function init(options) {
            // Set options
            _.merge(this.options, _defaults, options || {});

            return this;
        },


        /**
         * Renders ui/form
         */
        render : function render() {
            if (this.element) {
                this.remove();
            }

            this.element = $(formTpl(this.options.object));

            _.each(this.fields, function(val) {
                var f = field(val);
                f.attach();
            });

            return this;
        },


        /**
         * Attach ui/form to DOM
         * @param {String|jQueryElement|HTMLElement} to
         */
        // TODO: should jQuery's appendTo be preferred?
        attachTo : function attach(to) {
            var $el, $to;

            if (!this.element) {
                this.render();
            }

            if (!to) {
                // TODO: how to handle this?
                return false;
            }

            $el = $(this.element);
            $to = $(to);

            $el.appendTo($to);

            return this;
        },


        /**
         * Remove ui/form
         */
        remove : function remove() {
            if (this.element) {
                $(this.element).remove();
            }

            return this;
        },


        /**
         * Add ui/form/field to ui/form
         * @param {Object} options
         */
        addField : function addField(options) {
            if (!this.element) {
                // TODO: How to handle this?
                return false;
            }

            options = options || {};
            options.container = '.field-container';
            options.form = this.element;

            this.fields[options.object.input.name] = field(options).attachTo();

            return this;
        },


        /**
         * Handles form submission
         * @param {Function} callback
         */
        onSubmit : function onSubmit(callback) {
            if (!this.element) {
                return false;
            }

            $('form', this.element)
            .on('submit', function(e) {
                e.preventDefault();

                //todo: call tao/users@create
                callback(null, {
                    success: false,
                    status: 400,
                    errors: [
                        {
                            field : 'http://www.tao.lu/Ontologies/generis.rdf#password',
                            message : 'This field is too short (minimum 4)'
                        },
                        {
                            field : 'http://www.tao.lu/Ontologies/generis.rdf#password',
                            message : 'Must include at least one letter'
                        }
                    ],
                    data: []
                });

                return false;
            });

            return this;
        },


        // TODO: create event binding/unbinding methods
    };


    /**
     * Creates a form instance
     * @param {Object} options
     * @returns {form}
     */
    var formFactory = function formFactory(options) {
        var f = _.cloneDeep(form);
        return f.init(options);
    };


    return formFactory;
});
