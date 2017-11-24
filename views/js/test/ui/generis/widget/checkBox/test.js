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
    QUnit.module('Behavior');

    QUnit.asyncTest('DOM', function (assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(10);

        assert.equal($('.check-box', $container).length, 0, 'The checkbox is not rendered');

        generisWidgetCheckBoxFactory({}, {
            uri: 'http://foo#bar',
            label : 'Foo',
            range : [{
                uri :  'http://foo#v1',
                label : 'v1'
            }, {
                uri :  'http://foo#v2',
                label : 'v2'
            }]
        }).on('render', function(){

            var $element  = this.getElement();
            assert.equal($('.check-box', $container).length, 1, 'The checkbox is rendered');
            assert.deepEqual($('.check-box', $container)[0], $element[0], 'The rendered element is the component element');
            assert.ok($element.hasClass('rendered'));

            assert.equal($('.left > label', $element).text().trim(), 'Foo', 'The element label is correct');

            assert.equal($('.option', $element).length, 2, 'The element hsa 2 options');
            assert.equal($('.option:nth-child(1) label', $element).text().trim(), 'v1', '1st option label is correct');
            assert.equal($('.option:nth-child(1) input', $element).val(), 'http://foo#v1', '1st option value is correct');

            assert.equal($('.option:nth-child(2) label', $element).text().trim(), 'v2', '2nd option label is correct');
            assert.equal($('.option:nth-child(2) input', $element).val(), 'http://foo#v2', '2nd option value is correct');
            QUnit.start();
        })
        .render($container);
    });


    QUnit.asyncTest('change value', function (assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(6);

        generisWidgetCheckBoxFactory({}, {
            uri: 'http://foo#bar',
            label : 'Foo',
            range : [{
                uri :  'http://foo#v1',
                label : 'v1'
            }, {
                uri :  'http://foo#v2',
                label : 'v2'
            }]
        })
        .on('change', function(values){

            assert.equal(values.name, 'http://foo#bar', 'The field name is correct');
            assert.deepEqual(values.value, ['http://foo#v1'], 'The field value contains the option');
            QUnit.start();
        })
        .on('render', function(){

            var values;
            var $element  = this.getElement();
            var $1stOpt   = $('.option:nth-child(1) input', $element);

            assert.ok($element.hasClass('rendered'));
            assert.equal($1stOpt.length, 1, 'The option exists');

            values = this.serialize();

            assert.equal(values.name, 'http://foo#bar', 'The field name is correct');
            assert.deepEqual(values.value, [], 'The field value is empty');

            $1stOpt.click();
        })
        .render($container);
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

            tb2.validate();

            return false;
        });
    });
});

