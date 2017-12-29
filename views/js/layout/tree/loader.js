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
 * Copyright (c) 2017 Open Assessment Technologies SA;
 */

/**
 * Loads and register the different tree implementations
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'core/providerRegistry',
    'layout/tree/provider/jstree',
    'layout/tree/provider/resourceSelector'
], function(module, providerRegistry, jsTreeProvider, resourceSelectorProvider){
    'use strict';

    /**
     * If not config is set, this is the default tree provider
     */
    var defaultProvider = 'jstree';

    /**
     * Contains all tree providers
     */
    var treeProviderRegistry = providerRegistry({});

    /**
     * Check whether a provider exists
     * @param {String} providerName - the name of the provider
     * @returns {Boolean} true if the provider is registered
     */
    var providerExists = function providerExists(providerName){
        return providerName && treeProviderRegistry.getAvailableProviders().indexOf(providerName) !== -1;
    };

    //manually register the providers
    treeProviderRegistry.registerProvider(jsTreeProvider.name, jsTreeProvider);
    treeProviderRegistry.registerProvider(resourceSelectorProvider.name, resourceSelectorProvider);

    /**
     * Let's you load either the default tree provider or a specific one
     * @param {String} [providerName] - the name of the provider
     * @returns {treeProvider} the provider
     */
    return function loadTree(providerName){
        var providerToLoad = defaultProvider;
        var config = module.config();

        if(providerExists(providerName)){
            providerToLoad = providerName;
        } else if (providerExists(config.treeProvider)){
            providerToLoad = config.treeProvider;
        }
        return treeProviderRegistry.getProvider(providerToLoad);
    };
});
