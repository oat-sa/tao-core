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

define([
    'jquery',
    'lodash',
    'handlebars',
    'ui/generis/form/widgets/_widget'
], function(
    $,
    _,
    Handlebars,
    generisFormWidgetFactory
) {
    'use strict';


    var selectTpl = Handlebars.compile(
        `
        <div class="ui-generis-form-widgets">
            <label for="{{uri}}">{{label}}</label>
            <select name="{{uri}}">
                <option></option>
                <option>Option 1</option>
                <option>Numero two</option>
                <option>3</option>
            </select>
        </div>
        `
    );
    var textTpl = Handlebars.compile(
        `
        <div class="ui-generis-form-widgets">
            <label for="{{uri}}">{{label}}</label>
            <input name="{{uri}}" value="{{value}}">
        </div>
        `
    );



    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof generisFormWidgetFactory, 'function', 'The module exposes a function');
        assert.equal(typeof generisFormWidgetFactory(), 'object', 'The factory produces an object');
        assert.notStrictEqual(generisFormWidgetFactory(), generisFormWidgetFactory(), 'The factory provides a different object on each call');
    });

    QUnit
    .cases([
        { name : 'get', title : 'get' },
        { name : 'set', title : 'set' },
        { name : 'validate', title : 'validate' }
    ])
    .test('instance', function (data, assert) {
        var instance = generisFormWidgetFactory();
        assert.equal(typeof instance[data.name], 'function', 'The instance exposes a "' + data.title + '" function');
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('initialization', function (assert) {
        generisFormWidgetFactory()
        .on('render', function () {
            assert.ok(this.getElement().attr('hidden'), 'Widget is successfully hidden');
            assert.ok(this.validations[0], 'Required validation exists');
        })
        .setTemplate(textTpl)
        .init({
            hidden: true,
            required: true
        })
        .render();
    });

    QUnit.test('get', function (assert) {
        generisFormWidgetFactory()
        .on('render', function () {
            assert.equal(this.get(), 'bar', 'returns value');
            assert.equal(this.get(function () {}), this, 'returns this when callback passed');
            this.get(function (value) {
                assert.equal(value, 'bar', 'passes value to callback');
            });
        })
        .setTemplate(textTpl)
        .init({
            uri: 'foo',
            value: 'bar'
        })
        .render();
    });

    QUnit.test('set', function (assert) {
        generisFormWidgetFactory()
        .on('render', function () {
            assert.equal(this.set('baz'), 'baz', 'returns updated value');
            assert.equal(this.get(), 'baz', 'updates value');
            assert.equal(this.set('bar', console.log), this, 'returns this when callback passed')
            this.set('baz', function (value) {
                assert.equal(value, 'baz', 'passes updated value to callback');
            });
        })
        .setTemplate(textTpl)
        .init({
            uri: 'foo',
            value: 'bar'
        })
        .render();
    });

    QUnit.test('validate', function (assert) {
        var instance = generisFormWidgetFactory();

        // returns boolean or this

        assert.ok(true, 'validate()');
    });


    /**
     * Events
     */
    QUnit.module('Events');

    QUnit.test('change & blur', function (assert) {
        var instance = generisFormWidgetFactory();

        assert.ok(true, 'on(\'change blur\')');
    });


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        var textWidget = generisFormWidgetFactory({
            required: true
        })
        .setTemplate(textTpl)
        .on('init', function () {
            this.validations.push({
                predicate: /Bar/,
                message: 'Make it "Bar"'
            });
        })
        .on('render', function () {
            assert.ok(true);
        })
        .init({
            uri: 'http://www.tao.lu/Ontologies/generis.rdf#userFirstName',
            label: 'First Name',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
            value: 'Foo'
        })
        .render('#display-and-play > form > fieldset');

        var selectWidget = generisFormWidgetFactory({
            required: true
        })
        .setTemplate(selectTpl)
        .on('render', function () {
            assert.ok(true);
        })
        .init({
            uri: 'http://www.tao.lu/Ontologies/generis.rdf#select',
            label: 'Select Box',
            widget: 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#SelectBox',
            value: '3'
        })
        .render('#display-and-play > form > fieldset');

        $('#validate').on('click', function (e) {
            e.preventDefault();

            console.log(textWidget.validate(), selectWidget.validate());

            return false;
        });
    });
});

