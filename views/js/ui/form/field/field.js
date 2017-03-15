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
    'tpl!ui/form/field/tpl/text',
    'tpl!ui/form/field/tpl/password',
    'tpl!ui/form/field/tpl/select'
], function ($, _, __, textTpl, passwordTpl, selectTpl) {
    'use strict';


    /**
     * Default namespace/class
     * @type {String}
     */
    var _ns = '.ui-form-field';


    /**
     * Default properties for ui/form/field
     * @type {Object}
     */
    var _defaults = {
        container : _ns,
        object : {
            input : {
                name : 'input'
            },
            label : 'Label',
            required : false
        },
        type : 'text'
    };


    /**
     * Default templates
     * @type {Object}
     */
    var _templates = {
        default : textTpl,
        password : passwordTpl,
        select : selectTpl,
        text : textTpl
    };


    /**
     * Defines a ui/form/field object
     * @type {Object}
     */
    var field = {

        /**
         * Initializes the ui/form/field
         * @param {Object} options
         * @param {Array|Function} [validators]
         * @returns {field}
         */
        init : function init(options, validators) {
            // Set options
            _.defaults(this.options, options, _defaults);

            // Set template
            this.template = _templates[this.options.type] || _templates.default;

            // Set form
            // TODO: How to handle if form doesn't exist
            this.form = this.options.form;

            // Set fields' container
            this.container = this.options.container;

            // Set validations
            this.validators = Array.isArray(validators) ? validators :
                _.isFunction(validators) ? [validators] : [];

            return this;
        },


        /**
         * Renders ui/form/field
         */
        render : function render() {
            if (this.element) {
                this.remove();
            }

            this.element = this.template(this.options.object);
        },


        /**
         * Attach ui/form/field to ui/form
         */
        attach : function attach() {
            var $el, $to;

            if (!this.element) {
                this.render();
            }

            if (!this.form) {
                // TODO: Should I add container div to form if doesn't exist
                return false;
            }

            $el = $(this.element);
            $to = $(this.container, this.form);

            $el.append($to);
        },


        /**
         * Disable ui/form/field
         */
        disable : function disable() {
            if (this.element) {
                $(this.element).prop('disabled', function(i, v) {
                    return !v;
                });
            }
        },


        /**
         * Remove ui/form/field from ui/form
         */
        remove : function remove() {
            if (this.element) {
                $(this.element).remove();
            }
        }
    };


    /**
     * Creates a new ui/form/field instance
     * @param {Object} options
     * @returns {field}
     */
    var fieldFactory = function fieldFactory(options) {
        var f = _.clone(field);
        return f.init(options);
    };


    return fieldFactory;
});
