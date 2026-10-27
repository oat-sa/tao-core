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
define([
    'tao/lockDownBrowser/processBlockList'
], function (processBlockList) {
    'use strict';

    const API_TIMEOUT_MS = 5000;
    const API_WAIT_MS = 10000;
    const API_POLL_MS = 50;
    const LOCK_WAIT_MS = 30000;
    const LOCK_POLL_MS = 500;
    const REQUIRED_API_METHODS = ['getDeviceInfo', 'lock', 'unlock', 'isLocked', 'exit', 'checkBlocklist'];
    const EVENT_TYPES = ['breach', 'lost-focus', 'display-added', 'offline', 'online'];

    function wait(ms) {
        return new Promise(function (resolve) {
            setTimeout(resolve, ms);
        });
    }

    function withProcessCheckTimeout(promise, timeoutMs) {
        return new Promise(function (resolve, reject) {
            const timeoutId = setTimeout(function () {
                reject(processBlockList.createError(
                    processBlockList.ERROR_API_TIMEOUT,
                    'Process block list API timed out'
                ));
            }, timeoutMs);
            const finish = function (callback) {
                return function (value) {
                    clearTimeout(timeoutId);
                    callback(value);
                };
            };

            Promise.resolve(promise).then(finish(resolve), finish(reject));
        });
    }

    function createApiRejectedError(cause) {
        return processBlockList.createError(
            processBlockList.ERROR_API_REJECTED,
            'Process block list API failed',
            cause
        );
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

    function createKioskedDriver(options = {}) {
        const minVersion = options.minVersion || '0.0.0';
        const processCheckTimeout = options.processCheckTimeout || API_TIMEOUT_MS;
        const apiWaitMs = options.apiWaitMs || API_WAIT_MS;
        const lockWaitMs = Object.prototype.hasOwnProperty.call(options, 'lockWaitMs')
            ? options.lockWaitMs
            : LOCK_WAIT_MS;
        const lockPollMs = options.lockPollMs || LOCK_POLL_MS;
        const eventListeners = {};
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

            whenAvailable() {
                const apiDeadline = Date.now() + apiWaitMs;

                const waitForLockedSession = handle => {
                    const applyLocked = locked => {
                        sessionActive = locked;

                        return locked;
                    };

                    if (lockWaitMs <= 0) {
                        return isLockedAsync(handle).then(applyLocked);
                    }

                    const lockDeadline = Date.now() + lockWaitMs;

                    const pollLockedState = () =>
                        isLockedAsync(handle).then(locked => {
                            if (locked) {
                                sessionActive = true;

                                return true;
                            }

                            if (Date.now() >= lockDeadline) {
                                sessionActive = false;

                                return false;
                            }

                            return wait(lockPollMs).then(pollLockedState);
                        });

                    return pollLockedState();
                };

                const pollForApi = () => {
                    cachedHandle = null;
                    const handle = getHandle();

                    if (handle) {
                        return waitForLockedSession(handle);
                    }

                    if (Date.now() >= apiDeadline) {
                        sessionActive = false;

                        return Promise.resolve(false);
                    }

                    return wait(API_POLL_MS).then(pollForApi);
                };

                return pollForApi();
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

            addEventListeners: function addEventListeners(eventTypes, callback) {
                const handle = getHandle();
                const normalizedEventTypes = Array.isArray(eventTypes)
                    ? eventTypes.filter(function (eventType, index) {
                        return (
                            typeof eventType === 'string' &&
                            eventType.length > 0 &&
                            eventTypes.indexOf(eventType) === index
                        );
                    })
                    : [];

                if (!handle || typeof handle.api.addEventListener !== 'function') {
                    return;
                }

                this.removeEventListeners();

                normalizedEventTypes.forEach(function (eventType) {
                    const eventHandler = function (event) {
                        if (typeof callback === 'function') {
                            callback({
                                type: eventType,
                                occurredAt: new Date().toISOString(),
                                reason: event && event.reason,
                                simulated: event && event.simulated,
                                source: event && event.source,
                                blockedApplications: event && event.blockedApplications
                            });
                        }
                    };

                    eventListeners[eventType] = eventHandler;
                    handle.api.addEventListener(eventType, eventHandler);
                });
            },

            removeEventListeners: function removeEventListeners() {
                const handle = getHandle();

                if (handle && typeof handle.api.removeEventListener === 'function') {
                    Object.keys(eventListeners).forEach(function (eventType) {
                        handle.api.removeEventListener(eventType, eventListeners[eventType]);
                        delete eventListeners[eventType];
                    });
                }
            },

            addSecurityViolationListener: function addSecurityViolationListener(callback) {
                this.addEventListeners(['breach'], callback);
            },

            removeSecurityViolationListener: function removeSecurityViolationListener() {
                this.removeEventListeners();
            },

            exitApplication: function exitApplication() {
                const api = resolveNativeExitApi();

                if (!api) {
                    return;
                }

                api.exit();
            },

            checkDeniedProcesses: function checkDeniedProcesses(processes) {
                return Promise.resolve()
                    .then(function () {
                        return processBlockList.normalize(processes);
                    })
                    .then(function (normalized) {
                        const handle = getHandle();
                        const vendorMap = normalized.vendorMap;

                        if (!Object.keys(vendorMap).length) {
                            return [];
                        }

                        if (!handle || typeof handle.api.checkBlocklist !== 'function') {
                            throw processBlockList.createError(
                                processBlockList.ERROR_API_UNAVAILABLE,
                                'Process block list API not available'
                            );
                        }

                        return withProcessCheckTimeout(
                            Promise.resolve().then(() => handle.api.checkBlocklist(vendorMap, true)),
                            processCheckTimeout
                        )
                            .then(function (applications) {
                                return normalized.resolveBlockedApplications(applications);
                            })
                            .catch(function (error) {
                                if (
                                    error &&
                                    (
                                        error.code === processBlockList.ERROR_API_TIMEOUT ||
                                        error.code === processBlockList.ERROR_INVALID_RESPONSE
                                    )
                                ) {
                                    throw error;
                                }

                                throw createApiRejectedError(error);
                            });
                    });
            },

            simulateSecurityViolation: function simulateSecurityViolation(detail) {
                const handle = getHandle();
                let additionalApps = {};
                let checkPromise = Promise.resolve([]);

                detail = detail || {};

                if (detail.processBlackList) {
                    try {
                        additionalApps = processBlockList.normalize(detail.processBlackList).vendorMap;
                    } catch (err) {
                        additionalApps = {};
                    }
                }

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

                    if (typeof eventListeners.breach === 'function') {
                        eventListeners.breach(payload);
                    }

                    return payload;
                });
            }
        };
    }

    return {
        EVENT_TYPES: EVENT_TYPES,
        createKioskedDriver: createKioskedDriver,
        resolveNativeApiHandle: resolveNativeApiHandle,
        resolveNativeExitApi: resolveNativeExitApi
    };
});
