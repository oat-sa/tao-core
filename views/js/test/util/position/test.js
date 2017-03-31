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
define(['util/position'], function(position) {
    'use strict';


    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(1);
        assert.equal(typeof position, 'object', "The position module exposes an object");
    });

    QUnit.test('methods', function(assert) {
        QUnit.expect(2);
        assert.equal(typeof position.isInside, 'function', "The position module exposes a method isInside");
        assert.equal(typeof position.isOver, 'function', "The position module exposes a method isOver");
    });


    QUnit.module('isInside');

    QUnit.test('simple elements', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof position.isInside(), 'undefined', 'The method returns undefined without valid elements');
        assert.equal(typeof position.isInside(null, null), 'undefined', 'The method returns undefined without valid elements');
        assert.equal(typeof position.isInside({}, {}), 'undefined', 'The method returns undefined without valid elements');
    });

    QUnit.test('simple elements', function(assert) {

        var container        = document.getElementById('qunit-fixture');
        var contentContainer = container.querySelector('.content');
        var volatileElt      = container.querySelector('.volatile');
        var externalElt      = container.querySelector('.external');

        QUnit.expect(6);

        assert.ok(container instanceof HTMLElement, 'The container exists');
        assert.ok(contentContainer instanceof HTMLElement, 'The content container exists');
        assert.ok(volatileElt instanceof HTMLElement, 'The volatile element exists');
        assert.ok(externalElt instanceof HTMLElement, 'The external element exists');

        assert.ok(position.isInside(contentContainer, volatileElt), 'The volatile element is inside the content element');
        assert.ok( ! position.isInside(contentContainer, externalElt), 'The external element is outside the content element');

    });

    QUnit.test('absolute elements', function(assert) {

        var container        = document.getElementById('qunit-fixture');
        var contentContainer = container.querySelector('.content');
        var volatileElt      = container.querySelector('.volatile');
        var externalElt      = container.querySelector('.external');

        QUnit.expect(6);

        externalElt.style.position = 'absolute';
        externalElt.style.top = '-50px';
        externalElt.style.left = '-50px';

        volatileElt.style.position = 'absolute';
        volatileElt.style.top = '50px';
        volatileElt.style.left = '50px';

        assert.ok(container instanceof HTMLElement, 'The container exists');
        assert.ok(contentContainer instanceof HTMLElement, 'The content container exists');
        assert.ok(volatileElt instanceof HTMLElement, 'The volatile element exists');
        assert.ok(externalElt instanceof HTMLElement, 'The external element exists');

        assert.ok(position.isInside(contentContainer, volatileElt), 'The volatile element is inside the content element');
        assert.ok( ! position.isInside(contentContainer, externalElt), 'The external element is outside the content element');
    });

    QUnit.test('translate elements', function(assert) {

        var container        = document.getElementById('qunit-fixture');
        var contentContainer = container.querySelector('.content');
        var volatileElt      = container.querySelector('.volatile');

        QUnit.expect(6);

        volatileElt.style.position = 'absolute';
        volatileElt.style.top = '50px';
        volatileElt.style.left = '50px';

        assert.ok(container instanceof HTMLElement, 'The container exists');
        assert.ok(contentContainer instanceof HTMLElement, 'The content container exists');
        assert.ok(volatileElt instanceof HTMLElement, 'The volatile element exists');

        assert.ok(position.isInside(contentContainer, volatileElt), 'The volatile element is inside the content element');

        volatileElt.classList.add('move-out');

        assert.ok(! position.isInside(contentContainer, volatileElt), 'The volatile element is now outside the content element');

        volatileElt.classList.remove('move-out');

        assert.ok(position.isInside(contentContainer, volatileElt), 'The volatile element is inside the content element');
    });


    QUnit.module('isOver');

    QUnit.test('simple elements', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof position.isOver(), 'undefined', 'The method returns undefined without valid elements');
        assert.equal(typeof position.isOver(null, null), 'undefined', 'The method returns undefined without valid elements');
        assert.equal(typeof position.isOver({}, {}), 'undefined', 'The method returns undefined without valid elements');
    });

    QUnit.test('simple elements', function(assert) {

        var container        = document.getElementById('qunit-fixture');
        var contentContainer = container.querySelector('.content');
        var volatileElt      = container.querySelector('.volatile');
        var externalElt      = container.querySelector('.external');

        QUnit.expect(6);

        assert.ok(container instanceof HTMLElement, 'The container exists');
        assert.ok(contentContainer instanceof HTMLElement, 'The content container exists');
        assert.ok(volatileElt instanceof HTMLElement, 'The volatile element exists');
        assert.ok(externalElt instanceof HTMLElement, 'The external element exists');

        assert.ok(position.isOver(contentContainer, volatileElt), 'The volatile element is inside the content element');
        assert.ok( ! position.isOver(contentContainer, externalElt), 'The external element is outside the content element');

    });

    QUnit.test('absolute elements', function(assert) {

        var container        = document.getElementById('qunit-fixture');
        var contentContainer = container.querySelector('.content');
        var volatileElt      = container.querySelector('.volatile');
        var externalElt      = container.querySelector('.external');

        QUnit.expect(7);

        externalElt.style.position = 'absolute';
        externalElt.style.top = '-50px';
        externalElt.style.left = '-50px';

        volatileElt.style.position = 'absolute';
        volatileElt.style.top = '100px';
        volatileElt.style.left = '990px';       //the element overlaps on the right
        volatileElt.style.width = '100px';
        volatileElt.style.height = '100px';

        assert.ok(container instanceof HTMLElement, 'The container exists');
        assert.ok(contentContainer instanceof HTMLElement, 'The content container exists');
        assert.ok(volatileElt instanceof HTMLElement, 'The volatile element exists');
        assert.ok(externalElt instanceof HTMLElement, 'The external element exists');

        assert.ok(position.isOver(contentContainer, volatileElt), 'The volatile element is inside the content element');
        assert.ok( ! position.isInside(contentContainer, volatileElt), 'The volatile element is over the content element but not inside');
        assert.ok( ! position.isOver(contentContainer, externalElt), 'The external element is outside the content element');
    });

    QUnit.test('translate elements', function(assert) {

        var container        = document.getElementById('qunit-fixture');
        var contentContainer = container.querySelector('.content');
        var volatileElt      = container.querySelector('.volatile');

        QUnit.expect(6);

        volatileElt.style.position = 'absolute';
        volatileElt.style.top = '50px';
        volatileElt.style.left = '50px';

        assert.ok(container instanceof HTMLElement, 'The container exists');
        assert.ok(contentContainer instanceof HTMLElement, 'The content container exists');
        assert.ok(volatileElt instanceof HTMLElement, 'The volatile element exists');

        assert.ok(position.isOver(contentContainer, volatileElt), 'The volatile element is inside the content element');

        volatileElt.classList.add('move-out');

        assert.ok(! position.isOver(contentContainer, volatileElt), 'The volatile element is now outside the content element');

        volatileElt.classList.remove('move-out');

        assert.ok(position.isOver(contentContainer, volatileElt), 'The volatile element is inside the content element');
    });
});
