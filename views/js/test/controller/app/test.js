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
    'controller/app',
    'core/historyRouter',
    'core/logger',
    'ui/feedback'
], function ($, appController, historyRouterFactory, loggerFactory, feedback) {
    'use strict';

    var appControllerApi = [
        // core
        {title: 'start'},
        {title: 'apply'},
        {title: 'getRouter'},
        {title: 'getLogger'},
        {title: 'onError'},

        // eventifier
        {title: 'trigger'},
        {title: 'on'},
        {title: 'off'},

        // statifier
        {title: 'getState'},
        {title: 'setState'}
    ];


    QUnit.module('API', {
        teardown: function() {
            loggerFactory.removeAllListeners();
            feedback.removeAllListeners();
            appController.removeAllListeners();
        }
    });


    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.equal(typeof appController, 'object', "The appController module exposes an object");
    });


    QUnit
        .cases(appControllerApi)
        .test('module API ', function (data, assert) {
            QUnit.expect(1);
            assert.equal(typeof appController[data.title], 'function', 'The appController exposes a "' + data.title + '" function');
        });


    QUnit.test('getRouter', function(assert) {
        QUnit.expect(1);
         assert.equal(appController.getRouter(), historyRouterFactory(), 'The appController returns the history router');
    });


    QUnit.test('getLogger', function(assert) {
        QUnit.expect(3);
        assert.equal(typeof appController.getLogger(), "object", 'The appController returns a logger');
        assert.equal(typeof appController.getLogger().log, 'function', 'The logger has a log method');
        assert.equal(typeof appController.getLogger().error, 'function', 'The logger has an error method');
    });


    QUnit.asyncTest('onError', function(assert) {
        var expectedError = new Error('Test');

        QUnit.expect(3);
        QUnit.stop(1);

        loggerFactory.on('error', function(err) {
            assert.equal(err, expectedError, 'Should log the error');
            QUnit.start();
        });
        feedback.on('error', function(err) {
            assert.equal(err, expectedError.message, 'Should display the error');
            QUnit.start();
        });

        assert.equal(appController.onError(expectedError), appController, 'Should return the appController');
    });


    QUnit.asyncTest('apply', function(assert) {
        var $target = $('#qunit-fixture');

        QUnit.expect(5);
        QUnit.stop(1);

        appController.on('change', function(url) {
            assert.ok(appController.getState('dispatching'), 'The controller is dispatching: ' + url);
        });

        appController.on('started', function(url) {
            assert.ok(!appController.getState('dispatching'), 'The controller has dispatched: ' + url);
            QUnit.start();
        });

        assert.equal(appController.apply('.fixture', $target), appController, 'Should return the appController');

        $target.find('a').click();
    });


    QUnit.asyncTest('start', function(assert) {

        QUnit.expect(2);

        historyRouterFactory().on('forward', function(url) {
            assert.ok(true, 'The historyRouter has forwarded the route');
            assert.equal(url, window.location.href, 'The right url has been forwarded');
            QUnit.start();
        });

        appController.start();
    });
});
