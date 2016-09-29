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
 * This suppose the endpoint to match the following crierrias :
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
    'core/promise'
], function($, _,  Promise){
    'use strict';

    /**
     * Request content from a TAO endpoint
     * @param {String} url - the endpoint full url
     * @param {Object} [data] - additional parameters
     * @param {String} [method = 'GET'] - the HTTP method
     * @returns {Promise} that resolves with data or reject if something went wrong
     */
    return function request(url, data, method){
        return new Promise(function(resolve, reject){

            if(_.isEmpty(url)){
                return reject(new TypeError('At least give a URL...'));
            }

            $.ajax({
                url: url,
                type: method || 'GET',
                dataType: 'json',
                data : data
            })
            .done(function(response, status, xhr){
                if(_.isPlainObject(response)){
                    //there's some data
                    if(response.success){
                        return resolve(response.data);
                    }

                    //the server has handled the error
                    if(response.message){
                        return reject(new Error(response.message));
                    }
                    return reject(new Error(response.errorCode + ' : ' + (response.errorMsg || response.errorMessage)));

                } else if (xhr.status === 204){
                    //no content, so resolve with empty data.
                    return resolve();
                }
                return reject(new Error('No response'));
            })
            .fail(function(xhr){
                return reject(new Error(xhr.status + ' : ' + xhr.statusText));
            });
        });
    };
});
