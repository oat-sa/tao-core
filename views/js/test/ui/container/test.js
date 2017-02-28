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
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/container'
], function($, _, container) {
    'use strict';

    var containerApi = [
        { title : 'init' },
        { title : 'destroy' },
        { title : 'hasScope' },
        { title : 'changeScope' },
        { title : 'find' },
        { title : 'write' },
        { title : 'getData' },
        { title : 'setData' },
        { title : 'hasValue' },
        { title : 'getValue' },
        { title : 'setValue' },
        { title : 'getElement' },
        { title : 'getSelector' }
    ];


    QUnit.module('container');


    QUnit.test('module', function(assert) {
        QUnit.expect(3);
        assert.equal(typeof container, 'function', "The container module exposes a function");
        assert.equal(typeof container(), 'object', "The container factory produces an object");
        assert.notStrictEqual(container(), container(), "The container factory provides a different object on each call");
    });


    QUnit
        .cases(containerApi)
        .test('instance API ', function(data, assert) {
            var instance = container();
            QUnit.expect(1);
            assert.equal(typeof instance[data.title], 'function', 'The container instance exposes a "' + data.title + '" function');
            instance.destroy();
        });


    QUnit.test('init', function(assert) {

        var instance = container();

        QUnit.expect(9);

        assert.equal(typeof instance.getElement(), 'object', 'A container object exists');
        assert.equal(instance.getElement().length, 1, 'A container has been caught');
        assert.equal(instance.getSelector(), '.container', 'The default container selector has been set');

        assert.equal(instance.destroy(), instance, 'destroy() returns the instance');

        assert.equal(instance.getElement(), null, 'The container has been destroyed');

        assert.throws(function() {
            instance.init();
        }, "The instance should throw an error if no selector is provided");

        assert.throws(function() {
            instance.init(10);
        }, "The instance should throw an error if an invalid selector is provided");

        assert.equal(instance.init('.foo'), instance, 'init() returns the instance');
        assert.equal(instance.getSelector(), '.foo', 'The container selector has been set');

        instance.destroy();
    });


    QUnit.test('scope', function(assert) {

        var instance = container();

        QUnit.expect(8);

        assert.equal(typeof instance.getElement(), 'object', 'A container object exists');
        assert.equal(instance.getElement().length, 1, 'A container has been caught');
        assert.equal(instance.getSelector(), '.container', 'The default container selector has been set');

        assert.ok(instance.hasScope('.fixture'), 'The container has the wanted scope');
        assert.equal(instance.changeScope('.foo'), instance, 'changeScope() returns the instance');

        assert.ok(!instance.hasScope('.fixture'), 'The container does not have the .fixture scope anymore');
        assert.ok(instance.hasScope('.foo'), 'The container have the .foo scope');

        assert.equal(instance.getElement().attr('class'), 'container foo', 'The container has the expected CSS class');

        instance.destroy();
    });


    QUnit.test('find', function(assert) {

        var instance = container();

        QUnit.expect(5);

        assert.equal(typeof instance.getElement(), 'object', 'A container object exists');
        assert.equal(instance.getElement().length, 1, 'A container has been caught');

        assert.equal(typeof instance.find('.child'), 'object', 'The searched element exists');
        assert.equal(instance.find('.child').length, 1, 'The searched element has been caught');
        assert.equal(instance.find('.child').get(0), $('#qunit-fixture .child').get(0), 'The right element has been caught');

        instance.destroy();
    });


    QUnit.test('write', function(assert) {

        var instance = container('.paper');

        QUnit.expect(6);

        assert.equal(typeof instance.getElement(), 'object', 'A container object exists');
        assert.equal(instance.getElement().length, 1, 'A container has been caught');
        assert.equal(instance.getElement().children().length, 0, 'The container is empty');

        assert.equal(instance.write('<div>foo</div>'), instance, 'write() returns the instance');

        assert.equal(instance.getElement().children().length, 1, 'The container is not empty');
        assert.equal(instance.getElement().html().trim(), '<div>foo</div>', 'The container contains the right content');

        instance.destroy();
    });


    QUnit.test('data', function(assert) {

        var instance = container('.data');

        QUnit.expect(14);

        assert.equal(typeof instance.getElement(), 'object', 'A container object exists');
        assert.equal(instance.getElement().length, 1, 'A container has been caught');

        assert.deepEqual(instance.getValue('list'), [1, 2, 3], "The 'list' value has been read");
        assert.deepEqual(instance.getValue('record'), {foo: "bar"}, "The 'record' value has been read");

        assert.equal(instance.setValue('list', ["foo"]), instance, 'The method setValue returns the instance');
        assert.deepEqual(instance.getValue('list'), ["foo"], "The 'list' value has been changed");

        assert.deepEqual(instance.getData(), {list: ["foo"], record: {foo: "bar"}}, "The values has been read");
        assert.equal(instance.setData({foo: "bar"}), instance, 'The method setData returns the instance');
        assert.deepEqual(instance.getData(), {foo: "bar"}, "The values has been changed");

        assert.equal(instance.hasValue('foo'), true, "The container has the value 'foo'");
        assert.equal(instance.hasValue('bar'), false, "The container does not have the value 'bar");

        instance.setValue('empty', 0);
        assert.equal(instance.hasValue('empty'), true, "The container has the value 'empty'");

        assert.equal(instance.removeData(), instance, 'The method removeData returns the instance');
        assert.deepEqual(instance.getData(), {}, "The values has been removed");

        instance.destroy();
    });

});
