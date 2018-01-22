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
use /** @noinspection PhpDeprecationInspection */
    oat\tao\model\theme\DefaultTheme;
use oat\tao\model\theme\ConfigurablePlatformTheme;
use oat\tao\helpers\Template;

/**
 * Class ThemeConverter
 *
 * Class to convert legacy platform themes to ConfigurablePlatformTheme
 *
 * @package oat\tao\model\theme
 */
class ThemeConverter
{

    /**
     * Build an instance of ConfigurablePlatformTheme from a legacy theme
     *
     * @param object|array $theme
     * @return ConfigurablePlatformTheme
     * @throws \common_exception_MissingParameter
     */
    public static function convertFromLegacyTheme($theme)
    {
        if ($theme instanceof ConfigurablePlatformTheme) {
            return $theme;
        }

        // older themes are stored as an instance, newer ones as array
        if(is_array($theme)) {
            if(empty($theme[ThemeServiceInterface::THEME_CLASS_OFFSET])) {
                throw new \common_exception_MissingParameter(
                    ThemeServiceInterface::THEME_CLASS_OFFSET,
                    __METHOD__
                );
            }

            $options = !empty($theme[ThemeServiceInterface::THEME_OPTIONS_OFFSET])
                ? $theme[ThemeServiceInterface::THEME_OPTIONS_OFFSET]
                : []
            ;

            $theme = new $theme[ThemeServiceInterface::THEME_CLASS_OFFSET]($options);
        }

        // list of all previously used templates
        $templates = ['footer', 'header', 'header-logo', 'login-message'];

        // all keys associated with a public getter from previously used theme classes
        $getKeys = ['id', 'label', 'stylesheet', 'logoUrl', 'link', 'message', 'customTexts'];

        // collect options
        $options = [];
        if (method_exists($theme, 'getOptions')) {
            $options = $theme->getOptions();
        }

        if (method_exists($theme, 'getThemeData')) {
            $options = array_merge($options, $theme->getThemeData());
        }
        unset($options['data']);

        foreach ($getKeys as $key) {
            $method = 'get' . ucfirst($key);
            if (method_exists($theme, $method)) {
                $options[$key] = $theme->{$method}();
            }
        }
        // TAO default logo URL is different
        if($theme instanceof DefaultTheme) {
            $options['logoUrl'] = Template::img('tao-logo.png', 'tao');
        }

        if (method_exists($theme, 'getTemplate')) {
            if (empty($options[ConfigurablePlatformTheme::TEMPLATES])) {
                $options[ConfigurablePlatformTheme::TEMPLATES] = [];
            }
            foreach ($templates as $id) {
                $template = $theme->getTemplate($id);
                if(!is_null($template)) {
                    $options[ConfigurablePlatformTheme::TEMPLATES][$id] = $template;
                }
            }
        }

        // example: oat\taoExtension\model\theme\MyTheme
        $themeClass = get_class($theme);
        //@todo: map to container id
        if (empty($options[ConfigurablePlatformTheme::EXTENSION_ID])) {
            strtok($themeClass, '\\');
            $options[ConfigurablePlatformTheme::EXTENSION_ID] = strtok('\\');
        }

        if (empty($options[ConfigurablePlatformTheme::LABEL])) {
            $options[ConfigurablePlatformTheme::LABEL] = basename($themeClass);
        }
        return new ConfigurablePlatformTheme($options);
    }
}
