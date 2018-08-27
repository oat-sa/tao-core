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
define([
    'jquery',
    'lodash',
    'ui/loadingButton/loadingButton'
], function($, _, loadingButtonFactory) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof loadingButtonFactory, 'function', "The loadingButtonFactory module exposes a function");
        assert.equal(typeof loadingButtonFactory(), 'object', "The loadingButtonFactory produces an object");
        assert.notStrictEqual(loadingButtonFactory(), loadingButtonFactory(), "The loadingButtonFactory provides a different object on each call");
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
        var instance = loadingButtonFactory();
        assert.equal(typeof instance[data.title], 'function', 'The loadingButton exposes the component method "' + data.title);
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        var instance = loadingButtonFactory();
        assert.equal(typeof instance[data.title], 'function', 'The loadingButton exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
        { title : 'start' },
        { title : 'terminate' },
        { title : 'reset' },
    ]).test('Instance API ', function(data, assert) {
        var instance = loadingButtonFactory();
        assert.equal(typeof instance[data.title], 'function', 'The loadingButton exposes the method "' + data.title);
    });

    QUnit.module('Behavior');

    QUnit.asyncTest('enable/disable', function(assert) {
        var $container = $('#qunit-fixture');
        QUnit.expect(3);
        loadingButtonFactory()
            .on('render', function(){
                assert.equal(this.getElement().prop('disabled'), false, 'initially enabled');

                this.disable();
                assert.equal(this.getElement().prop('disabled'), true, 'initially disabled');

                this.enable();
                assert.equal(this.getElement().prop('disabled'), false, 'enabled again');

                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('click and start', function(assert) {
        var $container = $('#qunit-fixture');
        QUnit.expect(2);
        loadingButtonFactory({})
            .on('render', function(){
                assert.ok(true, 'rendered');
                this.getElement().click();
            })
            .on('started', function(){
                assert.ok(true, 'started after click');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('start, terminate and reset', function(assert) {
        var $container = $('#qunit-fixture');
        QUnit.expect(27);
        loadingButtonFactory({
            type : 'info',
            icon : 'delivery',
            title : 'Publish',
            label : 'Publish',
            terminatedLabel : 'Interrupted'
        })
        .on('render', function(){
            assert.ok(this.getElement().find('.start-icon').is(':visible'), 'start icon visible');
            assert.ok(this.getElement().find('.action-label').is(':visible'), 'action label visible');
            assert.equal(this.getElement().find('.action-label').text(), 'Publish', 'label correct');
            assert.ok(!this.getElement().find('.terminated-label').is(':visible'), 'terminate label hidden');
            assert.ok(!this.getElement().find('.spinning').is(':visible'), 'loading icon visible');
            assert.ok(!this.is('disabled'), 'component is enabled');
            this.start();
        })
        .on('started', function(){
            assert.ok(true, 'programmatically started');
            assert.ok(!this.getElement().find('.start-icon').is(':visible'), 'start icon hidden');
            assert.ok(this.getElement().find('.action-label').is(':visible'), 'action label visible');
            assert.equal(this.getElement().find('.action-label').text(), 'Publish', 'label correct');
            assert.ok(!this.getElement().find('.terminated-label').is(':visible'), 'terminate label hidden');
            assert.ok(this.getElement().find('.spinning').is(':visible'), 'loading icon visible');
            assert.ok(!this.is('disabled'), 'component is enabled');
            this.terminate();
        })
        .on('terminated', function(){
            assert.ok(true, 'programmatically terminated');
            assert.ok(this.getElement().find('.start-icon').is(':visible'), 'start icon visible');
            assert.ok(!this.getElement().find('.action-label').is(':visible'), 'action label hidden');
            assert.equal(this.getElement().find('.terminated-label').text(), 'Interrupted', 'terminate label correct');
            assert.ok(this.getElement().find('.terminated-label').is(':visible'), 'terminate visible');
            assert.ok(!this.getElement().find('.spinning').is(':visible'), 'loading icon hidden');
            assert.ok(this.is('disabled'), 'component is disabled');
            this.reset();
        })
        .on('reset', function(){
            assert.ok(true, 'programmatically reset');
            assert.ok(this.getElement().find('.start-icon').is(':visible'), 'start icon visible');
            assert.ok(this.getElement().find('.action-label').is(':visible'), 'action label visible');
            assert.equal(this.getElement().find('.action-label').text(), 'Publish', 'label correct');
            assert.ok(!this.getElement().find('.terminated-label').is(':visible'), 'terminate label hidden');
            assert.ok(!this.getElement().find('.spinning').is(':visible'), 'loading icon visible');
            assert.ok(!this.is('disabled'), 'component is enabled again');
            QUnit.start();
        })
        .render($container);
    });

    QUnit.module('Visual');

    QUnit.asyncTest('playground', function(assert) {
        var $container = $('#visual');
        var button = loadingButtonFactory({})
            .on('render', function(){
                assert.ok(true);
                QUnit.start();
            })
            .on('started', function(){
                _.delay(function(){
                    button.terminate();
                }, 2000);
            }).on('terminated', function(){
                _.delay(function(){
                    button.reset();
                }, 2000);
            })
            .render($container);
    });
});
