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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test the module core/request
 *
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/request',
    'core/promise',
    'core/tokenHandler',
    'lib/jquery.mockjax/jquery.mockjax'
], function ($, _, request, Promise, tokenHandlerFactory) {
    'use strict';

    var requestCases;
    var responses = {
        '//200': [{
            success: true,
            data: {'foo': 'bar'}
        }, 'OK', {
            status: 200
        }],

        '//200/error/1': [{
            success: false,
            errorCode: 1,
            errorMessage: 'oops'
        }, 'Error', {
            status: 200
        }],

        '//200/error/2': [{
            success: false,
            errorCode: 2,
            errorMsg: 'woops'
        }, 'Error', {
            status: 200
        }],

        '//200/error/fallback': [{
            success: false
        }, 'Error', {
            status: 200
        }],

        '//204': [null, 'No Content', {status: 204}]
    };

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.equal(typeof request, 'function', "The module exposes a function");
    });

    // prevent the AJAX mocks to pollute the logs
    $.mockjaxSettings.logger = null;
    $.mockjaxSettings.responseTime = 1;

    // restore AJAX method after each test
    QUnit.testDone(function () {
        $.mockjax.clear();
    });


    QUnit.module('Missed params');

    requestCases = [{
        title: 'no url',
        err : new TypeError('At least give a URL...')
    }];

    QUnit
        .cases(requestCases)
        .asyncTest('bad request call with ', function(caseData, assert){
            QUnit.expect(1);

            assert.throws(
                function() {
                    request(caseData.url, caseData.data, caseData.method, caseData.headers, caseData.background, caseData.noToken);
                },
                "throws an error"
            );
            QUnit.start();
        });


    QUnit.module('request');

    requestCases = [
        {
            title : '200 got content',
            url : '//200',
            content: { foo : 'bar' }
        },
        {
            title : '200 no token required',
            url : '//200',
            noToken: true,
            content: { foo : 'bar' }
        },
        {
            title : '200 with custom header',
            url : '//200',
            headers: { 'x-foo': 'bar' },
            noToken: true,
            content: { foo : 'bar', requestHeaders: { 'x-foo': 'bar' } }
        },
        {
            title : '204 no content',
            url : '//204'
        },
        {
            title : '500 error',
            url : '//500',
            reject : true,
            err : new Error('500 : Server Error')
        },
        {
            title : '200 error 1',
            url : '//200/error/1',
            reject : true,
            err : new Error('1 : oops')
        },
        {
            title : '200 error 2',
            url : '//200/error/2',
            reject : true,
            err : new Error('2 : woops')
        },
        {
            title : '200 error fallback',
            url : '//200/error/fallback',
            reject : true,
            err : new Error('The server has sent an empty response')
        }
    ];

    QUnit
        .cases(requestCases)
        .asyncTest('request with ', function(caseData, assert) {
            var tokenHandler = tokenHandlerFactory();

            // mock the endpoints:
            $.mockjax([
                {
                    url: /^\/\/200.*$/,
                    status: 200,
                    headers: {
                        // respond with:
                        'X-CSRF-Token': 'token2'
                    },
                    response: function(settings) {
                        var response = responses[settings.url][0];
                        var content;
                        if (response) {
                            content = response.data;
                            if (caseData.headers) {
                                response = _.cloneDeep(response);
                                if (!content) {
                                    content = {};
                                }
                                content.requestHeaders = settings.headers;
                            }
                            if (response.success === false) {
                                this.responseText = JSON.stringify(response);
                            }
                            else {
                                this.responseText = JSON.stringify({
                                    success: true,
                                    content: content
                                });
                            }
                        }
                    }
                },
                {
                    url: "//204",
                    status: 204,
                    headers: {
                        'X-CSRF-Token': 'token2'
                    }
                },
                {
                    url: "//500",
                    status: 500,
                    statusText: 'Server Error',
                }
            ]);

            tokenHandler.clearStore()
            .then(function() {
                return tokenHandler.setToken('token1');
            })
            .then(function() {
                var result = request(caseData);

                assert.ok(result instanceof Promise, 'The request function returns a promise');

                if (caseData.reject) {
                    QUnit.expect(3);

                    result.then(function() {
                        assert.ok(false, 'Should reject');
                        QUnit.start();
                    })
                    .catch(function(err) {
                        assert.equal(err.name, caseData.err.name, 'Reject error is the one expected');
                        assert.equal(err.message, caseData.err.message, 'Reject error is correct');
                        QUnit.start();
                    });

                }
                else {
                    QUnit.expect(caseData.noToken ? 2 : 3);

                    result.then(function(response) {
                        if (_.isUndefined(caseData.content)) {
                            assert.ok(_.isUndefined(response), 'No content encountered in empty response');
                        }
                        else {
                            assert.deepEqual(response.content, caseData.content, 'The given result is correct');
                        }

                        if (!caseData.noToken) {
                            tokenHandler.getToken().then(function(storedToken) {
                                assert.equal(storedToken, 'token2', 'The token was updated with the next in sequence');
                                QUnit.start();
                            });
                        }
                        else {
                            QUnit.start();
                        }
                    })
                    .catch(function() {
                        assert.ok(false, 'Should not reject');
                        QUnit.start();
                    });
                }
            });
        });
});
