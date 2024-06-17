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

class SolarThemeFileHelper extends AbstractSolarThemeHelper
{
    /**
     * Check and add prefix for Solar design translations
     *
     */
    public function checkPrefix(string $language): string
    {
        if (!$this->isContainPrefix($language)) {
            $localesDir = 'views/locales';
            $dir = dirname(__FILE__) . '/../../' . $localesDir . '/' . $this->addPrefix($language);
            if (is_dir($dir)) {
                $language = $this->addPrefix($language);
            }
        }

        return $language;
    }
}
