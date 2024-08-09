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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA ;
 */

 namespace oat\tao\helpers;

class MapLabelNameService
{
    private const ITEM = 'Item';
    private const MEDIA = 'Media';
    private const DELIVERY = 'Delivery';
    private const ASSETS = 'Assets';

    // New terms for isSolarDesignEnabled FF.
    private static array $mapLabelNames = [];

    public static function mapLabelName(string $labelName, bool $isSolarDesignEnabled): string
    {
        if ($isSolarDesignEnabled && array_key_exists($labelName, self::$mapLabelNames)) {
            return self::$mapLabelNames[$labelName];
        }

        return $labelName;
    }
}
