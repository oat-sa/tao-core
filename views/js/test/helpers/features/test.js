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

define(['helpers/features'], function(features) {
    'use strict';

    QUnit.module('features');

    const testData = [
        {lookup: 'levelOne/featureVisible', expected: true},
        {lookup: 'levelOne/featureHidden', expected: false},
        {lookup: 'levelOne/levelTwo/featureVisible', expected: true},
        {lookup: 'levelOne/levelTwo/featureHidden', expected: false},
        {lookup: 'levelOne/*/featureVisible', expected: true},
        {lookup: 'levelOne/*/featureHidden', expected: false},
        {lookup: 'levelOne/*', expected: true},
        {lookup: 'levelOne/levelTwo/LevelTree/*', expected: true},
        {lookup: 'levelOne/levelTwo/*/featureHidden', expected: false},
        {lookup: 'levelOne/*/featureHidden', expected: false},
        {lookup: '*/LevelTree/featureVisible', expected: false}

    ];

    QUnit
        .cases.init(testData)
        .test('Feature is visible check', function(data, assert) {
            assert.equal(
                features.isVisible(data.lookup),
                data.expected,
                `Data lookup "${data.lookup}"`
            );
        });
});
