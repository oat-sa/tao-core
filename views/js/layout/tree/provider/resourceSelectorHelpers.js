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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */
/**
 * Pure helpers for the resource-selector tree provider.
 */
define(['lodash'], function (_) {
    'use strict';

    /**
     * Build an action context from a selected resource without mutating it.
     * Classes expose classUri only (jstree contract); instances keep/derive classUri.
     *
     * @param {Object} resource
     * @param {Object} options
     * @param {String} options.classUri - current listing class
     * @param {*} [options.tree] - tree DOM node
     * @returns {Object}
     */
    function buildContext(resource, options) {
        var context = _.defaults(
            {
                id: resource.uri,
                rootClassUri: options.classUri,
                tree: options.tree
            },
            resource
        );

        if (resource.type === 'class') {
            context.classUri = resource.uri;
            delete context.uri;
        } else if (!context.classUri) {
            context.classUri = options.classUri;
        }

        return context;
    }

    /**
     * @param {Object|String} node
     * @returns {String|undefined}
     */
    function resolveRemovedUri(node) {
        return _.isString(node) ? node : node && (node.uri || node.id);
    }

    /**
     * @param {Object|String|null} defaultNode
     * @param {String} removedUri
     * @returns {Boolean}
     */
    function shouldClearDefaultNode(defaultNode, removedUri) {
        return !!(defaultNode && (defaultNode.uri === removedUri || defaultNode === removedUri));
    }

    /**
     * @param {String} classUri - active class folder
     * @param {String} removedUri
     * @returns {Boolean}
     */
    function isActiveClassFolder(classUri, removedUri) {
        return classUri === removedUri;
    }

    return {
        buildContext: buildContext,
        resolveRemovedUri: resolveRemovedUri,
        shouldClearDefaultNode: shouldClearDefaultNode,
        isActiveClassFolder: isActiveClassFolder
    };
});
