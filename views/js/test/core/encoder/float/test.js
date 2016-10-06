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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
define([
    'jquery',
    'core/encoder/float'
], function(
    $,
    FloatEncoder
){
    'use strict';

    QUnit.test('encode', function(assert){
        QUnit.expect(3);

        assert.ok(typeof FloatEncoder.encode === 'function');

        assert.equal(typeof FloatEncoder.encode(5.4), 'string');

        assert.equal(FloatEncoder.encode(5.4), '5.4');
    });

    QUnit.test('decode', function(assert){
        QUnit.expect(6);

        assert.ok(typeof FloatEncoder.decode === 'function');

        assert.equal(typeof FloatEncoder.decode('5.4'), 'number');

        assert.equal(FloatEncoder.decode('5.4'), 5.4);
        assert.equal(FloatEncoder.decode('5,4'), 5.4);
        assert.equal(FloatEncoder.decode('-5,4'), -5.4);
        assert.equal(FloatEncoder.decode('  -5,4   '), -5.4);
    });
});


