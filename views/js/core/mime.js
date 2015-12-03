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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define(['jquery', 'core/promise'], function ($, Promise) {
    'use strict';

    /**
     * Gets the MIME type of a resource.
     *
     * @param {String} url - The URL of the resource to get type of
     * @param {Function} [callback] - An optional function called when the response is received.
     *                                This callback must accept 2 arguments:
     *                                the first is the potential error if the request failed,
     *                                the second is the MIME type if the request succeed.
     * @returns {Promise} Returns a promise
     */
    function getMIME(url, callback) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                type: "HEAD",
                async: true,
                url: url,
                success: function onSuccess(message, text, jqXHR) {
                    var mime = jqXHR.getResponseHeader('Content-Type');
                    if (callback) {
                        callback(null, mime);
                    }
                    resolve(mime);
                },

                error: function(jqXHR, textStatus, errorThrown) {
                    if (callback) {
                        callback(errorThrown);
                    }
                    reject(errorThrown);
                }
            });
        });
    }

    return getMIME;
});
