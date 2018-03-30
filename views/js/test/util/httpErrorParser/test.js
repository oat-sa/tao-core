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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

define(['util/httpErrorParser'], function(httpErrorParser){

    'use strict';

    QUnit.test('Helper API', function(assert){
        QUnit.expect(2);
        assert.ok(typeof httpErrorParser === 'object');
        assert.ok(typeof httpErrorParser.parse === 'function');
    });

    QUnit.test('Parse error with responseText', function(assert){
        var xhr = {
            responseText : '{"success":false,"type":"Exception","message":"Foo"}',
            status : 500
        };
        var errorThrown = 'Bad request';
        var err = httpErrorParser.parse(xhr, '', errorThrown);

        QUnit.expect(5);

        assert.ok(err instanceof Error);
        assert.equal(err.message, 'Foo');
        assert.equal(err.response, xhr);
        assert.equal(err.code, 500);
        assert.equal(err.errorThrown, errorThrown);
    });

    QUnit.test('Parse error without responseText', function(assert){
        var xhr = {};
        var errorThrown = 'Bad request';
        var err = httpErrorParser.parse(xhr, '', errorThrown);

        QUnit.expect(1);

        assert.equal(err.message, 'Bad request');
    });
});


