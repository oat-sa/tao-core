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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
define(['core/statifier'], function (statifier) {
    'use strict';


    var statifierApi = [
        {title: 'getState'},
        {title: 'setState'},
        {title: 'clearStates'},
        {title: 'getStates'}
    ];


    QUnit.module('statifier');


    QUnit.test("api", function (assert) {
        var obj = {
            foo: "bar"
        };

        QUnit.expect(4);

        assert.equal(typeof statifier, 'function', "The module exports a function");
        assert.equal(typeof statifier(), 'object', "The factory returns an object");
        assert.notEqual(statifier(), statifier(), "The factory creates a new instance on each call");
        assert.equal(statifier(obj), obj, "The factory returns the provided object if any");
    });


    QUnit
        .cases(statifierApi)
        .test('method ', function (data, assert) {
            QUnit.expect(1);
            assert.equal(typeof statifier()[data.title], 'function', 'The statifier instance exposes a "' + data.title + '" function');
        });


    QUnit.module('statification');


    QUnit.test("delegates", function (assert) {
        var target = {
            something: function something() {
            },
            foo: "bar"
        };
        var states = statifier(target);

        QUnit.expect(7);

        assert.equal(states, target, 'The factory returned the provided object');
        assert.equal(typeof states.getState, 'function', "the target object holds the method getState()");
        assert.equal(typeof states.setState, 'function', "the target object holds the method setState()");
        assert.equal(typeof states.clearStates, 'function', "the target object holds the method clearStates()");
        assert.equal(typeof states.getStates, 'function', "the target object holds the method getStates()");
        assert.equal(typeof states.something, 'function', "the target object has kept its own methods");
        assert.equal(states.foo, 'bar', "the target object has kept its own properties");
    });


    QUnit.test("setState()/getState()", function(assert) {
        var states = statifier();

        QUnit.expect(11);

        assert.equal(states.getState('foo'), false, 'The state should not exist');

        assert.equal(states.setState('foo', true), states, 'The setState() method should return the instance');
        assert.equal(states.getState('foo'), true, 'The state should exist now');

        assert.equal(states.setState('foo', false), states, 'The setState() method should return the instance');
        assert.equal(states.getState('foo'), false, 'The state should not exist');

        assert.equal(states.setState('foo', 'bar'), states, 'The setState() method should return the instance');
        assert.equal(states.getState('foo'), true, 'A not empty string is equivalent to true, the state should exist');

        assert.equal(states.setState('foo', ''), states, 'The setState() method should return the instance');
        assert.equal(states.getState('foo'), false, 'An empty string is equivalent to false, the state should be removed');

        assert.equal(states.setState('foo'), states, 'The setState() method should return the instance');
        assert.equal(states.getState('foo'), true, 'When no value is provided, the state should be set');
    });


    QUnit.test("clearStates()", function(assert) {
        var states = statifier();

        QUnit.expect(9);

        assert.equal(states.getState('foo'), false, 'The state "foo" should not exist');
        assert.equal(states.setState('foo', true), states, 'The setState() method should return the instance');
        assert.equal(states.getState('foo'), true, 'The state "foo" should exist now');

        assert.equal(states.getState('bar'), false, 'The state "bar" should not exist');
        assert.equal(states.setState('bar', true), states, 'The setState() method should return the instance');
        assert.equal(states.getState('bar'), true, 'The state "bar" should exist now');

        assert.equal(states.clearStates(), states, 'The clearStates() method should return the instance');

        assert.equal(states.getState('foo'), false, 'The state "foo" should be removed');
        assert.equal(states.getState('bar'), false, 'The state "bar" should be removed');
    });


    QUnit.test("getStates()", function(assert) {
        var states = statifier();

        QUnit.expect(10);

        assert.deepEqual(states.getStates(), [], 'No state should exist');

        assert.equal(states.getState('foo'), false, 'The state "foo" should not exist');
        assert.equal(states.setState('foo', true), states, 'The setState() method should return the instance');
        assert.equal(states.getState('foo'), true, 'The state "foo" should exist now');

        assert.equal(states.getState('bar'), false, 'The state "bar" should not exist');
        assert.equal(states.setState('bar', true), states, 'The setState() method should return the instance');
        assert.equal(states.getState('bar'), true, 'The state "bar" should exist now');

        assert.deepEqual(states.getStates(), ['foo', 'bar'], 'The states "foo" and "bar" should be set');

        assert.equal(states.setState('foo', false), states, 'The setState() method should return the instance');

        assert.deepEqual(states.getStates(), ['bar'], 'Now only she states "bar" should be set');

    });

});
