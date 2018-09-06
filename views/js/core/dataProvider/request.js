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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
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
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/promise'
], function($, _, __, Promise){
    'use strict';

    /**
     * Create a new error based on the given response
     * @param {Object} response - the server body response as plain object
     * @param {String} fallbackMessage - the error message in case the response isn't correct
     * @param {Number} httpCode - the response HTTP code
     * @returns {Error} the new error
     */
    var createError = function createError(response, fallbackMessage, httpCode){
        var err;
        if(response && response.errorCode){
            err = new Error(response.errorCode + ' : ' + (response.errorMsg || response.errorMessage || response.error));
        } else {
            err = new Error(fallbackMessage);
        }
        err.response = response;
        if (httpCode) {
            err.code = httpCode;
        }
        return err;
    };

    /**
     * Request content from a TAO endpoint
     * @param {String} url - the endpoint full url
     * @param {Object} [data] - additional parameters
     * @param {String} [method = 'GET'] - the HTTP method
     * @param {Object} [headers] - the HTTP header
     * @param {Boolean} [background] - tells if the request should be done in the background, which in practice does not trigger the global handlers like ajaxStart or ajaxStop
     * @returns {Promise} that resolves with data or reject if something went wrong
     */
    return function request(url, data, method, headers, background){
        return new Promise(function(resolve, reject){

            if(_.isEmpty(url)){
                return reject(new TypeError('At least give a URL...'));
            }

            $.ajax({
                url: url,
                type: method || 'GET',
                dataType: 'json',
                headers: headers,
                data : data,
                global : !background//TODO fix this with TT-260
            })
            .done(function(response, status, xhr){
                if (xhr.status === 204 || (response && response.errorCode === 204)){
                    //no content, so resolve with empty data.
                    return resolve();
                }

                if(response && response.success === true){
                    //there's some data
                    return resolve(response.data);
                }

                //the server has handled the error
                return reject(createError(response,  __('The server has sent an empty response'), xhr.status));
            })
            .fail(function(xhr){
                var response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch(parseErr){
                    response = xhr.responseText;
                }

                return reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status));
            });
        });
    };
});
