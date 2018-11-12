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
    'lodash',
    'core/promise',
    'ui/dropdown'
], function($, _, Promise, dropdown) {
    'use strict';

    var dropdownApi;
    var dataItem = {text: "Data item", link: "http://www.example.com/", icon: "home", cls: "my-class"};
    var dataExpectedHead = '<a href="http://www.example.com/"><span class="icon-home></span><span class="text">Data item</span></a>';
    var dataExpectedBody = '<li class="my-class"><a href="http://www.example.com/"><span class="icon-home></span><span class="text">Data item</span></a></li>';
    var htmlItem = "<li><a><span>HTML item</span></a></li>";
    var htmlExpectedHead = "<a><span>HTML item</span></a>";
    var htmlExpectedBody = "<li><a><span>HTML item</span></a></li>";

    QUnit.module('dropdown');

    QUnit.test('module', 3, function(assert) {
        assert.equal(typeof dropdown, 'function', "The dropdown module exposes a function");
        assert.equal(typeof dropdown(), 'object', "The dropdown factory produces an object");
        assert.notStrictEqual(dropdown(), dropdown(), "The dropdown factory provides a different object on each call");
    });

    dropdownApi = [
        { name : 'init', title : 'init' },
        { name : 'destroy', title : 'destroy' },
        { name : 'render', title : 'render' },
        { name : 'show', title : 'show' },
        { name : 'hide', title : 'hide' },
        { name : 'trigger', title : 'trigger' },
        { name : 'on', title : 'on' },
        { name : 'off', title : 'off' },
        { name : 'getElement', title : 'getElement' },

        { name : 'open', title : 'open' },
        { name : 'close', title : 'close' },
        { name : 'toggle', title : 'toggle' },
        { name : 'setHeader', title : 'setHeader' },
        { name : 'setHtmlHeader', title : 'setHtmlHeader' },
        { name : 'addItem', title : 'addItem' },
        { name : 'insertItem', title : 'insertItem' },
        { name : 'addHtmlItem', title : 'addHtmlItem' },
        { name : 'insertHtmlItem', title : 'insertHtmlItem' },  // to write
        { name : 'removeItem', title : 'removeItem' },
        { name : 'clearItems', title : 'clearItems' }

        // -> setHeaderItem(obj)
        // -> setHeaderHtmlItem(html)
        // -> addItem(obj)
        // -> insertItem(obj,i)
        // -> addHtmlItem(html)
        // -> insertHtmlItem(html,i)
        // -> removeItem
        // -> removeItem(i)
        // -> clearItems()
    ];

    QUnit
    .cases(dropdownApi)
    .test('instance API ', function(data, assert) {
        var instance = dropdown();
        assert.equal(typeof instance[data.name], 'function', 'The dropdown instance exposes a "' + data.title + '" function');
    });

    // dropdown.init(config)
    // -> test dd properties

    // events received
    // -> configChange

    QUnit.test('creation', function(assert) {
        var dd = dropdown({
            renderTo: '#qunit-fixture',
            isOpen: false
        });

        QUnit.expect(5);

        assert.equal(typeof dd, 'object', "The dropdown instance is an object");
        assert.equal(typeof dd.getElement(), 'object', "The dropdown instance gets a DOM element");
        assert.ok(!!dd.getElement().length, "The dropdown instance gets a DOM element");

        dd.render();

        assert.equal(dd.getElement().find('.text').text(), 'Menu', "The dropdown has the default header");
        assert.equal(dd.getElement().find('ul').children().length, 0, "The dropdown list has no children");
    });

    QUnit.test('basic mouse events', function(assert) {
        var dd1, dd2, $toggler;
        dd1 = dropdown({
            renderTo: '#qunit-fixture',
            activatedBy: 'hover',
            isOpen: false
        });
        dd1.render();

        QUnit.expect(4);

        dd1.getElement().trigger('mouseenter');
        assert.equal(dd1.config.isOpen, true);
        dd1.getElement().trigger('mouseout');
        assert.equal(dd1.config.isOpen, false);

        dd2 = dropdown({
            renderTo: '#qunit-fixture',
            activatedBy: 'click',
            isOpen: false
        });
        dd2.render();

        $toggler = dd2.getElement().find('.toggler');
        $toggler.trigger('click');
        assert.equal(dd2.config.isOpen, true);
        $toggler.trigger('click');
        assert.equal(dd2.config.isOpen, false);
    });

    QUnit.test('adding data', function(assert) {
        var dd = dropdown({
            renderTo: '#qunit-fixture',
            isOpen: false
        });
        dd.render();

        dd.setHeader(dataItem);
        dd.render();
        assert.equal(dd.getElement().find('.dropdown-header').html(), dataExpectedHead, "Header data item added correctly");
        dd.addItem(dataItem);
        dd.render();
        assert.equal(dd.getElement().find('.dropdown-submenu li').first().html(), dataExpectedBody, "List data item added correctly");

    });


    /* QUnit.asyncTest('buttons', function(assert) {
        var message = 'test';
        var modal = dropdown({
            message: message,
            buttons: 'yes,no,ok,cancel',
            onYesBtn: function(event, btn) {
                assert.ok('true', '[yes button] The button has been activated');
                assert.equal(typeof btn, 'object', '[yes button] The button descriptor is provided');
                assert.equal(btn.id, 'yes', '[yes button] The right button descriptor is provided');

                QUnit.start();
            },

            onNoBtn: function(event, btn) {
                assert.ok('true', '[no button] The button has been activated');
                assert.equal(typeof btn, 'object', '[no button] The button descriptor is provided');
                assert.equal(btn.id, 'no', '[no button] The right button descriptor is provided');

                QUnit.start();
            },

            onOkBtn: function(event, btn) {
                assert.ok('true', '[ok button] The button has been activated');
                assert.equal(typeof btn, 'object', '[ok button] The button descriptor is provided');
                assert.equal(btn.id, 'ok', '[ok button] The right button descriptor is provided');

                QUnit.start();
            },

            onCancelBtn: function(event, btn) {
                assert.ok('true', '[cancel button] The button has been activated');
                assert.equal(typeof btn, 'object', '[cancel button] The button descriptor is provided');
                assert.equal(btn.id, 'cancel', '[cancel button] The right button descriptor is provided');

                QUnit.start();
            }
        });

        QUnit.stop(6);

        assert.equal(typeof modal, 'object', "The dropdown instance is an object");
        assert.equal(typeof modal.getDom(), 'object', "The dropdown instance gets a DOM element");
        assert.ok(!!modal.getDom().length, "The dropdown instance gets a DOM element");
        assert.equal(modal.getDom().parent().length, 0, "The dropdown box is not rendered by default");
        assert.equal(modal.getDom().find('.message').text(), message, "The dropdown box displays the message");

        assert.equal(modal.getDom().find('button').length, 4, "The dialog box displays 4 buttons");
        assert.equal(modal.getDom().find('button[data-control="yes"]').length, 1, "The dialog box displays a 'yes' button");
        assert.equal(modal.getDom().find('button[data-control="no"]').length, 1, "The dialog box displays a 'no' button");
        assert.equal(modal.getDom().find('button[data-control="ok"]').length, 1, "The dialog box displays a 'ok' button");
        assert.equal(modal.getDom().find('button[data-control="cancel"]').length, 1, "The dialog box displays a 'cancel' button");

        modal.getDom().find('button[data-control="yes"]').click();
        modal.getDom().find('button[data-control="no"]').click();
        modal.getDom().find('button[data-control="ok"]').click();
        modal.getDom().find('button[data-control="cancel"]').click();

        modal.setButtons([{
            id: 'test',
            type: 'info',
            icon: 'test',
            label: 'test'
        }]).on('testbtn.modal', function(event, btn) {
            assert.ok('true', '[test button] The button has been activated');
            assert.equal(typeof btn, 'object', '[test button] The button descriptor is provided');
            assert.equal(btn.id, 'test', '[test button] The right button descriptor is provided');

            QUnit.start();
        });

        assert.equal(modal.getDom().find('button').length, 1, "The dialog box displays only 1 button");
        assert.equal(modal.getDom().find('button[data-control="test"]').length, 1, "The dialog box displays a 'test' button");
        assert.equal(modal.getDom().find('button[data-control="test"]').text().trim(), 'test', "The dialog box displays has a 'test' label");
        assert.ok(modal.getDom().find('button[data-control="test"]').hasClass('btn-info'), "The 'test' button has the 'info' class");
        assert.ok(modal.getDom().find('button[data-control="test"]').hasClass('test'), "The 'test' button has the 'test' class");
        assert.equal(modal.getDom().find('button .icon-test').length, 1, "The 'test' button has a 'test' icon");

        modal.getDom().find('button[data-control="test"]').click();


        modal.setButtons(['ok', {
            id: 'done',
            type: 'info',
            icon: 'done',
            label: 'done'
        }]).on('donebtn.modal', function(event, btn) {
            assert.ok('true', '[done button] The button has been activated');
            assert.equal(typeof btn, 'object', '[done button] The button descriptor is provided');
            assert.equal(btn.id, 'done', '[done button] The right button descriptor is provided');

            QUnit.start();
        });

        assert.equal(modal.getDom().find('button').length, 2, "The dialog box displays 2 buttons");
        assert.equal(modal.getDom().find('button[data-control="ok"]').length, 1, "The dialog box displays a 'ok' button");
        assert.equal(modal.getDom().find('button[data-control="done"]').length, 1, "The dialog box displays a 'done' button");
        assert.equal(modal.getDom().find('button[data-control="done"]').text().trim(), 'done', "The dialog box displays has a 'done' label");
        assert.ok(modal.getDom().find('button[data-control="done"]').hasClass('btn-info'), "The 'done' button has the 'info' class");
        assert.ok(modal.getDom().find('button[data-control="done"]').hasClass('done'), "The 'done' button has the 'done' class");
        assert.equal(modal.getDom().find('button .icon-done').length, 1, "The 'done' button has a 'done' icon");

        modal.getDom().find('button[data-control="ok"]').click();
        modal.getDom().find('button[data-control="done"]').click();
    }); */


    /* QUnit.asyncTest('destroy', function(assert) {

        var message = 'foo';
        var content = 'bar';
        var renderTo = '#qunit-fixture';

        var modal = dialog({
            message: message,
            content: content,
            renderTo: renderTo
        });

        QUnit.expect(4);

        modal.on('create.modal', function() {
            assert.equal($(renderTo + ' .modal').length, 1, 'The modal element is created');
            assert.equal($(renderTo + ' .message').text(), message, 'The modal message is correct');

            modal.destroy();
        });
        modal.on('destroy.modal', function() {

            assert.equal($(renderTo + ' .modal').length, 1, 'The modal element is still there due to the way the modal works');
            assert.equal(modal.destroyed, true, 'The dropdown has the destroyed state');


            QUnit.start();
        });


        modal.render();
    }); */
    
});
