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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

/**
 * Helper allows to get error message from failed ajax request
 *
 * Usage example:
 * ```
 * $.ajax({
 * ...
 *    error : function (xhr, options, err){
 *      reject(httpErrorParser.parse(xhr, options, err));
 *    }
 *  });
 * ```
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
define([

], function () {
    'use strict';

    return {
        /**
         * Create an error instance.
         *
         * Returned error will have response and errorThrown properties to get original response and error values.
         *
         * @param {Object} xhr - jqXHR object
         * @param {String} options
         * @param {String} errorThrown  - textual portion of the HTTP status, such as "Not Found" or "Internal Server Error."
         * @returns {Error} the new error
         */
        parse : function parse (xhr, options, errorThrown) {
            var msg;
            var json;
            var error;
            try {
                json = JSON.parse(xhr.responseText);
                msg = json.message ? json.message : errorThrown;
            } catch(e) {
                msg = errorThrown;
            }
            error = new Error(msg);
            error.response = xhr;
            error.code = xhr.status;
            error.errorThrown  = errorThrown;
            return error;
        }
    };
});
