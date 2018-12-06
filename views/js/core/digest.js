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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

/**
 * Authentication provider against the local storage.
 * To be implemented.
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'lib/polyfill/webcrypto-shim'
], function(_) {
    'use strict';

    //get the native implementation of the CryptoSubtle
    var subtle = window.crypto.subtle || window.crypto.webkitSubtle;
    var supportedAlgorithms = [
        'SHA-1', //considered as not safe anymore
        'SHA-256',
        'SHA-384',
        'SHA-512'
    ];

    /**
     * Encode a buffer to an hexadecimal string
     * @param {Number[]|ArrayBuffer} buffer
     * @returns {String} the hex representation of the buffer
     */
    var bufferToHexString = function bufferToHexString(buffer) {
        return [].map.call(new Uint8Array(buffer), function(val){
            return  ('00' + val.toString(16)).slice(-2);
        }).join('');
    };

    /**
     * Create a hash/checksum from a given string
     * @param {String} utf8String - the string to hash
     * @param {String} [selectedAlgorithm = 'SHA-256'] - how to hash
     * @returns {Promise<String>} resolves with the hash of the string
     * @throws {TypeError} if the algorithm is not available or the input string is missing
     */
    return function digest(utf8String, selectedAlgorithm) {
        var algorithm;
        if(!_.isString(selectedAlgorithm)){
            selectedAlgorithm = 'SHA-256';
        }
        algorithm = selectedAlgorithm.toUpperCase();
        if(!_.contains(supportedAlgorithms, algorithm)){
            throw new TypeError('Unsupported digest algorithm : ' + algorithm);
        }
        if(!_.isString(utf8String)){
            throw new TypeError('Please encode a string, not a ' + (typeof utf8String) );
        }
        return subtle
            .digest(algorithm, new TextEncoder('utf-8').encode(utf8String))
            .then(function(buffer){
                return bufferToHexString(buffer);
            });

    };
});