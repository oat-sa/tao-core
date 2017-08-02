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
    'ui/generis/validator/validator',
    'ui/generis/widget/textBox/textBox',
], function(
    $,
    _,
    generisValidatorFactory,
    generisWidgetTextBoxFactory
) {
    'use strict';


    var fields = [{
        "uri" : "http://www.tao.lu/Ontologies/generis.rdf#userFirstName",
        "label" : "First Name",
        "widget" : "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox",
        "value"  : "99Bertrand"
    }, {
        "uri" : "http://www.tao.lu/Ontologies/generis.rdf#login",
        "label" : "Login",
        "widget" : "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox",
        "required" : true,
        "value"  : "bertrand"
    }];

    var validations = {
        beginWithAlpha: {
            message: 'Must begin with an alpha character or _ and contain only alphanumeric, _, -, and + characters',
            predicate: /^[a-zA-Z_]+[a-zA-Z\d_+\-]/
        },
        threeLetters: {
            message: 'Must contain at least nine letters',
            predicate: /^[a-zA-Z]{9,}/,
        }
    };


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof generisWidgetTextBoxFactory, 'function', 'The module exposes a function');
        assert.equal(typeof generisWidgetTextBoxFactory({}, {}), 'object', 'The factory produces an object');
        assert.notStrictEqual(generisWidgetTextBoxFactory({}, {}), generisWidgetTextBoxFactory({}, {}), 'The factory provides a different object on each call');
    });

    QUnit
    .cases([
        { name: 'get',          title: 'get',          type: 'function' },
        { name: 'set',          title: 'set',          type: 'function' },
        { name: 'validator',    title: 'validator',    type: 'object'   },
        { name: 'setValidator', title: 'setValidator', type: 'function' },
        { name: 'validate',     title: 'validate',     type: 'function' },
        { name: 'serialize',    title: 'serialize',    type: 'function' }
    ])
    .test('instance', function (data, assert) {
        var instance = generisWidgetTextBoxFactory({}, {});
        assert.equal(typeof instance[data.name], data.type, 'The instance exposes a(n) "' + data.title + '" ' + data.type);
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('get', function (assert) {
        var widget = generisWidgetTextBoxFactory({}, {
            uri: 'foo#bar',
            value: 'foobar'
        });

        assert.equal(widget.get(), 'foobar', 'returns correct value');
    });

    QUnit.test('set', function (assert) {
        var widget = generisWidgetTextBoxFactory({}, {
            uri: 'foo#bar',
            value: 'foobar'
        });

        assert.equal(widget.set('baz'), 'baz', 'returns updated value');
        assert.equal(widget.get(), 'baz', 'updates value');
    });

    QUnit.test('setValidator', function (assert) {
        var oldValidator;
        var widget = generisWidgetTextBoxFactory({}, {});

        oldValidator = widget.validator;
        widget.setValidator({});

        assert.notEqual(widget.validator, oldValidator, 'validator is replaced');
    });

    QUnit.test('validate', function (assert) {
        var widget = generisWidgetTextBoxFactory({
            validator: [{
                predicate: /test/,
                message: 'Must be "test"'
            }]
        }, {})
        .validate();

        assert.equal(widget.validator.errors.length, 1, 'validate properly generated errors');
    });

    QUnit.test('serialize', function (assert) {
        var obj = {
            uri: 'foo#bar',
            value: 'foobar'
        };
        var serialized;

        serialized = generisWidgetTextBoxFactory({}, obj).serialize();

        assert.equal(serialized.name, obj.uri, 'name property is correct');
        assert.equal(serialized.value, obj.value, 'value property is correct');
    });


    /**
     * Events
     */
    QUnit.module('Events');

    QUnit.test('change & blur', function (assert) {
        assert.ok(true, 'on(\'change blur\')');
    });


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        var tb1 = generisWidgetTextBoxFactory({}, fields[0])
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

        var tb2 = generisWidgetTextBoxFactory({}, fields[1])
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

        tb1.validator.addValidation(validations.beginWithAlpha);
        tb2.validator.addValidation(validations.threeLetters);

        $('#validate').on('click', function (e) {
            e.preventDefault();

            tb1.validate();
            console.log(tb1.serialize());

            tb2.validate();
            console.log(tb2.serialize());

            return false;
        });
    });
});

