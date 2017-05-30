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
    'css!tao/ui/form/field'
], function(
    $,
    _,
    __,
    componentFactory
) {
    'use strict';

    /**
     * The field factory
     * @param {Object} [options.templateVars] - Contains the variables for the template/widget
     * @param {Array[Object]} [options.validations] - Validations to run on field's value
     * @param {Function} options.widget - Widget html as a handlebar's template
     * @returns {ui/component}
     */
    function fieldFactory(options) {
        return componentFactory()

        .setTemplate(options.widget)

        .on('change', function () {
            // todo - run validations from options.validations
        })
        .on('render', function () {
            // todo - render field
            // todo - trigger change event
        })
        .on('destroy', function() {
            // todo - destroy field
        })

        .init(options.templateVars);
    }

    return fieldFactory;
});