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
 */

/**
 * The controller dedicated to the login page.
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'layout/loading-bar',
    'layout/version-warning',
    'ui/login/login'
], function ($, _, __, module, loadingBar, versionWarning, loginComponent) {
    'use strict';

    var _defaults = {
        disableAutocomplete: false,
        enablePasswordReveal: false,
        message: {
            error: ''
        }
    };

    /**
     * The login controller
     */
    return {

        /**
         * Controller entry point
         */
        start: function start(){

            var conf = _.defaults({}, module.config(), _defaults);
            var login = loginComponent($('#login-box-inner-container'), conf);

            login.on('init', function() {
                loadingBar.start();
            }).after('render', function() {
                versionWarning.init();

                loadingBar.stop();
            }).on('submit.login', function() {
                loadingBar.start();
            });
        }
    };
});
