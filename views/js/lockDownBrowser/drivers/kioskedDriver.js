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

    var API_TIMEOUT_MS = 5000;
    var API_WAIT_MS = 2500;
    var API_POLL_MS = 50;

    function wait(ms) {
        return new Promise(function (resolve) {
            setTimeout(resolve, ms);
        });
    }

    function semverCompare(a, b) {
        var pa = String(a).split('.').map(function (n) {
            return parseInt(n, 10) || 0;
        });
        var pb = String(b).split('.').map(function (n) {
            return parseInt(n, 10) || 0;
        });
        var i;
        var len = Math.max(pa.length, pb.length);

        for (i = 0; i < len; i++) {
            var na = pa[i] || 0;
            var nb = pb[i] || 0;

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
            if (win.kiosked) {
                return { kind: 'kiosked', api: win.kiosked };
            }
        } catch (err) {
            // cross-origin
        }

        try {
            if (win.kiosk && typeof win.kiosk.isLocked === 'function') {
                return { kind: 'kiosk', api: win.kiosk };
            }
        } catch (err) {
            // cross-origin
        }

        return null;
    }

    function resolveNativeApiHandle() {
        var candidates = [window, window.parent, window.top];
        var i;
        var handle;

        for (i = 0; i < candidates.length; i++) {
            handle = readNativeApiFromWindow(candidates[i]);

            if (handle) {
                return handle;
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
        var minVersion = (options && options.minVersion) || '0.0.0';
        var violationListener;
        var violationHandler;
        var violationUserCallback;
        var cachedHandle;

        function getHandle() {
            if (!cachedHandle) {
                cachedHandle = resolveNativeApiHandle();
            }

            return cachedHandle;
        }

        function isLockedSync(handle) {
            if (!handle) {
                return false;
            }

            if (handle.kind === 'kiosked' && typeof handle.api.isLocked === 'function') {
                return true;
            }

            return handle.kind === 'kiosk';
        }

        function isLockedAsync(handle) {
            if (!handle) {
                return Promise.resolve(false);
            }

            if (handle.kind === 'kiosked' && typeof handle.api.isLocked === 'function') {
                return Promise.race([handle.api.isLocked(), wait(API_TIMEOUT_MS)]).then(function (locked) {
                    return !!locked;
                }).catch(function () {
                    return true;
                });
            }

            if (handle.kind === 'kiosk' && typeof handle.api.isLocked === 'function') {
                return new Promise(function (resolve) {
                    var settled = false;

                    function finish(value) {
                        if (settled) {
                            return;
                        }

                        settled = true;
                        resolve(!!value);
                    }

                    try {
                        handle.api.isLocked(function (state) {
                            finish(state && state.isLocked);
                        });
                    } catch (err) {
                        finish(true);
                    }

                    wait(API_TIMEOUT_MS).then(function () {
                        finish(true);
                    });
                });
            }

            return Promise.resolve(true);
        }

        return {
            id: 'kiosked',

            whenAvailable: function whenAvailable() {
                var deadline = Date.now() + API_WAIT_MS;

                function poll() {
                    cachedHandle = null;
                    var handle = getHandle();

                    if (handle) {
                        return isLockedAsync(handle).then(function (locked) {
                            return locked || isLockedSync(handle);
                        });
                    }

                    if (Date.now() >= deadline) {
                        return Promise.resolve(false);
                    }

                    return wait(API_POLL_MS).then(poll);
                }

                return poll();
            },

            isSessionActive: function isSessionActive() {
                return isLockedSync(getHandle());
            },

            validateMinVersion: function validateMinVersion() {
                var handle = getHandle();

                if (!handle) {
                    return Promise.reject(new Error('Lock down browser API not available'));
                }

                var deviceInfoPromise;

                if (handle.kind === 'kiosked' && typeof handle.api.getDeviceInfo === 'function') {
                    deviceInfoPromise = Promise.race([handle.api.getDeviceInfo(), wait(API_TIMEOUT_MS)]);
                } else if (handle.kind === 'kiosk' && typeof handle.api.getDeviceInfo === 'function') {
                    deviceInfoPromise = new Promise(function (resolve, reject) {
                        try {
                            handle.api.getDeviceInfo(function (info) {
                                resolve(info);
                            });
                        } catch (err) {
                            reject(err);
                        }
                    });
                } else {
                    return Promise.reject(new Error('Lock down browser API not available'));
                }

                return deviceInfoPromise.then(function (deviceInfo) {
                    var version = deviceInfo && deviceInfo.app && deviceInfo.app.version;

                    if (!version || semverCompare(version, minVersion) < 0) {
                        return Promise.reject(new Error('Lock down browser version not supported'));
                    }
                });
            },

            addSecurityViolationListener: function addSecurityViolationListener(callback) {
                var handle = getHandle();

                violationUserCallback = callback;

                if (!handle) {
                    return;
                }

                if (handle.kind === 'kiosked' && typeof handle.api.addEventListener === 'function') {
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

                    return;
                }

                if (handle.kind === 'kiosk' && typeof handle.api.onBreach === 'function') {
                    violationHandler = function (detail) {
                        if (typeof violationUserCallback === 'function') {
                            violationUserCallback({ detail: detail || {} });
                        }
                    };

                    violationListener = violationHandler;
                    handle.api.onBreach(violationHandler);
                }
            },

            removeSecurityViolationListener: function removeSecurityViolationListener() {
                var handle = getHandle();

                if (handle && handle.kind === 'kiosked' && violationListener &&
                    typeof handle.api.removeEventListener === 'function') {
                    handle.api.removeEventListener('breach', violationListener);
                }

                violationListener = null;
                violationHandler = null;
                violationUserCallback = null;
            },

            exitApplication: function exitApplication() {
                var handle = getHandle();

                if (!handle) {
                    return;
                }

                if (handle.kind === 'kiosked' && typeof handle.api.exit === 'function') {
                    handle.api.exit();

                    return;
                }

                if (handle.kind === 'kiosk' && typeof handle.api.exit === 'function') {
                    handle.api.exit(false);
                }
            },

            checkDeniedProcesses: function checkDeniedProcesses(processBlackList) {
                var handle = getHandle();
                var additionalApps = normalizeProcessBlockList(processBlackList);

                if (!handle || !Object.keys(additionalApps).length) {
                    return Promise.resolve([]);
                }

                if (handle.kind === 'kiosked' && typeof handle.api.checkBlocklist === 'function') {
                    return handle.api.checkBlocklist(additionalApps, true).then(function (apps) {
                        return apps || [];
                    }).catch(function () {
                        return [];
                    });
                }

                return Promise.resolve([]);
            },

            simulateSecurityViolation: function simulateSecurityViolation(detail) {
                detail = detail || {};
                var handle = getHandle();
                var additionalApps = detail.processBlackList
                    ? normalizeProcessBlockList(detail.processBlackList)
                    : {};
                var checkPromise = Promise.resolve([]);

                if (handle && handle.kind === 'kiosked' && typeof handle.api.checkBlocklist === 'function') {
                    checkPromise = handle.api.checkBlocklist(additionalApps, true).catch(function () {
                        return [];
                    });
                }

                return checkPromise.then(function (blockedApplications) {
                    var payload = {
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
        resolveNativeApiHandle: resolveNativeApiHandle
    };
});
