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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

/**
 * Common HTTP request wrapper to get data from TAO.
 * This suppose the endpoint to match the following criteria :
 *   - Restful endpoint
 *   - contentType : application/json
 *   - the responseBody:
 *      { success : true, data : [the results]}
 *      { success : false, errorCode: 412, errorMsg : 'Something went wrong' }
 *   - 204 for empty content
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/request',
    'core/promise'
], function($, _, __, coreRequest, Promise){
    'use strict';

    /**
     * A wrapper for the core module which requests content from a TAO endpoint
     *
     * @param {String} url - the endpoint full url
     * @param {Object} [data] - additional parameters
     * @param {String} [method = 'GET'] - the HTTP method
     * @param {Object} [headers] - the HTTP header
     * @param {Boolean} [background] - tells if the request should be done in the background, which in practice does not trigger the global handlers like ajaxStart or ajaxStop
     * @param {Boolean} [noToken = false] - to disable the token
     * @returns {Promise} that resolves with data or reject if something went wrong
     */
    return function request(url, data, method, headers, background, noToken) {

        return coreRequest({
            url: url,
            data: data,
            method: method,
            headers: headers,
            background: background,
            noToken: noToken
        })
        .then(function(response) {
            if (response && response.success) {
                return Promise.resolve(response.data);
            } else {
                return Promise.reject(response.data);
            }
        })
        .catch(function(error) {
            console.error(error);
        });

    };
});
