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
    'ui/form/field/field',
    'tpl!tao/ui/form/field/comboBox/comboBox'
], function(
    $,
    _,
    __,
    fieldFactory,
    tpl
) {
    'use strict';

    /**
     * The field factory
     * @param {String} options.label - Field's label
     * @param {Boolean} [options.required] - Flag to require field
     * @param {String} [options.value] - Field's value
     * @param {Array} [options.values] - Field's range of values
     * @param {String} options.uri - Resource's ontology uri for field's name
     * @returns {ui/component}
     */
    function comboBoxFactory(options) {

        var config = _.assign({
            validations: [
                // todo - set validations
            ],
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox'
        }, options);

        return fieldFactory(config).setTemplate(tpl);
    }

    return comboBoxFactory;
});
