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
 * @author Anton Tsymuk <anton@taotesting.com>
 */
define([
    'jquery',
    'ui/dashboard'
], function ($, dashboard) {
    'use strict';

    var data = [
        {
            title: 'Test 1',
            score: 100,
            info: [
                { text: 'Test 1' },
            ]
        },
        {
            title: 'Test 2',
            score: 65,
            info: [
                { text: 'Test 2' },
            ],
        },
        {
            title: 'Test 3',
            score: 32,
            info: [
                { text: 'Test 3' },
            ]
        },
    ];

    var scoreState = {
        error: 50,
        warn: 90,
    };

    QUnit.module('dashboard');

    QUnit.test('module', function (assert) {
        assert.equal(typeof dashboard, 'function', 'The dashboard module exposes a function');
        assert.equal(typeof dashboard(), 'object', 'The dashboard factory produces an object');
        assert.notStrictEqual(dashboard(), dashboard(), 'The dashboard factory provides a different object on each call');
    });

    QUnit.test('instance API ', function (assert) {
        var componentApi = [
            'init',
            'destroy',
            'render',
            'show',
            'hide',
            'trigger',
            'on',
            'off',
            'getElement',

            'clearDashboard',
            'mapScoreToState',
            'renderMetrics',
            'toggleLoadingBar',
            'toggleWarningMessage',
        ]

        var instance = dashboard();

        componentApi.forEach(function (method){
            assert.equal(typeof instance[method], 'function', 'The dashboard instance exposes a "' + data.title + '" function');
        });
    });

    QUnit.test('render with default config', function (assert) {
        var instance = dashboard({
            renderTo: '#fixture-render',
        });

        assert.equal(typeof instance, 'object', 'The dashboard instance is an object');
        assert.ok(instance.getElement() instanceof $, 'The dashboard instance gets a DOM element');
        assert.equal(instance.getElement().length, 1, 'The dashboard instance gets a single element');

        instance.destroy();
    });

    QUnit.test('render with loading', function (assert) {
        var instance = dashboard({
            loading: true,
            renderTo: '#fixture-render',
        });

        var loadingBar = instance.getElement().find('.dashboard-loading');

        assert.ok(loadingBar.is(':visible'), 'The loading bar is displaed');

        instance.destroy();
    });

    QUnit.test('render without loading', function (assert) {
        var instance = dashboard({
            loading: false,
            data: data,
            renderTo: '#fixture-render',
        });

        var loadingBar = instance.getElement().find('.dashboard-loading');
        var dashboardMetrics = instance.getElement().find('.dashboard-metric');

        assert.equal(loadingBar.is(':visible'), false, 'The loading bar is not displaed');
        assert.equal(dashboardMetrics.length, data.length, 'The dashboard is rendered');

        instance.destroy();
    });

    QUnit.test('clear dashboard', function (assert) {
        var instance = dashboard({
            loading: false,
            data: data,
            renderTo: '#fixture-render',
        });

        var dashboardMetrics = instance.getElement().find('.dashboard-metric');
        var dashboardWarning = instance.getElement().find('.dashboard-warning');

        instance.toggleWarningMessage(true);

        assert.equal(dashboardMetrics.length, data.length, 'The dashboard is rendered');
        assert.ok(dashboardWarning.is(':visible'), 'The warning is visible');

        instance.clearDashboard();

        dashboardMetrics = instance.getElement().find('.dashboard-metric');

        assert.equal(dashboardMetrics.length, 0, 'The dashboard has been cleaned');
        assert.equal(dashboardWarning.is(':visible'), false, 'The warning is hidden');

        instance.destroy();
    });

    QUnit.test('map score to check status', function (assert) {
        var instance = dashboard({
            renderTo: '#fixture-render',
        });
        var instanceWithCustomScoreState = dashboard({
            scoreState: scoreState,
        });

        assert.equal(instance.mapScoreToState(32), 'error', 'The metric score mapped to error status');
        assert.equal(instance.mapScoreToState(65), 'warn', 'The metric score mapped to error status');
        assert.equal(instance.mapScoreToState(66), 'success', 'The metric score mapped to error status');

        assert.equal(instanceWithCustomScoreState.mapScoreToState(scoreState.error), 'error', 'The metric score mapped to error status');
        assert.equal(instanceWithCustomScoreState.mapScoreToState(scoreState.warn), 'warn', 'The metric score mapped to error status');
        assert.equal(instanceWithCustomScoreState.mapScoreToState(scoreState.warn + 1), 'success', 'The metric score mapped to error status');

        instance.destroy();
    });

    QUnit.test('render metrics', function (assert) {
        var instance = dashboard({
            data: data,
            renderTo: '#fixture-render',
        });

        var errorMetrics = instance.getElement().find('.score-error');
        var warnMetrics = instance.getElement().find('.score-warn');
        var successMetrics = instance.getElement().find('.score-success');

        assert.equal(errorMetrics.length, 1, 'The error scored metric has been rendered');
        assert.equal(warnMetrics.length, 1, 'The warn scored metric has been rendered');
        assert.equal(successMetrics.length, 1, 'The success scored metric has been rendered');

        instance.destroy();
    });

    QUnit.test('toggle loading bar', function (assert) {
        var instance = dashboard({
            renderTo: '#fixture-render',
        });

        var loadingBar = instance.getElement().find('.dashboard-loading');

        instance.toggleLoadingBar(true);

        assert.ok(loadingBar.is(':visible'), 'The loading bar is displaed');

        instance.toggleLoadingBar(false);

        assert.equal(loadingBar.is(':visible'), false, 'The loading bar is not displaed');

        instance.destroy();
    });

    QUnit.test('toggle warning message', function (assert) {
        var instance = dashboard({
            renderTo: '#fixture-render',
        });

        var warnningMessage = instance.getElement().find('.dashboard-warning');

        instance.toggleWarningMessage(true);

        assert.ok(warnningMessage.is(':visible'), 'The loading bar is displaed');

        instance.toggleWarningMessage(false);

        assert.equal(warnningMessage.is(':visible'), false, 'The loading bar is not displaed');

        instance.destroy();
    });

    QUnit.test('playground', function (assert) {
        dashboard({
            data: data,
            renderTo: '#visual-test'
        });

        dashboard({
            loading: true,
            renderTo: '#visual-test-loading',
        });

        assert.ok(true, 'started');
    });
});
