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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

//@see http://forge.taotesting.com/projects/tao/wiki/Front_js
define(function(){
    'use strict';

    return {
        'Main': {
            'actions' : {
                'index' : 'controller/main',
                'entry' : 'controller/entry',
                'getSectionActions' : 'controller/main/actions',
                'getSectionTrees' : 'controller/main/trees',
                'login' : 'controller/login'
            }
        },
        'PasswordRecovery': {
            'actions' : {
                'index' : 'controller/passwordRecovery'
            },
        },
        'Lock': {
            'actions' : {
                'locked' : 'controller/Lock/locked'
            }
        },
        'ExtensionsManager' : {
            'actions' : {
                'index' : 'controller/settings/extensionManager'
            }
        },
        'Users' : {
            'deps' : 'controller/users/disable-edit',
            'actions' : {
                'index' : 'controller/users/index',
                'add'   : 'controller/users/add'
            }
        },
        'Security' : {
            'actions' : {
                'index' : 'controller/security/cspHeaderForm'
            }
        },
        'WebHooks' : {
            'css': 'auth-selector',
            'actions' : {
                'addInstanceForm': 'controller/WebHooks/edit',
                'editInstance': 'controller/WebHooks/edit'
            }
        }
    };
});
