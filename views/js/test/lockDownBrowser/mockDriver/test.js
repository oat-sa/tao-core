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
    'tao/lockDownBrowser/drivers/kioskedDriver',
    'tao/lockDownBrowser/drivers/mockDriver'
], function (processBlockList, kioskedDriver, mockDriver) {
    'use strict';

    const configuredProcesses = [
        { process: 'notion.exe', label: 'Notion' },
        { process: 'zoom.exe', label: 'Zoom' }
    ];
    let originalKiosked;

    function createKioskedApi() {
        return {
            getDeviceInfo: function () {},
            lock: function () {},
            unlock: function () {},
            isLocked: function () {
                return Promise.resolve(true);
            },
            onBreach: function () {},
            exit: function () {},
            checkBlocklist: function () {
                return Promise.resolve(['notion.exe']);
            }
        };
    }

    QUnit.module('mockDriver', {
        beforeEach: function () {
            originalKiosked = window.kiosked;
        },

        afterEach: function () {
            const notice = document.getElementById('lockdown-browser-mock-block-notice');

            if (notice && notice.parentNode) {
                notice.parentNode.removeChild(notice);
            }

            window.kiosked = originalKiosked;
        }
    });

    QUnit.test('returns only configured active processes as labels', function (assert) {
        const driver = mockDriver.createMockDriver({
            mockActiveProcesses: ['NOTION.EXE', 'teams.exe']
        });

        return driver.checkDeniedProcesses(configuredProcesses).then(function (applications) {
            assert.deepEqual(applications, ['Notion']);
        });
    });

    QUnit.test('rejects invalid configuration', function (assert) {
        const driver = mockDriver.createMockDriver({
            mockActiveProcesses: ['notion.exe']
        });

        return driver.checkDeniedProcesses([{ process: 'notion.exe' }])
            .then(function () {
                assert.ok(false, 'invalid configuration must not resolve');
            })
            .catch(function (error) {
                assert.strictEqual(error.code, processBlockList.ERROR_INVALID_CONFIG);
            });
    });

    QUnit.test('returns the same label list format as the kiosked driver', function (assert) {
        const realDriver = kioskedDriver.createKioskedDriver();
        const simulatedDriver = mockDriver.createMockDriver({
            mockActiveProcesses: ['notion.exe']
        });

        window.kiosked = createKioskedApi();

        return Promise.all([
            realDriver.checkDeniedProcesses(configuredProcesses),
            simulatedDriver.checkDeniedProcesses(configuredProcesses)
        ]).then(function (results) {
            assert.deepEqual(results[0], ['Notion']);
            assert.deepEqual(results[1], results[0]);
        });
    });
});
