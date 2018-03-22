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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\theme;


use oat\oatbox\service\ConfigurableService;

abstract class ThemeServiceAbstract extends ConfigurableService implements ThemeServiceInterface
{
    /**
     * @inheritdoc
     *
     * @throws \common_exception_InconsistentData
     */
    public function getTheme()
    {
        $themeId = $this->getThemeIdFromThemeDetailsProviders();
        if (empty($themeId)) {
            $themeId = $this->getCurrentThemeId();
        }

        return $this->getThemeById($themeId);
    }

    /**
     * @inheritdoc
     */
    public function setTheme(Theme $theme, $protectAlreadyExistingThemes = true)
    {
        $this->addTheme($theme, $protectAlreadyExistingThemes);
        $this->setCurrentTheme($theme->getId());
    }

    /**
     * @inheritdoc
     */
    public function hasTheme($themeId)
    {
        $themes = $this->getAllThemes();
        if (array_key_exists($themeId, $themes)) {
            return true;
        }

        foreach ($themes as $currentTheme) {
            if ($currentTheme->getId() === $themeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getThemeById($themeId)
    {
        $themes = $this->getAllThemes();
        if (array_key_exists($themeId, $themes)) {
            return $themes[$themeId];
        }

        foreach ($themes as $currentTheme) {
            if ($currentTheme->getId() === $themeId) {
                return $currentTheme;
            }
        }

        throw new \common_exception_InconsistentData('The requested theme does not exist. (' . $themeId .')');
    }

    /**
     * Returns the theme id provided by the themeDetailsProviders.
     *
     * @return string
     */
    protected function getThemeIdFromThemeDetailsProviders()
    {
        $providers = $this->getThemeDetailsProviders();
        foreach ($providers as $provider) {
            if ($provider instanceof ThemeDetailsProviderInterface) {
                $themeId = $provider->getThemeId();
                if (!empty($themeId) && $themeId !== ' ') {
                    if ($this->hasTheme($themeId)) {
                        return $themeId;
                    }

                    \common_Logger::i(
                        'The requested theme ' . $themeId .
                        ' requested by the ' . get_class($provider) . ' provider does not exist!'
                    );
                }
            }
        }

        return '';
    }

    /**
     * Returns the unique identifier.
     *
     * @param Theme $theme
     *
     * @return string
     */
    protected function getUniqueId(Theme $theme)
    {
        $baseId = $theme->getId();

        $idNumber = 0;
        while ($this->hasTheme($baseId . $idNumber)) {
            $idNumber++;
        }

        return $baseId . $idNumber;
    }

    /**
     * Tells if the page has to be headless: without header and footer.
     *
     * @return bool|mixed
     */
    public function isHeadless()
    {
        if ($this->hasOption(self::OPTION_HEADLESS_PAGE)) {
            return $this->getOption(self::OPTION_HEADLESS_PAGE);
        }

        $isHeadless = $this->getIsHeadLessFromThemeDetailsProviders();
        if (empty($isHeadless)) {
            $isHeadless = false;
        }

        return $isHeadless;
    }

    /**
     * Returns the isHeadless details provided by the themeDetailsProviders.
     *
     * @return bool|mixed
     */
    protected function getIsHeadlessFromThemeDetailsProviders()
    {
        $providers = $this->getThemeDetailsProviders();
        foreach ($providers as $provider) {
            if ($provider instanceof ThemeDetailsProviderInterface) {
                $isHeadless = $provider->isHeadless();
                if (!empty($isHeadless)) {
                    return $isHeadless;
                }
            }
        }

        return false;
    }

    /**
     * Returns the theme details providers.
     *
     * @todo: think about this concept, because this could be a service.
     *
     * @return array
     */
    protected function getThemeDetailsProviders()
    {
        if ($this->hasOption(static::OPTION_THEME_DETAILS_PROVIDERS)) {
            return (array)$this->getOption(static::OPTION_THEME_DETAILS_PROVIDERS);
        }

        return [];
    }
}
