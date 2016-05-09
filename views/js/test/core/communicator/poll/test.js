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
     * <pre>$.ajax = ajaxMockSuccess(mockData);</pre>
     * @param {*} response - The mock data used as response
     * @param {Function} [validator] - An optional function called instead of the ajax method
     * @returns {Function}
     */
    function ajaxMockSuccess(response, validator) {
        var deferred = $.Deferred().resolve(response);
        return function () {
            validator && validator.apply(this, arguments);
            return deferred.promise();
        };
    }


    /**
     * A simple AJAX mock factory that fakes a failing ajax call.
     * To use it, just replace $.ajax with the returned value:
     * <pre>$.ajax = ajaxMockError(mockData);</pre>
     * @param {*} response - The mock data used as response
     * @param {Function} [validator] - An optional function called instead of the ajax method
     * @returns {Function}
     */
    function ajaxMockError(response, validator) {
        var deferred = $.Deferred().reject(response);
        return function () {
            validator && validator.apply(this, arguments);
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

        instance.init().catch(function() {
            assert.ok(true,'The provider needs the address of the remote service');
            QUnit.start();
        });
    });


    QUnit.asyncTest('init and destroy', function (assert) {
        QUnit.expect(7);

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
            assert.ok(true, 'The provider is initialized');

            instance.destroy().then(function () {
                assert.ok(true, 'The provider is destroyed');

                QUnit.start();
            })
        });
    });


    QUnit.asyncTest('open and close', function (assert) {
        QUnit.expect(15);

        var config = {
            service: 'service.url',
            interval: '500'
        };

        $.ajax = ajaxMockSuccess({}, function (ajaxConfig) {
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
            .on('receive', function() {
                assert.ok(true, 'The communicator has fired the "receive" event');

                instance.close().then(function() {
                    assert.ok(true, 'The connection is closed');

                    instance.destroy().then(function() {
                        assert.ok(true, 'The communictor is destroyed');
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

        instance.init().then(function() {
            assert.ok(true, 'The communicator is initialized');

            instance.open().then(function() {
                assert.ok(true, 'The connection is open');
            });
        });
    });


    QUnit.asyncTest('send success', function (assert) {
        QUnit.expect(19);
        QUnit.stop(1);

        var config = {
            service: 'service.url',
            token: 'token1'
        };

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var expectedResponse = {
            token: 'token2',
            messages: [{
                channel: requestChannel,
                message: 'bar'
            }],
            responses: [
                'ok'
            ]
        };

        var expectedRequest = [{
            channel: requestChannel,
            message: requestMessage
        }];

        $.ajax = ajaxMockSuccess(expectedResponse, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
            assert.equal(ajaxConfig.headers['X-Auth-Token'], config.token, 'The provider has set the right security token');
            assert.deepEqual(ajaxConfig.data, expectedRequest, 'The provider has sent the request');
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
            .channel(requestChannel, function(message) {
                assert.equal(message, expectedResponse.messages[0].message, 'The provider has received the message');
                QUnit.start();
            });

        assert.ok(!!instance, 'The provider exists');

        instance.send(requestChannel, requestMessage).catch(function () {
            assert.ok(true, 'The communicator cannot send a message while the instance is not initialized');
        });

        instance.init().then(function () {
            assert.ok(true, 'The provider is initialized');

            instance.send(requestChannel, requestMessage).catch(function () {
                assert.ok(true, 'The communicator cannot send a message while the connection is not open');
            });

            instance.open().then(function() {
                assert.ok(true, 'The connection is open');

                instance.send(requestChannel, requestMessage).then(function (response) {
                    assert.ok(true, 'The message is sent');

                    assert.deepEqual(response, expectedResponse.responses[0], 'The message has received the expected response');

                    instance.destroy().then(function () {
                        assert.ok(true, 'The provider is destroyed');

                        QUnit.start();
                    });
                })
            });
        });
    });


    QUnit.asyncTest('send failed #network', function (assert) {
        QUnit.expect(8);

        var config = {
            service: 'service.url',
            token: 'token1'
        };

        var requestChannel = 'foo';
        var requestMessage = 'hello';

        var expectedResponse = 'error';

        var expectedRequest = [{
            channel: requestChannel,
            message: requestMessage
        }];

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll', config);

        $.ajax = ajaxMockError(expectedResponse, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
            assert.equal(ajaxConfig.headers['X-Auth-Token'], config.token, 'The provider has set the right security token');
            assert.deepEqual(ajaxConfig.data, expectedRequest, 'The provider has sent the request');
        });

        assert.ok(!!instance, 'The provider exists');

        instance.channel(requestChannel, function() {
            assert.ok(false, 'The provider must not receive any message');
        });

        instance.init().then(function () {
            assert.ok(true, 'The provider is initialized');

            instance.open().then(function() {
                assert.ok(true, 'The connection is open');

                instance.send(requestChannel, requestMessage).catch(function () {
                    assert.ok(true, 'The message has not been received');

                    instance.destroy().then(function () {
                        assert.ok(true, 'The provider is destroyed');

                        QUnit.start();
                    });
                });
            })
        });
    });


    QUnit.asyncTest('receive', function (assert) {
        QUnit.expect(8);

        var config = {
            service: 'service.url',
            interval: '500'
        };

        var expectedChannel = 'foo';

        var expectedResponse = {
            messages: [{
                channel: expectedChannel,
                message: 'bar'
            }]
        };

        communicator.registerProvider('poll', poll);

        var instance = communicator('poll', config);

        $.ajax = ajaxMockSuccess(expectedResponse, function (ajaxConfig) {
            assert.equal(ajaxConfig.url, config.service, 'The provider has called the right service');
            assert.equal(typeof ajaxConfig.headers['X-Auth-Token'], 'undefined', 'The provider has not set any security token');
            assert.deepEqual(ajaxConfig.data, [], 'The provider has sent the request with no data');
        });

        assert.ok(!!instance, 'The provider exists');

        instance.channel(expectedChannel, function(message) {
            assert.equal(message, expectedResponse.messages[0].message, 'The provider has received the message');

            instance.destroy().then(function () {
                assert.ok(true, 'The provider is destroyed');

                QUnit.start();
            });
        });

        instance.init().then(function () {
            assert.ok(true, 'The provider is initialized');

            instance.open().then(function() {
                assert.ok(true, 'The connection is open');
            });
        });
    });
});
