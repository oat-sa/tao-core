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
 * Copyright (c) 2018 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'context',
    'core/pluginLoader',
    'ui/maths/calculator/plugins/core/degrad',
    'ui/maths/calculator/plugins/core/history',
    'ui/maths/calculator/plugins/core/remind',
    'ui/maths/calculator/plugins/core/stepNavigation'
], function (
    context,
    pluginLoaderFactory,
    pluginDegradFactory,
    pluginHistoryFactory,
    pluginRemindFactory,
    pluginStepNavigationFactory
) {
    'use strict';

    /**
     * Load the plugins dynamically
     * @param {Object} loadedPlugins - a collection of already loaded plugins
     * @param {Object} dynamicPlugins - a collection of plugins to load
     * @returns {Promise} resolves with the list of loaded plugins
     */
    return function loadPlugins(loadedPlugins, dynamicPlugins) {
        // The list of default plugins is directly built here instead of using a module variable to ensure the object
        // is unique to the instance. This wil avoid global polluting by successive instances, as nested objects and
        // arrays might be simply copied.
        var defaultPlugins = {
            core: [
                pluginDegradFactory,
                pluginHistoryFactory,
                pluginRemindFactory,
                pluginStepNavigationFactory
            ]
        };

        return pluginLoaderFactory(_.merge({}, defaultPlugins, loadedPlugins))
            .addList(dynamicPlugins)
            .load(context.bundle);
    };
});
