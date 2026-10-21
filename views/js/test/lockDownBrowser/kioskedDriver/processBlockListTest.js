/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
define([
    'tao/lockDownBrowser/processBlockList',
    'tao/lockDownBrowser/drivers/kioskedDriver'
], function (processBlockList, kioskedDriver) {
    'use strict';

    const configuredProcesses = [
        { process: 'notion.exe', label: 'Notion' },
        { process: 'zoom.exe', label: 'Zoom' }
    ];
    let originalKiosked;

    function createApi(checkBlocklist) {
        return {
            getDeviceInfo: function () {},
            lock: function () {},
            unlock: function () {},
            isLocked: function () {
                return Promise.resolve(true);
            },
            onBreach: function () {},
            exit: function () {},
            checkBlocklist: checkBlocklist
        };
    }

    QUnit.module('kioskedDriver process block list', {
        beforeEach: function () {
            originalKiosked = window.kiosked;
        },

        afterEach: function () {
            window.kiosked = originalKiosked;
        }
    });

    QUnit.test('passes the configured map and returns labels', function (assert) {
        let receivedMap;
        let receivedFlag;

        window.kiosked = createApi(function (processMap, includeRunning) {
            receivedMap = processMap;
            receivedFlag = includeRunning;

            return Promise.resolve(['NOTION.EXE']);
        });

        return kioskedDriver.createKioskedDriver()
            .checkDeniedProcesses(configuredProcesses)
            .then(function (applications) {
                assert.deepEqual(receivedMap, {
                    'notion.exe': 'Notion',
                    'zoom.exe': 'Zoom'
                });
                assert.strictEqual(receivedFlag, true);
                assert.deepEqual(applications, ['Notion']);
            });
    });

    QUnit.test('does not call the native API for an empty list', function (assert) {
        window.kiosked = createApi(function () {
            assert.ok(false, 'the native API must not be called');
        });

        return kioskedDriver.createKioskedDriver()
            .checkDeniedProcesses([])
            .then(function (applications) {
                assert.deepEqual(applications, []);
            });
    });

    QUnit.test('reports an unavailable API', function (assert) {
        window.kiosked = createApi();

        return kioskedDriver.createKioskedDriver()
            .checkDeniedProcesses(configuredProcesses)
            .then(function () {
                assert.ok(false, 'an unavailable API must not resolve');
            })
            .catch(function (error) {
                assert.strictEqual(error.code, processBlockList.ERROR_API_UNAVAILABLE);
            });
    });

    QUnit.test('reports native API rejection', function (assert) {
        window.kiosked = createApi(function () {
            return Promise.reject(new Error('native failure'));
        });

        return kioskedDriver.createKioskedDriver()
            .checkDeniedProcesses(configuredProcesses)
            .then(function () {
                assert.ok(false, 'a rejected API call must not resolve');
            })
            .catch(function (error) {
                assert.strictEqual(error.code, processBlockList.ERROR_API_REJECTED);
            });
    });

    QUnit.test('reports timeout', function (assert) {
        window.kiosked = createApi(function () {
            return new Promise(function () {});
        });

        return kioskedDriver.createKioskedDriver({ processCheckTimeout: 10 })
            .checkDeniedProcesses(configuredProcesses)
            .then(function () {
                assert.ok(false, 'a timed-out API call must not resolve');
            })
            .catch(function (error) {
                assert.strictEqual(error.code, processBlockList.ERROR_API_TIMEOUT);
            });
    });

    QUnit.test('reports a malformed API response', function (assert) {
        window.kiosked = createApi(function () {
            return Promise.resolve({ application: 'notion.exe' });
        });

        return kioskedDriver.createKioskedDriver()
            .checkDeniedProcesses(configuredProcesses)
            .then(function () {
                assert.ok(false, 'a malformed response must not resolve');
            })
            .catch(function (error) {
                assert.strictEqual(error.code, processBlockList.ERROR_INVALID_RESPONSE);
            });
    });
});
