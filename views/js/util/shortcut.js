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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * Helper allowing to register shortcuts on the whole page.
 *
 * You may register keyboard and mouse shortcuts, like:
 *
 * ```
 * Ctrl+C
 * Shift+leftMouseClick
 * ```
 *
 * **Known limitations:**
 * Due to browser implementation, some shortcuts may not work.
 * For instance on a french keyboard layout, the shortcut "Shift+;" wont work as the browser
 * will return the result of the uppercase key that is "Shift+." in this case.
 * For alphanumeric keys the issue is prevented (this is the more needed feature).
 *
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'util/shortcut/registry'
], function (shortcutRegistry) {
    'use strict';

    /**
     * Default options applied to each shortcut
     * @type {Object}
     */
    var defaultOptions = {
        propagate: false,
        prevent: true
    };

    /**
     * Shortcuts registry that manages shortcuts attached to the browser's window
     * @returns {shortcut}
     */
    return shortcutRegistry(window, defaultOptions);
});
