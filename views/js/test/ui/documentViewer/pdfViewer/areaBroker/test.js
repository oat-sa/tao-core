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
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'ui/documentViewer/providers/pdfViewer/pdfjs/areaBroker'
], function ($, areaBroker) {
    'use strict';

    var fixture = '#qunit-fixture';


    QUnit.module('API');


    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.equal(typeof areaBroker, 'function', "The module exposes a function");
    });


    QUnit.test('factory', function (assert) {
        var $fixture = $(fixture);
        var $container = $('.viewer', $fixture);
        var $bar = $('.bar', $container);
        var $actions = $('.actions', $container);
        var $info = $('.info', $container);
        var $content = $('.content', $container);
        var mapping = {
            'bar': $bar,
            'actions': $actions,
            'info': $info,
            'content': $content
        };

        QUnit.expect(7);

        assert.ok($container.length, "The container exists");

        assert.throws(function () {
            areaBroker();
        }, TypeError, 'A broker must be created with a container');

        assert.throws(function () {
            areaBroker('foo');
        }, TypeError, 'A broker must be created with an existing container');

        assert.throws(function () {
            areaBroker($container);
        }, TypeError, 'A broker must be created with an area mapping');

        assert.throws(function () {
            areaBroker($container, {
                content: $content
            });
        }, TypeError, 'A broker must be created with a full area mapping');


        assert.equal(typeof areaBroker($container, mapping), 'object', "The factory creates an object");
        assert.notEqual(areaBroker($container, mapping), areaBroker($container, mapping), "The factory creates new instances");
    });


    QUnit.test('broker api', function (assert) {
        var $fixture = $(fixture);
        var $container = $('.viewer', $fixture);
        var $bar = $('.bar', $container);
        var $actions = $('.actions', $container);
        var $info = $('.info', $container);
        var $content = $('.content', $container);
        var mapping = {
            'bar': $bar,
            'actions': $actions,
            'info': $info,
            'content': $content
        };
        var broker = areaBroker($container, mapping);

        QUnit.expect(4);

        assert.ok($container.length, "The container exists");
        assert.equal(typeof broker.defineAreas, 'function', 'The broker has a defineAreas function');
        assert.equal(typeof broker.getContainer, 'function', 'The broker has a getContainer function');
        assert.equal(typeof broker.getArea, 'function', 'The broker has a getArea function');
    });


    QUnit.module('Area mapping');


    QUnit.test('define mapping', function (assert) {
        var $fixture = $(fixture);
        var $container = $('.viewer', $fixture);
        var $bar = $('.bar', $container);
        var $actions = $('.actions', $container);
        var $info = $('.info', $container);
        var $content = $('.content', $container);
        var mapping = {
            'bar': $bar,
            'actions': $actions,
            'info': $info,
            'content': $content
        };
        var broker = areaBroker($container, mapping);

        QUnit.expect(8);

        assert.ok($container.length, "The container exists");

        assert.throws(function () {
            broker.defineAreas();
        }, TypeError, 'requires a mapping object');

        assert.throws(function () {
            broker.defineAreas({});
        }, TypeError, 'required mapping missing');

        assert.throws(function () {
            broker.defineAreas({
                'content': $content,
                'bar': $bar
            });
        }, TypeError, 'required mapping incomplete');

        broker.defineAreas(mapping);

        assert.deepEqual(broker.getArea('content'), $content, 'The content area match');
        assert.deepEqual(broker.getArea('bar'), $bar, 'The bar area match');
        assert.deepEqual(broker.getArea('actions'), $actions, 'The actions area match');
        assert.deepEqual(broker.getArea('info'), $info, 'The info area match');
    });


    QUnit.test('aliases', function (assert) {
        var $fixture = $(fixture);
        var $container = $('.viewer', $fixture);
        var $bar = $('.bar', $container);
        var $actions = $('.actions', $container);
        var $info = $('.info', $container);
        var $content = $('.content', $container);
        var mapping = {
            'bar': $bar,
            'actions': $actions,
            'info': $info,
            'content': $content
        };
        var broker = areaBroker($container, mapping);

        QUnit.expect(5);

        assert.ok($container.length, "The container exists");
        assert.deepEqual(broker.getContentArea(), $content, 'The content area match');
        assert.deepEqual(broker.getBarArea(), $bar, 'The bar area match');
        assert.deepEqual(broker.getActionsArea(), $actions, 'actions The area match');
        assert.deepEqual(broker.getInfoArea(), $info, 'The info area match');
    });


    QUnit.module('container');


    QUnit.test('retrieve', function (assert) {
        var $fixture = $(fixture);
        var $container = $('.viewer', $fixture);
        var $bar = $('.bar', $container);
        var $actions = $('.actions', $container);
        var $info = $('.info', $container);
        var $content = $('.content', $container);
        var mapping = {
            'bar': $bar,
            'actions': $actions,
            'info': $info,
            'content': $content
        };
        var broker = areaBroker($container, mapping);

        QUnit.expect(2);

        assert.ok($container.length, "The container exists");
        assert.deepEqual(broker.getContainer(), $container, 'The container match');
    });
});
