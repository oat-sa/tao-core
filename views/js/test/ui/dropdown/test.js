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
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'ui/dropdown'
], function($, dropdown) {
    'use strict';

    var htmlItem = '<a><span>HTML Item</span></a>';
    var items = [
        {
            content: '<span class="a">First thing</span>',
            cls: 'sep-before',
            icon: 'home',
            id: 'first'
        },
        {
            content: '<span class="a">Second thing</span>',
            cls: 'sep-before',
            icon: 'eye-slash',
            id: 'second'
        }
    ];

    QUnit.module('dropdown');

    QUnit.test('module', 3, function(assert) {
        assert.equal(typeof dropdown, 'function', "The dropdown module exposes a function");
        assert.equal(typeof dropdown(), 'object', "The dropdown factory produces an object");
        assert.notStrictEqual(dropdown(), dropdown(), "The dropdown factory provides a different object on each call");
    });

    QUnit
    .cases([
        { title : 'init' },
        { title : 'destroy' },
        { title : 'render' },
        { title : 'show' },
        { title : 'hide' },
        { title : 'trigger' },
        { title : 'on' },
        { title : 'off' },
        { title : 'getElement' },

        { title : 'open' },
        { title : 'close' },
        { title : 'toggle' },
        { title : 'setHeader' },
        { title : 'setItems' },
        { title : 'addItem' },
        { title : 'removeItem' },
        { title : 'clearItems' }
    ])
    .test('instance API ', function(data, assert) {
        var instance = dropdown();
        assert.equal(typeof instance[data.title], 'function', 'The dropdown instance exposes a "' + data.title + '" function');
    });


    QUnit.test('creation', function(assert) {
        var dd = dropdown({
            renderTo: '#fixture-init',
            isOpen: false
        });

        assert.equal(typeof dd, 'object', "The dropdown instance is an object");
        assert.ok(dd.getElement() instanceof $, "The dropdown instance gets a DOM element");
        assert.equal(dd.getElement().length, 1, "The dropdown instance gets a single element");

        assert.equal(dd.getElement().find('.dropdown-header').children().length, 0, "The dropdown header has no children");
        assert.equal(dd.getElement().find('.dropdown-submenu').children().length, 0, "The dropdown list has no children");
        assert.ok(! dd.getElement().find('.dropdown').hasClass('open'), "The dropdown is not open");

        dd.destroy();
    });

    QUnit.test('basic mouse events 1', function(assert) {
        var dd1 = dropdown({
            renderTo: '#fixture-mouse1',
            activatedBy: 'hover',
            isOpen: false
        });

        dd1.getElement().trigger('mouseenter');
        assert.equal(dd1.is('open'), true, "The menu opens on hover");
        dd1.getElement().trigger('mouseout');
        assert.equal(dd1.is('open'), false, "The menu closes on unhover");

        dd1.destroy();
    });

    QUnit.test('basic mouse events 2', function(assert) {
        var dd2 = dropdown({
            renderTo: '#fixture-mouse2',
            activatedBy: 'click',
            isOpen: false
        });

        dd2.getElement().find('.dropdown-header').trigger('click');
        assert.equal(dd2.is('open'), true, "The menu opens on click");
        dd2.getElement().find('.dropdown-header').trigger('click');
        assert.equal(dd2.is('open'), false, "The menu closes on click");

        dd2.destroy();
    });

    QUnit.asyncTest('event triggering', function(assert) {
        var dd3 = dropdown({
            renderTo: '#fixture-mouse3',
            activatedBy: 'click',
            isOpen: true
        }, {
            header: htmlItem,
            items: items
        });
        dd3.on('item-click', function () {
            assert.ok(true, "The component fires a generic event when an item is clicked");
            QUnit.start();
        })
        .on('item-click-second', function () {
            assert.ok(true, "The component fires a specific event when an item with an id is clicked");
            QUnit.start();
        });

        QUnit.expect(2);
        QUnit.stop(1);

        dd3.getElement().find('li:nth-child(2)').trigger('click');

        dd3.destroy();
    });

    QUnit.test('adding html items', function(assert) {
        var dd4 = dropdown({
            renderTo: '#fixture-html-items',
            isOpen: false
        });

        dd4.setHeader(htmlItem);
        dd4.addItem({content: htmlItem});

        assert.equal(dd4.getElement().find('.dropdown-header span').text(), 'HTML Item', "The dropdown header was set");
        assert.equal(dd4.getElement().find('.dropdown-submenu li:last-child span').text(), 'HTML Item', "The dropdown list item was set");

        dd4.setItems(items);
        assert.equal(dd4.getElement().find('.dropdown-submenu li').length, items.length, "The dropdown list was populated with the correct number of items");

        dd4.destroy();
    });

    QUnit.test('removing items', function(assert) {
        var dd5 = dropdown({
            renderTo: '#fixture-removal',
            isOpen: false
        });

        QUnit.expect(4);

        assert.equal(dd5.getElement().find('.dropdown-submenu li').length, 0, "The dropdown starts with 0 list items");

        dd5.addItem({content: '1'});
        dd5.addItem({content: '2'});
        dd5.addItem({content: '3'});
        assert.equal(dd5.getElement().find('.dropdown-submenu li').length, 3, "The dropdown gained 3 list items");

        dd5.removeItem(2);
        assert.equal(dd5.getElement().find('.dropdown-submenu li').length, 2, "Removing 1 list item works");

        dd5.clearItems();
        assert.equal(dd5.getElement().find('.dropdown-submenu li').length, 0, "Clearing all list items works");

        dd5.destroy();
    });

    // Leave some instances alive for user testing:
    QUnit.module('Visual');

    QUnit.test('playground', function(assert) {
        dropdown({
            renderTo: '#visual-test-hover',
            isOpen: false,
            activatedBy: 'hover',
            id: 'testid'
        }, {
            header: '<span class="a">Hoverable dropdown</a>',
            items: items,
        });

        dropdown({
            renderTo: '#visual-test-click',
            isOpen: true,
            activatedBy: 'click'
        }, {
            header: '<span class="a">Clickable dropdown</a>',
            items: items,
        });

        assert.ok(true, 'started');
    });

});
