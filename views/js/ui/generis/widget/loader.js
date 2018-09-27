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
    'lodash',
    'ui/generis/widget/checkBox/checkBox',
    'ui/generis/widget/comboBox/comboBox',
    'ui/generis/widget/hiddenBox/hiddenBox',
    'ui/generis/widget/textBox/textBox'
], function (
    _,
    checkBoxFactory,
    comboBoxFactory,
    hiddenBoxFactory,
    textBoxFactory
) {
    'use strict';

    var _default = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox';
    var _widgetFactories = {
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox': checkBoxFactory,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox': comboBoxFactory,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#StateWidget': comboBoxFactory,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox': hiddenBoxFactory,
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox': textBoxFactory
    };

    /**
     * Returns the correct widget factory based on the widget uri
     * @param {String} uri
     * @returns {Function}
     */
    return function (uri) {
        var factory;

        if (!uri || !_.contains(Object.keys(_widgetFactories), uri)) {
            factory = _widgetFactories[_default];
        } else {
            factory = _widgetFactories[uri];
        }

        return factory;
    };
});
