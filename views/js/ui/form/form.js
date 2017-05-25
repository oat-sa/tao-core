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
    'core/dataProvider/request',
    'tpl!tao/ui/form/form',
    'css!tao/ui/form/form'
], function(
    $,
    _,
    __,
    componentFactory,
    request,
    tpl
) {
    'use strict';

    /**
     * The form factory
     * @param {Object} [options.action] - form action with url, method, and parameters
     * @param {Array} [options.fields] - form fields (ui/form/field/field)
     * @param {jQuery} options.container - form container
     * @param {Function} options.success - form submission success callback
     * @param {Function} options.error - form submission error callback
     * @returns {ui/component}
     */
    function formFactory(options) {

        var config = _.assign({
            action: null,
            fields: [],
            container: null,
            success: null,
            error: null
        }, options);

        var specs = {
            /**
             * @param {ui/form/field/field} field
             */
            addField: function addField(fieldOptions) {
                // todo - create ui/form/field/field
                // todo - if state is rendered then render field
            }
        };

        return componentFactory(specs, config)
        .setTemplate(tpl)
        .on('submit', function (e) {
            e.preventDefault();

            // todo - enable progress bar
            // todo - submit form and handle success/error

            return false;
        })
        .on('render', function () {
            // todo - render fields
        })
        .on('destroy', function() {
            // todo - destroy fields
        });
    }

    return formFactory;
});