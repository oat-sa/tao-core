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
    'core/dataProvider/request',
    'core/dataProvider/proxy',
    'core/dataProvider/proxy/ajax'
], function(_, Promise, requestMock, proxyFactory, ajaxProvider) {
    'use strict';

    var ajaxProviderApi = [
        {title: 'init'},
        {title: 'destroy'},
        {title: 'create'},
        {title: 'read'},
        {title: 'write'},
        {title: 'remove'},
        {title: 'action'}
    ];

    QUnit.module('ajaxProvider', {
        beforeEach: function() {
            proxyFactory.clearProviders();
            proxyFactory.registerProvider('ajax', ajaxProvider);
            requestMock.api.removeAllListeners();
        }
    });

    QUnit.test('module', function(assert) {
        assert.expect(1);

        assert.equal(typeof ajaxProvider, 'object', 'The proxy/ajax module exposes an object');
    });

    QUnit
        .cases.init(ajaxProviderApi)
        .test('instance API ', function(data, assert) {
            assert.expect(1);
            assert.equal(typeof ajaxProvider[data.title], 'function', 'The proxy/ajax provider exposes a "' + data.title + '" function');
        });

    QUnit.test('ajax.init()', function(assert) {
        var ready = assert.async();
        var initConfig = {};
        var expectedConfig = {
            noCache: true,
            noToken: false,
            actions: {}
        };
        var result, proxy;

        assert.expect(9);

        proxy = proxyFactory('ajax');
        result = proxy
            .on('init', function(promise, config) {
                assert.ok(true, 'The proxyFactory has fired the "init" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "init" event');
                assert.deepEqual(config, expectedConfig, 'The proxyFactory has provided the config object through the "init" event');
            })
            .init(initConfig);

        assert.ok(result instanceof Promise, 'The proxyFactory.init() method has returned a promise');

        result
            .then(function() {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(proxy.getConfig(), expectedConfig, 'The proxyFactory has provided the config object through the "init" event');

                assert.equal(typeof proxy.processRequest, 'function', 'Internal method should exist');

                return proxy.destroy();
            })
            .then(function() {
                assert.ok(true, 'The promise should be resolved');
                assert.equal(proxy.processRequest, null, 'Internal method should be destroyed');
                ready();
            })
            .catch(function(err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                ready();
            });
    });

    QUnit.test('ajax.create()', function(assert) {
        var ready = assert.async();
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };
        var expectedUrl = 'http://foo.bar/create';
        var expectedMethod = 'PUT';
        var expectedResponse = {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        };
        var initConfig = {
            actions: {
                create: {
                    url: expectedUrl,
                    method: expectedMethod
                }
            }
        };

        assert.expect(10);

        proxy = proxyFactory('ajax')
            .on('create', function(promise, params) {
                assert.ok(true, 'The proxyFactory has fired the "create" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "create" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "create" event');
            });

        proxy.create()
            .then(function() {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function() {
                assert.ok(true, 'The proxy must be initialized');
            });

        requestMock.api.on('request', function(url, params, method) {
            assert.equal(url, expectedUrl, 'The url is correct');
            assert.equal(method, expectedMethod, 'The HTTP method is correct');
            delete params._;
            assert.deepEqual(params, expectedParams, 'The expected parameters have been provided');
            requestMock.api.trigger('success', expectedResponse);
        });

        proxy.init(initConfig)
            .then(function() {
                var result = proxy.create(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.create() method has returned a promise');

                return result;
            })
            .then(function(response) {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(response, expectedResponse, 'The expected responses have been provided');
                ready();
            })
            .catch(function(err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                ready();
            });
    });

    QUnit.test('ajax.read()', function(assert) {
        var ready = assert.async();
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };
        var expectedUrl = 'http://foo.bar/read';
        var expectedMethod = 'GET';
        var expectedResponse = {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        };
        var initConfig = {
            actions: {
                read: expectedUrl
            }
        };

        assert.expect(10);

        proxy = proxyFactory('ajax')
            .on('read', function(promise, params) {
                assert.ok(true, 'The proxyFactory has fired the "read" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "read" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "read" event');
            });

        proxy.read()
            .then(function() {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function() {
                assert.ok(true, 'The proxy must be initialized');
            });

        requestMock.api.on('request', function(url, params, method) {
            assert.equal(url, expectedUrl, 'The url is correct');
            assert.equal(method, expectedMethod, 'The HTTP method is correct');
            delete params._;
            assert.deepEqual(params, expectedParams, 'The expected parameters have been provided');
            requestMock.api.trigger('success', expectedResponse);
        });

        proxy.init(initConfig)
            .then(function() {
                var result = proxy.read(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.read() method has returned a promise');

                return result;
            })
            .then(function(response) {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(response, expectedResponse, 'The expected responses have been provided');
                ready();
            })
            .catch(function(err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                ready();
            });
    });

    QUnit.test('ajax.write()', function(assert) {
        var ready = assert.async();
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };
        var wrongParams = {
            wrong: 'wrong'
        };
        var expectedUrl = 'http://foo.bar/write';
        var expectedMethod = 'POST';
        var expectedResponse = {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        };
        var expectedError = {
            success: false,
            type: 'invalid',
            action: 'write',
            params: wrongParams
        };
        var initConfig = {
            actions: {
                write: {
                    url: expectedUrl,
                    method: expectedMethod,
                    validate: function(params) {
                        return _.isPlainObject(params) && !!params.foo;
                    }
                }
            }
        };

        assert.expect(13);

        proxy = proxyFactory('ajax')
            .on('write', function(promise, params) {
                assert.ok(true, 'The proxyFactory has fired the "write" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "write" event');
                promise.then(function() {
                    assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "write" event');
                }).catch(function() {
                    assert.ok(true, 'The proxy must be initialized');
                });
            });

        proxy.write()
            .then(function() {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function() {
                assert.ok(true, 'The proxy must be initialized');
            });

        requestMock.api.on('request', function(url, params, method) {
            assert.equal(url, expectedUrl, 'The url is correct');
            assert.equal(method, expectedMethod, 'The HTTP method is correct');
            delete params._;
            assert.deepEqual(params, expectedParams, 'The expected parameters have been provided');
            requestMock.api.trigger('success', expectedResponse);
        });

        proxy.init(initConfig)
            .then(function() {
                return proxy.write(wrongParams)
                    .then(function() {
                        assert.ok(false, 'The promise should be rejected');
                    })
                    .catch(function(err) {
                        assert.deepEqual(err, expectedError, 'The expected error descriptor should be provided');

                        return proxy.write(expectedParams);
                    })
                    .then(function(response) {
                        assert.ok(true, 'The promise should be resolved');
                        assert.deepEqual(response, expectedResponse, 'The expected responses have been provided');
                        ready();
                    });
            })
            .catch(function(err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                ready();
            });

    });

    QUnit.test('ajax.remove()', function(assert) {
        var ready = assert.async();
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };
        var expectedUrl = 'http://foo.bar/remove';
        var expectedMethod = 'GET';
        var expectedResponse = {
            success: true
        };
        var initConfig = {
            actions: {
                remove: expectedUrl
            }
        };

        assert.expect(10);

        proxy = proxyFactory('ajax')
            .on('remove', function(promise, params) {
                assert.ok(true, 'The proxyFactory has fired the  "remove" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the  "remove" event');
                assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the  "remove" event');
            });

        proxy.remove()
            .then(function() {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function() {
                assert.ok(true, 'The proxy must be initialized');
            });

        requestMock.api.on('request', function(url, params, method) {
            assert.equal(url, expectedUrl, 'The url is correct');
            assert.equal(method, expectedMethod, 'The HTTP method is correct');
            delete params._;
            assert.deepEqual(params, expectedParams, 'The expected parameters have been provided');
            requestMock.api.trigger('success', expectedResponse);
        });

        proxy.init(initConfig)
            .then(function() {
                var result = proxy.remove(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.remove() method has returned a promise');

                return result;
            })
            .then(function(response) {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(response, expectedResponse, 'The expected responses have been provided');
                ready();
            })
            .catch(function(err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                ready();
            });
    });

    QUnit.test('ajax.action()', function(assert) {
        var ready = assert.async();
        var proxy;
        var expectedParams = {
            foo: 'bar'
        };
        var expectedUrl = 'http://foo.bar/foo';
        var expectedMethod = 'GET';
        var expectedAction = 'foo';
        var expectedResponse = {
            success: true
        };
        var expectedError = {
            success: false,
            type: 'notimplemented',
            action: 'unknown',
            params: {}
        };
        var initConfig = {
            actions: {
                foo: {
                    url: expectedUrl,
                    method: expectedMethod
                }
            }
        };

        assert.expect(14);

        proxy = proxyFactory('ajax')
            .on('action', function(promise, action, params) {
                assert.ok(true, 'The proxyFactory has fired the "action" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "action" event');
                promise.then(function() {
                    assert.equal(action, expectedAction, 'The proxyFactory has provided the action name through the "action" event');
                    assert.deepEqual(params, expectedParams, 'The proxyFactory has provided the params through the "action" event');
                })
                .catch(function() {
                    assert.ok(true, 'The promise should be rejected on wrong data');
                });
            });

        proxy.action()
            .then(function() {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function() {
                assert.ok(true, 'The proxy must be initialized');
            });

        requestMock.api.on('request', function(url, params, method) {
            assert.equal(url, expectedUrl, 'The url is correct');
            assert.equal(method, expectedMethod, 'The HTTP method is correct');
            delete params._;
            assert.deepEqual(params, expectedParams, 'The expected parameters have been provided');
            requestMock.api.trigger('success', expectedResponse);
        });

        proxy.init(initConfig)
            .then(function() {
                return proxy.action('unknown')
                    .then(function() {
                        assert.ok(false, 'The promise should be rejected');
                    })
                    .catch(function(err) {
                        assert.deepEqual(err, expectedError, 'The expected error descriptor should be provided');

                        return proxy.action(expectedAction, expectedParams);
                    })
                    .then(function(response) {
                        assert.ok(true, 'The promise should be resolved');
                        assert.deepEqual(response, expectedResponse, 'The expected response have been provided');
                        ready();
                    });
            })
            .catch(function(err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                ready();
            });
    });
});
