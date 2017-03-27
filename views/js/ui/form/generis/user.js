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
    "lodash",
    "ui/form/form"
], function(_, form) {
    "use strict";


    var _response = {
        "success": true,
        "data": {
            "class": {
                "uri": "http://www.tao.lu/Ontologies/generis.rdf#User",
                "label": "User"
            },
            "properties": [
                {
                    "uri": "http://www.w3.org/2000/01/rdf-schema#label",
                    "label": "Label",
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox",
                    "range": "http://www.w3.org/2000/01/rdf-schema#Literal",
                    "required": true
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#userFirstName",
                    "label": "First Name",
                    "range": "http://www.w3.org/2000/01/rdf-schema#Literal",
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#userLastName",
                    "label": "Last Name",
                    "range": "http://www.w3.org/2000/01/rdf-schema#Literal",
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#userMail",
                    "label": "Mail",
                    "range": "http://www.w3.org/2000/01/rdf-schema#Literal",
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#userDefLg",
                    "label": "Data Language",
                    "range": "http://www.tao.lu/Ontologies/TAO.rdf#Languages",
                    "required": true,
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#userUILg",
                    "label": "Interface Language",
                    "range": "http://www.tao.lu/Ontologies/TAO.rdf#Languages",
                    "required": true,
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#login",
                    "label": "Login",
                    "range": "http://www.w3.org/2000/01/rdf-schema#Literal",
                    "required": true,
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#userRoles",
                    "label": "Roles",
                    "range": "http://www.tao.lu/Ontologies/TAO.rdf#UserRole",
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#password",
                    "label": "Password",
                    "range": "http://www.w3.org/2000/01/rdf-schema#Literal",
                    "required": true,
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox"
                }, {
                    "uri": "http://www.tao.lu/Ontologies/generis.rdf#passwordConfirm",
                    "label": "Password Confirm",
                    "range": "http://www.w3.org/2000/01/rdf-schema#Literal",
                    "required": true,
                    "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox"
                }
            ],
            "values": {
                "http://www.tao.lu/Ontologies/TAO.rdf#Languages": [
                    {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langda-DK",
                        "label": "Danish"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langde-DE",
                        "label": "German"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langel-GR",
                        "label": "Greek"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langen-US",
                        "label": "English"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langes-ES",
                        "label": "Spanish"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langfr-FR",
                        "label": "French"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langis-IS",
                        "label": "Icelandic"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langit-IT",
                        "label": "Italian"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langja-JP",
                        "label": "Japanese"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langnl-NL",
                        "label": "Dutch"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langpt-PT",
                        "label": "Portuguese"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langsv-SE",
                        "label": "Swedish"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Languk-UA",
                        "label": "Ukrainian"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langzh-CN",
                        "label": "Simplified Chinese from China"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#Langzh-TW",
                        "label": "Traditional Chinese from Taiwan"
                    }
                ],
                "http://www.tao.lu/Ontologies/TAO.rdf#UserRole": [
                    {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole",
                        "label": "Global Manager"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAOItem.rdf#ItemAuthor",
                        "label": "Item Author"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#LockManagerRole",
                        "label": "Lock Manager"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAOProctor.rdf#ProctorRole",
                        "label": "Proctor"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#SysAdminRole",
                        "label": "System Administrator"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#TaskQueueManager",
                        "label": "Task Queue Manager"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAOItem.rdf#TestAuthor",
                        "label": "Test Author"
                    }, {
                        "uri": "http//www.tao.lu/Ontologies/TAO.rdf#DeliveryRole",
                        "label": "Test Taker"
                    }
                ]
            }
        },
        "version": "TAO-3.2.0-sprint46"
    };


    /**
     * Factory for ui/form/generis/user
     * @param {Object} config
     * @param {String} [config.action] - URI that processes form (default is `"/tao/users/add"`)
     * @param {String} config.container - Container to render form
     * @param {String} [config.method] - The HTTP method the browser uses to submit the form (default is `"get"`)
     */
    var userGenerisFormFactory = function userGenerisFormFactory(config) {
        var userForm;

        config = config || {};

        userForm = form({
            action : config.action || "/tao/users/add",
            name : _response.data.class.uri,
            method : config.method || "post"
        });

        userForm.render(config.container);

        // "uri": "http://www.tao.lu/Ontologies/generis.rdf#userUILg",
        // "label": "Interface Language",
        // "widget": "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox",
        // "range": "http://www.tao.lu/Ontologies/TAO.rdf#Languages",
        // "required": true
        _.each(_response.data.properties, function(property) {
            userForm.addField({
                input : {
                    name : property.uri,
                    options : _response.data.values[property.range],
                    value : property.value
                },
                label : property.label,
                required : property.required,
                type : property.widget
            });
        });

        return userForm;
    };


    return userGenerisFormFactory;
});