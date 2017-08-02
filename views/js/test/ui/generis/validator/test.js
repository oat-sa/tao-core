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
    'ui/generis/validator/validator'
], function(
    $,
    _,
    generisValidatorFactory
) {
    'use strict';

    var validations = [
        {
            predicate: /three/i,
            message: 'three'
        },
        {
            predicate: /one/i,
            message: 'one',
            precedence: 1
        },
        {
            predicate: function (value) {
                return value.indexOf('two') > -1;
            },
            message: 'two',
            precedence: 99
        }
    ];


    /**
     * Api
     */
    QUnit.module('Api');

    QUnit.test('module', 3, function (assert) {
        var obj1 = generisValidatorFactory();
        var obj2 = generisValidatorFactory();
        assert.equal(typeof generisValidatorFactory, 'function', 'The module exposes a function');
        assert.equal(typeof obj1, 'object', 'The factory produces an object');
        assert.notStrictEqual(obj1, obj2, 'The factory provides a different object on each call');
    });

    QUnit
    .cases([
        { name: 'errors',            title: 'errors',            type: 'object' },
        { name: 'validations',       title: 'validations',       type: 'object' },
        { name: 'run',               title: 'run',               type: 'function' },
        { name: 'clear',             title: 'clear',             type: 'function' },
        { name: 'display',           title: 'display',           type: 'function' },
        { name: 'addValidation',     title: 'addValidation',     type: 'function' },
        { name: 'removeValidations', title: 'removeValidations', type: 'function' },
    ])
    .test('instance', function (data, assert) {
        var instance = generisValidatorFactory();
        assert.equal(typeof instance[data.name], data.type, 'The instance exposes a(n) "' + data.title + '" ' + data.type);
    });


    /**
     * Methods
     */
    QUnit.module('Methods');

    QUnit.test('run', function (assert) {
        var validator = generisValidatorFactory({
            validations: validations
        });

        validator.run('');
        assert.deepEqual(validator.errors, ['one', 'two', 'three'], 'errors will be sorted by precedence');

        validator.run('one two three');
        assert.ok(!validator.errors.length, 'can find no errors');
    });

    QUnit.test('clear', function (assert) {
        var validator = generisValidatorFactory({
            validations: validations
        });

        validator.run('');
        assert.ok(validator.errors.length, 'first populate with errors');

        validator.clear();
        assert.ok(!validator.errors.length, 'then show clear removes all errors');
    });

    QUnit.test('display', function (assert) {
        assert.ok(true, 'display is a visual test');
    });

    QUnit.test('addValidation', function (assert) {
        var validator = generisValidatorFactory({
            validations: validations
        });

        validator.addValidation({});
        assert.equal(validator.validations.length, 4, 'added validation');
    });

    QUnit.test('removeValidations', function (assert) {
        var validator = generisValidatorFactory({
            validations: validations
        });

        validator.removeValidations();
        assert.equal(validator.validations.length, 0, 'cleared validations');
    });

    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        var inputOne, selectTwo;
        var validationOne, validationTwo;

        inputOne = $('#field-1').find('input');

        validationOne = generisValidatorFactory({
            validations: validations
        })
        .render('#field-1');

        selectTwo = $('#field-2').find('select');

        validationTwo = generisValidatorFactory({
            validations: validations
        })
        .render('#field-2');

        $('form')
        .on('submit', function (e) {
            e.preventDefault();

            validationOne.run(inputOne.val());
            validationOne.display();

            validationTwo.run(selectTwo.val());
            validationTwo.display();

            return false;
        });

        assert.ok(true);
    });
});