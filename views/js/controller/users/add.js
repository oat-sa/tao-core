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

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'module',
    'helpers',
    'users', //todo
    'ui/form/generis/user'
], function ($, _, module, helpers, users, userForm) {
    'use strict';


    /**
     * Controller object
     */
    var controller = {

        /**
         * Ran at the start... maybe dom ready?
         */
        start : function () {
            var config, form;

            config = module.config();
            form = userForm();

            //todo : why didn't module get the config?
            config.formContainer = '.form-container';
            form.renderTo(config.formContainer);

            // Hidden - User form sent
            form.form.addField({
                object : {
                    input : {
                        class : 'global',
                        name : 'user_form_sent',
                        value : '1'
                    },
                    type : 'hidden'
                }
            });

            // Hidden - Tao forms instance
            form.form.addField({
                object : {
                    input : {
                        name : 'tao.forms.instance',
                        value : '1'
                    },
                    type : 'hidden'
                }
            });

            // Class URI (hidden)
            form.form.addField({
                object : {
                    input : {
                        name : 'classUri',
                        value : 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_User'
                    },
                    type : 'hidden'
                }
            });

            // URI (hidden)
            form.form.addField({
                object : {
                    input : {
                        name : 'uri',
                        value : 'http_2_taoplatform_1_data_0_rdf_3_i1490197173849770'
                    },
                    type : 'hidden'
                }
            });

            // ID (hidden)
            form.form.addField({
                object : {
                    input : {
                        name : 'id',
                        value : 'http://taoplatform/data.rdf#i1490197173849770'
                    },
                    type : 'hidden'
                }
            });

            // On submit
            form.form.onSubmit(function(err, data) {
                if (err) {
                    throw Error('Error occurred on new user form submission.');
                }

                if (data.status === 201) {
                    //todo show success message and maybe a spinner
                    _.delay(function() {
                        window.location = helpers._url(
                            'index',
                            'main',
                            'tao',
                            {
                                structure : 'users',
                                ext : 'tao',
                                section : 'list_users'
                            }
                        );
                    }, 1000);
                } else if (400 <= data.status && data.status < 500) {
                    _.each(data.errors, function(error) {
                        var field = form.form.fields[error.field];
                        if (field) {
                            field.showError(error.message);
                        } else {
                            //todo flash error [that isn't associated with a field]
                        }
                    });
                } else {
                    //todo flash 500 errors
                }
            });
        }

    };


    /**
     * controller/users/add
     * @exports {Object} controller
     */
    return controller;

    // /**
    //  * The user add controller
    //  * @exports controller/users/add
    //  */
    // return {
    //     start : function() {
    //         var conf = module.config();
    //         var url  = helpers._url('checkLogin', 'Users', 'tao');

    //         users.checkLogin(conf.loginId, url);

    //         if (conf.exit === true) {
    //             setTimeout(function() {
    //                 //TODO would be better to clean up the form and switch the section
    //                 window.location = helpers._url(
    //                     'index',
    //                     'Main',
    //                     'tao',
    //                     {
    //                         structure: 'users',
    //                         ext : 'tao',
    //                         section : 'list_users'
    //                     }
    //                 );
    //             }, 1000);
    //         }
    //     }
    // };
});
