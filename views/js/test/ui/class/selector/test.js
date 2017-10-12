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
    'ui/class/selector',
    'json!test/ui/class/selector/classes.json'
], function($, classSelector, classes) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof classSelector, 'function', "The classSelector module exposes a function");
        assert.equal(typeof classSelector(), 'object', "The classSelector factory produces an object");
        assert.notStrictEqual(classSelector(), classSelector(), "The classSelector factory provides a different object on each call");
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
        var instance = classSelector();
        assert.equal(typeof instance[data.title], 'function', 'The classSelector exposes the component method "' + data.title);
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        var instance = classSelector();
        assert.equal(typeof instance[data.title], 'function', 'The classSelector exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
        { title : 'getValue' },
        { title : 'setValue' },
    ]).test('Instance API ', function(data, assert) {
        var instance = classSelector();
        assert.equal(typeof instance[data.title], 'function', 'The classSelector exposes the method "' + data.title);
    });


    QUnit.module('Behavior');

    QUnit.asyncTest('Lifecycle', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(2);

        classSelector($container, {
            classes : classes
        })
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

        QUnit.expect(10);

        assert.equal($('.class-selector', $container).length, 0, 'No class selector in the container');

        classSelector($container, {
            classes : classes
        })
        .on('render', function(){

            var $element = this.getElement();

            assert.equal($('.class-selector', $container).length, 1, 'The class selector has been inserted');
            assert.equal($('.class-selector', $container)[0], $element[0], 'The component element is correct');

            assert.equal($('.class-selector > a.selected', $container).length, 1, 'The selection element has been inserted');
            assert.equal($('.class-selector > a.selected', $container).text(), 'Select a class', 'The selection placeholder is correct');
            assert.equal($('.class-selector .options', $container).length, 1, 'The options selector has been inserted');
            assert.equal($('ul li', $element).length, 37, 'The options list has been inserted');
            assert.equal($('ul:first-child > li:first-child > a', $element).length, 1, 'The root class node has been inserted');
            assert.equal($('ul:first-child > li:first-child > a', $element).data('uri'), 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject', 'The root class uri is correct');
            assert.equal($('ul:first-child > li:first-child > ul > li:last-child > a', $element).data('uri'), 'http://bertao/tao.rdf#i14727380063820347', 'The last 1st level node class uri is correct');

            QUnit.start();
        });
    });

    QUnit.asyncTest('folding', function(assert) {

        var $container = $('#qunit-fixture');

        QUnit.expect(4);

        assert.equal($('.class-selector', $container).length, 0, 'No class selector in the container');

        classSelector($container, {
            classes : classes
        })
        .on('render', function(){

            var $element = this.getElement();
            var $options   = $('.options', $element);

            assert.ok($options.hasClass('folded'), 'The option container is folded');

            $('a.selected', $element).click();

            assert.ok( ! $options.hasClass('folded'), 'The option container is unfolded');

            $('ul:first-child > li:first-child > a', $element).click();

            assert.ok($options.hasClass('folded'), 'Selecting an element fold the options container');

            QUnit.start();
        });
    });

    QUnit.asyncTest('selection', function(assert) {

        var $container = $('#qunit-fixture');

        QUnit.expect(8);

        assert.equal($('.class-selector', $container).length, 0, 'No class selector in the container');

        classSelector($container, {
            classes : classes
        })
        .on('render', function(){

            var $element = this.getElement();
            var $aNode   = $('ul:first-child > li:first-child > ul > li:last-child > a', $element);

            assert.ok($('a.selected', $element).hasClass('empty'), 'The selection element is empty');
            assert.equal(this.getValue(), null,  'There is no value selected');

            assert.equal($aNode.data('uri'), 'http://bertao/tao.rdf#i14727380063820347', 'The last 1st level node class uri is correct');
            assert.equal($aNode.text(), 'Trainee', 'The last 1st level node class uri is correct');

            $aNode.click();

            assert.ok(! $('a.selected', $element).hasClass('empty'), 'The selection element is not empty anymore');

            assert.equal(this.getValue(), 'http://bertao/tao.rdf#i14727380063820347',  'The correct value is selected');
            assert.equal($('a.selected', $element).text(), 'Trainee',  'The node\'s text is used by the selection element');

            QUnit.start();
        });
    });

    QUnit.asyncTest('value change', function(assert) {

        var $container = $('#qunit-fixture');

        QUnit.expect(7);

        assert.equal($('.class-selector', $container).length, 0, 'No class selector in the container');

        classSelector($container, {
            classes : classes,
            placeholder : 'Foo bar'
        })
        .on('render', function(){

            var $element = this.getElement();

            assert.ok($('a.selected', $element).hasClass('empty'), 'The selection element is empty');
            assert.equal($('a.selected', $container).text(), 'Foo bar',  'The placeholder value is correct');
            assert.equal(this.getValue(), null,  'There is no value selected');

            this.setValue('http://bertao/tao.rdf#i14727195563004295');
        })
        .on('change', function(newValue){

            assert.equal($('a.selected', $container).text(), 'Designer',  'The node\'s text is used by the selection element');
            assert.equal(this.getValue(), newValue,  'There new value is correct');
            assert.equal(this.getValue(), 'http://bertao/tao.rdf#i14727195563004295',  'There new value is correct');

            QUnit.start();
        });
    });

    QUnit.asyncTest('default value not in the classes', function(assert) {

        var $container = $('#qunit-fixture');

        QUnit.expect(4);

        assert.equal($('.class-selector', $container).length, 0, 'No class selector in the container');

        classSelector($container, {
            classes : classes,
            classUri : 'http://bertao/tao.rdf#i7654321',
            label : '7654321'
        })
        .on('render', function(){

            var $element = this.getElement();

            assert.ok(! $('a.selected', $element).hasClass('empty'), 'The selection element starts with a value');
            assert.equal($('a.selected', $container).text(), '7654321',  'The placeholder value is correct');
            assert.equal(this.getValue(), 'http://bertao/tao.rdf#i7654321',  'There default value is correct selected');

            QUnit.start();
        });
    });

    QUnit.asyncTest('default value in the classes', function(assert) {

        var $container = $('#qunit-fixture');

        QUnit.expect(4);

        assert.equal($('.class-selector', $container).length, 0, 'No class selector in the container');

        classSelector($container, {
            classes : classes,
            classUri : 'http://bertao/tao.rdf#i14727195563004295',
        })
        .on('render', function(){

            var $element = this.getElement();

            assert.ok(! $('a.selected', $element).hasClass('empty'), 'The selection element starts with a value');
            assert.equal(this.getValue(), 'http://bertao/tao.rdf#i14727195563004295',  'There default value is correct');
            assert.equal($('a.selected', $container).text(), 'Designer',  'The default class label is correct');

            QUnit.start();
        });
    });


    QUnit.module('Visual');

    QUnit.asyncTest('playground', function(assert) {
        var container = document.getElementById('visual');

        QUnit.expect(1);

        classSelector( container, {
            classes : classes
        })
        .on('render', function(){
            assert.ok(true);
            QUnit.start();
        });
    });
});
