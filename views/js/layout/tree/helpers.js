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
 * Copyright (c) 2024 Open Assessment Technologies SA;
 */
define(['jquery'], function ($) {
    return {
        /**
         * Updates "A" level CSS variables.
         * @param {object} $node
         */
        setALevelVar($node) {
            $node.find('a').each(function () {
                $(this).attr('style', `--tree-level: ${$(this).parent().attr('data-level')}`);
            })
        },

        /**
         * Set the levels for each tree node.
         * @param {object} response
         */
        setTreeLevels(response) {
            const treeData = response.tree || response;
            const parentLevel = response.level;

            //populate treeData with level info
            function addLevelInfo(node, level) {
                if (Array.isArray(node)) {
                    node.forEach((n) => {
                        addLevelInfo(n, level);
                    })
                } else {
                    node.attributes = node.attributes || {}
                    node.attributes['data-level'] = level;
                    if (node.children) {
                        node.children.forEach(child => {
                            addLevelInfo(child, level + 1);
                        })
                    }
                }
            }

            addLevelInfo(treeData, typeof parentLevel !== 'undefined' ? parentLevel + 1 : 0);
        }
    }
});
