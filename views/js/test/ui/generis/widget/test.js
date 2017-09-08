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
    'ui/generis/widget/loader',
    'json!test/ui/generis/data'
], function(
    $,
    _,
    generisValidatorFactory,
    generisWidgetLoader,
    data
) {
    'use strict';


    var widgetProperties = [
        { name: 'get',          type: 'function' },
        { name: 'set',          type: 'function' },
        { name: 'setValidator', type: 'function' },
        { name: 'validate',     type: 'function' },
        { name: 'serialize',    type: 'function' },
        { name: 'validator',    type: 'object'   }
    ];
    var widgetUris = [
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#CheckBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboSearchBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
        'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox'
    ];


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit
    .cases(widgetUris)
    .test('module', 3, function (widgetUri, assert) {
        var factory = generisWidgetLoader(widgetUri);

        assert.equal(typeof factory, 'function', 'The module exposes a function');
        assert.equal(typeof factory({}, {}), 'object', 'The factory produces an object');
        assert.notStrictEqual(factory({}, {}), factory({}, {}), 'The factory provides a different object on each call');
    });

    QUnit
    .cases(widgetUris)
    .test('instance', function (widgetUri, assert) {
        var factory = generisWidgetLoader(widgetUri);

        _.each(widgetProperties, function (property) {
            var instance = factory({}, {});
            assert.equal(typeof instance[property.name], property.type, 'The instance exposes a(n) "' + property.name + '" ' + property.type);
        });
    });


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        var widgets = [];

        _.each(data.properties, function (property) {
            var factory = generisWidgetLoader(property.widget);
            var widget;

            property.range = data.values[property.range];
            property.required = true;
            widget = factory({}, property)
                .render('#display-and-play > form > fieldset');

            widgets.push(widget);
        });

        $('#validate').on('click', function (e) {
            e.preventDefault();

            _.each(widgets, function (widget) {
                console.log(widget.serialize());
            });

            return false;
        });

        assert.ok(true);
    });
});

