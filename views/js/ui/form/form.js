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


    var _defaults = {
        action : '',
        method : 'get',
        name : '',
        submit : {
            value : __('Save')
        }
    };


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
             */
            addField : function addField(fieldConfig) {
                var f = field(fieldConfig);

                f.render('.field-container');

                if (!this.fields) {
                    this.fields = {};
                }

                this.fields[fieldConfig.input.name] = field(fieldConfig);
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

        .init(config);
    };

    return formFactory;
});