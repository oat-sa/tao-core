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
    'ui/form/form'
], function(
    $,
    _,
    Handlebars,
    formFactory
) {
    'use strict';

    var formApi;
    var template = Handlebars.compile(
        `<div id='ahh'>
            <label for="{{uri}}">{{label}}</label>
            <input name="{{uri}}" value="{{value}}">
        </div>`
    );


    QUnit.module('Api');

    // factory
    QUnit.test('module', 3, function (assert) {
        assert.equal(typeof formFactory, 'function', "The form module exposes a function");
        assert.equal(typeof formFactory(), 'object', "The form factory produces an object");
        assert.notStrictEqual(formFactory(), formFactory(), "The form factory provides a different object on each call");
    });


    formApi = [
        { name : 'getField', title : 'getField' },
        { name : 'addField', title : 'addField' },
        { name : 'removeField', title : 'removeField' },
        { name : 'validate', title : 'validate' }
    ];

    // instance
    QUnit
    .cases(formApi)
    .test('instance', function (data, assert) {
        var instance = formFactory();
        assert.equal(typeof instance[data.name], 'function', 'The form instance exposes a "' + data.title + '" function');
    });


    QUnit.module('Functionality');

    // can get, add, and remove fields
    QUnit.test('get, add, and remove fields', function (assert) {
        var form = formFactory();

        form.addField('field-one', {});
        form.addField('field-two', {});

        form.removeField('field-one');

        assert.ok(form.getField('field-two'), 'Added field exists');
        assert.ok(!form.getField('field-one'), 'Removed field does not exist');
    });


    // loads
    QUnit.asyncTest('loads form data from url', function (assert) {
        QUnit.expect(1);

        formFactory({
            request: {
                url: '/tao/views/js/test/ui/form/form.json'
            }
        })
        .on('load', function () {
            assert.ok(
                this.getField('http://taoplatform#field-one'),
                'Form data loaded successfully from request'
            );
        });

        QUnit.start();
    });

    QUnit.asyncTest('loads form data from json', function (assert) {
        QUnit.expect(1);

        $.ajax('/tao/views/js/test/ui/form/form.json')
        .success(function (res) {
            var form = formFactory({
                json: res.data
            });
            form.on('load', function () {
                assert.ok(
                    this.getField('http://taoplatform#field-one'),
                    'Form data loaded successfully from json'
                );
            });

            QUnit.start();
        });
    });


    // validations
    QUnit.test('validates form', function (assert) {
        var form = formFactory().render();

        form.addField('valid', {
            templateVars: {
                label: 'Hello',
                value: 'World',
                uri: 'http://taoplatform#field-one'
            },
            widget: template,
            validations: [
                {
                    predicate: /^World$/i,
                    message: 'Must be "World"'
                }
            ]
        });

        assert.ok(form.validate(), 'Form validated successfully');

        form.addField('invalid', {
            templateVars: {
                label: 'Foo',
                value: 'Bar',
                uri: 'http://taoplatform#field-two'
            },
            widget: template,
            validations: [
                {
                    predicate: /.{4,}/,
                    message: 'Must contain at least 4 characters'
                }
            ]
        });

        assert.ok(!form.validate(), 'Form invalidated successfully');
    });


    // submits
    QUnit.asyncTest('submits form', function (assert) {
        QUnit.expect(2);

        // success
        formFactory({
            action: '/tao/views/js/test/ui/form/success.json'
        })
        .on('success', function (data) {
            assert.ok(data, 'Form submitted successfully');
        })
        .render()
        .getElement().find('form').trigger('submit');

        // error
        formFactory({
            action: '/tao/views/js/test/ui/form/error.json'
        })
        .on('error', function (error) {
            assert.ok(error, 'Form submitted with error');
        })
        .render()
        .getElement().find('form').trigger('submit');

        QUnit.start();
    });


    QUnit.module('Visual Test');

    // display & play
    QUnit.test('Display and play', function (assert) {
        QUnit.expect(1);

        formFactory({
            action: '/tao/views/js/test/ui/form/success.json',
            name: 'Test form',
            templateVars: {
                submit: {
                    value: 'Push me!!!'
                }
            }
        })

        .on('render', function () {
            assert.ok(true);
        })

        .on('success', function (data) {
            console.log('success', data);
        })

        .on('error', function (error) {
            console.log('error', error);
        })

        .addField('field-one', {
            templateVars: {
                label: 'Hello',
                value: 'World',
                uri: 'http://taoplatform#field-one'
            },
            widget: template,
            validations: [
                {
                    predicate: /^World$/i,
                    message: 'Must be "World"'
                }
            ]
        })

        .addField('field-two', {
            templateVars: {
                label: 'Foo',
                value: 'Bar',
                uri: 'http://taoplatform#field-two'
            },
            widget: template,
            validations: [
                {
                    predicate: /.{4,}/,
                    message: 'Must contain at least 4 characters'
                }
            ]
        })

        .render('#display-and-play');
    });

});
