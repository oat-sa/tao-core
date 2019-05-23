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
 *   - 403 if CSRF token validation fails
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'context',
    'core/promise',
    'core/promiseQueue',
    'core/tokenHandler',
    'core/logger'
], function($, _, __, module, context, Promise, promiseQueue, tokenHandlerFactory, loggerFactory) {
    'use strict';

    var tokenHeaderName = 'X-CSRF-Token';

    var tokenHandler = tokenHandlerFactory();

    var queue = promiseQueue();

    var logger = loggerFactory('core/request');

    /**
     * Create a new error based on the given response
     * @param {Object} response - the server body response as plain object
     * @param {String} fallbackMessage - the error message in case the response isn't correct
     * @param {Number} httpCode - the response HTTP code
     * @param {Boolean} httpSent - the sent status
     * @returns {Error} the new error
     */
    var createError = function createError(response, fallbackMessage, httpCode, httpSent) {
        var err;
        if (response && response.errorCode) {
            err = new Error(response.errorCode + ' : ' + (response.errorMsg || response.errorMessage || response.error));
        } else {
            err = new Error(fallbackMessage);
        }
        err.response = response;
        err.sent = httpSent;

        if (httpCode) {
            err.code = httpCode;
        }
        return err;
    };

    /**
     * Request content from a TAO endpoint
     * @param {Object} options
     * @param {String} options.url - the endpoint full url
     * @param {String} [options.method='GET'] - the HTTP method
     * @param {Object} [options.data] - additional parameters (if method is 'POST')
     * @param {Object} [options.headers] - the HTTP headers
     * @param {String} [options.contentType] - what kind of data we're sending - usually 'json'
     * @param {String} [options.dataType] - what kind of data expected in response
     * @param {Boolean} [options.noToken=false] - by default, a token is always sent. If noToken=true, disables the token requirement
     * @param {Boolean} [options.background] - if true, the request should be done in the background, which in practice does not trigger the global handlers like ajaxStart or ajaxStop
     * @param {Boolean} [options.sequential] - if true, the request must join a queue to be run sequentially
     * @param {Number}  [options.timeout] - timeout in seconds for the AJAX request
     * @returns {Promise} resolves with response, or reject if something went wrong
     */
    return function request(options) {
        // Allow external config to override user option
        if (module.config().noToken) {
            options.noToken = true;
        }

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
                        headers[tokenHeaderName] = token || 'none';
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
                    token = xhr.getResponseHeader(tokenHeaderName);
                    logger.debug('received %s header %s', tokenHeaderName, token);

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
                        method: options.method || 'GET',
                        headers: customHeaders,
                        data: options.data,
                        contentType: options.contentType || noop,
                        dataType: options.dataType || 'json',
                        async: true,
                        timeout: options.timeout * 1000 || context.timeout * 1000 || 0,
                        beforeSend: function() {
                            if (!_.isEmpty(customHeaders)) {
                                logger.debug('sending %s header %s', tokenHeaderName, customHeaders && customHeaders[tokenHeaderName]);
                            }
                        },
                        global: !options.background //TODO fix this with TT-260
                    })
                    .done(function(response, status, xhr) {
                        setTokenFromXhr(xhr)
                            .then(function() {
                                if (xhr.status === 204 || (response && response.errorCode === 204) || status === 'nocontent') {
                                    // no content, so resolve with empty data.
                                    return resolve();
                                }

                                // handle case where token expired or invalid
                                if (xhr.status === 403 || (response && response.errorCode === 403)) {
                                    return reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status, xhr.readyState > 0));
                                }

                                if (response && response.success === true) {
                                    // there's some data
                                    return resolve(response);
                                }

                                //the server has handled the error
                                reject(createError(response, __('The server has sent an empty response'), xhr.status, xhr.readyState > 0));
                            })
                            .catch(function(error) {
                                logger.error(error);
                                reject(createError(response, error, xhr.status, xhr.readyState > 0));
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
                                reject(createError(response, xhr.status + ' : ' + xhr.statusText, xhr.status, xhr.readyState > 0));
                            })
                            .catch(function(error) {
                                logger.error(error);
                                reject(createError(response, error, xhr.status, xhr.readyState > 0));
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
