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

interface ConfigurableTheme
{
    const THEME_DATA = 'data';
    const THEME_CSS = 'stylesheet';

    const THEME_DATA_LOGO_URL = 'logo-url';
    const THEME_DATA_LINK     = 'link';
    const THEME_DATA_MESSAGE  = 'message';

    /**
     * Get options under data key
     * Options to configure header & footer template
     *
     * @return array
     */
    public function getThemeData();

    /**
     * Get the logo url of current theme
     * Logo url is used into header
     *
     * @return string
     */
    public function getLogoUrl();

    /**
     * Get the url link of current theme
     * Url is used into header, to provide link to logo
     * Url is used into footer, to provide link to footer message
     *
     * @return string
     */
    public function getLink();

    /**
     * Get the message of current theme
     * Message is used into header, to provide title to logo
     * Message is used into footer, as footer message
     *
     * @return string
     */
    public function getMessage();
}