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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define(['util/config'], function(configHelper) {
    'use strict';

    QUnit.module('helpers/config');


    QUnit.test('module', function(assert) {
        QUnit.expect(1);
        assert.equal(typeof configHelper, 'object', "The config helper module exposes an object");
    });


    QUnit.cases([
        { title : 'build' },
        { title : 'from' }
    ]).test('helpers/config API ', function(data, assert) {
        QUnit.expect(1);
        assert.equal(typeof configHelper[data.title], 'function', 'The config helper exposes a "' + data.title + '" function');
    });


    QUnit.test('helpers/config.build', function(assert) {
        var source = {
            foo: 'bar'
        };
        var defaults = {
            foo: 'foo',
            msg: 'hello'
        };
        var expected = {
            foo: 'bar',
            msg: 'hello'
        };

        QUnit.expect(3);

        assert.deepEqual(configHelper.build(), {}, 'The config helper build() returns an empty object if no data is provided');
        assert.deepEqual(configHelper.build(source), source, 'The config helper build() returns the expected config');
        assert.deepEqual(configHelper.build(source, defaults), expected, 'The config helper build() returns the expected config with defaults values');
    });


    QUnit.test('helpers/config.from', function(assert) {
        var source = {
            foo: 'bar',
            tro: 'lolo',
            test: true,
            bar: 'foo',
            n: 10
        };
        var defaults = {
            foo: 'foo',
            msg: 'hello'
        };
        var entries = {
            foo: true,
            yop: false,
            bar: true
        };
        var expected = {
            foo: 'bar',
            bar: 'foo'
        };
        var expectedDefaults = {
            foo: 'bar',
            bar: 'foo',
            msg: 'hello'
        };

        QUnit.expect(4);

        assert.deepEqual(configHelper.from(), {}, 'The config helper from() returns an empty object if no data is provided');
        assert.deepEqual(configHelper.from(source, entries), expected, 'The config helper from() returns the expected config');
        assert.deepEqual(configHelper.from(source, entries, defaults), expectedDefaults, 'The config helper from() returns the expected config with defaults values');

        assert.throws(function() {
            configHelper.from(source, {no: true});
        }, 'The config helper from() throws an error is a required config entry is missing');
    });
});
