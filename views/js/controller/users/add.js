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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'module',
    'helpers',
    'users'
], function(
    $,
    module,
    helpers,
    users
) {
    'use strict';

    /**
     * The user add controller
     * @exports controller/users/add
     */
    return {
        start: function() {
            var conf = module.config();
            var url  = helpers._url('checkLogin', 'Users', 'tao');
            users.checkLogin(conf.loginId, url);

            if(conf.exit === true){

                setTimeout(function(){
                    //TODO would be better to clean up the form and switch the section
                    window.location = helpers._url('index', 'Main', 'tao', {structure: 'users', ext : 'tao', section : 'list_users'});
                }, 1000);
            }
        }
    };
});
