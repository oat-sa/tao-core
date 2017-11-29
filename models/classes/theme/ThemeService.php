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
use oat\oatbox\service\ConfigurableService;
/**
 *
 * @author Joel Bout
 */
class ThemeService extends ConfigurableService {

    const SERVICE_ID = 'tao/theming';

    const OPTION_AVAILABLE = 'available';

    const OPTION_CURRENT = 'current';

    const OPTION_THEME_DETAILS_PROVIDERS = 'themeDetailsProviders';

    const OPTION_HEADLESS_PAGE = 'headless_page';

    /**
     * Returns the id of the current theme
     * @return string
     */
    public function getCurrentThemeId()
    {
        $themeId = $this->getThemeIdFromThemeDetailsProviders();
        if (empty($themeId)) {
            $themeId = $this->getOption(self::OPTION_CURRENT);
        }
        return $themeId;
    }

    /**
     * Get the current Theme
     *
     * @return Theme
     */
    public function getTheme()
    {
        return $this->getThemeById($this->getCurrentThemeId());
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
     * Add and set a theme as default
     *
     * @param Theme $theme
     * @param bool  $protectAlreadyExistingThemes
     *
     * @throws \common_exception_Error
     */
    public function setTheme(Theme $theme, $protectAlreadyExistingThemes = true)
    {
        $id = $this->addTheme($theme, $protectAlreadyExistingThemes);
        $this->setCurrentTheme($id);
    }

    /**
     * Add a Theme but don't activate it
     *
     * @param Theme $theme
     * @param bool  $protectAlreadyExistingThemes
     *
     * @return string
     */
    public function addTheme(Theme $theme, $protectAlreadyExistingThemes = true)
    {
        $themes = $this->getOption(self::OPTION_AVAILABLE);
        $baseId = method_exists($theme, 'getId') ? $theme->getId() : '';
        $nr     = '';
        if ($protectAlreadyExistingThemes) {
            $nr = 0;
            while (isset($themes[$baseId . $nr])) {
                $nr++;
            }
        }
        $themes[$baseId.$nr] = [
            'class' => get_class($theme),
            'options' => ($theme instanceof Configurable) ? $theme->getOptions() : []
        ];
        $this->setOption(self::OPTION_AVAILABLE, $themes);
        return $baseId.$nr;
    }

    /**
     * Switch between themes
     *
     * @param string $themeId
     * @throws \common_exception_Error
     */
    public function setCurrentTheme($themeId)
    {
        $themes = $this->getOption(self::OPTION_AVAILABLE);
        if (!isset($themes[$themeId])) {
            throw new \common_exception_Error('Theme '.$themeId.' not found');
        }
        $this->setOption(self::OPTION_CURRENT, $themeId);
    }

    /**
     * Return all available Themes
     *
     * @return Theme[]
     */
    public function getAllThemes()
    {
        return $this->getOption(self::OPTION_AVAILABLE);
    }

    protected function hasTheme($id)
    {
        $themes = $this->getOption(self::OPTION_AVAILABLE);
        return isset($themes[$id]);
    }

    /**
     * Get Theme identified by id
     *
     * @param string $id
     * @throws \common_exception_InconsistentData
     * @return Theme
     */
    protected function getThemeById($id)
    {
        $themes = $this->getOption(self::OPTION_AVAILABLE);
        if (isset($themes[$id])) {
            $theme = $themes[$id];
            if (is_array($theme) && isset($theme['class'])) {
                $options = isset($theme['options']) ? $theme['options'] : [];
                $theme = $this->getServiceManager()->build($theme['class'], $options);
            }
            return $theme;
        } else {
            throw new \common_exception_InconsistentData('Theme '.$id.' not found');
        }
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
     * @return array
     */
    protected function getThemeDetailsProviders()
    {
        if ($this->hasOption(static::OPTION_THEME_DETAILS_PROVIDERS)) {
            return (array)$this->getOption(static::OPTION_THEME_DETAILS_PROVIDERS);
        }

        return [];
    }


    /**
     * Remove a theme with a certain id
     *
     * @param $id
     * @return bool
     */
    public function removeThemeById($id) {
        $themes = $this->getOption(self::OPTION_AVAILABLE);
        if(is_null($themes)) {
            return false;
        }
        unset($themes[$id]);
        $this->setOption(self::OPTION_AVAILABLE, $themes);
        return true;
    }
}
