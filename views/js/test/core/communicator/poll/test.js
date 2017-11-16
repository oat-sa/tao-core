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
define(['jquery', 'lodash', 'core/communicator', 'core/communicator/poll'], function ($, _, communicator, poll) {
    'use strict';

    // backup/restore ajax method between each test
    var ajaxBackup;
    QUnit.testStart(function () {
        ajaxBackup = $.ajax;
    });
    QUnit.testDone(function () {
        $.ajax = ajaxBackup;
        communicator.clearProviders();
    });


    /**
     * A simple AJAX mock factory that fakes a successful ajax call.
     * To use it, just replace $.ajax with the returned value:
     * <pre>$.ajax = ajaxMock(function(promise) { promise.resolve(mockData); });</pre>
     * <pre>$.ajax = ajaxMock(function(promise) { promise.reject(mockError); });</pre>
     * @param {Function} resolver - A data resolver that will receive a jQuery promise as argument and must resolve it or reject it
     * @param {Function} [validator] - An optional function called instead of the ajax method
     * @returns {Function}
     */
    function ajaxMock(resolver, validator) {
        return function () {
            var deferred = $.Deferred();
            validator && validator.apply(this, arguments);
            _.defer(resolver, deferred);
            return deferred.promise();
        };
    }


    QUnit.module('communicator/poll factory');


    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.equal(typeof poll, 'object', "The communicator/poll module exposes an object");

    });


    var pollApi = [
        {name: 'init', title: 'init'},
        {name: 'destroy', title: 'destroy'},
        {name: 'open', title: 'open'},
        {name: 'close', title: 'close'},
        {name: 'send', title: 'send'}
    ];

    QUnit
        .cases(pollApi)
        .test('api', function (data, assert) {
            assert.equal(typeof poll[data.name], 'function', 'The communicator/poll api exposes a "' + data.name + '" function');
        });


    QUnit.module('provider');


    QUnit.asyncTest('create error', function (assert) {
        QUnit.expect(1);

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll');

        instance.init().catch(function () {
            assert.ok(true, 'The provider needs the address of the remote service');
            QUnit.start();
        });
    });


    QUnit.asyncTest('init and destroy', function (assert) {
        QUnit.expect(11);

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll', {service: 'service.url'})
            .on('init', function () {
                assert.ok(true, 'The communicator has fired the "init" event');
            })
            .on('ready', function () {
                assert.ok(true, 'The communicator has fired the "ready" event');
            })
            .on('destroy', function () {
                assert.ok(true, 'The communicator has fired the "destroy" event');
            })
            .on('destroyed', function () {
                assert.ok(true, 'The communicator has fired the "destroyed" event');
            });

        assert.ok(!!instance, 'The provider exists');

        instance.init().then(function () {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            // double check for init fallback when already ready
            // the init events must not be triggered as the communicator is already ready
            instance.init().then(function () {
                assert.equal(instance.getState('ready'), true, 'The provider is initialized');

                instance.destroy().then(function () {
                    assert.equal(instance.getState('ready'), false, 'The provider is destroyed');

                    // double check for destroy fallback when already destroyed
                    instance.destroy().then(function () {
                        assert.equal(instance.getState('ready'), false, 'The provider is already destroyed');

                        QUnit.start();
                    });
                });
            });
        });
    });


    QUnit.asyncTest('open and close', function (assert) {
        QUnit.expect(16);

        var config = {
            service: 'service.url',
            interval: 500
        };

        $.ajax = ajaxMock(function(promise) {
            promise.resolve({});
        }, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
        });

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll', config)
            .on('open', function () {
                assert.ok(true, 'The communicator has fired the "open" event');
            })
            .on('opened', function () {
                assert.ok(true, 'The communicator has fired the "opened" event');
            })
            .on('close', function () {
                assert.ok(true, 'The communicator has fired the "close" event');
            })
            .on('closed', function () {
                assert.ok(true, 'The communicator has fired the "closed" event');
            })
            .on('receive', function () {
                assert.ok(true, 'The communicator has fired the "receive" event');

                instance.close().then(function () {
                    assert.equal(instance.getState('open'), false, 'The connection is closed');

                    instance.destroy().then(function () {
                        assert.ok(true, 'The communicator is destroyed');
                        QUnit.start();
                    });
                });
            });

        assert.ok(!!instance, 'The provider exists');

        instance.open().catch(function () {
            assert.ok(true, 'The communicator cannot connect while the instance is not initialized');
        });

        instance.close().catch(function () {
            assert.ok(true, 'The communicator cannot disconnect while the instance is not initialized');
        });

        instance.init().then(function () {
            assert.equal(instance.getState('ready'), true, 'The communicator is initialized');

            instance.open().then(function () {
                assert.equal(instance.getState('open'), true, 'The connection is open');

                // double check for open fallback when already open
                // the init events must not be triggered as the communicator is already open
                instance.open().then(function () {
                    assert.equal(instance.getState('ready'), true, 'The provider is initialized');
                });
            });
        });
    });


    QUnit.asyncTest('send success', function (assert) {
        QUnit.expect(28);

        var config = {
            service: 'service.url',
            token: 'token1'
        };

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var testPath = [{
            token: 'token1',
            request: [],
            response: {
                token: 'token2',
                messages: [],
                responses: []
            }
        }, {
            token: 'token2',
            request: [{
                channel: requestChannel,
                message: requestMessage
            }],
            response: {
                token: 'token3',
                messages: [{
                    channel: requestChannel,
                    message: 'bar'
                }],
                responses: [
                    'ok'
                ]
            }
        }];

        var currentStep = 0;

        var expectedToken = testPath[currentStep].token;
        var expectedRequest = testPath[currentStep].request;
        var expectedResponse = testPath[currentStep].response;

        $.ajax = ajaxMock(function(promise) {
            promise.resolve(expectedResponse);

            currentStep = Math.min(currentStep + 1, testPath.length - 1);
            expectedToken = testPath[currentStep].token;
            expectedRequest = testPath[currentStep].request;
            expectedResponse = testPath[currentStep].response;
        }, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
            assert.equal(ajaxConfig.headers['X-Auth-Token'], expectedToken, 'The provider has set the right security token');
            assert.deepEqual(JSON.parse(ajaxConfig.data), expectedRequest, 'The provider has sent the request');
        });

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll', config)
            .on('send', function (promise, channel, message) {
                assert.ok(true, 'The communicator has fired the "send" event');
                assert.ok(promise instanceof Promise, 'The promise is provided');
                assert.equal(channel, requestChannel, 'The right channel is provided');
                assert.equal(message, requestMessage, 'The right message is provided');
            })
            .on('sent', function (channel, message) {
                assert.ok(true, 'The communicator has fired the "sent" event');
                assert.equal(channel, requestChannel, 'The right channel is provided');
                assert.equal(message, requestMessage, 'The right message is provided');
            })
            .on('close', function () {
                assert.ok(true, 'The communicator has fired the "close" event');
            })
            .on('closed', function () {
                assert.ok(true, 'The communicator has fired the "closed" event');
            })
            .on('receive', function(response) {
                assert.ok(true, 'A receive event is triggered');
                assert.equal(response, expectedResponse, 'A response is received');
            })
            .channel(requestChannel, function (message) {
                assert.equal(message, expectedResponse.messages[0].message, 'The provider has received the message');
                // QUnit.start();
            });

        assert.ok(!!instance, 'The provider exists');

        instance.send(requestChannel, requestMessage).catch(function () {
            assert.ok(true, 'The communicator cannot send a message while the instance is not initialized');
        });

        instance.init().then(function () {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            instance.send(requestChannel, requestMessage).catch(function () {
                assert.ok(true, 'The communicator cannot send a message while the connection is not open');
            });

            instance.open().then(function () {
                assert.equal(instance.getState('open'), true, 'The connection is open');

                instance.send(requestChannel, requestMessage).then(function (response) {
                    assert.ok(true, 'The message is sent');

                    assert.deepEqual(response, expectedResponse.responses[0], 'The message has received the expected response');

                    // do not explicitly call the close() method,
                    // it will be invoked by the destroy() method,
                    // thus "close" and "closed" events must be triggered
                    instance.destroy().then(function () {
                        assert.ok(true, 'The provider is destroyed');

                        QUnit.start();
                    });
                });
            });
        });
    });


    QUnit.asyncTest('send and stop', function (assert) {
        QUnit.expect(15);

        var config = {
            service: 'service.url',
            token: 'token1'
        };

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var testPath = [{
            token: 'token1',
            request: [],
            response: {
                token: 'token2',
                messages: [],
                responses: []
            }
        }, {
            token: 'token2',
            request: [{
                channel: requestChannel,
                message: requestMessage
            }],
            response: {
                token: 'token3',
                messages: [{
                    channel: requestChannel,
                    message: 'bar'
                }],
                responses: [
                    'ok'
                ]
            }
        }];

        var currentStep = 0;

        var expectedToken = testPath[currentStep].token;
        var expectedRequest = testPath[currentStep].request;
        var expectedResponse = testPath[currentStep].response;
        var instance;

        $.ajax = ajaxMock(function(promise) {
            instance.polling.stop();
            promise.resolve(expectedResponse);

            currentStep = Math.min(currentStep + 1, testPath.length - 1);
            expectedToken = testPath[currentStep].token;
            expectedRequest = testPath[currentStep].request;
            expectedResponse = testPath[currentStep].response;
        }, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
            assert.equal(ajaxConfig.headers['X-Auth-Token'], expectedToken, 'The provider has set the right security token');
            assert.deepEqual(JSON.parse(ajaxConfig.data), expectedRequest, 'The provider has sent the request');
        });

        communicator.registerProvider('poll', poll);

        instance = communicator('poll', config)
            .on('send', function (promise, channel, message) {
                assert.ok(true, 'The communicator has fired the "send" event');
                assert.ok(promise instanceof Promise, 'The promise is provided');
                assert.equal(channel, requestChannel, 'The right channel is provided');
                assert.equal(message, requestMessage, 'The right message is provided');
            })
            .on('message', function () {
                //should not be called
                assert.ok(false, 'Message triggered on stopped polling service.');
            });



        assert.ok(!!instance, 'The provider exists');

        instance.init().then(function () {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            instance.open().then(function () {
                assert.equal(instance.getState('open'), true, 'The connection is open');

                instance.send(requestChannel, requestMessage).then(function (response) {
                    assert.ok(true, 'The message is sent');

                    // do not explicitly call the close() method,
                    // it will be invoked by the destroy() method,
                    // thus "close" and "closed" events must be triggered
                    instance.destroy().then(function () {
                        assert.ok(true, 'The provider is destroyed');

                        QUnit.start();
                    });
                });

            });
        });
    });


    QUnit.asyncTest('send failed #network', function (assert) {
        QUnit.expect(25);

        var config = {
            service: 'service.url',
            token: 'token1'
        };

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var testPath = [{
            token: 'token1',
            request: [],
            response: {
                token: 'token2',
                messages: [],
                responses: []
            }
        }, {
            token: 'token2',
            request: [{
                channel: requestChannel,
                message: requestMessage
            }],
            response: 'error'
        }];

        var currentStep = 0;

        var expectedToken = testPath[currentStep].token;
        var expectedRequest = testPath[currentStep].request;
        var expectedResponse = testPath[currentStep].response;
        var mustFail = false;

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll', config)
            .on('error', function(error) {
                assert.ok(true, 'An error event is triggered');
                assert.equal(typeof error, 'object', 'An error object is provided');
                assert.equal(typeof error.sent, 'boolean', 'The error object contains the sent value');
                assert.equal(error.sent, false, 'The request was never sent');
                assert.equal(error.source, 'network', 'The error object contains the error source');
            });

        $.ajax = ajaxMock(function (promise) {
            if (mustFail) {
                promise.reject(expectedResponse);
            } else {
                promise.resolve(expectedResponse);
                mustFail = true;
            }

            currentStep = Math.min(currentStep + 1, testPath.length - 1);
            expectedToken = testPath[currentStep].token;
            expectedRequest = testPath[currentStep].request;
            expectedResponse = testPath[currentStep].response;
        }, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
            assert.equal(ajaxConfig.headers['X-Auth-Token'], expectedToken, 'The provider has set the right security token');
            assert.deepEqual(JSON.parse(ajaxConfig.data), expectedRequest, 'The provider has sent the request');
        });

        assert.ok(!!instance, 'The provider exists');

        instance.channel(requestChannel, function () {
            assert.ok(false, 'The provider must not receive any message');
        });

        instance.init().then(function () {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            instance.open().then(function () {
                assert.equal(instance.getState('open'), true, 'The connection is open');

                instance.send(requestChannel, requestMessage).catch(function () {
                    assert.ok(true, 'The message has not been received');

                    // double send error to check the token reset
                    instance.send(requestChannel, requestMessage).catch(function () {
                        assert.ok(true, 'The message has not been received');

                        instance.destroy().then(function () {
                            assert.ok(true, 'The provider is destroyed');

                            QUnit.start();
                        });
                    });
                });
            });
        });
    });


    QUnit.asyncTest('receive', function (assert) {
        QUnit.expect(9);

        var config = {
            service: 'service.url',
            interval: 500
        };

        var expectedChannel = 'foo';

        var expectedResponse = {
            messages: [{
                channel: expectedChannel,
                message: 'bar'
            }, {
                message: 'malformed'
            }]
        };

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll', config);

        $.ajax = ajaxMock(function(promise) {
            promise.resolve(expectedResponse);
        }, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
            assert.equal(typeof ajaxConfig.headers['X-Auth-Token'], 'undefined', 'The provider has not set any security token');
            assert.deepEqual(JSON.parse(ajaxConfig.data), [], 'The provider has sent the request with no data');
        });

        assert.ok(!!instance, 'The provider exists');

        var received = [new Promise(function(resolve) {
            instance.channel(expectedChannel, function (message) {
                assert.equal(message, expectedResponse.messages[0].message, 'The provider has received the message');
                resolve();
            });
        }), new Promise(function(resolve) {
            instance.channel('malformed', function(message) {
                assert.equal(message, expectedResponse.messages[1], 'The provider has received the malformed message');
                resolve();
            });
        })];

        Promise.all(received).then(function() {
            instance.destroy().then(function () {
                assert.ok(true, 'The provider is destroyed');

                QUnit.start();
            });
        });

        instance.init().then(function () {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            instance.open().then(function () {
                assert.equal(instance.getState('open'), true, 'The connection is open');
            });
        });
    });
});
