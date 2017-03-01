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
    'lodash',
    'core/promise',
    'core/dataProvider/proxy'
], function (_, Promise, proxyFactory) {
    'use strict';

    var defaultProxy = {
        init: _.noop,
        destroy: _.noop,
        create: _.noop,
        read: _.noop,
        write: _.noop,
        remove: _.noop,
        action: _.noop
    };

    var proxyApi = [
        {title: 'init'},
        {title: 'destroy'},
        {title: 'create'},
        {title: 'read'},
        {title: 'write'},
        {title: 'remove'},
        {title: 'action'},
        {title: 'addExtraParams'},
        {title: 'getTokenHandler'},
        {title: 'getConfig'},
        {title: 'getMiddlewares'},
        {title: 'setMiddlewares'}
    ];


    QUnit.module('proxyFactory', {
        setup: function () {
            proxyFactory.clearProviders();
        }
    });


    QUnit.test('module', function (assert) {
        QUnit.expect(5);

        assert.equal(typeof proxyFactory, 'function', "The proxyFactory module exposes a function");
        assert.equal(typeof proxyFactory.registerProvider, 'function', "The proxyFactory module exposes a registerProvider method");
        assert.equal(typeof proxyFactory.getProvider, 'function', "The proxyFactory module exposes a getProvider method");

        proxyFactory.registerProvider('default', defaultProxy);

        assert.equal(typeof proxyFactory(), 'object', "The proxyFactory factory produces an object");
        assert.notStrictEqual(proxyFactory(), proxyFactory(), "The proxyFactory factory provides a different object on each call");
    });


    QUnit
        .cases(proxyApi)
        .test('instance API ', function (data, assert) {
            var instance;
            proxyFactory.registerProvider('default', defaultProxy);
            instance = proxyFactory('default');
            QUnit.expect(1);
            assert.equal(typeof instance[data.title], 'function', 'The proxyFactory instance exposes a "' + data.title + '" function');
        });


    QUnit.asyncTest('proxy.init()', function (assert) {
        var initConfig = {};
        var result, proxy;

        QUnit.expect(9);

        proxyFactory.registerProvider('default', _.defaults({
            init: function (config) {
                assert.ok(true, 'The proxyFactory has delegated the call');
                assert.deepEqual(config, initConfig, 'The proxyFactory has provided the config object as a parameter');
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default');
        result = proxy
            .on('init', function (promise, config) {
                assert.ok(true, 'The proxyFactory has fired the "init" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "init" event');
                assert.deepEqual(config, initConfig, 'The proxyFactory has provided the config object through the "init" event');
            })
            .init(initConfig);

        assert.ok(result instanceof Promise, 'The proxyFactory.init() method has returned a promise');

        result
            .then(function (resolvedProxy) {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(proxy.getConfig(), initConfig, 'The proxyFactory has provided the config object');
                assert.equal(resolvedProxy, proxy, 'The promise has been resolved with the initialized proxy');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('proxy.destroy()', function (assert) {
        var proxy;

        QUnit.expect(5);

        proxyFactory.registerProvider('default', _.defaults({
            destroy: function () {
                assert.ok(true, 'The proxyFactory has delegated the call');
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default')
            .on('destroy', function (promise) {
                assert.ok(true, 'The proxyFactory has fired the "destroy" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "destroy" event');
            });

        proxy.init()
            .then(function () {
                var result = proxy.destroy();

                assert.ok(result instanceof Promise, 'The proxyFactory.destroy() method has returned a promise');

                return result;
            })
            .then(function () {
                assert.ok(true, 'The promise should be resolved');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });

    });


    QUnit.asyncTest('proxy.create()', function (assert) {
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };

        QUnit.expect(8);

        proxyFactory.registerProvider('default', _.defaults({
            create: function (params) {
                assert.ok(true, 'The proxyFactory has delegated the call');
                assert.deepEqual(params, expectedParams, 'The delegated method received the expected parameters');
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default')
            .on('create', function (promise, params) {
                assert.ok(true, 'The proxyFactory has fired the "create" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "create" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "create" event');
            });

        proxy.create()
            .then(function () {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function () {
                assert.ok(true, 'The proxy must be initialized');
            });

        proxy.init()
            .then(function () {
                var result = proxy.create(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.create() method has returned a promise');

                return result;
            })
            .then(function () {
                assert.ok(true, 'The promise should be resolved');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('proxy.read()', function (assert) {
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };

        QUnit.expect(8);

        proxyFactory.registerProvider('default', _.defaults({
            read: function (params) {
                assert.ok(true, 'The proxyFactory has delegated the call');
                assert.deepEqual(params, expectedParams, 'The delegated method received the expected parameters');
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default')
            .on('read', function (promise, params) {
                assert.ok(true, 'The proxyFactory has fired the "read" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "read" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "read" event');
            });

        proxy.read()
            .then(function () {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function () {
                assert.ok(true, 'The proxy must be initialized');
            });

        proxy.init()
            .then(function () {
                var result = proxy.read(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.read() method has returned a promise');

                return result;
            })
            .then(function () {
                assert.ok(true, 'The promise should be resolved');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('proxy.write()', function (assert) {
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };

        QUnit.expect(8);

        proxyFactory.registerProvider('default', _.defaults({
            write: function (params) {
                assert.ok(true, 'The proxyFactory has delegated the call');
                assert.deepEqual(params, expectedParams, 'The delegated method received the expected parameters');
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default')
            .on('write', function (promise, params) {
                assert.ok(true, 'The proxyFactory has fired the "write" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "write" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "write" event');
            });

        proxy.write()
            .then(function () {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function () {
                assert.ok(true, 'The proxy must be initialized');
            });

        proxy.init()
            .then(function () {
                var result = proxy.write(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.write() method has returned a promise');

                return result;
            })
            .then(function () {
                assert.ok(true, 'The promise should be resolved');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('proxy.remove()', function (assert) {
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };

        QUnit.expect(8);

        proxyFactory.registerProvider('default', _.defaults({
            remove: function (params) {
                assert.ok(true, 'The proxyFactory has delegated the call');
                assert.deepEqual(params, expectedParams, 'The delegated method received the expected parameters');
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default')
            .on('remove', function (promise, params) {
                assert.ok(true, 'The proxyFactory has fired the "remove" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "remove" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "remove" event');
            });

        proxy.remove()
            .then(function () {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function () {
                assert.ok(true, 'The proxy must be initialized');
            });

        proxy.init()
            .then(function () {
                var result = proxy.remove(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.remove() method has returned a promise');

                return result;
            })
            .then(function () {
                assert.ok(true, 'The promise should be resolved');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('proxy.action()', function (assert) {
        var proxy;
        var expectedAction = 'fooBar';
        var expectedParams = {
            foo: 'bar'
        };

        QUnit.expect(10);

        proxyFactory.registerProvider('default', _.defaults({
            action: function (action, params) {
                assert.ok(true, 'The proxyFactory has delegated the call');
                assert.equal(action, expectedAction, 'The proxyFactory has provided the action name');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params');
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default')
            .on('action', function (promise, action, params) {
                assert.ok(true, 'The proxyFactory has fired the "action" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "action" event');
                assert.equal(action, expectedAction, 'The proxyFactory has provided the action through the "action" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "action" event');
            });

        proxy.action(expectedAction, expectedParams)
            .then(function () {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function () {
                assert.ok(true, 'The proxy must be initialized');
            });

        proxy.init()
            .then(function () {
                var result = proxy.action(expectedAction, expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.action() method has returned a promise');

                return result;
            })
            .then(function () {
                assert.ok(true, 'The promise should be resolved');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('proxy.addExtraParams()', function (assert) {
        var expectedAction = 'fooBar';
        var expectedParams = {
            foo: 'bar'
        };
        var extraParams = {
            noz: 'moo'
        };
        var extraParamsSet = false;
        var proxy;

        QUnit.expect(14);

        proxyFactory.registerProvider('default', _.defaults({
            read: function () {
                return Promise.resolve();
            },
            write: function () {
                return Promise.resolve();
            },
            action: function () {
                return Promise.resolve();
            }
        }, defaultProxy));

        proxy = proxyFactory('default');

        proxy.init()
            .then(function () {
                proxy
                    .on('read', function (promise, params) {
                        assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "action" event');
                        if (extraParamsSet) {
                            assert.deepEqual(params, _.merge({}, expectedParams, extraParams), 'The proxyFactory has provided the params through the "read" event with extra parameters');

                            extraParamsSet = false;
                            proxy.read(expectedParams);
                        } else {
                            assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "read" event without extra parameters');

                            proxy.addExtraParams(extraParams);
                            extraParamsSet = true;
                            proxy.write(expectedParams);
                        }
                    })
                    .on('write', function (promise, params) {
                        assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "action" event');
                        if (extraParamsSet) {
                            assert.deepEqual(params, _.merge({}, expectedParams, extraParams), 'The proxyFactory has provided the params through the "write" event with extra parameters');

                            extraParamsSet = false;
                            proxy.write(expectedParams);
                        } else {
                            assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "write" event without extra parameters');

                            proxy.addExtraParams(extraParams);
                            extraParamsSet = true;
                            proxy.action(expectedAction, expectedParams);
                        }
                    })
                    .on('action', function (promise, action, params) {
                        assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "action" event');
                        assert.equal(action, expectedAction, 'The proxyFactory has provided the action through the "action" event');

                        if (extraParamsSet) {
                            assert.deepEqual(params, _.merge({}, expectedParams, extraParams), 'The proxyFactory has provided the params through the "action" event with extra parameters');

                            extraParamsSet = false;
                            proxy.action(expectedAction, expectedParams);
                        } else {
                            assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "action" event without extra parameters');

                            QUnit.start();
                        }
                    });

                proxy.addExtraParams(extraParams);
                extraParamsSet = true;
                return proxy.read(expectedParams);
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.test('proxy.getTokenHandler()', function (assert) {
        var proxy, securityToken;

        proxyFactory.registerProvider('default', defaultProxy);
        proxy = proxyFactory('default');
        securityToken = proxy.getTokenHandler();

        QUnit.expect(3);

        assert.equal(typeof securityToken, 'object', 'The proxy has built a securityToken handler');
        assert.equal(typeof securityToken.getToken, 'function', 'The securityToken handler has a getToken method');
        assert.equal(typeof securityToken.setToken, 'function', 'The securityToken handler has a setToken method');
    });


    QUnit.asyncTest('proxy with middleware', function (assert) {
        var proxy;
        var current;
        var expectedParams = {
            foo: 'bar'
        };
        var expectedData = {
            success: true,
            list: [1, 2, 3]
        };
        var expectedResponse = _.merge({
            record: expectedParams
        }, expectedData);

        var middleware = {
            use: function () {
            },
            apply: function (request, response) {
                var params = current === 'init' ? [{}] : [expectedParams];
                assert.deepEqual(request, {command: current, params: params}, "The request command has been set");
                assert.deepEqual(response, expectedData, "The response has been provided");

                response.record = expectedParams;
                return response;
            }
        };

        QUnit.expect(7);

        proxyFactory.registerProvider('default', _.defaults({
            init: function () {
                current = 'init';
                return expectedData;
            },
            read: function () {
                current = 'read';
                return expectedData;
            }
        }, defaultProxy));

        proxy = proxyFactory('default', middleware);

        proxy.init()
            .then(function () {
                assert.equal(proxy.getMiddlewares(), middleware, 'The proxy should return the registered middleware handler');

                return proxy.read(expectedParams);
            })
            .then(function (response) {
                assert.deepEqual(response, expectedResponse, "The correct response has been read");
                proxy.setMiddlewares(null);
                assert.equal(proxy.getMiddlewares(), null, 'The middleware handler has been changed');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('proxy #error', function (assert) {
        var proxy;
        var expectedError = new Error("Test");

        QUnit.expect(5);

        proxyFactory.registerProvider('default', _.defaults({
            read: function () {
                assert.ok(true, 'The proxyFactory has delegated the call');
                return Promise.reject(expectedError);
            }
        }, defaultProxy));

        proxy = proxyFactory('default')
            .on('error', function (err) {
                assert.ok(true, 'The proxyFactory has fired the "error" event');
                assert.deepEqual(err, expectedError, 'The proxyFactory has provided the error through the "error" event');
            });

        proxy.init()
            .then(function () {
                return proxy.read();
            })
            .then(function () {
                assert.ok(false, 'The promise should be rejected');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(true, 'The promise should be rejected');
                assert.deepEqual(err, expectedError, 'The proxyFactory has provided the error');
                QUnit.start();
            });
    });
});
