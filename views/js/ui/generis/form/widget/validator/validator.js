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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'css!tao/ui/generis/form/widgets/validation/validation'
], function(
    $,
    _,
    __,
    componentFactory
) {
    'use strict';

    /**
     * The factory
     * @param {(string|function|RegExp)} options.predicate - Expression to be run
     * @param {string} options.message - Message for failed validation
     */
    function factory(options) {
        options = options || {};

        return componentFactory({
            run: function (value) {
                var predicate = options.predicate;
                var ret;

                if (predicate instanceof RegExp) {
                    ret = predicate(value);
                } else if (typeof predicate === 'function') {
                    ret = predicate.test(value);
                }

                return ret;
            },
            clear: function () {

            },
            show: function () {

            },
            hide: function () {

            }
        }, {
            container: '.validation-container',
            errors: []
        });
    }

    return factory;
});