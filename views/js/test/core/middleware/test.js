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
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'core/middleware'
], function (middlewaresHandlerFactory) {
    'use strict';

    var middlewareApi = [
        {title: 'use'},
        {title: 'apply'}
    ];


    QUnit.module('middlewaresHandler');


    QUnit.test('module', function (assert) {
        QUnit.expect(3);

        assert.equal(typeof middlewaresHandlerFactory, 'function', "The middlewaresHandlerFactory module exposes a function");
        assert.equal(typeof middlewaresHandlerFactory(), 'object', "The middlewaresHandlerFactory factory produces an object");
        assert.notStrictEqual(middlewaresHandlerFactory(), middlewaresHandlerFactory(), "The middlewaresHandlerFactory factory provides a different object on each call");
    });


    QUnit
        .cases(middlewareApi)
        .test('instance API ', function (data, assert) {
            var instance = middlewaresHandlerFactory();
            QUnit.expect(1);
            assert.equal(typeof instance[data.title], 'function', 'The middlewaresHandlerFactory instance exposes a "' + data.title + '" function');
        });


    QUnit.asyncTest('middlewares.apply() #success', function (assert) {
        var middlewares = middlewaresHandlerFactory();
        var request = {
            command: 'read',
            params: {
                foo: 'bar'
            }
        };
        var response = {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        };
        var context = {
            name: 'foo'
        };

        QUnit.expect(9);

        middlewares
            .use(function (req, res, next) {
                assert.ok(true, 'The global middleware has been called');
                assert.deepEqual(req, request, 'The request has been provided');
                assert.deepEqual(res, response, 'The response has been provided');
                assert.deepEqual(this, context, 'The right context has been set');
                next();
            })
            .use('read', function (req, res, next) {
                assert.ok(true, 'The read middleware has been called');
                assert.deepEqual(req, request, 'The request has been provided');
                assert.deepEqual(res, response, 'The response has been provided');
                assert.deepEqual(this, context, 'The right context has been set');
                next();
            })
            .use('refresh', function (req, res, next) {
                assert.ok(false, 'The refresh middleware should not be called');
                next(false);
            })
            .apply(request, response, context)
            .then(function (res) {
                assert.deepEqual(res, response, 'The response has been provided');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('middlewares.apply() #fails', function (assert) {
        var middlewares = middlewaresHandlerFactory();
        var request = {
            command: 'read',
            params: {
                foo: 'bar'
            }
        };
        var response = {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        };
        var context = {
            name: 'foo'
        };
        var error = {
            code: -1,
            message: "oups!"
        };

        QUnit.expect(5);

        middlewares
            .use(function (req, res, next) {
                assert.ok(false, 'The global middleware should not be called');
                next();
            })
            .use('read', function (req, res, next) {
                assert.ok(true, 'The read middleware has been called');
                assert.deepEqual(req, request, 'The request has been provided');
                assert.deepEqual(res, response, 'The response has been provided');
                assert.deepEqual(this, context, 'The right context has been set');
                next(error);
            })
            .use('refresh', function (req, res, next) {
                assert.ok(false, 'The refresh middleware should not be called');
                next(false);
            })
            .apply(request, response, context)
            .then(function () {
                assert.ok(false, 'The promise should be rejected');
                QUnit.start();
            })
            .catch(function (err) {
                assert.deepEqual(err, error, 'The error has been provided');
                QUnit.start();
            });
    });


    QUnit.asyncTest('middlewares.apply() #failed response', function (assert) {
        var middlewares = middlewaresHandlerFactory();
        var request = {
            command: 'read',
            params: {
                foo: 'bar'
            }
        };
        var response = {
            success: false,
            message: "oups"
        };
        var context = {
            name: 'foo'
        };

        QUnit.expect(9);

        middlewares
            .use(function (req, res, next) {
                assert.ok(true, 'The global middleware has been called');
                assert.deepEqual(req, request, 'The request has been provided');
                assert.deepEqual(res, response, 'The response has been provided');
                assert.deepEqual(this, context, 'The right context has been set');
                next();
            })
            .use('read', function (req, res, next) {
                assert.ok(true, 'The read middleware has been called');
                assert.deepEqual(req, request, 'The request has been provided');
                assert.deepEqual(res, response, 'The response has been provided');
                assert.deepEqual(this, context, 'The right context has been set');
                next();
            })
            .use('refresh', function (req, res, next) {
                assert.ok(false, 'The refresh middleware should not be called');
                next(false);
            })
            .apply(request, response, context)
            .then(function () {
                assert.ok(false, 'The promise should be rejected');
                QUnit.start();
            })
            .catch(function (err) {
                assert.deepEqual(err, response, 'The error has been provided');
                QUnit.start();
            });
    });


    QUnit.asyncTest('middlewares.apply() #missing next', function (assert) {
        var middlewares = middlewaresHandlerFactory();
        var request = {
            command: 'read',
            params: {
                foo: 'bar'
            }
        };
        var response = {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        };
        var context = {
            name: 'foo'
        };
        var to = setTimeout(function() {
            assert.ok(true, 'The next has never be called...');
            QUnit.start();
        }, 500);

        QUnit.expect(3);

        middlewares
            .use(function () {
                assert.ok(true, 'The global middleware has been called');
            })
            .use('read', function (req, res, next) {
                assert.ok(true, 'The read middleware has been called');
                next();
            })
            .use('refresh', function (req, res, next) {
                assert.ok(false, 'The refresh middleware should not be called');
                next(false);
            })
            .apply(request, response, context)
            .then(function () {
                assert.ok(false, 'The promise should not be rejected');
                clearTimeout(to);
                QUnit.start();
            })
            .catch(function () {
                assert.ok(false, 'The promise should not be rejected');
                clearTimeout(to);
                QUnit.start();
            });
    });
});
