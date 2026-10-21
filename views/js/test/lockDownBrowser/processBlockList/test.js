/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
define(['tao/lockDownBrowser/processBlockList'], function (processBlockList) {
    'use strict';

    const configuredProcesses = [
        { process: 'notion.exe', label: 'Notion' },
        { process: 'zoom.exe', label: 'Zoom' }
    ];

    QUnit.module('processBlockList');

    QUnit.test('validates configuration and maps API results to labels', function (assert) {
        const normalized = processBlockList.create(configuredProcesses);

        assert.deepEqual(normalized.vendorMap, {
            'notion.exe': 'Notion',
            'zoom.exe': 'Zoom'
        });
        assert.deepEqual(processBlockList.create([]).vendorMap, {}, 'accepts an explicitly empty list');
        assert.strictEqual(
            processBlockList.normalize(configuredProcesses),
            processBlockList.normalize(configuredProcesses),
            'normalizes the same configured list only once'
        );
        assert.deepEqual(
            normalized.resolveBlockedApplications(['NOTION.EXE', 'Zoom']),
            ['Notion', 'Zoom'],
            'maps configured process names and labels'
        );
    });

    QUnit.test('rejects malformed and duplicate configuration', function (assert) {
        assert.throws(function () {
            processBlockList.create();
        }, function (error) {
            return error.code === processBlockList.ERROR_INVALID_CONFIG;
        }, 'requires an array');

        assert.throws(function () {
            processBlockList.create([{ process: 'notion.exe' }]);
        }, function (error) {
            return error.code === processBlockList.ERROR_INVALID_CONFIG;
        }, 'requires process and label');

        assert.throws(function () {
            processBlockList.create([
                { process: 'notion.exe', label: 'Notion' },
                { process: 'NOTION.EXE', label: 'Duplicate' }
            ]);
        }, function (error) {
            return error.code === processBlockList.ERROR_INVALID_CONFIG;
        }, 'rejects case-insensitive duplicate processes');
    });

    QUnit.test('rejects malformed API responses', function (assert) {
        const normalized = processBlockList.create(configuredProcesses);

        assert.throws(function () {
            normalized.resolveBlockedApplications(null);
        }, function (error) {
            return error.code === processBlockList.ERROR_INVALID_RESPONSE;
        }, 'requires an array response');

        assert.throws(function () {
            normalized.resolveBlockedApplications(['']);
        }, function (error) {
            return error.code === processBlockList.ERROR_INVALID_RESPONSE;
        }, 'requires non-empty application names');
    });
});
