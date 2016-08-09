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
 * Test tpl
 */
define([
    'tpl!test/tpl/samples/join_keyvalue',
    'tpl!test/tpl/samples/join_array',
], function (tplJoinKeyValue, tplJoinArray){
    'use strict';

    var fixture = '#qunit-fixture';

    QUnit.module('registered handlers');

    QUnit.test('join - key value', function (assert){
        var values = {a:'v1', b:'v2', c:'v3'};
        var rendering = tplJoinKeyValue({
            values : values
        });
        assert.equal(rendering, 'a="v1" b="v2" c="v3"', 'join key value rendering ok');
    });

    QUnit.test('join - array value', function (assert){
        var values = {a:'v1', b:'v2', c:'v3'};
        var rendering = tplJoinArray({
            values : values
        });
        assert.equal(rendering, '*v1* or *v2* or *v3*', 'join array rendering ok');
    });

});