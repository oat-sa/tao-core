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
    'ui/component',
    'ui/form/field/field',
    'tpl!ui/form/form',
    'css!ui/form/form'
], function ($, _, __, component, field, formTpl) {
    'use strict';


    /**
     * Property for ui/form configuration defaults
     */
    var _defaults = {
        action : '',
        method : 'get',
        name : '',
        submit : {
            value : __('Save')
        }
    };


    /**
     * Property for location of field container
     */
    var _fieldContainer = '.field-container';


    /**
     * Factory for ui/form component
     * @param {Object} [config]
     * @param {String} [config.action] - The form action attribute (default is `''`)
     * @param {String} [config.method] - The form method attribute (default is `'get'`)
     * @param {String} [config.name] - The name of the form (default is `''`)
     * @param {String} [config.submit.value] - The submit button text (default is `'Save'`)
     */
    var formFactory = function formFactory(config) {
        return component({
            /**
             * Add ui/form/field to ui/form
             * @param {Object} fieldConfig - Config options for ui/form/field
             * @returns {Object} - The created ui/form/field
             */
            addField : function addField(fieldConfig) {
                var newField = field(fieldConfig);

                if (!this.fields) {
                    this.fields = [];
                }

                // Ensure fields are unique by field name
                _.remove(this.fields, function(existingField) {
                    return existingField.config.input.name === newField.config.input.name;
                });

                // Insert into array to preserve order
                this.fields.push(newField);

                return newField;
            },

            /**
             * Retrieve a ui/form/field from ui/form
             * @param {String} name - Name of ui/form/field
             * @returns {Object} - The matched ui/form/field, else undefined
             */
            getField : function getField(name) {
                return _.find(this.fields, function(existingField) {
                    return existingField.config.input.name === name;
                });
            },

            /**
             * Handle form submission
             * @param {Function} callback - Function called on return of form submission
             */
            onSubmit : function onSubmit(callback) {
                var $form = this.getElement().find('form');

                $form.on('submit', function(e) {
                    e.preventDefault();

                    //todo add spinner & disable form

                    //todo call action
                    callback(null, {
                        success : true,
                        errors : [],
                        data : []
                    });

                    return false;
                });
            }
        }, _defaults)

        .setTemplate(formTpl)

        .on('render', function() {
            _.each(this.fields, function(existingField) {
                existingField.render(_fieldContainer);
            });
        })

        .init(config);
    };


    /**
     * @exports ui/form
     */
    return formFactory;
});