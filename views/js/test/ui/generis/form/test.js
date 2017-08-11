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
    'ui/generis/form/form',
    'json!test/ui/generis/data.json'
], function(
    $,
    _,
    generisFormFactory,
    generisData
) {
    'use strict';


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof generisFormFactory, 'function', 'The module exposes a function');
        assert.equal(typeof generisFormFactory(), 'object', 'The factory produces an object');
        assert.notStrictEqual(generisFormFactory(), generisFormFactory(), 'The factory provides a different object on each call');
    });

    QUnit
    .cases([
        { name: 'data',           title: 'data',           type: 'object' },
        { name: 'errors',         title: 'errors',         type: 'object' },
        { name: 'widgets',        title: 'widgets',        type: 'object' },
        { name: 'addWidget',      title: 'addWidget',      type: 'function' },
        { name: 'getWidget',      title: 'getWidget',      type: 'function' },
        { name: 'removeWidget',   title: 'removeWidget',   type: 'function' },
        { name: 'validate',       title: 'validate',       type: 'function' },
        { name: 'serializeArray', title: 'serializeArray', type: 'function' },
        { name: 'getValues',      title: 'getValues',      type: 'function' },
    ])
    .test('instance', function (data, assert) {
        var instance = generisFormFactory();
        assert.equal(typeof instance[data.name], data.type, 'The instance exposes a(n) "' + data.title + '" ' + data.type);
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('addWidget', function (assert) {
        var form = generisFormFactory();
        assert.equal(form.widgets.length, 0, 'no widgets yet');

        form.addWidget({
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox'
        });
        assert.equal(form.widgets.length, 1, 'successfully added widget');
    });

    QUnit.test('getWidget', function (assert) {
        var form = generisFormFactory(generisData);

        var firstName = form.getWidget('http://www.tao.lu/Ontologies/generis.rdf#userFirstName');
        var missing   = form.getWidget('http://jdoe.com#missing');

        assert.equal(missing, null, 'A missing widget returns null');
        assert.equal(typeof firstName, 'object', 'The firstName widget is found');
        assert.equal(firstName.config.uri, 'http://www.tao.lu/Ontologies/generis.rdf#userFirstName', 'The property URI is correct');
        assert.equal(firstName.config.label, 'First Name', 'The property label is correct');
        assert.equal(firstName.config.value, 'Bertrand', 'The property value is correct');
    });

    QUnit.test('removeWidget', function (assert) {
        var form = generisFormFactory();

        form.addWidget({
            uri: 'foo#bar',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox'
        });
        assert.equal(form.widgets.length, 1, 'has a widget');

        form.removeWidget('foo#bar');
        assert.equal(form.widgets.length, 0, 'successfully removed widget');
    });

    QUnit.test('validate', function (assert) {
        var form = generisFormFactory();

        form.addWidget({
            required: true,
            uri: 'foo#bar',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox'
        });

        assert.equal(form.errors.length, 0, 'no errors yet');
        form.validate();
        assert.equal(form.errors.length, 1, 'successfully validated form');
    });

    QUnit.test('serializeArray', function (assert) {
        var form = generisFormFactory();
        var serialized;

        form
        .addWidget({
            uri: 'foo#bar',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
            value: 'foobar'
        })
        .addWidget({
            uri: 'bar#foo',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
            value: 'baz'
        });

        serialized = form.serializeArray();

        assert.deepEqual(serialized, [{
            name: 'foo#bar',
            value: 'foobar'
        }, {
            name: 'bar#foo',
            value: 'baz'
        }], 'properly serializes form');
    });

    QUnit.test('getValues', function (assert) {
        var form = generisFormFactory();
        var values;

        form
        .addWidget({
            uri: 'foo#bar',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
            value: 'foobar'
        })
        .addWidget({
            uri: 'bar#foo',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
            value: 'baz'
        });

        values = form.getValues();

        assert.deepEqual(values, {
            'foo#bar' : 'foobar',
            'bar#foo' : 'baz'
        }, 'The form values are correct');
    });


    /**
     * Events
     */
    QUnit.module('Events');

    QUnit.asyncTest('submit', function (assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(5);

        generisFormFactory(generisData)
            .on('render', function(){
                var self = this;
                //wait for widgets to be rendered...
                setTimeout(function(){
                    var $element   = self.getElement();
                    var $firstName = $('[name="http://www.tao.lu/Ontologies/generis.rdf#userFirstName"]', $element);
                    var $lastName  = $('[name="http://www.tao.lu/Ontologies/generis.rdf#userLastName"]', $element);
                    var $submitBtn = $(':submit', $element);

                    assert.equal($firstName.length, 1, 'The firstname field is rendered');
                    assert.equal($lastName.length, 1, 'The lastname field is rendered');
                    assert.equal($submitBtn.length, 1, 'The submit button is rendered');

                    $firstName.val('John');
                    $lastName.val('Doe');

                    $submitBtn.click();
                }, 300);
            })
            .on('submit', function(){
                var values = this.getValues();

                assert.equal(values["http://www.tao.lu/Ontologies/generis.rdf#userFirstName"], 'John');
                assert.equal(values["http://www.tao.lu/Ontologies/generis.rdf#userLastName"], 'Doe');

                QUnit.start();
            })
            .render($container);
    });


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        generisFormFactory(generisData)
        .on('render', function () {
            assert.ok(true);
        })
        .on('submit', function () {
            var self = this;


            this
            .toggleLoading(true)
            .validate();

            setTimeout(function () {
                self.toggleLoading(false);
                if (! self.errors.length) {
                    console.log('serialized form data', self.serializeArray());
                } else {
                    console.log('errors in form', self.errors);
                }
            }, 3000);
        })
        .render('#display-and-play');
    });
});

