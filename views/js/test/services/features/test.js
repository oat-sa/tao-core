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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA ;
 */

define(['services/features'], function(features) {
    'use strict';

    QUnit.module('features');

    QUnit.test('module', function(assert) {
        assert.equal(typeof features, 'object', 'The features module exposes a function');
        assert.equal(typeof features.isVisible, 'function', 'The features API exposes the isVisible() function');
        assert.equal(typeof features.isVisible(''), 'boolean', 'The features method isVisible() returns boolean');
    });

    const testData = [
        {lookup: 'items/*', visible: false},
        {lookup: 'items/featureVisible', visible: true},
        {lookup: 'items/featureHidden', visible: false},
        {lookup: 'items/category/featureVisible', visible: true},
        {lookup: 'items/category/featureHidden', visible: false},
        {lookup: 'items/*/featureVisible', visible: true},
        {lookup: 'items/*/featureHidden', visible: false},
        {lookup: 'items/category/subcategory/*', visible: true},
        {lookup: '*/featureVisible', visible: true},
        {lookup: '*/subcategory/featureVisible', visible: false}
    ];

    QUnit
        .cases.init(testData)
        .test('Feature is visible check', function(data, assert) {
            assert.equal(
                features.isVisible(data.lookup),
                data.visible,
                `Lookup for "${data.lookup}" to be ${data.visible}`
            );
        });
});
