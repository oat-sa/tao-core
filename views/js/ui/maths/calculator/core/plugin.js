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
 * Wrapper for calculator plugins factory
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define(['lodash', 'core/plugin'], function(_, pluginFactory){
    'use strict';

    /**
     * A pluginFactory configured for the calculator
     * @returns {Function} the preconfigured plugin factory
     */
    return function calculatorPluginFactory(provider, defaultConfig) {
        return pluginFactory(provider, _.defaults({
            //alias getHost to getCalculator
            hostName : 'calculator'
        }, defaultConfig));
    };
});
