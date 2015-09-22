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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Helper for overloading functions.
 * Depending on the number of arguments that are passed in to the method a different bound function will be executed
 * @example
 * <pre>
 * function find() {
 *   functionOverload.addMethod(this, 'find', function(arg1) {
 *     console.log('first action');
 *   });
 *   functionOverload.addMethod(this, 'find', function(arg1, arg2) {
 *     console.log('second action');
 *   });
 * }
 * find('one'); //first action'
 * find('one', 'two'); //second action'
 * </pre>
 * @author Aleh Hutnikau
 */
define ([

], function () {
    'use strict';

    /**
     * Add behaviour to the method depending on the number of arguments that are passed in to the method
     * @param object - owner of method
     * @param name - name of method
     * @param fn - function to be bound
     */
    function addMethod (object, name, fn) {
        var old = object[name];
        object[name] = function () {
            if (fn.length === arguments.length) {
                return fn.apply(this, arguments);
            } else if (typeof old === 'function') {
                return old.apply(this, arguments);
            }
        };
        return this;
    }

    return {
        addMethod : addMethod
    };
});