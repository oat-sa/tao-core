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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\theme;

use oat\tao\model\theme\ConfigurablePlatformTheme;

/**
 * Class UrlSourceTheme
 *
 * @deprecated use oat\tao\model\theme\ConfigurablePlatformTheme instead
 * @package oat\tao\model\theme
 */
class ConfigurableTheme extends ConfigurablePlatformTheme implements Theme
{
// @todo
    const THEME_DATA = 'data';

    /**
     * Get options under data key
     * Options to configure header & footer template
     *
     * @return array
     */
    public function getThemeData()
    {
        if ($this->hasOption(static::THEME_DATA) && is_array($this->getOption(static::THEME_DATA))) {
            return $this->getOption(static::THEME_DATA);
        }
        
        return [];
    }
}
