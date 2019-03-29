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
    'controller/app',
    'core/historyRouter',
    'core/logger',
    'ui/feedback'
], function($, _, appController, historyRouterFactory, loggerFactory, feedback) {
    'use strict';

    var appControllerApi = [

        // Core
        {title: 'start'},
        {title: 'apply'},
        {title: 'getRouter'},
        {title: 'getLogger'},
        {title: 'onError'},

        // Eventifier
        {title: 'trigger'},
        {title: 'on'},
        {title: 'off'},

        // Statifier
        {title: 'getState'},
        {title: 'setState'}
    ];

    QUnit.module('API', {
        afterEach: function(assert) {
            loggerFactory.removeAllListeners();
            feedback.removeAllListeners();
            appController.removeAllListeners();
        }
    });

    QUnit.test('module', function(assert) {
        assert.expect(1);

        assert.equal(typeof appController, 'object', 'The appController module exposes an object');
    });

    QUnit
        .cases.init(appControllerApi)
        .test('module API ', function(data, assert) {
            assert.expect(1);
            assert.equal(typeof appController[data.title], 'function', 'The appController exposes a "' + data.title + '" function');
        });

    QUnit.test('getRouter', function(assert) {
        assert.expect(1);
        assert.equal(appController.getRouter(), historyRouterFactory(), 'The appController returns the history router');
    });

    QUnit.test('getLogger', function(assert) {
        assert.expect(3);
        assert.equal(typeof appController.getLogger(), 'object', 'The appController returns a logger');
        assert.equal(typeof appController.getLogger().log, 'function', 'The logger has a log method');
        assert.equal(typeof appController.getLogger().error, 'function', 'The logger has an error method');
    });

    QUnit.test('onError', function(assert) {
        var expectedError = new Error('Test');
        var ready = assert.async();
        var ready2 = assert.async();

        assert.expect(3);

        loggerFactory.on('error', function(err) {
            assert.equal(err, expectedError, 'Should log the error');
            ready();
        });
        feedback.on('error', function(err) {
            assert.equal(err, expectedError.message, 'Should display the error');
            ready2();
        });

        assert.equal(appController.onError(expectedError), appController, 'Should return the appController');
    });

    QUnit.test('apply', function(assert) {
        var $target = $('#qunit-fixture');

        var ready = assert.async();
        assert.expect(5);

        appController.on('change', function(url) {
            assert.ok(appController.getState('dispatching'), 'The controller is dispatching: ' + url);
        });

        appController.on('started', function(url) {
            assert.ok(!appController.getState('dispatching'), 'The controller has dispatched: ' + url);
        });

        assert.equal(appController.apply('.fixture', $target), appController, 'Should return the appController');

        $target.find('a').click();

        _.delay(function () {
            ready();
        }, 300);
    });

    QUnit.test('start', function(assert) {
        var ready = assert.async();

        assert.expect(2);

        historyRouterFactory().on('forward', function(url) {
            assert.ok(true, 'The historyRouter has forwarded the route');
            assert.equal(url, window.location.href, 'The right url has been forwarded');
            ready();
        });

        appController.start();
    });
});
