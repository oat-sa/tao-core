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
     * Returns the id of the current theme
     *
     * @return string
     */
    public function getCurrentThemeId();

    /**
     * Get the current Theme
     *
     * @return Theme
     */
    public function getTheme();

    /**
     * Gets the Theme identified by id
     *
     * @param string $id
     *
     * @return Theme
     *
     * @throws \common_exception_InconsistentData
     */
    public function getThemeById($id);

    /**
     * Returns all available Themes
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
     * Returns TRUE if the theme exists.
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasTheme($id);

    /**
     * Sets the current theme.
     *
     * @param string $themeId
     *
     * @throws \common_exception_Error
     */
    public function setCurrentTheme($themeId);

    /**
     * Tells if the page has to be headless: without header and footer.
     *
     * @return bool|mixed
     */
    public function isHeadless();
}
