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
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/areaBroker',
    'ui/areaComponent'
], function ($, _, areaBroker, areaComponentFactory){
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
        { method: 'getComponent' },
        { method: 'addElement' },
        { method: 'getElement' },
        { method: 'initAll' },
        { method: 'renderAll' },
        { method: 'destroyAll' }
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


    QUnit.module('Elements');

    QUnit.test('addElement / getElement expected behavior', function (assert) {
        var broker = getTestBroker();

        var $headerElement1 = $('<div>', { html: 'header element 1' }),
            $headerElement2 = $('<div>', { html: 'header element 2' });

        QUnit.expect(2);

        broker.addElement('header', 'headerElement1', $headerElement1);
        broker.addElement('header', 'headerElement2', $headerElement2);

        assert.ok(broker.getElement('header', 'headerElement1') === $headerElement1, 'the element match');
        assert.ok(broker.getElement('header', 'headerElement2') === $headerElement2, 'the element match');
    });

    QUnit.test('addElement incorrect use', function (assert) {
        var broker = getTestBroker();

        QUnit.expect(7);

        assert.throws(function() {
            broker.addElement();
        }, TypeError, 'addElement requires a valid area name');

        assert.throws(function() {
            broker.addElement('unknownArea');
        }, TypeError, 'addElement requires a valid area name');

        assert.throws(function() {
            broker.addElement('header');
        }, TypeError, 'addElement requires a valid elementId');

        assert.throws(function() {
            broker.addElement('header', 'headerElement1');
        }, TypeError, 'addElement requires a valid element');

        assert.throws(function() {
            broker.addElement('header', 'headerElement1', {});
        }, TypeError, 'addElement requires a valid element');

        assert.throws(function() {
            broker.addElement('header', 'headerElement1', function() { });
        }, TypeError, 'addElement requires a valid element');

        assert.throws(function() {
            broker.addElement('header', 'headerComp1', '<div>my first element</div>');
            broker.addElement('header', 'headerComp1', '<div>my second element has the same id than the first one!</div>');
        }, TypeError, 'addElement requires a unique element id');
    });

    QUnit.test('getElement incorrect use', function (assert) {
        var broker = getTestBroker();
        QUnit.expect(2);

        assert.ok(
            _.isUndefined(broker.getElement('unknownArea')),
            'requesting a element of an unknown area returns undefined'
        );

        assert.ok(
            _.isUndefined(broker.getElement('header', 'unknownElement')),
            'requesting an unknown element returns undefined'
        );
    });

    QUnit.test('addElement aliases', function (assert){
        var broker = getTestBroker();

        var $headerElement1 = $('<div>', { html: 'header element 1' }),
            $headerElement2 = $('<div>', { html: 'header element 2' });

        QUnit.expect(6);

        assert.ok(typeof (broker.addHeaderElement) === 'function', 'the broker has a addHeaderElement method');
        assert.ok(typeof (broker.addFooterElement) === 'function', 'the broker has a addFooterElement method');
        assert.ok(typeof (broker.addBodyElement) === 'function',   'the broker has a addBodyElement method');
        assert.ok(typeof (broker.addPanelElement) === 'undefined', 'aliases are available only for required areas');

        broker.addHeaderElement('headerElement1', $headerElement1);
        broker.addHeaderElement('headerElement2', $headerElement2);

        assert.ok(broker.getElement('header', 'headerElement1') === $headerElement1, 'the element match');
        assert.ok(broker.getElement('header', 'headerElement2') === $headerElement2, 'the element match');
    });


    QUnit.module('Components');

    QUnit.test('default component', function (assert) {
        var $fixture = $(fixture),
            $container = $('.container', $fixture),
            $body = $('.body', $container);

        var broker = getTestBroker();

        var $bodyElement1 = $('<div>', { class: 'body-element-1', html: 'body element 1 content' }),
            $bodyElement2 = $('<div>', { class: 'body-element-2', html: 'body element 2 content' }),
            $bodyElement3 = $('<div>', { class: 'body-element-3', html: 'body element 3 content' }),
            $bodyElement;

        QUnit.expect(7);

        broker.addBodyElement('body-2', $bodyElement2);
        broker.addBodyElement('body-1', $bodyElement1);
        broker.addBodyElement('body-3', $bodyElement3);

        broker.renderAll();

        $bodyElement = $body.find('.body-element-1');
        assert.equal($bodyElement.length, 1, 'body element 1has been rendered');
        assert.equal($bodyElement.html(), 'body element 1 content', 'the element contains the right content');

        $bodyElement = $body.find('.body-element-2');
        assert.equal($bodyElement.length, 1, 'body element 2 has been rendered');
        assert.equal($bodyElement.html(), 'body element 2 content', 'the element contains the right content');

        $bodyElement = $body.find('.body-element-3');
        assert.equal($bodyElement.length, 1, 'body element 3 has been rendered');
        assert.equal($bodyElement.html(), 'body element 3 content', 'the element contains the right content');

        assert.equal(
            $body.text(),
            'body element 2 contentbody element 1 contentbody element 3 content',
            'elements have been rendered in the right order'
        );
    });

    QUnit.test('setComponent expected behavior', function (assert) {
        var $fixture = $(fixture),
            $container = $('.container', $fixture),
            $body = $('.body', $container);

        var broker = getTestBroker();

        var $result;

        var testComponent = areaComponentFactory()
            .on('render', function testRenderer($areaContainer) {
                var allElements = this.getElements();

                $areaContainer.append($('<div>', {
                    class: 'custom-rendered',
                    html: 'I have been rendered using a custom renderer'
                }));

                assert.equal(allElements[0].id, 'element5',             'element 1 is correct');
                assert.equal(allElements[0].$element.text(), 'content5','element 1 is correct');
                assert.equal(allElements[1].id, 'element1',             'element 2 is correct');
                assert.equal(allElements[1].$element.text(), 'content1','element 2 is correct');
                assert.equal(allElements[2].id, 'element3',             'element 3 is correct');
                assert.equal(allElements[2].$element.text(), 'content3','element 3 is correct');
                assert.equal(allElements[3].id, 'element2',             'element 4 is correct');
                assert.equal(allElements[3].$element.text(), 'content2','element 4 is correct');
                assert.equal(allElements[4].id, 'element4',             'element 5 is correct');
                assert.equal(allElements[4].$element.text(), 'content4','element 5 is correct');
                assert.equal(allElements.length, 5, 'callback has been passed the right elements in the right order');
            })
            .init();

        QUnit.expect(13);

        broker.addBodyElement('element5', '<div>content5</div>');
        broker.addBodyElement('element1', '<div>content1</div>');
        broker.addBodyElement('element3', '<div>content3</div>');
        broker.addBodyElement('element2', '<div>content2</div>');
        broker.addBodyElement('element4', '<div>content4</div>');

        broker.setComponent('body', testComponent);

        broker.renderAll();

        $result = $body.find('.custom-rendered');
        assert.equal($result.length, 1, 'custom renderer has been used');
        assert.equal($result.text(), 'I have been rendered using a custom renderer', 'custom renderer has been used');
    });

    QUnit.test('setComponent incorrect use', function (assert) {
        var broker = getTestBroker();

        QUnit.expect(6);

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

        assert.throws(function() {
            broker.setRenderer('header', 'headerComp1', areaComponentFactory());
            broker.setRenderer('header', 'headerComp1', areaComponentFactory());
        }, TypeError, 'setComponent requires a unique component id');
    });

    QUnit.test('setComponent aliases', function (assert) {
        var broker = getTestBroker();

        QUnit.expect(4);

        assert.ok(typeof (broker.setHeaderComponent) === 'function',  'the broker has a setHeaderComponent method');
        assert.ok(typeof (broker.setFooterComponent) === 'function',  'the broker has a setFooterComponent method');
        assert.ok(typeof (broker.setBodyComponent)   === 'function',  'the broker has a setBodyComponent method');
        assert.ok(typeof (broker.setPanelComponent)  === 'undefined', 'aliases are available only for required areas');
    });

    QUnit.test('getComponent expected behavior', function (assert) {
        var broker = getTestBroker();

        var customComponent = areaComponentFactory(),
            boundComponent;

        QUnit.expect(2);

        broker.setHeaderComponent(customComponent);

        boundComponent = broker.getComponent('header');
        assert.ok(customComponent === boundComponent, 'getComponent returns the correct component');

        boundComponent = broker.getHeader();
        assert.ok(customComponent === boundComponent, 'getComponent returns the correct component');
    });

    QUnit.module('component lifecycle');

    QUnit.test('initAll() / renderAll() / destoryAll()', function (assert) {
        var $fixture = $(fixture),
            $container = $('.container', $fixture);

        var $header = $('.header', $container),
            $footer = $('.footer', $container),
            $body   = $('.body', $container),
            $panel  = $('.panel', $container);

        var broker = getTestBroker();

        QUnit.expect(8);

        broker.setHeaderComponent(areaComponentFactory()
            .on('render', function($areaContainer) {
                $areaContainer.append('header content');
            }));

        broker.setFooterComponent(areaComponentFactory()
            .on('render', function($areaContainer) {
                $areaContainer.append('footer content');
            }));

        broker.setBodyComponent(areaComponentFactory()
            .on('render', function($areaContainer) {
                $areaContainer.append('body content');
            }));

        broker.setComponent('panel', areaComponentFactory()
            .on('render', function($areaContainer) {
                $areaContainer.append('panel content');
            }));

        broker.initAll();

        broker.renderAll();

        assert.equal($header.text(), 'header content', 'header has been rendered');
        assert.equal($footer.text(), 'footer content', 'footer has been rendered');
        assert.equal($body.text(), 'body content', 'body has been rendered');
        assert.equal($panel.text(), 'panel content', 'panel has been rendered');

        broker.destroyAll();

        assert.equal($header.text(), '', 'header component has been destroy');
        assert.equal($footer.text(), '', 'footer component has been destroy');
        assert.equal($body.text(), '', 'body component has been destroy');
        assert.equal($panel.text(), '', 'panel component has been destroy');
    });


});
