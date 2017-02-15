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
    'jquery',
    'lodash',
    'core/promise',
    'core/dataProvider/proxy',
    'core/dataProvider/proxy/htmlData'
], function ($, _, Promise, proxyFactory, htmlDataProvider) {
    'use strict';

    var readCases;
    var removeCases;
    var htmlDataProviderApi = [
        {title: 'init'},
        {title: 'destroy'},
        {title: 'create'},
        {title: 'read'},
        {title: 'write'}
    ];


    QUnit.module('htmlDataProvider', {
        setup: function () {
            proxyFactory.clearProviders();
            proxyFactory.registerProvider('htmlData', htmlDataProvider);
        }
    });


    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.equal(typeof htmlDataProvider, 'object', "The proxy/htmlData module exposes an object");
    });


    QUnit
        .cases(htmlDataProviderApi)
        .test('instance API ', function (data, assert) {
            QUnit.expect(1);
            assert.equal(typeof htmlDataProvider[data.title], 'function', 'The proxy/htmlData provider exposes a "' + data.title + '" function');
        });


    QUnit.asyncTest('htmlData.init()', function (assert) {
        var initConfig = {
            container: '#fixture-1'
        };
        var expectedConfig = {
            container: null,
            eraseOnRead: false,
            keys: []
        };
        var result, proxy;

        QUnit.expect(11);

        proxy = proxyFactory('htmlData');
        result = proxy
            .on('init', function (promise, config) {
                assert.ok(true, 'The proxyFactory has fired the "init" event');
                assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "init" event');
                assert.deepEqual(config, expectedConfig, 'The proxyFactory has provided the config object through the "init" event');
            })
            .init(initConfig);

        assert.ok(result instanceof Promise, 'The proxyFactory.init() method has returned a promise');

        result
            .then(function () {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(proxy.getConfig(), expectedConfig, 'The proxyFactory has provided the config object through the "init" event');

                assert.equal(typeof proxy.get, 'function', 'Internal method should exist');
                assert.equal(typeof proxy.set, 'function', 'Internal method should exist');

                return proxy.destroy();
            })
            .then(function() {
                assert.ok(true, 'The promise should be resolved');
                assert.equal(proxy.get, null, 'Internal method should be destroyed');
                assert.equal(proxy.set, null, 'Internal method should be destroyed');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    QUnit.asyncTest('htmlData.create()', function (assert) {
        var proxy;
        var initConfig = {
            container: '#fixture-1'
        };
        var expectedParams = {
            list: [1, 2, 3]
        };
        var existingData = {
            foo: 'bar'
        };
        var expectedResponse = {
            success: true,
            data: expectedParams
        };

        QUnit.expect(9);

        assert.deepEqual($(initConfig.container).data(), existingData, 'The DOM element have some data');

        proxy = proxyFactory('htmlData')
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

        proxy.init(initConfig)
            .then(function () {
                var result = proxy.create(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.create() method has returned a promise');

                return result;
            })
            .then(function (response) {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(response, expectedResponse, 'The expected responses have been provided');
                assert.deepEqual($(initConfig.container).data(), expectedParams, 'The DOM element has been hydrated');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });

    readCases = [{
        title: 'not erase',
        params: {},
        config: {
            container: '#fixture-2'
        },
        before: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        after: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        response: {
            success: true,
            data: {
                list: [1, 2, 3],
                record: {
                    foo: 'bar'
                }
            }
        }
    }, {
        title: 'erase',
        params: {},
        config: {
            container: '#fixture-3',
            eraseOnRead: true
        },
        before: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        after: {},
        response: {
            success: true,
            data: {
                list: [1, 2, 3],
                record: {
                    foo: 'bar'
                }
            }
        }
    }, {
        title: 'filter by params',
        params: {
            keys: ['list']
        },
        config: {
            container: '#fixture-4',
            eraseOnRead: true
        },
        before: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        after: {
            record: {
                foo: 'bar'
            }
        },
        response: {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        }
    }, {
        title: 'filter by config',
        params: {},
        config: {
            container: '#fixture-5',
            eraseOnRead: true,
            keys: ['list']
        },
        before: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        after: {
            record: {
                foo: 'bar'
            }
        },
        response: {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        }
    }];

    QUnit
        .cases(readCases)
        .asyncTest('htmlData.read() ', function (data, assert) {
            var proxy;


            QUnit.expect(9);

            assert.deepEqual($(data.config.container).data(), data.before, 'The DOM element have data');

            proxy = proxyFactory('htmlData')
                .on('read', function (promise, params) {
                    assert.ok(true, 'The proxyFactory has fired the "read" event');
                    assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "read" event');
                    assert.deepEqual(params, data.params, 'The proxyFactory has provided the params through the "read" event');
                });

            proxy.read()
                .then(function () {
                    assert.ok(false, 'The proxy must be initialized');
                })
                .catch(function () {
                    assert.ok(true, 'The proxy must be initialized');
                });

            proxy.init(data.config)
                .then(function () {
                    var result = proxy.read(data.params);

                    assert.ok(result instanceof Promise, 'The proxyFactory.read() method has returned a promise');

                    return result;
                })
                .then(function (response) {
                    assert.ok(true, 'The promise should be resolved');
                    assert.deepEqual(response, data.response, 'The expected responses have been provided');
                    assert.deepEqual($(data.config.container).data(), data.after, 'The DOM element has the expected remaining data');
                    QUnit.start();
                })
                .catch(function (err) {
                    assert.ok(false, 'The promise should not be rejected');
                    console.error(err);
                    QUnit.start();
                });
        });


    QUnit.asyncTest('htmlData.write()', function (assert) {
        var proxy;
        var initConfig = {
            container: '#fixture-6'
        };
        var expectedParams = {
            list: [1, 2, 3]
        };
        var existingData = {
            foo: 'bar'
        };
        var expectedWriteResponse = {
            success: true,
            data: expectedParams
        };
        var expectedReadResponse = {
            success: true,
            data: {
                foo: 'bar',
                list: [1, 2, 3]
            }
        };

        QUnit.expect(10);

        assert.deepEqual($(initConfig.container).data(), existingData, 'The DOM element have some data');

        proxy = proxyFactory('htmlData')
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

        proxy.init(initConfig)
            .then(function () {
                var result = proxy.write(expectedParams);

                assert.ok(result instanceof Promise, 'The proxyFactory.write() method has returned a promise');

                return result;
            })
            .then(function (response) {
                assert.ok(true, 'The promise should be resolved');
                assert.deepEqual(response, expectedWriteResponse, 'The expected responses have been provided');
                assert.deepEqual($(initConfig.container).data(), expectedReadResponse.data, 'The DOM element has been hydrated');
                return proxy.read();
            })
            .then(function(response) {
                assert.deepEqual(response, expectedReadResponse, 'The data should be read');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });


    removeCases = [{
        title: 'all',
        params: {},
        config: {
            container: '#fixture-7'
        },
        before: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        after: {},
        response: {
            success: true
        },
        read: {
            success: true,
            data: {
                list: [1, 2, 3],
                record: {
                    foo: 'bar'
                }
            }
        }
    }, {
        title: 'filter by params',
        params: {
            keys: ['list']
        },
        config: {
            container: '#fixture-8'
        },
        before: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        after: {
            record: {
                foo: 'bar'
            }
        },
        response: {
            success: true
        },
        read: {
            success: true,
            data: {
                list: [1, 2, 3],
                record: {
                    foo: 'bar'
                }
            }
        }
    }, {
        title: 'filter by config',
        params: {},
        config: {
            container: '#fixture-9',
            keys: ['list']
        },
        before: {
            list: [1, 2, 3],
            record: {
                foo: 'bar'
            }
        },
        after: {
            record: {
                foo: 'bar'
            }
        },
        response: {
            success: true
        },
        read: {
            success: true,
            data: {
                list: [1, 2, 3]
            }
        }
    }];

    QUnit
        .cases(removeCases)
        .asyncTest('htmlData.remove() ', function (data, assert) {
            var proxy;

            QUnit.expect(9);

            assert.deepEqual($(data.config.container).data(), data.before, 'The DOM element have data');

            proxy = proxyFactory('htmlData')
                .on('remove', function (promise, params) {
                    assert.ok(true, 'The proxyFactory has fired the "remove" event');
                    assert.ok(promise instanceof Promise, 'The proxyFactory has provided the promise through the "remove" event');
                    assert.deepEqual(params, data.params, 'The proxyFactory has provided the params through the "remove" event');
                });

            proxy.remove()
                .then(function () {
                    assert.ok(false, 'The proxy must be initialized');
                })
                .catch(function () {
                    assert.ok(true, 'The proxy must be initialized');
                });

            proxy.init(data.config)
                .then(function () {
                    return proxy.read();
                })
                .then(function(response) {
                    assert.deepEqual(response, data.read, 'The expected data have been found');
                    assert.deepEqual($(data.config.container).data(), data.before, 'The DOM element still have data');

                    return proxy.remove(data.params);
                }).then(function(response) {
                    assert.deepEqual(response, data.response, 'The expected response has been provided');
                    assert.deepEqual($(data.config.container).data(), data.after, 'The DOM element has the expected remaining data');
                    QUnit.start();
                })
                .catch(function (err) {
                    assert.ok(false, 'The promise should not be rejected');
                    console.error(err);
                    QUnit.start();
                });
    });


    QUnit.asyncTest('htmlData.action()', function (assert) {
        var proxy;

        QUnit.expect(3);

        proxy = proxyFactory('htmlData')
            .on('action', function () {
                assert.ok(true, 'Should be triggered');
            });

        proxy.action('foo')
            .then(function () {
                assert.ok(false, 'The proxy must be initialized');
            })
            .catch(function () {
                assert.ok(true, 'The proxy must be initialized');
            });

        proxy.init()
            .then(function () {
                return proxy.action('foo');
            })
            .then(function() {
                assert.ok(true, 'The promise should be resolved');
                QUnit.start();
            })
            .catch(function (err) {
                assert.ok(false, 'The promise should not be rejected');
                console.error(err);
                QUnit.start();
            });
    });
});
