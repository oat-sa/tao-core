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
 * Copyright (c) 2016-2019 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'core/communicator',
    'core/communicator/poll',
    'lib/jquery.mockjax/jquery.mockjax'
], function($, _, communicator, poll) {
    'use strict';

    var pollApi;

    // prevent the AJAX mocks to pollute the logs
    $.mockjaxSettings.logger = null;
    $.mockjaxSettings.responseTime = 1;

    // restore AJAX method after each test
    QUnit.testDone(function () {
        $.mockjax.clear();
        communicator.clearProviders();
    });

    QUnit.module('communicator/poll factory');

    QUnit.test('module', function(assert) {
        assert.expect(1);

        assert.equal(typeof poll, 'object', 'The communicator/poll module exposes an object');
    });


    pollApi = [
        {name: 'init', title: 'init'},
        {name: 'destroy', title: 'destroy'},
        {name: 'open', title: 'open'},
        {name: 'close', title: 'close'},
        {name: 'send', title: 'send'}
    ];

    QUnit
        .cases.init(pollApi)
        .test('api', function(data, assert) {
            assert.equal(typeof poll[data.name], 'function', 'The communicator/poll api exposes a "' + data.name + '" function');
        });

    QUnit.module('provider');

    QUnit.test('create error', function(assert) {
        var ready = assert.async();
        var instance;

        assert.expect(1);

        communicator.registerProvider('poll', poll);

        instance = communicator('poll');

        instance.init().catch(function() {
            assert.ok(true, 'The provider needs the address of the remote service');
            ready();
        });
    });

    QUnit.test('init and destroy', function(assert) {
        var ready = assert.async();
        var instance;

        assert.expect(11);

        communicator.registerProvider('poll', poll);

        instance = communicator('poll', {service: 'service.url'})
            .on('init', function() {
                assert.ok(true, 'The communicator has fired the "init" event');
            })
            .on('ready', function() {
                assert.ok(true, 'The communicator has fired the "ready" event');
            })
            .on('destroy', function() {
                assert.ok(true, 'The communicator has fired the "destroy" event');
            })
            .on('destroyed', function() {
                assert.ok(true, 'The communicator has fired the "destroyed" event');
            });

        assert.ok(!!instance, 'The provider exists');

        instance.init().then(function() {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            // Double check for init fallback when already ready
            // the init events must not be triggered as the communicator is already ready
            instance.init().then(function() {
                assert.equal(instance.getState('ready'), true, 'The provider is initialized');

                instance.destroy().then(function() {
                    assert.equal(instance.getState('ready'), false, 'The provider is destroyed');

                    // Double check for destroy fallback when already destroyed
                    instance.destroy().then(function() {
                        assert.equal(instance.getState('ready'), false, 'The provider is already destroyed');

                        ready();
                    });
                });
            });
        });
    });

    QUnit.test('open and close', function(assert) {
        var ready = assert.async();

        var config = {
            service: 'service.url',
            interval: 500
        };
        var instance;

        // mock the AJAX request handling + response:
        $.mockjax({
            url: 'service.url',
            response: function(settings) {
                assert.equal(settings.url, config.service, 'The provider has called the right service');
                this.responseText = JSON.stringify({
                    success: true
                });
            }
        });

        communicator.registerProvider('poll', poll);

        assert.expect(16);

        instance = communicator('poll', config)
            .on('open', function () {
                assert.ok(true, 'The communicator has fired the "open" event');
            })
            .on('opened', function() {
                assert.ok(true, 'The communicator has fired the "opened" event');
            })
            .on('close', function() {
                assert.ok(true, 'The communicator has fired the "close" event');
            })
            .on('closed', function() {
                assert.ok(true, 'The communicator has fired the "closed" event');
            })
            .on('receive', function() {
                assert.ok(true, 'The communicator has fired the "receive" event');

                instance.close().then(function() {
                    assert.equal(instance.getState('open'), false, 'The connection is closed');

                    instance.destroy().then(function() {
                        assert.ok(true, 'The communicator is destroyed');
                        ready();
                    });
                });
            });

        assert.ok(!!instance, 'The provider exists');

        instance.open().catch(function() {
            assert.ok(true, 'The communicator cannot connect while the instance is not initialized');
        });

        instance.close().catch(function() {
            assert.ok(true, 'The communicator cannot disconnect while the instance is not initialized');
        });

        instance.init().then(function() {
            assert.equal(instance.getState('ready'), true, 'The communicator is initialized');

            instance.open().then(function() {
                assert.equal(instance.getState('open'), true, 'The connection is open');

                // Double check for open fallback when already open
                // the init events must not be triggered as the communicator is already open
                instance.open().then(function() {
                    assert.equal(instance.getState('ready'), true, 'The provider is initialized');
                });
            });
        });
    });

    QUnit.test('send success', function(assert) {
        var ready = assert.async();

        var config = {
            service: 'service.url',
            token: 'token1'
        };
        var instance;

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var testPath = [{
            request: [],
            response: {
                success: true,
                messages: [],
                responses: []
            }
        }, {
            request: [{
                channel: requestChannel,
                message: requestMessage
            }],
            response: {
                success: true,
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

        var expectedRequest = testPath[currentStep].request;
        var expectedResponse = testPath[currentStep].response;

        // mock the AJAX request handling + response:
        $.mockjax({
            url: 'service.url',
            response: function(request) {
                assert.equal(request.url, config.service, 'The provider has called the right service');
                assert.deepEqual(JSON.parse(request.data), expectedRequest, 'The provider has sent the request');
                this.responseText = JSON.stringify(expectedResponse);
            },
            onAfterComplete: function() {
                // advance to next test step:
                _.defer(function() {
                    currentStep = Math.min(currentStep + 1, testPath.length - 1);
                    expectedRequest = testPath[currentStep].request;
                    expectedResponse = testPath[currentStep].response;
                });
            }
        });

        communicator.registerProvider('poll', poll);

        assert.expect(26);

        instance = communicator('poll', config)
            .on('send', function (promise, channel, message) {
                assert.ok(true, 'The communicator has fired the "send" event');
                assert.ok(promise instanceof Promise, 'The promise is provided');
                assert.equal(channel, requestChannel, 'The right channel is provided');
                assert.equal(message, requestMessage, 'The right message is provided');
            })
            .on('sent', function(channel, message) {
                assert.ok(true, 'The communicator has fired the "sent" event');
                assert.equal(channel, requestChannel, 'The right channel is provided');
                assert.equal(message, requestMessage, 'The right message is provided');
            })
            .on('close', function() {
                assert.ok(true, 'The communicator has fired the "close" event');
            })
            .on('closed', function() {
                assert.ok(true, 'The communicator has fired the "closed" event');
            })
            .on('receive', function(response) {
                assert.ok(true, 'A receive event is triggered');
                assert.deepEqual(response, expectedResponse, 'A response is received');
            })
            .channel(requestChannel, function(message) {
                assert.equal(message, expectedResponse.messages[0].message, 'The provider has received the message');

            });

        assert.ok(!!instance, 'The provider exists');

        instance.send(requestChannel, requestMessage).catch(function() {
            assert.ok(true, 'The communicator cannot send a message while the instance is not initialized');
        });

        instance.init().then(function() {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            instance.send(requestChannel, requestMessage).catch(function() {
                assert.ok(true, 'The communicator cannot send a message while the connection is not open');
            });

            instance.open().then(function() {
                assert.equal(instance.getState('open'), true, 'The connection is open');

                instance.send(requestChannel, requestMessage).then(function(response) {
                    assert.ok(true, 'The message is sent');

                    assert.deepEqual(response, expectedResponse.responses[0], 'The message has received the expected response');

                    // Do not explicitly call the close() method,
                    // it will be invoked by the destroy() method,
                    // thus 'close' and "closed" events must be triggered
                    instance.destroy().then(function() {
                        assert.ok(true, 'The provider is destroyed');

                        ready();
                    });
                });
            });
        });
    });

    QUnit.test('send and stop', function(assert) {
        var ready = assert.async();

        var config = {
            service: 'service.url',
            token: 'token1'
        };

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var testPath = [{
            request: [],
            response: {
                success: true,
                messages: [],
                responses: []
            }
        }, {
            request: [{
                channel: requestChannel,
                message: requestMessage
            }],
            response: {
                success: true,
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

        var expectedRequest = testPath[currentStep].request;
        var expectedResponse = testPath[currentStep].response;
        var instance;

        // mock the AJAX request handling + response:
        $.mockjax({
            url: 'service.url',
            response: function(request) {
                assert.equal(request.url, config.service, 'The provider has called the right service');
                assert.deepEqual(JSON.parse(request.data), expectedRequest, 'The provider has sent the request');

                instance.polling.stop();

                this.responseText = JSON.stringify(expectedResponse);
            },
            onAfterComplete: function() {
                // advance to next test step:
                _.defer(function() {
                    currentStep = Math.min(currentStep + 1, testPath.length - 1);
                    expectedRequest = testPath[currentStep].request;
                    expectedResponse = testPath[currentStep].response;
                });
            }
        });

        communicator.registerProvider('poll', poll);

        assert.expect(13);

        instance = communicator('poll', config)
            .on('send', function(promise, channel, message) {
                assert.ok(true, 'The communicator has fired the "send" event');
                assert.ok(promise instanceof Promise, 'The promise is provided');
                assert.equal(channel, requestChannel, 'The right channel is provided');
                assert.equal(message, requestMessage, 'The right message is provided');
            })
            .on('message', function() {

                //Should not be called
                assert.ok(false, 'Message triggered on stopped polling service.');
            });

        assert.ok(!!instance, 'The provider exists');

        instance.init().then(function() {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            instance.open().then(function() {
                assert.equal(instance.getState('open'), true, 'The connection is open');

                instance.send(requestChannel, requestMessage).then(function() {
                    assert.ok(true, 'The message is sent');

                    // Do not explicitly call the close() method,
                    // it will be invoked by the destroy() method,
                    // thus 'close' and "closed" events must be triggered
                    instance.destroy().then(function() {
                        assert.ok(true, 'The provider is destroyed');

                        ready();
                    });
                });

            });
        });
    });

    QUnit.test('send failed #network', function(assert) {
        var ready = assert.async();

        var config = {
            service: 'service.url',
            token: 'token1'
        };
        var instance;

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var testPath = [{
            request: [],
            response: {
                success: true,
                messages: [],
                responses: []
            }
        }, {
            request: [{
                channel: requestChannel,
                message: requestMessage
            }],
            response: {
                success: false,
                type: 'error'
            }
        }];

        var currentStep = 0;

        var expectedRequest = testPath[currentStep].request;
        var expectedResponse = testPath[currentStep].response;
        var mustFail = false;

        communicator.registerProvider('poll', poll);

        assert.expect(22);

        instance = communicator('poll', config)
            .on('error', function(error) {
                assert.ok(true, 'An error event is triggered');
                assert.equal(typeof error, 'object', 'An error object is provided');
                assert.equal(typeof error.sent, 'boolean', 'The error object contains the sent value');
                assert.equal(error.sent, false, 'The request was never sent');
                assert.equal(error.source, 'network', 'The error object contains the error source');
            });

        // mock the AJAX request handling + response:
        $.mockjax({
            url: 'service.url',
            response: function(request) {
                assert.equal(request.url, config.service, 'The provider has called the right service');
                assert.deepEqual(JSON.parse(request.data), expectedRequest, 'The provider has sent the request');

                if (mustFail) {
                    this.isTimeout = true;
                } else {
                    this.responseText = JSON.stringify(expectedResponse);
                    mustFail = true;
                }
            },
            onAfterComplete: function() {
                // advance to next test step:
                _.defer(function() {
                    currentStep = Math.min(currentStep + 1, testPath.length - 1);
                    expectedRequest = testPath[currentStep].request;
                    expectedResponse = testPath[currentStep].response;
                });
            }
        });

        assert.ok(!!instance, 'The provider exists');

        instance.channel(requestChannel, function() {
            assert.ok(false, 'The provider must not receive any message');
        });

        instance.init()
            .then(function() {
                assert.equal(instance.getState('ready'), true, 'The provider is initialized');
                return instance.open();
            })
            .then(function() {
                assert.equal(instance.getState('open'), true, 'The connection is open');
                return instance.send(requestChannel, requestMessage);
            })
            .then(function() {
                assert.ok(false, 'The message should not be received');
                ready();
            })
            .catch(function() {
                assert.ok(true, 'The message has not been received');

                // Double send error to check the token reset
                return instance.send(requestChannel, requestMessage);
            })
            .then(function() {
                assert.ok(false, 'The message should not be received');
                ready();
            })
            .catch(function() {
                assert.ok(true, 'The message has not been received');
                return instance.destroy();
            })
            .then(function() {
                assert.ok(true, 'The provider is destroyed');
                ready();
            })
            .catch(function (err) {
                assert.ok(false, 'The operation should not fail!');
                assert.pushResult({
                    result: false,
                    message: err
                });
                ready();
            });
    });

    QUnit.test('receive', function(assert) {
        var ready = assert.async();

        var config = {
            service: 'service.url',
            interval: 500
        };
        var instance;

        var expectedChannel = 'foo';

        var expectedResponse = {
            success: true,
            messages: [{
                channel: expectedChannel,
                message: 'bar'
            }, {
                message: 'malformed'
            }]
        };
        var received;

        communicator.registerProvider('poll', poll);

        assert.expect(8);

        instance = communicator('poll', config);

        // mock the AJAX request handling + response:
        $.mockjax({
            url: 'service.url',
            response: function(request) {
                assert.equal(request.url, config.service, 'The provider has called the right service');
                assert.deepEqual(JSON.parse(request.data), [], 'The provider has sent the request with no data');

                this.responseText = JSON.stringify(expectedResponse);
            }
        });

        assert.ok(!!instance, 'The provider exists');

        received = [new Promise(function(resolve) {
            instance.channel(expectedChannel, function (message) {
                assert.equal(message, expectedResponse.messages[0].message, 'The provider has received the message');
                resolve();
            });
        }), new Promise(function(resolve) {
            instance.channel('malformed', function(message) {
                assert.deepEqual(message, expectedResponse.messages[1], 'The provider has received the malformed message');
                resolve();
            });
        })];

        Promise.all(received).then(function() {
            instance.destroy().then(function() {
                assert.ok(true, 'The provider is destroyed');

                ready();
            });
        });

        instance.init().then(function() {
            assert.equal(instance.getState('ready'), true, 'The provider is initialized');

            instance.open().then(function() {
                assert.equal(instance.getState('open'), true, 'The connection is open');
            });
        });
    });
});
