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
    'lodash',
    'ui/form/form'
], function(_, form) {
    'use strict';


    /**
     * Defines a new user form object
     * @type {Object}
     */
    var newUserForm = {

        /**
         * Fields property
         * @type {Array}
         */
        fields : [
            {
                label : 'Label',
                rdfs : 'http://www.w3.org/2000/01/rdf-schema#label',
                required : true,
                type : 'text',
                value : ''
            },
            {
                label : 'First Name',
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#userFirstName',
                type : 'text',
                value : ''
            },
            {
                label : 'Last Name',
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#userLastName',
                type : 'text',
                value : ''
            },
            {
                label : 'Mail',
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#userMail',
                type : 'text',
                value : ''
            },
            {
                label : 'Data Language',
                options : [
                    { value : ' ', label : ' ' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langda-DK', label : 'Danish' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langde-DE', label : 'German' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langel-GR', label : 'Greek' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langen-US', label : 'English' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langes-ES', label : 'Spanish' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langfr-FR', label : 'French' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langis-IS', label : 'Icelandic' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langit-IT', label : 'Italian' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langja-JP', label : 'Japanese' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langnl-NL', label : 'Dutch' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langpt-PT', label : 'Portuguese' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langsv-SE', label : 'Swedish' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Languk-UA', label : 'Ukrainian' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-CN', label : 'Simplified Chinese from China' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-TW', label : 'Traditional Chinese from Taiwan' }
                ],
                range : 'http://www.tao.lu/Ontologies/TAO.rdf#Languages',
                required : true,
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#userDefLg',
                type : 'select',
                value : ''
            },
            {
                label : 'Interface Language',
                options : [
                    { value : ' ', label : ' ' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langda-DK', label : 'Danish' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langde-DE', label : 'German' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langel-GR', label : 'Greek' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langen-US', label : 'English' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langes-ES', label : 'Spanish' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langfr-FR', label : 'French' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langis-IS', label : 'Icelandic' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langit-IT', label : 'Italian' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langja-JP', label : 'Japanese' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langnl-NL', label : 'Dutch' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langpt-PT', label : 'Portuguese' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langsv-SE', label : 'Swedish' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Languk-UA', label : 'Ukrainian' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-CN', label : 'Simplified Chinese from China' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langzh-TW', label : 'Traditional Chinese from Taiwan' }
                ],
                range : 'http://www.tao.lu/Ontologies/TAO.rdf#Languages',
                required : true,
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#userUILg',
                type : 'select',
                value : ''
            },
            {
                label : 'Login',
                required : true,
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#login',
                type : 'text',
                value : ''
            },
            {
                label : 'Roles',
                options : [
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_GlobalManagerRole',  name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_0', label : 'Global Manager' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAOItem_0_rdf_3_ItemAuthor',     name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_1', label : 'Item Author' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_LockManagerRole',    name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_2', label : 'Lock Manager' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAOProctor_0_rdf_3_ProctorRole', name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_3', label : 'Proctor' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_SysAdminRole',       name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_4', label : 'System Administrator' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_TaskQueueManager',   name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_5', label : 'Task Queue Manager' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAOItem_0_rdf_3_TestAuthor',     name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_6', label : 'Test Author' },
                    { value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_DeliveryRole',       name : 'http_2_www_0_tao_0_lu_1_Ontologies_1_generis_0_rdf_3_userRoles_7', label : 'Test Taker' }
                ],
                range : 'http://www.tao.lu/Ontologies/generis.rdf#UserRole',
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#userRoles',
                type : 'checkbox_list',
                value : ''
            },
            {
                label : 'Password',
                required : true,
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#password',
                type : 'password_confirm',
                value : ''
            },
            {
                inaccessible : true,
                label : 'Time Zone',
                range : null, //TODO
                rdfs : 'http://www.tao.lu/Ontologies/generis.rdf#userTimezone',
                type : 'select',
                value : ''
            }
        ],


        /**
         * Form property
         * @type {ui/form}
         */
        form : null,

        /**
         * Initializes the new user form
         * @param {Object} [options]
         */
        init : function(options) {
            _.merge(this.options, options || {});

            this.form = form({
                object : {
                    action : '/tao/users/add',
                    name : 'user_form',
                    method : 'post'
                }
            });

            return this;
        },


        /**
         * Options property
         * @type {Object}
         */
        options : {},


        /**
         * Renders ui/form/generis/user
         * @param {jQuery|HTMLElement|String} to
         */
        renderTo : function(to) {
            var self = this;

            if (!to) {
                return false;
            }

            this.form.attachTo(to);

            _.each(this.fields, function(field) {
                if (field.inaccessible) {
                    return;
                }

                self.form.addField({
                    object : {
                        input : {
                            name : field.rdfs,
                            options : field.options
                        },
                        label : field.label,
                        required : field.required,
                        type : field.type,
                        value : field.value //TODO get unique label for user label
                    }
                });
            });

            return this;
        }
    };


    /**
     * Create a new user form instance
     * @param {Object} [options]
     * @returns {newUserForm}
     */
    var newUserFormFactory = function newUserFormFactory(options) {
        var f = _.cloneDeep(newUserForm);
        return f.init(options);
    };


    return newUserFormFactory;
});