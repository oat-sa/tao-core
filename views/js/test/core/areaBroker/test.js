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
    'core/areaBroker',
    'ui/component'
], function ($, _, areaBroker, componentFactory){
    'use strict';

    var fixture = '#qunit-fixture';
    var required   = ['header', 'footer', 'body'];

    var brokerApi;

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

    brokerApi = [
        { method: 'defineAreas' },
        { method: 'getContainer' },
        { method: 'getArea' },
        { method: 'setComponent' },
        { method: 'getComponent' }
    ];

    QUnit
        .cases(brokerApi)
        .test('broker api', function (data, assert){
            var broker = getTestBroker();
            QUnit.expect(1);
            assert.equal(typeof broker[data.method], 'function', 'The broker has the method ' + data.method);
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

        QUnit.expect(10);

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

        broker = areaBroker([], $container, {});
        assert.throws(function() {
            broker.getArea('unknown');
        }, Error, 'trying to get an area without a mapping defined throws an error');


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

    QUnit.test('setComponent expected behavior', function (assert) {
        var $fixture = $(fixture),
            $container = $('.container', $fixture),
            $body = $('.body', $container);

        var broker = getTestBroker();

        var $result;

        var testComponent = componentFactory()
            .on('render', function testRenderer($areaContainer) {
                $areaContainer.append($('<div>', {
                    class: 'custom-rendered',
                    html: 'I have been rendered using a custom renderer'
                }));
            })
            .init();

        QUnit.expect(3);

        broker.setComponent('body', testComponent);

        assert.ok(_.isFunction(broker['getBody']), 'an alias has been set to retrieve the component');

        broker.getBody().render($body);

        $result = $body.find('.custom-rendered');
        assert.equal($result.length, 1, 'custom renderer has been used');
        assert.equal($result.text(), 'I have been rendered using a custom renderer', 'custom renderer has been used');
    });

    QUnit.test('setComponent incorrect use', function (assert) {
        var broker = getTestBroker();

        QUnit.expect(5);

        assert.throws(function() {
            broker.setComponent();
        }, TypeError, 'setComponent requires a valid area name');

        assert.throws(function() {
            broker.setComponent('unknownArea');
        }, TypeError, 'setComponent requires a valid area name');

        assert.throws(function() {
            broker.setComponent('header');
        }, TypeError, 'setComponent requires a valid component');

        assert.throws(function() {
            broker.setComponent('header', 'component');
        }, TypeError, 'setComponent requires a valid component');

        assert.throws(function() {
            broker.setComponent('header', 'headerElement1', function() { });
        }, TypeError, 'setComponent requires a valid component');

    });

    QUnit.test('getComponent expected behavior', function (assert) {
        var broker = getTestBroker();

        var customComponent = componentFactory(),
            boundComponent;

        QUnit.expect(2);

        broker.setComponent('header', customComponent);

        boundComponent = broker.getComponent('header');
        assert.ok(customComponent === boundComponent, 'getComponent returns the correct component');

        boundComponent = broker.getHeader();
        assert.ok(customComponent === boundComponent, 'getHeader alias is correctly set');
    });


});
