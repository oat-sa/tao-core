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
    'core/promise',
    'core/promiseQueue',
    'core/tokenHandler',
    'ui/feedback'
], function($, _, __, Promise, promiseQueue, tokenHandlerFactory, feedback){
    'use strict';

    var tokenHandler = tokenHandlerFactory();

    var queue = promiseQueue();

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
     * @param {Boolean} [noToken = false] - to disable the token
     * @param {Object} [ajaxParams] - extra parameters for the internal $.ajax() call
     * @param {Boolean} [returnXhr = false] - if false, returns response.data, otherwise response (for TR API)
     * @returns {Promise} that resolves with data or reject if something went wrong
     */
    return function request(url, data, method, headers, background, noToken, ajaxParams, returnXhr){
    // TODO: convert function signature to options object

        // Function wrapper so the contents can be run now or added to a queue
        var runRequest = function runRequest() {
            return new Promise(function(resolve, reject){

                // Function which actually makes the request
                var runAjax = function runAjax(customHeaders) {
                    return $.ajax(_.defaults({
                        url: url,
                        type: method || 'GET',
                        dataType: 'json',
                        headers: customHeaders,
                        data : data,
                        beforeSend: function() {
                            console.log('sending header token', customHeaders && customHeaders['X-CSRF-Token']);
                        },
                        global : !background//TODO fix this with TT-260
                    }, ajaxParams))
                    .done(function(response, status, xhr){
                        //console.log('response', response);
                        //console.log('response full header', xhr.getAllResponseHeaders());
                        console.log('response header specific', xhr.getResponseHeader('X-CSRF-Token'));
                        console.log('dataProvider/request received token', response.token);
                        // store the response token for the next request
                        // store with client timestamp so we can expire against client time
                        tokenHandler.setToken({
                            value: xhr.getResponseHeader('X-CSRF-Token') || response.token || 'someToken' + ('' + Date.now()).slice(9),
                            receivedAt: Date.now()
                        });

                        if (xhr.status === 204 || (response && response.errorCode === 204)){
                            //no content, so resolve with empty data.
                            return resolve();
                        }

                        // handle case where token expired or invalid
                        if (xhr.status === 401 || (response && response.errorCode === 401)){
                            feedback().error(__('Unauthorised request'));
                            reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status));
                        }

                        if(response && response.success === true){
                            //there's some data
                            return resolve(returnXhr ? response : response.data);   // response.data for non-TR?
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
                };

                if(_.isEmpty(url)){
                    return reject(new TypeError('At least give a URL...'));
                }

                if (!noToken) {
                    // we must get a token from the store
                    tokenHandler.getToken()
                        .then(function(token) {
                            if (token && token.value) {
                                headers = _.extend({}, headers, {
                                    'X-Requested-With': 'XMLHttpRequest', // already present in jQuery.ajax?
                                    'X-CSRF-Token': token.value || 'none',
                                    'X-Auth-Token': token.value || 'none'  // header for current TR only
                                });
                            }
                            else {
                                headers = _.extend({}, headers);
                            }
                            return headers;
                        })
                        .then(function(customHeaders) {
                            runAjax(customHeaders);
                        });
                }
                else {
                    runAjax(headers);
                }
            });
        };

        if (noToken === true) {
            //no token protection, run the request
            return runRequest();
        }
        else if (tokenHandler.getQueueLength() === 1) {
            // limited tokens, sequential queue must be used
            return queue.serie(runRequest);
        }
        else {
            // tokens ready
            return runRequest();
        }

    };
});
