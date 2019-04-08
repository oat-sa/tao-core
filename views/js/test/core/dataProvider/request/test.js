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
 * Test the module core/dataProvider/request
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'lodash', 'core/dataProvider/request', 'core/promise'], function($, _, request, Promise) {
    'use strict';

    var requestCases;
    var $ajax = $.ajax;
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
    var errors = {
        '//500': [{status: 500, statusText: 'Server Error'}]
    };

    QUnit.module('API');

    QUnit.test('module', function (assert){
        assert.expect(1);

        assert.equal(typeof request, 'function', 'The module exposes a function');
    });


    QUnit.module('Missed params');

    requestCases = [{
        title: 'no url',
        err : new TypeError('At least give a URL...')
    }];

    QUnit
        .cases.init(requestCases)
        .test('bad request call with ', function(caseData, assert){
            var ready = assert.async();

            assert.expect(1);

            assert.throws(
                function() {
                    request(caseData.url, caseData.data, caseData.method, caseData.headers, caseData.background, caseData.noToken);
                },
                "throws an error"
            );
            ready();
        });


    QUnit.module('request', {
        beforeEach: function() {

            //mock the jquery ajax method
            $.ajax = function(options){
                return {
                    done: function(cb){
                        var response = responses[options.url];
                        if (response) {
                            if (options.headers && !_.isEmpty(options.headers)) {
                                response = _.cloneDeep(response);
                                if (!response[0].data) {
                                    response[0].data = {};
                                }
                                response[0].data.requestHeaders = options.headers;
                            }
                            cb.apply(null, response);
                        }
                        return this;
                    },
                    fail: function(cb){
                        if (errors[options.url]) {
                            cb.apply(null, errors[options.url]);
                        }
                        return this;
                    }
                };
            };
        },
        afterEach: function teardown() {
            $.ajax = $ajax;
        }
    });

    requestCases = [{
        title: 'no url',
        reject: true,
        err: new TypeError('At least give a URL...')
    }, {
        title: '200 got content',
        url: '//200',
        content: {foo: 'bar'},
        noToken: true
    }, {
        title: '200 header',
        url: '//200',
        headers: {'x-foo': 'bar'},
        content: {foo: 'bar', requestHeaders: {'x-foo': 'bar'}},
        noToken: true
    }, {
        title: '204 no content',
        url: '//204',
        noToken: true
    }, {
        title: '500 error',
        url: '//500',
        noToken: true,
        reject: true,
        err: new Error('500 : Server Error')
    }, {
        title: '200 error 1',
        url: '//200/error/1',
        noToken: true,
        reject: true,
        err: new Error('1 : oops')
    }, {
        title: '200 error 2',
        url: '//200/error/2',
        noToken: true,
        reject: true,
        err: new Error('2 : woops')
    }, {
        title: '200 error fallback',
        url: '//200/error/fallback',
        noToken: true,
        reject: true,
        err: new Error('The server has sent an empty response')
    }];

    QUnit
        .cases.init(requestCases)
        .test('request with ', function(data, assert){
            var ready = assert.async();

            var result = request(data.url, data.data, data.method, data.headers, data.background, data.noToken);
            assert.ok(result instanceof Promise, 'The request function returns a promise');

            if(data.reject){

                assert.expect(3);

                result.then(function(){
                    assert.ok(false, 'Should reject');
                    ready();
                })
                .catch(function(err){
                    assert.equal(err.name, data.err.name, 'Reject error is the one expected');
                    assert.equal(err.message, data.err.message, 'Reject error is correct');
                    ready();
                });

            } else {
                assert.expect(2);

                result.then(function(content){
                    assert.deepEqual(content, data.content, 'The given result is correct');
                    ready();
                })
                .catch(function(){
                    assert.ok(false, 'Should not reject');
                    ready();
                });
            }
        });
});
