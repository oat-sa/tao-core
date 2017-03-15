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
    var _ns = '.ui-form';


    /**
     * Default properties for form
     * @type {Object}
     */
    var _defaults = {
        fields : [],
        name : _ns,
        object : {}
    };


    /**
     * Defines a form object
     * @type {Object}
     */
    var form = {

        /**
         * Initializes the form
         * @param {Object} options
         * @returns {form}
         */
        init : function init(options) {
            // Set options
            _.defaults(this.options, options, _defaults);

            return this;
        },


        /**
         * Renders ui/form
         */
        render : function render() {
            if (this.element) {
                this.remove();
            }

            this.element = formTpl(this.options.object);

            _.each(this.fields, function(val) {
                var f = field(val);
                f.attach();
            });
        },


        /**
         * Attach ui/form to DOM
         * @param {String|jQueryElement|HTMLElement} to
         */
        attach : function attach(to) {
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

            $el.append($to);
        },


        /**
         * Remove ui/form
         */
        remove : function remove() {
            if (this.element) {
                $(this.element).remove();
            }
        }


        // TODO: addField method


        // TODO: create event binding/unbinding methods
    };


    /**
     * Creates a form instance
     * @param {Object} options
     * @returns {form}
     */
    var formFactory = function formFactory(options) {
        var f = _.clone(form);
        return f.init(options);
    };


    return formFactory;
});
