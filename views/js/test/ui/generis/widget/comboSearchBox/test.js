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
    'ui/generis/widget/comboBox/comboBox',
    'ui/generis/widget/comboSearchBox/comboSearchBox',
    'json!test/ui/generis/widget/comboSearchBox/data.json'
], function(
    $,
    _,
    validator,
    comboBox,
    comboSearchBox,
    data
) {
    'use strict';


    var ranges = {
        "http://www.tao.lu/Ontologies/TAO.rdf#Languages" : [{
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#Langda-DK",
            "label" : "Dansih"
        },{
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#Langen-US",
            "label" : "English"
        },{
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR",
            "label" : "French"
        },{
            "uri" : "http://www.tao.lu/Ontologies/TAO.rdf#Langde-DE",
            "label" : "German"
        }]
    };

    var validations = {
        required: {
            message: 'Select something...',
            predicate: function (value) {
                return !!value;
            }
        },
        notGerman: {
            message: 'Anything but German',
            predicate: function (value) {
                var german = 'http://www.tao.lu/Ontologies/TAO.rdf#Langde-DE';
                return value !== german;
            }
        }
    };


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('get', function (assert) {
        var widget = comboSearchBox({}, {
            uri: 'foo#bar',
            value: 'foobar'
        });

        assert.equal(widget.get(), 'foobar', 'returns correct value');
    });

    QUnit.test('set', function (assert) {
        var widget = comboSearchBox({}, {
            uri: 'foo#bar',
            value: 'foobar'
        });

        assert.equal(widget.set('baz'), 'baz', 'returns updated value');
        assert.equal(widget.get(), 'baz', 'updates value');
    });

    QUnit.test('setValidator', function (assert) {
        var oldValidator;
        var widget = comboSearchBox({}, {});

        oldValidator = widget.validator;
        widget.setValidator({});

        assert.notEqual(widget.validator, oldValidator, 'validator is replaced');
    });

    QUnit.test('validate', function (assert) {
        var widget = comboSearchBox({
            validator: [ validations.notGerman ]
        }, {
            value: 'http://www.tao.lu/Ontologies/TAO.rdf#Langde-DE',
            range: ranges['http://www.tao.lu/Ontologies/TAO.rdf#Languages']
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

        serialized = comboSearchBox({}, obj).serialize();

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
        var prop, tb1, tb2;

        prop = data.properties[0];
        prop.range = data.values[prop.range];

        tb1 = comboBox({}, prop)
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

        tb2 = comboSearchBox({}, prop)
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

