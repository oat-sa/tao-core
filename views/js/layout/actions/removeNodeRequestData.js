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
 * Build the removeNode POST payload from an action context.
 * Class selection exposes classUri only; instances expose uri + classUri.
 */
define(['uri'], function (uri) {
    'use strict';

    /**
     * @param {Object} actionContext
     * @param {String} [actionContext.uri]
     * @param {String} [actionContext.classUri]
     * @param {String} [actionContext.id]
     * @param {String} [actionContext.signature]
     * @returns {{id: *, signature: *, classUri: string, uri?: string}}
     */
    return function removeNodeRequestData(actionContext) {
        const data = {
            id: actionContext.id,
            signature: actionContext.signature
        };

        if (actionContext.uri) {
            data.uri = uri.decode(actionContext.uri);
            data.classUri = uri.decode(actionContext.classUri);
        } else {
            data.classUri = uri.decode(actionContext.classUri);
        }

        return data;
    };
});
