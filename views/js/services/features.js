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
 * Copyright (c) 2022 Open Assessment Technologies SA;
 */

/**
 * Feature visibility check examples:
 *  configuration: {path/featureA: 'show'}
 *
 *  feature.isVisible('path/featureA') return true
 *  feature.isVisible('path/featureB') return true
 *  feature.isVisible('path/featureB', false) return false
 */
define(['module', 'core/logger'], function (module, loggerFactory) {
    'use strict';
    const config = module.config();
    const featuresVisibilityList = config.visibility || {};
    const featuresKeys = Object.keys(featuresVisibilityList);
    const logger = loggerFactory('services/features');

    /**
     * Build regexp from lookupPath and converting '*' to '\S+'
     * @param {String} lookupPath raw string of path to lookup
     * @returns {RegExp} regexp to lookup in features list
     */
    const buildRegexp = lookupPath => {
        lookupPath = lookupPath.replace('*', '\\S+');

        try {
            return new RegExp(`^${lookupPath}$`);
        } catch (e) {
            logger.warn(`Lookup feature path ${lookupPath} was not found`);
            return new RegExp('^\0$');
        }
    };

    return {
        /**
         * Check if the feature is visible by provided featurePath
         * and check 'show' or 'hide' status from configuration
         * second parameter is visibility by default if feature is missed from configuration.
         * @param {String} featurePath full path to feature ex('items/feature')
         * @param {Boolean} isVisibleByDefault feature visibility if missed from configurations
         * @returns {Boolean} true if feature is visible
         */
        isVisible(featurePath = '', isVisibleByDefault = true) {
            let matchingPath = null;

            featuresKeys.some(path => {
                const exactMatch = path === featurePath;

                if (exactMatch || buildRegexp(path).test(featurePath)) {
                    matchingPath = path;
                }

                if (exactMatch) {
                    return true;
                }
            });

            return matchingPath === null ? isVisibleByDefault : featuresVisibilityList[matchingPath] === 'show';
        }
    };
});
