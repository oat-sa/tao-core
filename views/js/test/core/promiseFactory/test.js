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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'core/promiseFactory'
], function($, _, promiseFactory) {
    'use strict';

    QUnit.module('promiseFactory');


    QUnit.test('module', 3, function(assert) {
        assert.equal(typeof promiseFactory, 'function', "The promiseFactory module exposes a function");
        assert.equal(typeof promiseFactory(), 'object', "The promiseFactory factory produces an object");
        assert.notStrictEqual(promiseFactory(), promiseFactory(), "The promiseFactory factory provides a different object on each call");
    });


    var promiseApi = [
        { name : 'resolve', title : 'resolve' },
        { name : 'reject', title : 'reject' },
        { name : 'then', title : 'then' },
        { name : 'catch', title : 'catch' }
    ];

    QUnit
        .cases(promiseApi)
        .test('instance API ', 1, function(data, assert) {
            var instance = promiseFactory();
            assert.equal(typeof instance[data.name], 'function', 'The promiseFactory instance exposes a "' + data.title + '" function');
        });


    QUnit.asyncTest('promise.resolve', 1, function(assert) {
        var promise = promiseFactory();

        promise.then(function() {
            assert.ok('true', 'The promise must be resolved');
            QUnit.start();
        }).catch(function(){
            assert.ok('false', 'The promise must not be rejected');
            QUnit.start();
        });

        promise.resolve();
    });


    QUnit.asyncTest('promise.reject', 1, function(assert) {
        var promise = promiseFactory();

        promise.then(function() {
            assert.ok('false', 'The promise must not be resolved');
            QUnit.start();
        }).catch(function(){
            assert.ok('true', 'The promise must be rejected');
            QUnit.start();
        });

        promise.reject();
    });
});
