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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test the areaBroker
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/areaBroker'
], function ($, _, areaBroker){
    'use strict';

    var fixture = '#qunit-fixture';
    var required   = ['header', 'footer', 'body'];

    QUnit.module('API');

    QUnit.test('module', function (assert){
        QUnit.expect(1);

        assert.equal(typeof areaBroker, 'function', "The module exposes a function");
    });

    QUnit.test('factory', function (assert){
        var $fixture = $(fixture);

        var $container = $('.container', $fixture);

        var $header     = $('.header', $container);
        var $footer     = $('.footer', $container);
        var $body       = $('.body', $container);
        var $panel      = $('.panel', $container);

        var mapping    = {
            'header'     : $header,
            'footer'     : $footer,
            'body'       : $body,
            'panel'      : $panel
        };

        QUnit.expect(7);

        assert.ok($container.length,  "The container exists");

        assert.throws(function(){
            areaBroker();
        }, TypeError, 'A broker must be created with a container');

        assert.throws(function(){
            areaBroker(required, 'foo');
        }, TypeError, 'A broker must be created with an existing container');

        assert.throws(function(){
            areaBroker(required, $container);
        }, TypeError, 'A broker must be created with an area mapping');

        assert.throws(function(){
            areaBroker(required, $container, {
                'header'     : $header
            });
        }, TypeError, 'A broker must be created with an full area mapping');


        assert.equal(typeof areaBroker(required, $container, mapping), 'object', "The factory creates an object");
        assert.notEqual(areaBroker(required, $container, mapping), areaBroker(required, $container, mapping), "The factory creates new instances");
    });

    QUnit.test('broker api', function (assert){
        var $fixture = $(fixture);
        var $container = $('.container', $fixture);
        var $header     = $('.header', $container);
        var $footer     = $('.footer', $container);
        var $body       = $('.body', $container);
        var $panel      = $('.panel', $container);
        var mapping    = {
            'header'     : $header,
            'footer'     : $footer,
            'body'       : $body,
            'panel'      : $panel
        };
        var broker = areaBroker(required, $container, mapping);

        QUnit.expect(4);

        assert.ok($container.length,  "The container exists");

        assert.equal(typeof broker.defineAreas, 'function', 'The broker has a defineAreas function');
        assert.equal(typeof broker.getContainer, 'function', 'The broker has a getContainer function');
        assert.equal(typeof broker.getArea, 'function', 'The broker has a getArea function');
    });

    QUnit.module('Area mapping');

    QUnit.test('define mapping', function (assert){
        var $fixture = $(fixture);

        var $container = $('.container', $fixture);

        var $header     = $('.header', $container);
        var $footer     = $('.footer', $container);
        var $body       = $('.body', $container);
        var $panel      = $('.panel', $container);
        var mapping    = {
            'header'     : $header,
            'footer'     : $footer,
            'body'       : $body,
            'panel'      : $panel
        };
        var broker = areaBroker(required, $container, mapping);

        QUnit.expect(9);

        assert.ok($container.length,  "The container exists");


        assert.throws(function(){
            broker.defineAreas();
        }, TypeError, 'requires a mapping object');

        assert.throws(function(){
            broker.defineAreas({});
        }, TypeError, 'required mapping missing');

        assert.throws(function(){
            broker.defineAreas({
                'body'       : $body,
                'panel'      : $panel
            });
        }, TypeError, 'required mapping incomplete');

        broker.defineAreas(mapping);

        assert.deepEqual(broker.getArea('header'), $header, 'The area match');
        assert.deepEqual(broker.getArea('footer'), $footer, 'The area match');
        assert.deepEqual(broker.getArea('body'), $body, 'The area match');
        assert.deepEqual(broker.getArea('panel'), $panel, 'The area match');

        assert.equal(broker.getArea('foo'), undefined, 'The area does not exists');

    });

    QUnit.test('getArea aliases', function (assert){
        QUnit.expect(5);
        var $fixture = $(fixture);
        var $container = $('.container', $fixture);

        assert.ok($container.length,  "The container exists");

        var $header     = $('.header', $container);
        var $footer     = $('.footer', $container);
        var $body       = $('.body', $container);
        var $panel      = $('.panel', $container);
        var mapping    = {
            'header'     : $header,
            'footer'     : $footer,
            'body'       : $body,
            'panel'      : $panel
        };
        var broker = areaBroker(required, $container, mapping);

        assert.deepEqual(broker.getHeaderArea(), $header, 'The area match');
        assert.deepEqual(broker.getFooterArea(), $footer, 'The area match');
        assert.deepEqual(broker.getBodyArea(), $body, 'The area match');
        assert.ok(typeof broker.getPanelArea === 'undefined', 'aliases are available only for required areas');
    });


    QUnit.module('container');

    QUnit.test('retrieve', function (assert){
        QUnit.expect(2);
        var $fixture = $(fixture);
        var $container = $('.container', $fixture);
        var $header     = $('.header', $container);
        var $footer     = $('.footer', $container);
        var $body       = $('.body', $container);
        var $panel      = $('.panel', $container);
        var mapping    = {
            'header'     : $header,
            'footer'     : $footer,
            'body'       : $body,
            'panel'      : $panel
        };

        assert.ok($container.length,  "The container exists");

        var broker = areaBroker(required, $container, mapping);

        assert.deepEqual(broker.getContainer(), $container, 'The container match');
    });

    function getTestBroker() {
        var $fixture = $(fixture);
        var $container = $('.container', $fixture);

        var $header     = $('.header', $container);
        var $footer     = $('.footer', $container);
        var $body       = $('.body', $container);
        var $panel      = $('.panel', $container);
        var mapping    = {
            'header'     : $header,
            'footer'     : $footer,
            'body'       : $body,
            'panel'      : $panel
        };
        return areaBroker(required, $container, mapping);
    }

    QUnit.module('components');

    QUnit.test('addComponent / getComponent correct behavior', function (assert) {
        var broker = getTestBroker();

        var $headerComponent1 = $('<div>', { html: 'header component 1' }),
            $headerComponent2 = $('<div>', { html: 'header component 2' });

        QUnit.expect(2);

        broker.addComponent('header', 'headerComponent1', $headerComponent1);
        broker.addComponent('header', 'headerComponent2', $headerComponent2);

        assert.ok(broker.getComponent('header', 'headerComponent1') === $headerComponent1, 'the component match');
        assert.ok(broker.getComponent('header', 'headerComponent2') === $headerComponent2, 'the component match');
    });

    QUnit.test('addComponent incorrect use', function (assert) {
        var broker = getTestBroker();

        QUnit.expect(5);

        assert.throws(function() {
            broker.addComponent('unknownArea');
        }, TypeError, 'addComponent requires a valid area name');

        assert.throws(function() {
            broker.addComponent('header');
        }, TypeError, 'addComponent requires a valid componentId');

        assert.throws(function() {
            broker.addComponent('header', 'headerComponent1');
        }, TypeError, 'addComponent requires a valid component');

        assert.throws(function() {
            broker.addComponent('header', 'headerComponent1', {});
        }, TypeError, 'addComponent requires a valid component');

        assert.throws(function() {
            broker.addComponent('header', 'headerComponent1', function() { });
        }, TypeError, 'addComponent requires a valid component');
    });

    QUnit.test('getComponent incorrect use', function (assert) {
        var broker = getTestBroker();

        QUnit.expect(2);

        assert.ok(
            _.isUndefined(broker.getComponent('unknownArea')),
            'requesting a component of an unknown area returns undefined'
        );

        assert.ok(
            _.isUndefined(broker.getComponent('header', 'unknownComponent')),
            'requesting an unknown component returns undefined'
        );
    });

    QUnit.test('components aliases', function (assert){
        QUnit.expect(5);
        var $fixture = $(fixture);
        var $container = $('.container', $fixture);

        assert.ok($container.length,  "The container exists");

        var $header     = $('.header', $container);
        var $footer     = $('.footer', $container);
        var $body       = $('.body', $container);
        var $panel      = $('.panel', $container);
        var mapping    = {
            'header'     : $header,
            'footer'     : $footer,
            'body'       : $body,
            'panel'      : $panel
        };
        var broker = areaBroker(required, $container, mapping);

        assert.ok(typeof (broker.addHeaderComponent) === 'function', 'the broker has a addHeaderComponent method');
        assert.ok(typeof (broker.addFooterComponent) === 'function', 'the broker has a addFooterComponent method');
        assert.ok(typeof (broker.addBodyComponent) === 'function',   'the broker has a addBodyComponent method');
        assert.ok(typeof (broker.addPanelComponent) === 'undefined', 'aliases are available only for required areas');
        // assert.deepEqual(broker.getFooterArea(), $footer, 'The area match');
        // assert.deepEqual(broker.getBodyArea(), $body, 'The area match');
        // assert.ok(typeof broker.getPanelArea === 'undefined', 'aliases are available only for required areas');
    });

});
