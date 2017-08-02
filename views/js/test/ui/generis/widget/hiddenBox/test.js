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
    'ui/generis/widget/hiddenBox/hiddenBox',
], function(
    $,
    _,
    generisValidatorFactory,
    generisWidgetHiddenBoxFactory
) {
    'use strict';


    var fields = [{
        "uri" : "http://www.tao.lu/Ontologies/generis.rdf#password",
        "label" : "Password",
        "widget" : "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox",
        "required" : true
    },{
        "uri" : "http://www.tao.lu/Ontologies/generis.rdf#password",
        "label" : "Password",
        "widget" : "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox",
        "required" : true
    }];

    var validations = {
        required: {
            message: 'Must match',
            predicate: function (value) {
                return value.value === value.confirmation;
            }
        },
        goodStrength: {
            message: 'Good password strength',
            predicate: function (value) {
                return /^(?=.*[a-z])(?=.*[A-Z])(?=.*[\d])(?=.*[!@#\$%\^&\*])(?=.{8,})/.test(value.value);
            }
        }
    };


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof generisWidgetHiddenBoxFactory, 'function', 'The module exposes a function');
        assert.equal(typeof generisWidgetHiddenBoxFactory({}, {}), 'object', 'The factory produces an object');
        assert.notStrictEqual(generisWidgetHiddenBoxFactory({}, {}), generisWidgetHiddenBoxFactory({}, {}), 'The factory provides a different object on each call');
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
        var instance = generisWidgetHiddenBoxFactory({}, {});
        assert.equal(typeof instance[data.name], data.type, 'The instance exposes a(n) "' + data.title + '" ' + data.type);
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('get', function (assert) {
        var value;
        var widget = generisWidgetHiddenBoxFactory({}, {
            uri: 'foo#bar',
            value: 'foobar'
        });

        value = widget.get();

        assert.equal(value.value, 'foobar', 'returns correct value');
        assert.equal(value.confirmation, 'foobar', 'returns correct confirmation value');
    });

    QUnit.test('set', function (assert) {
        var getValue;
        var setValue;
        var widget = generisWidgetHiddenBoxFactory({}, {
            uri: 'foo#bar',
            value: 'foobar'
        });

        setValue = widget.set('baz');
        getValue = widget.get();

        assert.equal(setValue.value, 'baz', 'returns updated value');
        assert.equal(getValue.value, 'baz', 'updates value');
    });

    QUnit.test('setValidator', function (assert) {
        var oldValidator;
        var widget = generisWidgetHiddenBoxFactory({}, {});

        oldValidator = widget.validator;
        widget.setValidator({});

        assert.notEqual(widget.validator, oldValidator, 'validator is replaced');
    });

    QUnit.test('validate', function (assert) {
        var widget = generisWidgetHiddenBoxFactory({
            validator: [ validations.goodStrength ]
        }, {
            value: 'abc123'
        })
        .validate();

        assert.equal(widget.validator.errors.length, 1, 'validate properly generated errors');
    });

    QUnit.test('serialize', function (assert) {
        var obj = {
            uri: 'foo#bar',
            value: 'foobar'
        };
        var serialized;

        serialized = generisWidgetHiddenBoxFactory({}, obj).serialize();

        assert.equal(serialized.name, obj.uri, 'name property is correct');
        assert.deepEqual(serialized.value, obj.value, 'value property is correct');
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
        var tb1, tb2;

        tb1 = generisWidgetHiddenBoxFactory({}, fields[0])
        .setValidator([ validations.required ])
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

        tb2 = generisWidgetHiddenBoxFactory({}, fields[1])
        .setValidator([ validations.goodStrength ])
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

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

