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
define(['lodash', 'core/promise'], function (_, Promise) {
    'use strict';

    /**
     * Gets a promise with nested resolve/reject methods
     * @returns {Promise}
     */
    function promiseFactory() {
        var cb = {};

        // create a promise and extract the control callbacks
        var promise = new Promise(function(resolve, reject) {
            cb.resolve = resolve;
            cb.reject = reject;
        });

        // need to assign the control callbacks since the Promise instance does not include them
        _.assign(promise, cb);

        return promise;
    }

    return promiseFactory;
});
