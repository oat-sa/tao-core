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
define([
    'tao/lockDownBrowser/processBlockList'
], function (processBlockList) {
    'use strict';

    const MOCK_BLOCK_NOTICE_ID = 'lockdown-browser-mock-block-notice';

    function clearMockBlockNotice() {
        const el = document.getElementById(MOCK_BLOCK_NOTICE_ID);

        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    }

    function showMockBlockNotice(blockedApplications) {
        let el;

        clearMockBlockNotice();

        if (!blockedApplications.length) {
            return;
        }

        el = document.createElement('div');

        el.id = MOCK_BLOCK_NOTICE_ID;
        el.setAttribute('role', 'alert');
        el.textContent = `[lockDownBrowser mock] Blocked applications running: ${blockedApplications.join(', ')}`;
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

    function resolveMockBlockedLabels(processes, mockActiveProcesses) {
        const normalized = processBlockList.normalize(processes);

        return mockActiveProcesses.reduce(function (blockedApplications, processName) {
            const label = normalized.getLabel(processName);

            if (label) {
                blockedApplications.push(label);
            }

            return blockedApplications;
        }, []);
    }

    function resolveSimulatedBlockedLabels(processes, mockActiveProcesses) {
        let labelsByProcess = null;

        if (Array.isArray(processes) && processes.length) {
            try {
                labelsByProcess = processBlockList.normalize(processes).vendorMap;
            } catch (err) {
                labelsByProcess = null;
            }
        }

        return mockActiveProcesses.map(function (processName) {
            const key = String(processName).toLowerCase();

            return labelsByProcess && labelsByProcess[key] ? labelsByProcess[key] : String(processName);
        });
    }

    function createMockDriver(options) {
        let eventListener;
        let subscribedEventTypes = [];
        const mockActiveProcesses = (options && options.mockActiveProcesses) || [];

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

            addEventListeners: function addEventListeners(eventTypes, callback) {
                subscribedEventTypes = eventTypes.slice();
                eventListener = callback;
            },

            removeEventListeners: function removeEventListeners() {
                subscribedEventTypes = [];
                eventListener = null;
            },

            addSecurityViolationListener: function addSecurityViolationListener(callback) {
                this.addEventListeners(['breach'], callback);
            },

            removeSecurityViolationListener: function removeSecurityViolationListener() {
                this.removeEventListeners();
            },

            exitApplication: function exitApplication() {
                // ponytail: mock exit — real Kiosk closes the app; here we only log for dev
                if (window.console && typeof window.console.info === 'function') {
                    window.console.info('[lockDownBrowser mock] exitApplication');
                }
            },

            checkDeniedProcesses: function checkDeniedProcesses(processes) {
                return Promise.resolve().then(function () {
                    const blockedApplications = resolveMockBlockedLabels(processes, mockActiveProcesses);

                    if (mockActiveProcesses.length && window.console && typeof window.console.warn === 'function') {
                        window.console.warn('[lockDownBrowser mock] Blocked applications running:', blockedApplications);
                    }

                    showMockBlockNotice(blockedApplications);

                    return blockedApplications;
                });
            },

            simulateSecurityViolation: function simulateSecurityViolation(detail) {
                let blockedApplications = [];

                detail = detail || {};

                if (mockActiveProcesses.length) {
                    blockedApplications = resolveSimulatedBlockedLabels(
                        detail.processBlackList,
                        mockActiveProcesses
                    );
                }

                if (blockedApplications.length) {
                    if (window.console && typeof window.console.warn === 'function') {
                        window.console.warn(
                            '[lockDownBrowser mock] Simulated breach — blocked applications:',
                            blockedApplications
                        );
                    }

                    showMockBlockNotice(blockedApplications);
                }

                const payload = {
                    type: 'breach',
                    occurredAt: new Date().toISOString(),
                    simulated: true,
                    reason: detail.reason || 'simulateSecurityViolation',
                    blockedApplications: blockedApplications,
                    source: detail.source || 'mockDriver'
                };

                if (subscribedEventTypes.indexOf('breach') !== -1 && typeof eventListener === 'function') {
                    eventListener(payload);
                }

                return Promise.resolve(payload);
            }
        };
    }

    return {
        createMockDriver: createMockDriver
    };
});
