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

use oat\generis\model\WidgetRdf;

interface WidgetDefinitions extends WidgetRdf
{
    public const PROPERTY_CALENDAR               = self::NAMESPACE . '#Calendar';
    public const PROPERTY_TREE_BOX               = self::NAMESPACE . '#TreeBox';
    public const PROPERTY_FILE                   = self::NAMESPACE . '#AsyncFile';
    public const PROPERTY_VERSIONED_FILE         = self::NAMESPACE . '#VersionedFile';
    public const PROPERTY_JSON_OBJECT            = self::NAMESPACE . '#JsonObject';
    public const PROPERTY_WIDGET_SEARCH_TEXT_BOX = self::NAMESPACE . '#SearchTextBox';
}
