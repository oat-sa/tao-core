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

define(['module', 'core/logger'], function(module, loggerFactory) {
    'use strict';
    const config = module.config();
    const featuresVisibilityList = config.visibility || {};
    const featuresKeys = Object.keys(featuresVisibilityList);

    /**
     * Build regexp from lookupPath and converting '*' to '\S+'
     * @param {String} lookupPath raw string of path to lookup
     * @returns {RegExp} regexp to lookup in features list
     */
    const buildRegexp = lookupPath => {
        lookupPath = lookupPath.replace('*', '\\S+');

        try {
            return RegExp(`^${lookupPath}$`);
        } catch (e) {
            const logger = loggerFactory('services/features');
            logger.warn(`Lookup feature path ${lookupPath} was not found`);

            return RegExp('');
        }
    };

    return {
        /**
         * Check is feature configured to be visible
         * based on client_lib_config_registry.conf.php
         * possible match is exact match (item/feature) or wildcard match (item/*)
         * using 2 wildcard symbols * is not supported
         * @param {String} featurePath path to feature supporting * ex('test/itemSession/*')
         * @returns {Boolean} true if feature is visible
         */
        isVisible: (featurePath = '') => {
            const regexp = buildRegexp(featurePath);
            let targetKey = null;

            featuresKeys.some(path => {
                const exactMatch = path === featurePath;

                if (exactMatch || regexp.test(path)) {
                    targetKey = path;
                }

                if (exactMatch) {
                    return true;
                }
            });

            return targetKey !== null && featuresVisibilityList[targetKey] === 'show';
        }
    };
});
