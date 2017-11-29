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


use oat\oatbox\Configurable;

/**
 *
 * @author Joel Bout
 */
class ThemeService extends ThemeServiceAbstract
{
    /**
     * @inheritdoc
     */
    public function getCurrentThemeId()
    {
        return $this->getOption(static::OPTION_CURRENT);
    }

    /**
     * @inheritdoc
     */
    public function addTheme(Theme $theme, $protectAlreadyExistingThemes = true)
    {
        $themes  = $this->getAllThemes();
        $themeId = $theme->getId();
        
        if ($protectAlreadyExistingThemes) {
            $themeId = $this->getUniqueId($theme);
        }
        
        $themes[$themeId] = [
            static::THEME_CLASS_OFFSET   => get_class($theme),
            static::THEME_OPTIONS_OFFSET => ($theme instanceof Configurable) ? $theme->getOptions() : []
        ];

        $this->setOption(static::OPTION_AVAILABLE, $themes);

        return $themeId;
    }

    /**
     * @inheritdoc
     */
    public function setCurrentTheme($themeId)
    {
        if (!$this->hasTheme($themeId)) {
            throw new \common_exception_Error('Theme '. $themeId .' not found');
        }

        $this->setOption(static::OPTION_CURRENT, $themeId);
    }

    /**
     * @inheritdoc
     */
    public function getAllThemes()
    {
        $themes = (array)$this->getOption(static::OPTION_AVAILABLE);
        foreach ($themes as $key => $theme) {
            if (is_array($theme) && isset($theme[static::THEME_CLASS_OFFSET])) {
                $options = isset($theme[static::THEME_OPTIONS_OFFSET])
                    ? $theme[static::THEME_OPTIONS_OFFSET]
                    : []
                ;

                $theme   = $this->getServiceManager()->build($theme[static::THEME_CLASS_OFFSET], $options);
            }

            $themes[$key] = $theme;
        }

        return $themes;
    }

    /**
     * @inheritdoc
     */
    public function removeThemeById($themeId)
    {
        if(!$this->hasTheme($themeId)) {
            return false;
        }

        $themes = $this->getOption(static::OPTION_AVAILABLE);
        unset($themes[$themeId]);

        $this->setOption(static::OPTION_AVAILABLE, $themes);

        return true;
    }
}
