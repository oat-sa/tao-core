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
    'ui/badge/badge'
], function($, _, badgeFactory) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof badgeFactory, 'function', "The badgeFactory module exposes a function");
        assert.equal(typeof badgeFactory(), 'object', "The badgeFactory produces an object");
        assert.notStrictEqual(badgeFactory(), badgeFactory(), "The badgeFactory provides a different object on each call");
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
        var instance = badgeFactory();
        assert.equal(typeof instance[data.title], 'function', 'The badge exposes the component method "' + data.title);
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        var instance = badgeFactory();
        assert.equal(typeof instance[data.title], 'function', 'The badge exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
        { title : 'update' },
    ]).test('Instance API ', function(data, assert) {
        var instance = badgeFactory();
        assert.equal(typeof instance[data.title], 'function', 'The badge exposes the method "' + data.title);
    });

    QUnit.module('Behavior');

    QUnit.asyncTest('simple rendering', function(assert) {
        var $container = $('#qunit-fixture');
        QUnit.expect(4);
        badgeFactory({
            type : 'info',
            value : 9,
            loading : true
        })
        .on('render', function(){
            var $component = $container.find('.badge-component');
            assert.equal($component.length, 1, 'rendered');
            assert.equal($component.find('.badge').text(), '9', 'value correct');
            assert.ok($component.find('.badge').hasClass('badge-info'), 'class ok');
            assert.ok($component.find('.loader').is(':visible'), 'loading visible');
            QUnit.start();
        })
        .render($container);
    });

    QUnit.asyncTest('update', function(assert) {
        var $container = $('#qunit-fixture');
        QUnit.expect(11);
        badgeFactory({
            type : 'info',
            value : 9,
            loading : true
        })
        .on('render', function(){
            var $component = $container.find('.badge-component');

            assert.equal($component.length, 1, 'rendered');
            assert.equal($component.find('.badge').text(), '9', 'value correct');
            assert.ok($component.find('.badge').hasClass('badge-info'), 'class ok');
            assert.ok($component.find('.loader').is(':visible'), 'loading visible');

            this.update({loading: false});

            assert.equal($component.find('.badge').text(), '9', 'value still correct');
            assert.ok($component.find('.badge').hasClass('badge-info'), 'class ok');
            assert.ok(!$component.find('.loader').is(':visible'), 'loading is hidden');

            this.update({value: 0});
            assert.equal($component.find('.badge').text(), '', 'value emptied');
            assert.ok(!$component.find('.badge').hasClass('badge-info'), 'class info gone');
            assert.ok($component.find('.badge').hasClass('icon-result-ok'), 'class ok');
            assert.ok(!$component.find('.loader').is(':visible'), 'loading is hidden');

            QUnit.start();
        })
        .render($container);
    });

    QUnit.test('invalid type', function(assert){
        var $container = $('#qunit-fixture');
        QUnit.expect(1);
        assert.throws(function(){

            badgeFactory({
                type : 'invalid-value',
                value : 9,
                loading : true
            }).render($container);

        }, Error, 'An exception should be thrown if the type is invalid');
    });

    QUnit.module('Visual');

    QUnit.asyncTest('playground', function(assert) {
        var $container = $('#visual');
        badgeFactory({
            type : 'success',
            value : 9,
            loading : true
        })
        .on('render', function(){
            assert.ok(true);
            QUnit.start();
        })
        .render($container);
    });
});
