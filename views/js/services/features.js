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

define(['module'], function (module) {
    'use strict';
    const config = module.config();
    const featuresVisibilityList = config.visibility || [];
    const featuresKeys = Object.keys(featuresVisibilityList);

    /**
     * Build regexp from lookupPath converting '*' to '\S*'
     * @param {String} lookupPath raw string of path to lookup
     * @returns {RegExp} regexp to lookup in features list
     */
    const buildRegexp = lookupPath => {
        lookupPath = lookupPath.replaceAll('*', '\\S*');

        try {
            return RegExp(`^${lookupPath}$`);
        } catch (e) {
            console.warn(`Lookup feature ${lookupPath} was not properly checked`);

            return RegExp('');
        }
    };

    return {
        /**
         * Check is feature configured to be visible
         * based on client_lib_config_registry.conf.php
         * possible match is exact match (item/feature) or wildcard match (item/*)
         * using 2 wildcarcard symbols * is not supported
         * @param {String} featurePath path to feature supporting * ex('test/itemSession/*')
         * @returns {Boolean} true if feature is visible
         */
        isVisible: featurePath => {
            const regexp = buildRegexp(featurePath);
            let targetKey = null;

            for (let i in featuresKeys) {
                const exactMatch = featuresKeys[i] === featurePath;

                if (exactMatch || regexp.test(featuresKeys[i])) {
                    targetKey = featuresKeys[i];
                }

                if (exactMatch) {
                    break;
                }
            }

            return targetKey !== null && featuresVisibilityList[targetKey] === 'show';
        }
    };
});
