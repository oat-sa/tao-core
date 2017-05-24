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
    'i18n',
    'lodash',
    'core/dataProvider/request',
    'ui/form/form'
], function(
    $,
    _,
    __,
    request,
    formFactory
) {
    'use strict';

    /**
     * The form factory
     * @param {Object} [options.action] - Form's action endpoint (i.e. where to submit form)
     * @param {jQuery} options.container - Form's container
     * @param {Object} [options.data] - Form's data endpoint (i.e. data to populate form)
     * @param {String} [options.title] - Form's title
     * @returns {ui/component}
     */
    function generisFormFactory(options) {

        var config = _.assign({
            action: null,
            container: null,
            title: null
        }, options);

        // todo - set fields from data endpoint

        // todo - set success callback

        // todo - set error callback

        return formFactory(config);
    }

    return generisFormFactory;
});