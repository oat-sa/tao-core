<?php
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
 *
 */

namespace oat\tao\model;

interface WidgetDefinitions
{
	const PROPERTY_CALENDAR = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Calendar';
	const PROPERTY_TEXTBOX = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox';
	const PROPERTY_TREEBOX = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TreeBox';
	const PROPERTY_TEXTAREA = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextArea';
	const PROPERTY_HTMLAREA = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea';
	const PROPERTY_PASSWORD = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Password';
	const PROPERTY_HIDDENBOX = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox';
	const PROPERTY_RADIOBOX = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#RadioBox';
	const PROPERTY_COMBOBOX = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox';
	const PROPERTY_CHECKBOX = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox';
	const PROPERTY_FILE = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#AsyncFile';
	const PROPERTY_VERSIONEDFILE = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#VersionedFile';
	const PROPERTY_JSONOBJECT = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#JsonObject';
}