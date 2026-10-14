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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 *
 * Mock lock down browser driver for local QA (no native API).
 */
define([], function () {
    'use strict';

    function normalizeProcessBlockList(processBlackList) {
        if (!processBlackList) {
            return {};
        }

        if (!Array.isArray(processBlackList)) {
            return processBlackList;
        }

        return processBlackList.reduce(function (map, entry) {
            if (typeof entry === 'string') {
                map[entry.toLowerCase()] = entry;

                return map;
            }

            if (entry && entry.process) {
                map[String(entry.process).toLowerCase()] = entry.label || entry.process;
            }

            return map;
        }, {});
    }

    var MOCK_BLOCK_NOTICE_ID = 'lockdown-browser-mock-block-notice';

    function clearMockBlockNotice() {
        var el = document.getElementById(MOCK_BLOCK_NOTICE_ID);

        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    }

    function showMockBlockNotice(blockedApplications) {
        clearMockBlockNotice();

        if (!blockedApplications.length) {
            return;
        }

        var el = document.createElement('div');

        el.id = MOCK_BLOCK_NOTICE_ID;
        el.setAttribute('role', 'alert');
        el.textContent = '[lockDownBrowser mock] Blocked applications running: ' + blockedApplications.join(', ');
        el.style.cssText = [
            'position:fixed',
            'top:12px',
            'left:50%',
            'transform:translateX(-50%)',
            'z-index:10001',
            'max-width:90%',
            'padding:8px 14px',
            'font:13px/1.4 sans-serif',
            'color:#fff',
            'background:rgba(180,60,40,0.92)',
            'border-radius:4px',
            'box-shadow:0 2px 8px rgba(0,0,0,0.25)',
            'pointer-events:none'
        ].join(';');

        if (document.body) {
            document.body.appendChild(el);
        }
    }

    function resolveMockBlockedLabels(processBlackList, mockActiveProcesses) {
        var map = normalizeProcessBlockList(processBlackList);

        return mockActiveProcesses.map(function (processName) {
            var key = String(processName).toLowerCase();

            return map[key] || processName;
        });
    }

    function createMockDriver(options) {
        var violationListener;
        var mockActiveProcesses = (options && options.mockActiveProcesses) || [];

        return {
            id: 'kioskedMock',

            whenAvailable: function whenAvailable() {
                return Promise.resolve(true);
            },

            isSessionActive: function isSessionActive() {
                return true;
            },

            validateMinVersion: function validateMinVersion() {
                return Promise.resolve();
            },

            addSecurityViolationListener: function addSecurityViolationListener(callback) {
                violationListener = callback;
            },

            removeSecurityViolationListener: function removeSecurityViolationListener() {
                violationListener = null;
            },

            exitApplication: function exitApplication() {
                // ponytail: mock exit — real Kiosk closes the app; here we only log for dev
                if (typeof console !== 'undefined' && console.info) {
                    console.info('[lockDownBrowser mock] exitApplication');
                }
            },

            checkDeniedProcesses: function checkDeniedProcesses(processBlackList) {
                var map = normalizeProcessBlockList(processBlackList);
                var keys = Object.keys(map);

                if (mockActiveProcesses.length) {
                    var blockedApplications = resolveMockBlockedLabels(processBlackList, mockActiveProcesses);

                    if (typeof console !== 'undefined' && console.warn) {
                        console.warn('[lockDownBrowser mock] Blocked applications running:', blockedApplications);
                    }

                    showMockBlockNotice(blockedApplications);

                    return Promise.resolve(blockedApplications);
                }

                clearMockBlockNotice();

                if (!keys.length) {
                    return Promise.resolve([]);
                }

                return Promise.resolve([]);
            },

            simulateSecurityViolation: function simulateSecurityViolation(detail) {
                detail = detail || {};
                var blockedApplications = [];

                if (mockActiveProcesses.length) {
                    blockedApplications = resolveMockBlockedLabels(
                        detail.processBlackList,
                        mockActiveProcesses
                    );
                }

                if (blockedApplications.length) {
                    if (typeof console !== 'undefined' && console.warn) {
                        console.warn('[lockDownBrowser mock] Simulated breach — blocked applications:', blockedApplications);
                    }

                    showMockBlockNotice(blockedApplications);
                }

                var payload = {
                    simulated: true,
                    reason: detail.reason || 'simulateSecurityViolation',
                    blockedApplications: blockedApplications,
                    source: detail.source || 'mockDriver'
                };

                if (typeof violationListener === 'function') {
                    violationListener(payload);
                }

                return Promise.resolve(payload);
            }
        };
    }

    return {
        createMockDriver: createMockDriver
    };
});
