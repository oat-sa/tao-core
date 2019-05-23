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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * Register common form widgets
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'ui/form/widget/definitions',
    'ui/form/widget/widget',
    'ui/form/widget/checkBox',
    'ui/form/widget/comboBox',
    'ui/form/widget/hiddenBox',
    // 'ui/form/widget/radioBox',
    'ui/form/widget/textArea',
    'ui/form/widget/textBox'
], function (
    widgetDefinitions,
    widgetFactory,
    widgetCheckBoxProvider,
    widgetComboBoxProvider,
    widgetHiddenBoxProvider,
    // widgetRadioBoxProvider,
    widgetTextAreaProvider,
    widgetTextBoxProvider
) {
    'use strict';

    widgetFactory.registerProvider(widgetDefinitions.CHECKBOX, widgetCheckBoxProvider);
    widgetFactory.registerProvider(widgetDefinitions.COMBOBOX, widgetComboBoxProvider);
    widgetFactory.registerProvider(widgetDefinitions.HIDDENBOX, widgetHiddenBoxProvider);
    // widgetFactory.registerProvider(widgetDefinitions.RADIOBOX, widgetRadioBoxProvider);
    widgetFactory.registerProvider(widgetDefinitions.TEXTAREA, widgetTextAreaProvider);
    widgetFactory.registerProvider(widgetDefinitions.TEXTBOX, widgetTextBoxProvider);

    return widgetFactory;
});
