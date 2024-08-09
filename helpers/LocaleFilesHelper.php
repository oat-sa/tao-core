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

class LocaleFilesHelper
{
    public const LANG_POSTFIX = '-S';

    /**
     * Check if the Solar design is enabled and the postfix has not yet been added
     */
    public static function isContainPostfix(string $language): bool
    {
        $pattern = sprintf('/%s$/', self::LANG_POSTFIX);

        return preg_match($pattern, $language) === 1;
    }

    /**
     * Concatenate postfix for Solar design translations
     */
    protected static function addPostfix(string $language): string
    {
        return $language . self::LANG_POSTFIX;
    }

    /**
     * Check and add postfix for Solar design translations
     */
    public static function checkPostfix(string $language): string
    {
        if (!self::isContainPostfix($language)) {
            $language = self::addPostfix($language);
        }

        return $language;
    }

    /**
     * Check and add postfix for Solar design translations
     */
    public static function checkPostfixDirectory(string $language): string
    {
        if (self::isContainPostfix($language)) {
            return $language;
        }
        $localesDir = 'views/locales';
        $dir = sprintf('%s/../%s/%s', dirname(__FILE__), $localesDir, self::addPostfix($language));
        if (is_dir($dir)) {
            return self::addPostfix($language);
        }

        return $language;
    }

    public static function isPostfixApplied($localeDir, $pattern = null): bool
    {
        return null !== $pattern && preg_match($pattern, $localeDir) === 1;
    }
}
