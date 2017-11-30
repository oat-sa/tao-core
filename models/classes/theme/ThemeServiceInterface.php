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


interface ThemeServiceInterface
{
    /** Service identifier in the ServiceManager. */
    const SERVICE_ID = 'tao/theming';

    /** The option of the theme collection. */
    const OPTION_AVAILABLE = 'available';

    /** The option of the current theme name. */
    const OPTION_CURRENT = 'current';

    /** The class name offset in the stored version. */
    const THEME_CLASS_OFFSET = 'class';

    /** The options offset in the stored version. */
    const THEME_OPTIONS_OFFSET = 'options';

    const OPTION_THEME_DETAILS_PROVIDERS = 'themeDetailsProviders';

    const OPTION_HEADLESS_PAGE = 'headless_page';

    /**
     * Returns the identifier of the current Theme.
     *
     * @return string
     */
    public function getCurrentThemeId();

    /**
     * Returns the current Theme.
     *
     * @return Theme
     */
    public function getTheme();

    /**
     * Returns the Theme identified by the requested identifier.
     *
     * @param string $themeId
     *
     * @return Theme
     *
     * @throws \common_exception_InconsistentData
     */
    public function getThemeById($themeId);

    /**
     * Returns all the available Themes.
     *
     * @return Theme[]
     */
    public function getAllThemes();

    /**
     * Adds and sets a theme as default.
     *
     * @param Theme $theme
     * @param bool  $protectAlreadyExistingThemes
     *
     * @throws \common_exception_Error
     */
    public function setTheme(Theme $theme, $protectAlreadyExistingThemes = true);

    /**
     * Adds a Theme.
     *
     * @param Theme $theme
     * @param bool  $protectAlreadyExistingThemes
     *
     * @return string
     */
    public function addTheme(Theme $theme, $protectAlreadyExistingThemes = true);

    /**
     * Returns TRUE if the Theme exists.
     *
     * @param string $themeId
     *
     * @return bool
     */
    public function hasTheme($themeId);

    /**
     * Sets the current Theme.
     *
     * @param string $themeId
     *
     * @throws \common_exception_Error
     */
    public function setCurrentTheme($themeId);

    /**
     * Removes the Theme identified by the requested identifier.
     *
     * @param string $themeId
     *
     * @return bool
     */
    public function removeThemeById($themeId);

    /**
     * Tells if the page has to be headless: without header and footer.
     *
     * @return bool|mixed
     */
    public function isHeadless();
}
