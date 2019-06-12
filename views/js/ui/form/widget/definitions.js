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
 * Defines constants that match the form widget types available by default
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define(function () {
    'use strict';

    /**
     * Defines the URI of each available widget
     * @type {Object}
     */
    const widgetDefinitions = {
        TEXTBOX: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
        TEXTAREA: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea',
        HIDDENBOX: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
        RADIOBOX: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox',
        COMBOBOX: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox',
        CHECKBOX: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox'

        /* @todo */
        // HTMLAREA: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea',
        // PASSWORD: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Password',
        // CALENDAR: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Calendar',
        // TREEBOX: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeBox',
        // FILE: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#AsyncFile',
        // VERSIONEDFILE: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#VersionedFile',
        // JSONOBJECT: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#JsonObject',
    };

    /**
     * Designates the default widget applied when the URI is missing
     */
    widgetDefinitions.DEFAULT = widgetDefinitions.TEXTBOX;

    return widgetDefinitions;
});
