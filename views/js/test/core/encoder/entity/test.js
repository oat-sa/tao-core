/*
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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 *
 */

/**
 * Test {@module core/encoder/entity}
 *
 */
define([
    'core/encoder/entity'
], function(entity){
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert){
        assert.equal(typeof entity, 'object', 'The module expose an object');
        assert.equal(typeof entity.encode, 'function', 'The encode method is available');
        assert.equal(typeof entity.decode, 'function', 'The decode method is available');
    });

    QUnit.module('encode');

    QUnit.cases([{
        title : 'no special chars',
        input : 'Hello Foo bar world !',
        output : 'Hello Foo bar world !'
    }, {
        title : 'xss',
        input : 'Hello Foo<script>alert("foo")</script>bar world !',
        output : 'Hello Foo&#60;script&#62;alert(&#34;foo&#34;)&#60;/script&#62;bar world !'
    }]).test('encode ', function(data, assert){
        assert.equal(entity.encode(data.input), data.output);
    });

    QUnit.module('decode');

    QUnit.cases([{
        title : 'no special chars',
        input : 'Hello Foo bar world !',
        output : 'Hello Foo bar world !'
    }, {
        title : 'xss',
        input : 'Hello Foo&#60;script&#62;alert(&#34;foo&#34;)&#60;/script&#62;bar world !',
        output : 'Hello Foo<script>alert("foo")</script>bar world !'
    }]).test('decode ', function(data, assert){
        assert.equal(entity.decode(data.input), data.output);
    });
});


