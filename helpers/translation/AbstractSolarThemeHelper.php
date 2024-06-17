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

namespace oat\tao\helpers\translation;

use oat\tao\helpers\LayoutHelper;

abstract class AbstractSolarThemeHelper
{
    public const LANG_PREFIX = '-S';

    private LayoutHelper $layoutHelper;

    public function __construct(
        LayoutHelper $layoutHelper
    ) {
        $this->layoutHelper = $layoutHelper;
    }

    /**
     * Check if the Solar design is enabled and the prefix has not yet been added
     *
     */
    public function isContainPrefix(string $language): bool
    {
        $pattern = '/' . self::LANG_PREFIX . '$/';

        return !$this->layoutHelper->isSolarDesignEnabled() || preg_match($pattern, $language, $matches);
    }

    /**
     * Concatenate prefix for Solar design translations
     *
     */
    protected function addPrefix(string $language): string
    {
        return $language . self::LANG_PREFIX;
    }

    /**
     * Check and add prefix for Solar design translations
     *
     */
    abstract public function checkPrefix(string $language): string;
}