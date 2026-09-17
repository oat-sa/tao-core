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
define(['lodash', 'layout/tree/provider/resourceSelectorHelpers'], function (_, helpers) {
    'use strict';

    var rootClassUri = 'http://example.test/Root';
    var treeStub = { nodeType: 1 };

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        assert.expect(5);
        assert.equal(typeof helpers, 'object', 'exposes an object');
        assert.equal(typeof helpers.buildContext, 'function', 'buildContext');
        assert.equal(typeof helpers.resolveRemovedUri, 'function', 'resolveRemovedUri');
        assert.equal(typeof helpers.shouldClearDefaultNode, 'function', 'shouldClearDefaultNode');
        assert.equal(typeof helpers.isActiveClassFolder, 'function', 'isActiveClassFolder');
    });

    QUnit.module('buildContext');

    QUnit.test('class selection exposes classUri without uri', function (assert) {
        assert.expect(5);

        var resource = {
            uri: 'http://example.test/Class',
            type: 'class',
            label: 'Class',
            classUri: 'http://example.test/Parent'
        };
        var snapshot = _.cloneDeep(resource);
        var context = helpers.buildContext(resource, {
            classUri: rootClassUri,
            tree: treeStub
        });

        assert.strictEqual(context.classUri, resource.uri, 'classUri is the selected class');
        assert.strictEqual(Object.prototype.hasOwnProperty.call(context, 'uri'), false, 'omits uri');
        assert.strictEqual(context.id, resource.uri, 'id from resource uri');
        assert.strictEqual(context.rootClassUri, rootClassUri, 'rootClassUri from listing');
        assert.deepEqual(resource, snapshot, 'does not mutate the resource');
    });

    QUnit.test('instance selection preserves existing classUri', function (assert) {
        assert.expect(4);

        var parentUri = 'http://example.test/Parent';
        var resource = {
            uri: 'http://example.test/Instance',
            type: 'instance',
            label: 'Instance',
            classUri: parentUri
        };
        var snapshot = _.cloneDeep(resource);
        var context = helpers.buildContext(resource, {
            classUri: rootClassUri,
            tree: treeStub
        });

        assert.strictEqual(context.uri, resource.uri, 'keeps uri');
        assert.strictEqual(context.classUri, parentUri, 'preserves resource classUri');
        assert.strictEqual(context.tree, treeStub, 'attaches tree on context only');
        assert.deepEqual(resource, snapshot, 'does not mutate the resource');
    });

    QUnit.test('instance selection derives classUri when missing', function (assert) {
        assert.expect(3);

        var resource = {
            uri: 'http://example.test/Instance',
            type: 'instance',
            label: 'Instance'
        };
        var snapshot = _.cloneDeep(resource);
        var context = helpers.buildContext(resource, {
            classUri: rootClassUri,
            tree: treeStub
        });

        assert.strictEqual(context.uri, resource.uri, 'keeps uri');
        assert.strictEqual(context.classUri, rootClassUri, 'derives classUri from listing');
        assert.deepEqual(resource, snapshot, 'does not mutate the resource');
    });

    QUnit.module('afterResourceRemoved helpers');

    QUnit.test('resolveRemovedUri positive and negative cases', function (assert) {
        assert.expect(5);

        assert.strictEqual(helpers.resolveRemovedUri('http://example.test/A'), 'http://example.test/A');
        assert.strictEqual(helpers.resolveRemovedUri({ uri: 'http://example.test/B' }), 'http://example.test/B');
        assert.strictEqual(helpers.resolveRemovedUri({ id: 'http://example.test/C' }), 'http://example.test/C');
        assert.notOk(helpers.resolveRemovedUri(null), 'null is a no-op');
        assert.notOk(helpers.resolveRemovedUri({}), 'empty object is a no-op');
    });

    QUnit.test('shouldClearDefaultNode when default node is deleted', function (assert) {
        assert.expect(4);

        var removed = 'http://example.test/Default';
        assert.ok(
            helpers.shouldClearDefaultNode({ uri: removed }, removed),
            'clears object default matching uri'
        );
        assert.ok(helpers.shouldClearDefaultNode(removed, removed), 'clears string default');
        assert.notOk(
            helpers.shouldClearDefaultNode({ uri: 'http://example.test/Other' }, removed),
            'keeps unrelated default'
        );
        assert.notOk(helpers.shouldClearDefaultNode(null, removed), 'no default to clear');
    });

    QUnit.test('isActiveClassFolder when active class is deleted', function (assert) {
        assert.expect(3);

        var active = 'http://example.test/Active';
        assert.ok(helpers.isActiveClassFolder(active, active), 'active class folder deleted');
        assert.notOk(
            helpers.isActiveClassFolder(active, 'http://example.test/Other'),
            'different uri is not the active folder'
        );
        assert.notOk(helpers.isActiveClassFolder(rootClassUri, active), 'root listing stays put');
    });
});
