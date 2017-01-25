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
        var broker = getTestBroker();

        QUnit.expect(3);

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

        assert.ok(typeof (broker.getArea('foo')) === 'undefined', 'The area does not exists');

    });

    QUnit.test('getArea aliases', function (assert){
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

        QUnit.expect(5);

        assert.ok($container.length,  "The container exists");

        assert.deepEqual(broker.getHeaderArea(), $header, 'The area match');
        assert.deepEqual(broker.getFooterArea(), $footer, 'The area match');
        assert.deepEqual(broker.getBodyArea(), $body, 'The area match');
        assert.ok(typeof broker.getPanelArea === 'undefined', 'aliases are available only for required areas');
    });


    QUnit.module('container');

    QUnit.test('retrieve', function (assert){
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

        QUnit.expect(2);

        assert.ok($container.length,  "The container exists");

        assert.deepEqual(broker.getContainer(), $container, 'The container match');
    });


    QUnit.module('Components');

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

        QUnit.expect(6);

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

        assert.throws(function() {
            broker.addComponent('header', 'headerComp1', 'my first component');
            broker.addComponent('header', 'headerComp1', 'my second component has the same id than the first one!');
        }, TypeError, 'addComponent requires a unique component id');
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

    QUnit.test('addComponent aliases', function (assert){
        var broker = getTestBroker();

        var $headerComponent1 = $('<div>', { html: 'header component 1' }),
            $headerComponent2 = $('<div>', { html: 'header component 2' });

        QUnit.expect(6);

        assert.ok(typeof (broker.addHeaderComponent) === 'function', 'the broker has a addHeaderComponent method');
        assert.ok(typeof (broker.addFooterComponent) === 'function', 'the broker has a addFooterComponent method');
        assert.ok(typeof (broker.addBodyComponent) === 'function',   'the broker has a addBodyComponent method');
        assert.ok(typeof (broker.addPanelComponent) === 'undefined', 'aliases are available only for required areas');

        broker.addHeaderComponent('headerComponent1', $headerComponent1);
        broker.addHeaderComponent('headerComponent2', $headerComponent2);

        assert.ok(broker.getComponent('header', 'headerComponent1') === $headerComponent1, 'the component match');
        assert.ok(broker.getComponent('header', 'headerComponent2') === $headerComponent2, 'the component match');
    });


    QUnit.asyncTest('default renderer', function (assert) {
        var $fixture = $(fixture),
            $container = $('.container', $fixture),
            $body = $('.body', $container);

        var broker = getTestBroker();

        var $bodyComponent1 = $('<div>', { class: 'body-component-1', html: 'body component 1 content' }),
            $bodyComponent2 = $('<div>', { class: 'body-component-2', html: 'body component 2 content' }),
            $bodyComponent3 = $('<div>', { class: 'body-component-3', html: 'body component 3 content' }),
            $bodyComponent;

        broker.addBodyComponent('body-2', $bodyComponent2);
        broker.addBodyComponent('body-1', $bodyComponent1);
        broker.addBodyComponent('body-3', $bodyComponent3);

        broker.render('body').then(function() {
            $bodyComponent = $body.find('.body-component-1');
            assert.equal($bodyComponent.length, 1, 'body component 1has been rendered');
            assert.equal($bodyComponent.html(), 'body component 1 content', 'the component contains the right content');

            $bodyComponent = $body.find('.body-component-2');
            assert.equal($bodyComponent.length, 1, 'body component 2 has been rendered');
            assert.equal($bodyComponent.html(), 'body component 2 content', 'the component contains the right content');

            $bodyComponent = $body.find('.body-component-3');
            assert.equal($bodyComponent.length, 1, 'body component 3 has been rendered');
            assert.equal($bodyComponent.html(), 'body component 3 content', 'the component contains the right content');

            assert.equal(
                $body.text(),
                'body component 2 contentbody component 1 contentbody component 3 content',
                'components have been rendered in the right order'
            );

            QUnit.start();
        });

    });

});
