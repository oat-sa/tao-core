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
    'test/mocks/ajax'
], function($, _, ajaxMock) {
    'use strict';

    var ajaxBackup;

    QUnit.module('API');

    // backup/restore ajax method between each test
    QUnit.testStart(function() {
        ajaxBackup = $.ajax;
    });
    QUnit.testDone(function() {
        $.ajax = ajaxBackup;
    });

    QUnit.test('module', function(assert) {
        QUnit.expect(4);
        assert.equal(typeof ajaxMock, 'object', "The mocks/ajax module exposes an object");
        assert.equal(typeof ajaxMock.push, 'function', "The mocks/ajax module exposes a push() function");
        assert.equal(typeof ajaxMock.pop, 'function', "The mocks/ajax module exposes a pop() function");
        assert.equal(typeof ajaxMock.mock, 'function', "The mocks/ajax module exposes a mock() function");
    });

    QUnit.test('push / pop', function(assert) {
        var mock1 = function() {};
        var mock2 = function() {};

        QUnit.expect(8);

        assert.equal($.ajax, ajaxBackup, 'The current AJAX manager is the default one');

        ajaxMock.push(mock1);

        assert.notEqual($.ajax, ajaxBackup, 'The current AJAX manager is not the default one');
        assert.equal($.ajax, mock1, 'The current AJAX manager is mock1');

        ajaxMock.push();
        assert.equal($.ajax, mock1, 'The current AJAX manager is still mock1');

        $.ajax = mock2;
        assert.notEqual($.ajax, mock1, 'The current AJAX manager is not mock1 anymore');
        assert.equal($.ajax, mock2, 'The current AJAX manager is mock2');

        ajaxMock.pop();

        assert.equal($.ajax, mock1, 'The current AJAX manager has been restored to mock1');

        ajaxMock.pop();

        assert.equal($.ajax, ajaxBackup, 'The default AJAX manager has been restored');
    });

    QUnit.asyncTest('mock success', function(assert) {
        var expectedURL = 'http://foo.bar/test?v=123';
        var expectedResponse = {
            foo: 'bar'
        };

        function validate(url) {
            assert.equal(url, expectedURL, 'The request has called the right URL');
            assert.ok(true, 'The validator has been called');
        }

        QUnit.expect(3);

        ajaxMock.mock(true, expectedResponse, validate);

        $.ajax(expectedURL)
            .done(function(data) {
                assert.equal(data, expectedResponse, 'The request has returned the expected response');
                QUnit.start();
            })
            .fail(function() {
                assert.ok(false, 'The request should not fail');
                QUnit.start();
            });
    });

    QUnit.asyncTest('mock failed', function(assert) {
        var expectedURL = 'http://foo.bar/test?v=123';
        var expectedResponse = {
            foo: 'bar'
        };

        function validate(url) {
            assert.equal(url, expectedURL, 'The request has called the right URL');
            assert.ok(true, 'The validator has been called');
        }

        QUnit.expect(3);

        ajaxMock.mock(false, expectedResponse, validate);

        $.ajax(expectedURL)
            .done(function() {
                assert.ok(false, 'The request should fail');
                QUnit.start();
            })
            .fail(function(err) {
                assert.equal(err, expectedResponse, 'The request has returned the expected response');
                QUnit.start();
            });
    });

    QUnit.asyncTest('mock dynamic success', function(assert) {
        var expectedURL = 'http://foo.bar/test?v=123';
        var expectedResponse = {
            foo: 'bar'
        };

        function success(url) {
            assert.equal(url, expectedURL, 'The request has called the right URL');
            return true;
        }

        QUnit.expect(2);

        ajaxMock.mock(success, expectedResponse);

        $.ajax(expectedURL)
            .done(function(data) {
                assert.equal(data, expectedResponse, 'The request has returned the expected response');
                QUnit.start();
            })
            .fail(function() {
                assert.ok(false, 'The request should not fail');
                QUnit.start();
            });
    });

    QUnit.asyncTest('mock dynamic fail', function(assert) {
        var expectedURL = 'http://foo.bar/test?v=123';
        var expectedResponse = {
            foo: 'bar'
        };

        function success(url) {
            assert.equal(url, expectedURL, 'The request has called the right URL');
            return false;
        }

        QUnit.expect(2);

        ajaxMock.mock(success, expectedResponse);

        $.ajax(expectedURL)
            .done(function() {
                assert.ok(false, 'The request should fail');
                QUnit.start();
            })
            .fail(function(err) {
                assert.equal(err, expectedResponse, 'The request has returned the expected response');
                QUnit.start();
            });
    });

    QUnit.asyncTest('mock dynamic response', function(assert) {
        var expectedURL = 'http://foo.bar/test?v=123';
        var expectedResponse = {
            foo: 'bar'
        };

        function response(url) {
            assert.equal(url, expectedURL, 'The request has called the right URL');
            return expectedResponse;
        }

        QUnit.expect(2);

        ajaxMock.mock(true, response);

        $.ajax(expectedURL)
            .done(function(data) {
                assert.equal(data, expectedResponse, 'The request has returned the expected response');
                QUnit.start();
            })
            .fail(function() {
                assert.ok(false, 'The request should not fail');
                QUnit.start();
            });
    });

    QUnit.asyncTest('mock dynamic failed response', function(assert) {
        var expectedURL = 'http://foo.bar/test?v=123';
        var expectedResponse = {
            foo: 'bar'
        };

        function response(url) {
            assert.equal(url, expectedURL, 'The request has called the right URL');
            return expectedResponse;
        }

        QUnit.expect(2);

        ajaxMock.mock(false, response);

        $.ajax(expectedURL)
            .done(function(err) {
                assert.ok(false, 'The request should fail');
                QUnit.start();
            })
            .fail(function(err) {
                assert.equal(err, expectedResponse, 'The request has returned the expected response');
                QUnit.start();
            });
    });
});
