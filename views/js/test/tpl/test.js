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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test tpl
 */
define([
    'tpl!test/tpl/samples/dompurify_script',
    'tpl!test/tpl/samples/join_keyvalue',
    'tpl!test/tpl/samples/property',
    'tpl!test/tpl/samples/join_array'
], function(tplDomPurifyScript, tplJoinKeyValue, tplProperty,  tplJoinArray) {
    'use strict';

    QUnit.module('registered handlers');

    QUnit.cases.init([{
        title: 'try to inject a script',
        html: '<b>bold</b><script>alert("dirty!")</script><a href="#">link</a>',
        expected: '<b>bold</b><a href="#">link</a>'
    }, {
        title: 'try to inject a tpl var',
        html: '<b>bold</b><span>{{foo}}</span>',
        expected: '<b>bold</b><span>{{foo}}</span>'
    }, {
        title: 'try to inject an html var',
        html: '<b>bold</b><span>{{{moo}}}</span>',
        expected: '<b>bold</b><span>{{{moo}}}</span>'
    }, {
        title: 'keep data-attr remove onEvent',
        html: '<button data-action="yolo" onclick="hijack()">Hello</button>',
        expected: '<button data-action="yolo">Hello</button>'
    }]).test('dompurify - script', function (data, assert) {

        var rendering = tplDomPurifyScript({
            dirtyHtml: data.html,
            foo: 'bar',
            moo: '<strong>bar</strong>'
        });
        assert.equal(rendering, '<div>' + data.expected + '</div>', 'purified dom rendering ok');
    });

    QUnit.test('join - key value', function(assert) {
        var values = {a: 'v1', b: 'v2', c: 'v3'};
        var rendering = tplJoinKeyValue({
            values: values
        });
        assert.equal(rendering, 'a="v1" b="v2" c="v3"', "join key value rendering ok");
    });

    QUnit.test('join - array value', function(assert) {
        var values = {a: 'v1', b: 'v2', c: 'v3'};
        var rendering = tplJoinArray({
            values: values
        });
        assert.equal(rendering, '*v1* or *v2* or *v3*', 'join array rendering ok');
    });

    QUnit.test('property helper', function(assert) {
        var rendering = tplProperty({
            data: {id: 0}
        });
        console.log(rendering);
        assert.equal(rendering, '<span>0</span>', 'property helper rendering ok');
    });

});
