/*
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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Notify user about logout
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */
define(['jquery', 'lodash', 'i18n', 'util/url', 'ui/dialog/alert'],
    function ($, _, __, url, alert) {
        'use strict';

        var defaults = {
            message: __('You have been logged out. Please login again'),
            redirectUrl: url.route('logout', 'Main', 'tao')
        };

        /**
         * @param {Object} options
         * @param {String} [options.message] - Message to be displayed before redirect
         * @param {String} [options.redirectUrl] - Target URI
         */
        return function logoutEvent(options) {
            options = _.defaults(options || {}, defaults);
            alert(options.message, function () {
                window.location = options.redirectUrl;
            });
        };
    });


