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
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/switch/switch',
], function ($, switchFactory) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof switchFactory, 'function', "The switchFactory module exposes a function");
        assert.equal(typeof switchFactory(), 'object', "The switchFactory produces an object");
        assert.notStrictEqual(switchFactory(), switchFactory(), "The switchFactory provides a different object on each call");
    });

    QUnit.cases([
        { title : 'init' },
        { title : 'destroy' },
        { title : 'render' },
        { title : 'show' },
        { title : 'hide' },
        { title : 'enable' },
        { title : 'disable' },
        { title : 'is' },
        { title : 'setState' },
        { title : 'getContainer' },
        { title : 'getElement' },
        { title : 'getTemplate' },
        { title : 'setTemplate' },
    ]).test('Component API ', function(data, assert) {
        var instance = switchFactory();
        assert.equal(typeof instance[data.title], 'function', 'The switch exposes the component method "' + data.title);
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        var instance = switchFactory();
        assert.equal(typeof instance[data.title], 'function', 'The switch exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
        { title : 'getName' },
        { title : 'getValue' },
        { title : 'isOn' },
        { title : 'isOff' },
        { title : 'setOn' },
        { title : 'setOff' },
        { title : 'toggle' },
    ]).test('Instance API ', function(data, assert) {
        var instance = switchFactory();
        assert.equal(typeof instance[data.title], 'function', 'The switch exposes the method "' + data.title);
    });


    QUnit.module('Behavior');

    QUnit.asyncTest('Lifecycle', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(2);

        switchFactory($container)
            .on('init', function(){
                assert.ok( !this.is('rendered'), 'The component is not yet rendered');
            })
            .on('render', function(){
                assert.ok(this.is('rendered'), 'The component is now rendered');

                this.destroy();
            })
            .on('destroy', function(){

                QUnit.start();
            });
    });

    QUnit.asyncTest('Rendering', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(9);

        assert.equal($('.switch', $container).length, 0, 'No resource tree in the container');

        switchFactory($container)
        .after('render', function(){

            var $element = this.getElement();

            assert.equal($('.switch', $container).length, 1, 'The component has been inserted');
            assert.equal($('.switch', $container)[0], $element[0], 'The component element is correct');

            assert.equal($('.on', $element).length, 1, 'The component has the on button');
            assert.equal($('.on', $element).text().trim(), 'On', 'The on button label is correct');
            assert.ok( ! $('.on', $element).hasClass('active'), 'The on button starts unselected');

            assert.equal($('.off', $element).length, 1, 'The component has the off button');
            assert.equal($('.off', $element).text().trim(), 'Off', 'The off button label is correct');
            assert.ok( $('.off', $element).hasClass('active'), 'The off button starts active');

            QUnit.start();
        });
    });

    QUnit.asyncTest('Modified defaults', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(10);

        assert.equal($('.switch', $container).length, 0, 'No resource tree in the container');

        switchFactory($container,{
            title  : 'hello',
            on : {
                label : 'day',
                active : true
            },
            off : {
                label : 'night'
            }
        })
        .after('render', function(){

            var $element = this.getElement();

            assert.equal($('.switch', $container).length, 1, 'The component has been inserted');
            assert.equal($('.switch', $container)[0], $element[0], 'The component element is correct');
            assert.equal($element.attr('title'), 'hello', 'The component title has been changed');

            assert.equal($('.on', $element).length, 1, 'The component has the on button');
            assert.equal($('.on', $element).text().trim(), 'day', 'The on button label is correct');
            assert.ok($('.on', $element).hasClass('active'), 'The on button starts selected');

            assert.equal($('.off', $element).length, 1, 'The component has the off button');
            assert.equal($('.off', $element).text().trim(), 'night', 'The off button label is correct');
            assert.ok(! $('.off', $element).hasClass('active'), 'The off button starts unselected');

            QUnit.start();
        });
    });

    QUnit.asyncTest('states', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(13);

        assert.equal($('.switch', $container).length, 0, 'No resource tree in the container');

        switchFactory($container)
            .on('render', function(){

                assert.ok(this.isOff(), 'The component starts off');
                assert.ok( ! this.isOn(), 'The component starts off');

                this.setOn();

                assert.ok( ! this.isOff(), 'The component is now on');
                assert.ok(this.isOn(), 'The component is now on');

                this.setOn();

                assert.ok( ! this.isOff(), 'The component is still on');
                assert.ok(this.isOn(), 'The component is still on');

                this.setOff();

                assert.ok(this.isOff(), 'The component is off');
                assert.ok( ! this.isOn(), 'The component is off');

                this.toggle();

                assert.ok( ! this.isOff(), 'The component is now on');
                assert.ok(this.isOn(), 'The component is now on');

                this.toggle();

                assert.ok(this.isOff(), 'The component is off');
                assert.ok( ! this.isOn(), 'The component is off');

                QUnit.start();
            });
    });

    QUnit.asyncTest('toggle by clicking', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(11);

        assert.equal($('.switch', $container).length, 0, 'No resource tree in the container');

        switchFactory($container)
            .on('change', function(value){

                assert.ok( ! this.isOff(), 'The component is now on');
                assert.ok(this.isOn(), 'The component is now on');
                assert.equal(value, 'on', 'The event value matches');
                assert.equal(this.getValue(), 'on', 'The event value matches');
                assert.ok( ! $('.off', this.getElement()).hasClass('active'), 'The off button is now inactive');
                assert.ok($('.on', this.getElement()).hasClass('active'), 'The on button is active');

                QUnit.start();
            })
            .on('render', function(){

                assert.ok(this.isOff(), 'The component starts off');
                assert.ok( ! this.isOn(), 'The component starts off');
                assert.ok($('.off', this.getElement()).hasClass('active'), 'The off button starts active');
                assert.ok( ! $('.on', this.getElement()).hasClass('active'), 'The on button starts inactive');

                this.getElement().find('input').click();
            });
    });

    QUnit.module('Visual');

    QUnit.asyncTest('simple button', function(assert){
        var container = document.getElementById('outside');

        QUnit.expect(1);
        switchFactory(container, {
            on : {
                active : true
            },
            off : {
                label : 'night'
            }

        })
            .on('render', function(){
                assert.ok(true);
                QUnit.start();
            });
    });
});
