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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

/**
 * Test the digest module
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['core/digest'], function(digest){
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        assert.equal(typeof digest, 'function', "The module exposes an function");
    });


    QUnit.module('Behavior');

    QUnit.test('valid inputs', function(assert) {

        assert.throws(function(){
            digest();
        }, TypeError, 'An input string is required');

        assert.throws(function(){
            digest({});
        }, TypeError, 'An input string is required');

        assert.throws(function(){
            digest('foo', 'MD5');
        }, TypeError, 'A valid algorithm is required');

        //do not throw
        digest('foo');
    });

    QUnit.cases([{
        title : 'short text SHA-1',
        input : 'lorem',
        algo  : 'SHA-1',
        output : 'b58e92fff5246645f772bfe7a60272f356c0151a'
    }, {
        title : 'short text default algo (SHA-256)',
        input : 'lorem',
        output : '3400bb495c3f8c4c3483a44c6bc1a92e9d94406db75a6f27dbccc11c76450d8a'
    }, {
        title : 'short text lower case algo',
        input : 'lorem',
        algo  : 'sha-256',
        output : '3400bb495c3f8c4c3483a44c6bc1a92e9d94406db75a6f27dbccc11c76450d8a'
    }, {
        title : 'long text SHA-256',
        input : 'Earum nobis nulla veniam aut sapiente vel. Voluptate praesentium sed et beatae',
        algo  : 'SHA-256',
        output : '2dc12c750a45d5bd7b51ec9186a4be0e07e70b11218efe471d4677b66034b303'
    }, {
        title : 'long text SHA-512',
        input : 'Earum nobis nulla veniam aut sapiente vel. Voluptate praesentium sed et beatae',
        algo  : 'SHA-512',
        output : '00d7c8367e02fb59989bb05fa86421d7afbbf397b0babc2d2f2fb02da9ec65092e782242ac47a912de2d9e6979c543c1b42a93f2ca258a07f0095e67d28571e6'
    }]).asyncTest('digest', function(data, assert) {

        QUnit.expect(1);

        digest(data.input, data.algo)
            .then(function(hash){
                assert.equal(hash, data.output, 'The generated hash matches');
                QUnit.start();
            })
            .catch(function(err){
                assert.ok(false, err.message);
                QUnit.start();
            });
    });
});