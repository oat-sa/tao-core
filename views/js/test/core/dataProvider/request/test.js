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
define([
    'jquery',
    'core/dataProvider/request',
    'core/promise'
], function ($, request, Promise){
    'use strict';

    var requestCases;
    var $ajax = $.ajax;

    QUnit.module('API');

    QUnit.test('module', function (assert){
        QUnit.expect(1);

        assert.equal(typeof request, 'function', "The module exposes a function");
    });

    QUnit.module('request', {
        setup : function(){

            //mock the jquery ajax method
            $.ajax = function(options){
                return {
                    done : function(cb){
                        if(options.url === '//200'){
                            cb({
                                success : true,
                                data : { 'foo' : 'bar'}
                            });
                        }
                        if(options.url === '//204'){
                            cb(null, 'No Content', { status : 204});
                        }
                        return this;
                    },
                    fail : function(cb){
                        if(options.url === '//500'){
                            cb({ status : 500, statusText : 'Server Error'});
                        }
                        return this;
                    }
                };
            };
        },
        teardown : function teardown(){
            $.ajax = $ajax;
        }
    });

    requestCases = [{
        title: 'no url',
        reject: true,
        err : new TypeError('At least give a URL...')
    }, {
        title : '200 got content',
        url : '//200',
        content: { foo : 'bar'}
    }, {
        title : '204 no content',
        url : '//204'
    }, {
        title : '500 error',
        url : '//500',
        reject : true,
        err : new Error('500 : Server Error')
    }];

    QUnit
        .cases(requestCases)
        .asyncTest('request with ', function(data, assert){

            var result = request(data.url, data.data, data.method);
            assert.ok(result instanceof Promise, 'The request function returns a promise');

            if(data.reject){

                QUnit.expect(3);

                result.then(function(){
                    assert.ok(false, 'Should reject');
                    QUnit.start();
                })
                .catch(function(err){
                    assert.equal(data.err.name, err.name, 'Reject error is the one expected');
                    assert.equal(data.err.message, err.message, 'Reject error is correct');
                    QUnit.start();
                });

            } else {
                QUnit.expect(2);

                result.then(function(content){
                    assert.deepEqual(content, data.content, 'The given reuslt is correct');
                    QUnit.start();
                })
                .catch(function(){
                    assert.ok(false, 'Should not reject');
                    QUnit.start();
                });
            }
        });
});
