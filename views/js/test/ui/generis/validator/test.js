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
        { name: 'show',              title: 'show',              type: 'function' },
        { name: 'hide',              title: 'hide',              type: 'function' },
        { name: 'run',               title: 'run',               type: 'function' },
        { name: 'clear',             title: 'clear',             type: 'function' },
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

    QUnit.test('initialization', function (assert) {
        generisValidatorFactory()
        .on('init', function () {
            assert.ok(true, 'validator is successfully initialized');
        })
        .init();
    });

    QUnit.test('run', function (assert) {
        generisValidatorFactory({
            validations: validations
        })
        .on('init', function () {
            this.run('');
            assert.ok(this.errors.length, 'can find errors');

            assert.deepEqual(this.errors, ['one', 'two', 'three'], 'errors will be sorted by precedence');

            this.run('one two three');
            assert.ok(!this.errors.length, 'can find no errors');
        })
        .init();
    });

    QUnit.test('clear', function (assert) {
        generisValidatorFactory({
            validations: validations
        })
        .on('init', function () {
            this.run('');
            assert.ok(this.errors.length, 'first populate with errors');

            this.clear();
            assert.ok(!this.errors.length, 'then show clear removes all errors');
        })
        .init();
    });

    QUnit.test('display', function (assert) {
        assert.ok(true, 'todo');
    });

    QUnit.test('addValidation', function (assert) {
        generisValidatorFactory({
            validations: validations
        })
        .on('init', function () {
            this.addValidation({});
            assert.equal(this.validations.length, 4, 'added validation');
        })
        .init();
    });

    QUnit.test('removeValidations', function (assert) {
        generisValidatorFactory({
            validations: validations
        })
        .on('init', function () {
            this.removeValidations();
            assert.equal(this.validations.length, 0, 'cleared validations');
        })
        .init();
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
        .init()
        .render('#field-1');

        selectTwo = $('#field-2').find('select');

        validationTwo = generisValidatorFactory({
            validations: validations
        })
        .init()
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