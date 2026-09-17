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
define(['layout/actions/removeNodeRequestData', 'uri'], function (removeNodeRequestData, uri) {
    'use strict';

    QUnit.module('removeNodeRequestData');

    QUnit.test('module', function (assert) {
        assert.expect(1);
        assert.equal(typeof removeNodeRequestData, 'function', 'The module exposes a function');
    });

    QUnit.test('class-only context sends classUri and no uri', function (assert) {
        assert.expect(4);

        const classUri = 'http://example.test/Class';
        const data = removeNodeRequestData({
            id: 'class-id',
            signature: 'class-sig',
            classUri: uri.encode(classUri)
        });

        assert.strictEqual(data.id, 'class-id', 'keeps id');
        assert.strictEqual(data.signature, 'class-sig', 'keeps signature');
        assert.strictEqual(data.classUri, classUri, 'decodes classUri');
        assert.strictEqual(Object.prototype.hasOwnProperty.call(data, 'uri'), false, 'omits uri');
    });

    QUnit.test('instance context sends decoded uri and classUri', function (assert) {
        assert.expect(4);

        const instanceUri = 'http://example.test/Instance';
        const classUri = 'http://example.test/Class';
        const data = removeNodeRequestData({
            id: 'instance-id',
            signature: 'instance-sig',
            uri: uri.encode(instanceUri),
            classUri: uri.encode(classUri)
        });

        assert.strictEqual(data.id, 'instance-id', 'keeps id');
        assert.strictEqual(data.signature, 'instance-sig', 'keeps signature');
        assert.strictEqual(data.uri, instanceUri, 'decodes uri');
        assert.strictEqual(data.classUri, classUri, 'decodes classUri');
    });
});
