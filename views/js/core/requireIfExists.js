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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'core/promise'
], function (Promise) {
    'use strict';

    /**
     * Requires an optional module. It the module does not exist, an empty resource is provided (null).
     * @param {String} uri - The URI of the module to require
     * @returns {Promise} - Returns a promise that will be resolved either with the loaded resource or an empty resource.
     */
    function requireIfExists(uri) {
        // the promise will always be resolved
        return new Promise(function(resolve) {
            // if a require issue occurs, fallback to an empty resource
            function failed(err) {
                // only catch error related to the required module
                var failedId = err.requireModules && err.requireModules[0];
                if (failedId === uri) {
                    // fake the module, then ensure it is truly loaded
                    requirejs.undef(failedId);
                    define(failedId, function () {
                        return null;
                    });
                    require([failedId], resolve);
                } else {
                    // others errors are not handled
                    throw err;
                }
            }

            // require the module with error handling
            require([uri], resolve, failed);
        });
    }

    return requireIfExists;
});
