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
 * Kiosk{ed} vendor driver — only module that touches window.kiosked / window.kiosk.
 */
define([], function () {
    'use strict';

    const API_TIMEOUT_MS = 5000;
    const API_WAIT_MS = 2500;
    const API_POLL_MS = 50;
    const REQUIRED_API_METHODS = ['getDeviceInfo', 'lock', 'unlock', 'isLocked', 'onBreach', 'exit'];

    function wait(ms) {
        return new Promise(function (resolve) {
            setTimeout(resolve, ms);
        });
    }

    function semverCompare(a, b) {
        const pa = String(a)
            .split('.')
            .map(function (n) {
                return parseInt(n, 10) || 0;
            });
        const pb = String(b)
            .split('.')
            .map(function (n) {
                return parseInt(n, 10) || 0;
            });
        const len = Math.max(pa.length, pb.length);

        for (let i = 0; i < len; i++) {
            const na = pa[i] || 0;
            const nb = pb[i] || 0;

            if (na > nb) {
                return 1;
            }
            if (na < nb) {
                return -1;
            }
        }

        return 0;
    }

    function readNativeApiFromWindow(win) {
        if (!win) {
            return null;
        }

        try {
            const api = win.kiosked;

            if (
                api &&
                REQUIRED_API_METHODS.every(function (method) {
                    return typeof api[method] === 'function';
                })
            ) {
                return { kind: 'kiosked', api: api };
            }
        } catch (err) {
            // cross-origin
        }

        return null;
    }

    function resolveNativeApiHandle() {
        const candidates = [window, window.parent, window.top];

        for (let i = 0; i < candidates.length; i++) {
            const handle = readNativeApiFromWindow(candidates[i]);

            if (handle) {
                return handle;
            }
        }

        return null;
    }

    function resolveNativeExitApi() {
        const candidates = [window, window.parent, window.top];

        for (let i = 0; i < candidates.length; i++) {
            try {
                if (candidates[i].kiosked && typeof candidates[i].kiosked.exit === 'function') {
                    return candidates[i].kiosked;
                }
            } catch (err) {
                // cross-origin
            }
        }

        return null;
    }

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

    function createKioskedDriver(options) {
        const minVersion = (options && options.minVersion) || '0.0.0';
        let violationListener;
        let violationHandler;
        let violationUserCallback;
        let cachedHandle;
        let sessionActive = false;

        function getHandle() {
            if (!cachedHandle) {
                cachedHandle = resolveNativeApiHandle();
            }

            return cachedHandle;
        }

        function isLockedAsync(handle) {
            if (!handle) {
                return Promise.resolve(false);
            }

            return Promise.race([
                Promise.resolve().then(function () {
                    return handle.api.isLocked();
                }),
                wait(API_TIMEOUT_MS).then(function () {
                    return false;
                })
            ])
                .then(function (locked) {
                    return locked === true;
                })
                .catch(function () {
                    return false;
                });
        }

        return {
            id: 'kiosked',

            whenAvailable: function whenAvailable() {
                const deadline = Date.now() + API_WAIT_MS;

                function poll() {
                    cachedHandle = null;
                    const handle = getHandle();

                    if (handle) {
                        return isLockedAsync(handle).then(function (locked) {
                            sessionActive = locked;

                            return locked;
                        });
                    }

                    if (Date.now() >= deadline) {
                        sessionActive = false;

                        return Promise.resolve(false);
                    }

                    return wait(API_POLL_MS).then(poll);
                }

                return poll();
            },

            isSessionActive: function isSessionActive() {
                return sessionActive;
            },

            validateMinVersion: function validateMinVersion() {
                const handle = getHandle();
                let deviceInfoPromise;

                if (!handle) {
                    return Promise.reject(new Error('Lock down browser API not available'));
                }

                if (typeof handle.api.getDeviceInfo === 'function') {
                    deviceInfoPromise = Promise.race([handle.api.getDeviceInfo(), wait(API_TIMEOUT_MS)]);
                } else {
                    return Promise.reject(new Error('Lock down browser API not available'));
                }

                return deviceInfoPromise.then(function (deviceInfo) {
                    const version = deviceInfo && deviceInfo.app && deviceInfo.app.version;

                    if (!version || semverCompare(version, minVersion) < 0) {
                        return Promise.reject(new Error('Lock down browser version not supported'));
                    }
                });
            },

            addSecurityViolationListener: function addSecurityViolationListener(callback) {
                const handle = getHandle();

                violationUserCallback = callback;

                if (!handle) {
                    return;
                }

                if (typeof handle.api.addEventListener === 'function') {
                    violationHandler = function (e) {
                        if (e && typeof e.preventDefault === 'function') {
                            e.preventDefault();
                        }

                        Promise.race([
                            Promise.all([
                                typeof handle.api.getDeviceInfo === 'function' ? handle.api.getDeviceInfo() : null,
                                typeof handle.api.getProcessList === 'function' ? handle.api.getProcessList() : null
                            ]),
                            wait(API_TIMEOUT_MS)
                        ]).then(function (details) {
                            if (typeof violationUserCallback === 'function') {
                                violationUserCallback({
                                    deviceInfo: details && details[0],
                                    processList: details && details[1],
                                    reason: e && e.reason
                                });
                            }
                        });
                    };

                    violationListener = violationHandler;
                    handle.api.addEventListener('breach', violationHandler);
                }
            },

            removeSecurityViolationListener: function removeSecurityViolationListener() {
                const handle = getHandle();

                if (handle && violationListener && typeof handle.api.removeEventListener === 'function') {
                    handle.api.removeEventListener('breach', violationListener);
                }

                violationListener = null;
                violationHandler = null;
                violationUserCallback = null;
            },

            exitApplication: function exitApplication() {
                const handle = getHandle();
                const api = handle ? handle.api : resolveNativeExitApi();

                if (api) {
                    api.exit();
                }
            },

            checkDeniedProcesses: function checkDeniedProcesses(processBlackList) {
                const handle = getHandle();
                const additionalApps = normalizeProcessBlockList(processBlackList);

                if (!handle || !Object.keys(additionalApps).length) {
                    return Promise.resolve([]);
                }

                if (typeof handle.api.checkBlocklist === 'function') {
                    return handle.api
                        .checkBlocklist(additionalApps, true)
                        .then(function (apps) {
                            return apps || [];
                        })
                        .catch(function () {
                            return [];
                        });
                }

                return Promise.resolve([]);
            },

            simulateSecurityViolation: function simulateSecurityViolation(detail) {
                detail = detail || {};
                const handle = getHandle();
                const additionalApps = detail.processBlackList ? normalizeProcessBlockList(detail.processBlackList) : {};
                let checkPromise = Promise.resolve([]);

                if (handle && typeof handle.api.checkBlocklist === 'function') {
                    checkPromise = handle.api.checkBlocklist(additionalApps, true).catch(function () {
                        return [];
                    });
                }

                return checkPromise.then(function (blockedApplications) {
                    const payload = {
                        simulated: true,
                        reason: detail.reason || 'simulateSecurityViolation',
                        blockedApplications: blockedApplications || [],
                        source: detail.source
                    };

                    if (typeof violationUserCallback === 'function') {
                        violationUserCallback(payload);
                    }

                    return payload;
                });
            }
        };
    }

    return {
        createKioskedDriver: createKioskedDriver,
        resolveNativeApiHandle: resolveNativeApiHandle,
        resolveNativeExitApi: resolveNativeExitApi
    };
});
