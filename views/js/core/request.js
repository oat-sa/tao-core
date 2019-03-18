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
 *   - contentType : application/json; charset=UTF-8
 *   - headers : contains 'X-CSRF-Token' value when needed
 *   - the responseBody:
 *      { success : true, data : [the results]}
 *      { success : false, data : {Exception}, message : 'Something went wrong' }
 *   - 204 for empty content
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'context',
    'core/promise',
    'core/promiseQueue',
    'core/tokenHandler',
    'core/logger'
], function($, _, __, context, Promise, promiseQueue, tokenHandlerFactory, loggerFactory) {
    'use strict';

    var tokenHandler = tokenHandlerFactory();

    var queue = promiseQueue();

    var logger = loggerFactory('core/request');

    /**
     * Create a new error based on the given response
     * @param {Object} response - the server body response as plain object
     * @param {String} fallbackMessage - the error message in case the response isn't correct
     * @param {Number} httpCode - the response HTTP code
     * @returns {Error} the new error
     */
    var createError = function createError(response, fallbackMessage, httpCode) {
        var err;
        if (response && response.errorCode) {
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
     * @param {Object} options
     * @param {String} options.url - the endpoint full url
     * @param {String} [options.method = 'GET'] - the HTTP method
     * @param {Object} [options.data] - additional parameters (if method is 'POST')
     * @param {Object} [options.headers] - the HTTP headers
     * @param {String} [options.contentType] - will usually be 'json'
     * @param {Boolean} [options.noToken = false] - if true, disables the token requirement
     * @param {Boolean} [options.background] - if true, the request should be done in the background, which in practice does not trigger the global handlers like ajaxStart or ajaxStop
     * @param {Boolean} [options.sequential] - if true, the request must join a queue to be run sequentially
     * @param {Number}  [options.timeout] - timeout in seconds for the AJAX request
     * @returns {Promise} resolves with response, or reject if something went wrong
     */
    return function request(options) {

        if (_.isEmpty(options.url)) {
            throw new TypeError('At least give a URL...');
        }

        /**
         * Function wrapper which allows the contents to be run now, or added to a queue
         * @returns {Promise} resolves with response, or rejects if something went wrong
         */
        function runRequest() {

            /**
             * Fetches a security token and appends it to headers, if required
             * @returns {Promise<Object>} - resolves with headers object
             */
            var computeHeaders = function computeHeaders() {
                var headers = _.extend({}, options.headers);
                if (!options.noToken) {
                    return tokenHandler.getToken().then(function(token) {
                        headers['X-CSRF-Token'] = token || 'none';
                        return headers;
                    });
                }
                return Promise.resolve(headers);
            };

            /**
             * Extracts returned security token from headers and adds it to store
             * @param {Object} xhr
             * @returns {Promise} - resolves when done
             */
            var setTokenFromXhr = function setTokenFromXhr(xhr) {
                var token;

                if (_.isFunction(xhr.getResponseHeader)) {
                    token = xhr.getResponseHeader('X-CSRF-Token');
                    logger.debug('received X-CSRF-Token header %s', token);

                    if (token) {
                        return tokenHandler.setToken(token);
                    }
                }
                return Promise.resolve();
            };

            return computeHeaders().then(function(customHeaders) {
                return new Promise(function(resolve, reject) {
                    var noop;
                    $.ajax({
                        url: options.url,
                        type: options.method || 'GET',
                        dataType: 'json',
                        headers: customHeaders,
                        data: options.data,
                        async: true,
                        timeout: options.timeout * 1000 || context.timeout * 1000 || 0,
                        contentType: options.contentType || noop,
                        beforeSend: function() {
                            logger.debug('sending X-CSRF-Token header %s', customHeaders && customHeaders['X-CSRF-Token']);
                        },
                        global: !options.background //TODO fix this with TT-260
                    })
                    .done(function(response, status, xhr) {
                        setTokenFromXhr(xhr)
                            .then(function() {
                                if (xhr.status === 204 || (response && response.errorCode === 204) || status === 'nocontent') {
                                    // no content, so resolve with empty data.
                                    resolve();
                                }

                                // handle case where token expired or invalid
                                if (xhr.status === 401 || (response && response.errorCode === 401)) {
                                    reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status));
                                }

                                if (response && response.success === true) {
                                    // there's some data
                                    resolve(response);
                                }

                                //the server has handled the error
                                reject(createError(response, __('The server has sent an empty response'), xhr.status));
                            })
                            .catch(function(error) {
                                logger.error(error);
                                reject(createError(response, error, xhr.status));
                            });
                    })
                    .fail(function(xhr, textStatus, errorThrown) {
                        var response;
                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch (parseErr) {
                            response = xhr.responseText;
                        }

                        response = _.defaults(response, {
                            success: false,
                            source: 'network',
                            cause : options.url,
                            purpose: 'proxy',
                            context: this,
                            code: xhr.status,
                            sent: xhr.readyState > 0,
                            type: 'error',
                            message: errorThrown || __('An error occurred!')
                        });

                        setTokenFromXhr(xhr)
                            .then(function () {
                                reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status));
                            })
                            .catch(function(error) {
                                logger.error(error);
                                reject(createError(response, error, xhr.status));
                            });
                    });
                });
            });
        }

        // Decide how to launch the request based on certain params:
        return tokenHandler.getQueueLength()
            .then(function(queueLength) {
                if (options.noToken === true) {
                    // no token protection, run the request
                    return runRequest();
                }
                else if (options.sequential || queueLength === 1) {
                    // limited tokens, sequential queue must be used
                    return queue.serie(runRequest);
                }
                else {
                    // tokens ready
                    return runRequest();
                }
            });
    };
});
