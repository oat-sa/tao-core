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
    'ui/generis/widget/checkBox/checkBox',
], function(
    $,
    _,
    generisValidatorFactory,
    generisWidgetCheckBoxFactory
) {
    'use strict';


    var fields = [{
        "uri" : "http://www.tao.lu/Ontologies/generis.rdf#userRoles",
        "label" : "Roles",
        "range" : "http://www.tao.lu/Ontologies/TAO.rdf#UserRole",
        "widget" : "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox",
        "required" : true,
        "values" : [
            "http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole",
            "http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole"
        ]
    }, {
        "uri" : "http://www.tao.lu/Ontologies/generis.rdf#userRoles",
        "label" : "Roles",
        "range" : "http://www.tao.lu/Ontologies/TAO.rdf#UserRole",
        "widget" : "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox",
        "required" : true,
        "values" : [
            "http://www.tao.lu/Ontologies/TAO.rdf#TestAuthor",
            "http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole"
        ]
    }];

    var ranges = {
        "http://www.tao.lu/Ontologies/TAO.rdf#UserRole" : [{
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#GlobalManagerRole",
            "label" : "Global Manager Role"
        }, {
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole",
            "label" : "System Administrator"
        }, {
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#TestAuthor",
            "label" : "Test Author"
        }, {
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole",
            "label" : "Test Taker"
        }]
    };

    var validations = {
        oneOrOther: {
            message: 'Cannot be both test taker and test author',
            predicate: function (values) {
                var testAuthor = 'http://www.tao.lu/Ontologies/TAO.rdf#TestAuthor';
                var testTaker = 'http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole';

                return ! (_.contains(values, testAuthor) && _.contains(values, testTaker));
            }
        },
        sysAdminOnly: {
            message: 'A Systems Administrator cannot have other roles',
            predicate: function (values) {
                var sysAdmin = 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';

                return _.contains(values, sysAdmin) ? values.length === 1 : true;
            }
        }
    };


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof generisWidgetCheckBoxFactory, 'function', 'The module exposes a function');
        assert.equal(typeof generisWidgetCheckBoxFactory({}, {}), 'object', 'The factory produces an object');
        assert.notStrictEqual(generisWidgetCheckBoxFactory({}, {}), generisWidgetCheckBoxFactory({}, {}), 'The factory provides a different object on each call');
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
        var instance = generisWidgetCheckBoxFactory({}, {});
        assert.equal(typeof instance[data.name], data.type, 'The instance exposes a(n) "' + data.title + '" ' + data.type);
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('get', function (assert) {
        var widget = generisWidgetCheckBoxFactory({}, {
            uri: 'foo#bar',
            values: [ 'foobar' ]
        });

        assert.ok(_.contains(widget.get(), 'foobar'), 'returns correct value');
    });

    QUnit.test('set', function (assert) {
        var widget = generisWidgetCheckBoxFactory({}, {
            uri: 'foo#bar',
            value: 'foobar'
        });

        assert.ok(_.contains(widget.set('baz'), 'baz'), 'returns updated value');
        assert.ok(_.contains(widget.get(), 'baz'), 'updates value');
    });

    QUnit.test('setValidator', function (assert) {
        var oldValidator;
        var widget = generisWidgetCheckBoxFactory({}, {});

        oldValidator = widget.validator;
        widget.setValidator({});

        assert.notEqual(widget.validator, oldValidator, 'validator is replaced');
    });

    QUnit.test('validate', function (assert) {
        var widget = generisWidgetCheckBoxFactory({
            validator: [ validations.sysAdminOnly ]
        }, {
            values: [
                'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',
                'http://www.tao.lu/Ontologies/TAO.rdf#TestAuthor'
            ]
        })
        .validate();

        assert.equal(widget.validator.errors.length, 1, 'validate properly generated errors');
    });

    QUnit.test('serialize', function (assert) {
        var obj = {
            uri: 'foo#bar',
            values: [ 'foobar' ]
        };
        var serialized;

        serialized = generisWidgetCheckBoxFactory({}, obj).serialize();

        assert.equal(serialized.name, obj.uri, 'name property is correct');
        assert.deepEqual(serialized.value, obj.values, 'value property is correct');
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

        fields[0].range = ranges[fields[0].range];
        fields[1].range = ranges[fields[1].range];

        tb1 = generisWidgetCheckBoxFactory({}, fields[0])
        .setValidator([ validations.sysAdminOnly ])
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

        tb2 = generisWidgetCheckBoxFactory({}, fields[1])
        .setValidator([ validations.oneOrOther ])
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

