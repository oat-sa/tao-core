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
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'lodash'
], function (_) {
    'use strict';

    /**
     * RegExp that split strings separated by spaces
     * @type {RegExp}
     */
    var reSplit = /\s/g;

    /**
     * The namespace separator
     * @type {String}
     */
    var namespaceSep = '.';

    /**
     * The default namespace
     */
    var defaultNamespace = '@';

    /**
     * The namespace helper
     * @typedef {namespaceHelper}
     * @type {namespaceHelper}
     */
    var namespaceHelper = {
        /**
         * Splits a string into single names
         * @param {String} names - the string containing the names separated by spaces
         * @param {Boolean} [normalize] - lower case the string to normalize all the names
         * @returns {String[]} the list of names (no empty, no duplicate)
         */
        split: function split(names, normalize) {
            if (!_.isString(names) || _.isEmpty(names)) {
                return [];
            }
            if (normalize) {
                names = names.toLowerCase();
            }
            return _(names.trim().split(reSplit)).compact().uniq().value();
        },

        /**
         * Get the name without the namespace: the 'foo' of 'foo.bar'
         * @param {String} namespaced - the namespaced name
         * @returns {String} the name part
         */
        getName: function getName(namespaced) {
            if (!_.isString(namespaced) || _.isEmpty(namespaced)) {
                return '';
            }
            if (namespaced.indexOf(namespaceSep) > -1) {
                return namespaced.substr(0, namespaced.indexOf(namespaceSep));
            }
            return namespaced;
        },

        /**
         * Get the namespace part of a namespaced name: the 'bar' of 'foo.bar'
         * @param {String} namespaced - the namespaced name
         * @param {String} [defaultNs] - the default namespace
         * @returns {String} the namespace, that defaults to defaultNs
         */
        getNamespace: function getNamespace(namespaced, defaultNs) {
            if (!_.isString(namespaced) || _.isEmpty(namespaced)) {
                return '';
            }
            if (namespaced.indexOf(namespaceSep) > -1) {
                return namespaced.substr(namespaced.indexOf(namespaceSep) + 1);
            }
            return defaultNs || defaultNamespace;
        },

        /**
         * Add a namespace to each name
         * @param {Array|String} names - The list of names to namespace
         * @param {String} [namespace] - The namespace to set
         * @param {Boolean} [normalize] - lower case the string to normalize all the names
         * @returns {String} - The list of namespaced names
         */
        namespaceAll: function namespaceAll(names, namespace, normalize) {
            var suffix;
            if (!_.isArray(names)) {
                names = namespaceHelper.split(names, normalize);
            }
            if (normalize) {
                namespace = namespace.toLowerCase();
            }
            suffix = namespace ? namespaceSep + namespace : '';
            return _(names).map(function (sh) {
                if (sh.indexOf(namespaceSep) < 0) {
                    return sh + suffix;
                }
                return sh;
            }).compact().uniq().value().join(' ');
        }
    };

    return namespaceHelper;
});
