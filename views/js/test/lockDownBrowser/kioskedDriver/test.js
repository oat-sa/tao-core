define(['tao/lockDownBrowser/drivers/kioskedDriver'], function (kioskedDriver) {
    'use strict';

    QUnit.module('kioskedDriver');

    function createApi(isLocked) {
        return {
            getDeviceInfo: function () {},
            lock: function () {},
            unlock: function () {},
            isLocked: isLocked || function () {
                return Promise.resolve(true);
            },
            exit: function () {},
            checkBlocklist: function () {
                return Promise.resolve([]);
            }
        };
    }

    function createDriver() {
        return kioskedDriver.createKioskedDriver({
            lockWaitMs: 0
        });
    }

    function assertDeviceInfoTimeout(assert, options, expectedTimeout) {
        const originalKiosked = window.kiosked;
        const originalSetTimeout = window.setTimeout;
        const api = createApi();
        let timeoutDelay;

        api.getDeviceInfo = function () {
            return new Promise(function () {});
        };
        window.kiosked = api;
        window.setTimeout = function (callback, delay) {
            timeoutDelay = delay;
            return originalSetTimeout(callback, 0);
        };

        return kioskedDriver
            .createKioskedDriver(options)
            .validateMinVersion()
            .then(
                function () {
                    assert.ok(false, 'validation should reject a device info timeout');
                },
                function (error) {
                    assert.equal(timeoutDelay, expectedTimeout, 'the expected timeout is used');
                    assert.equal(
                        error.message,
                        `Unable to get lock down browser device info within ${expectedTimeout} ms`
                    );
                }
            )
            .finally(function () {
                window.setTimeout = originalSetTimeout;
                window.kiosked = originalKiosked;
            });
    }

    QUnit.test('ignores the outdated window.kiosk API', function (assert) {
        const originalKiosked = window.kiosked;
        const originalKiosk = window.kiosk;

        window.kiosked = null;
        window.kiosk = {
            isLocked: function () {
                return true;
            }
        };

        assert.strictEqual(kioskedDriver.resolveNativeApiHandle(), null);

        window.kiosked = originalKiosked;
        window.kiosk = originalKiosk;
    });

    QUnit.test('resolves the documented window.kiosked API', function (assert) {
        const originalKiosked = window.kiosked;
        const api = createApi();

        window.kiosked = api;

        assert.deepEqual(kioskedDriver.resolveNativeApiHandle(), {
            kind: 'kiosked',
            api: api
        });

        window.kiosked = originalKiosked;
    });

    QUnit.test('rejects an incomplete window.kiosked API', function (assert) {
        const originalKiosked = window.kiosked;

        window.kiosked = {};

        assert.strictEqual(kioskedDriver.resolveNativeApiHandle(), null);

        window.kiosked = originalKiosked;
    });

    QUnit.test('uses an exit-only window.kiosked API to close the application', function (assert) {
        const originalKiosked = window.kiosked;
        let exitCalls = 0;

        window.kiosked = {
            exit: function () {
                exitCalls++;
            }
        };

        assert.strictEqual(kioskedDriver.resolveNativeExitApi(), window.kiosked);
        kioskedDriver.createKioskedDriver().exitApplication();
        assert.equal(exitCalls, 1);

        window.kiosked = originalKiosked;
    });

    QUnit.test('does not suppress the native breach behavior', function (assert) {
        const done = assert.async();
        const originalKiosked = window.kiosked;
        const api = createApi();
        let breachListener;
        let preventDefaultCalled = false;

        api.addEventListener = function (type, listener) {
            assert.strictEqual(type, 'breach');
            breachListener = listener;
        };
        api.getProcessList = function () {
            return Promise.resolve([]);
        };
        window.kiosked = api;

        createDriver().addSecurityViolationListener(function (detail) {
            assert.strictEqual(detail.reason, 'blocked-application');
            assert.notOk(preventDefaultCalled, 'Kiosked remains responsible for its native error screen');
            window.kiosked = originalKiosked;
            done();
        });

        breachListener({
            reason: 'blocked-application',
            preventDefault: function () {
                preventDefaultCalled = true;
            }
        });
    });

    QUnit.test('subscribes to and removes multiple application events', function (assert) {
        const originalKiosked = window.kiosked;
        const api = createApi();
        const listeners = {};
        const removed = [];
        const received = [];
        const eventTypes = ['lost-focus', 'display-added', 'online'];
        const driver = createDriver();

        api.addEventListener = function (type, listener) {
            listeners[type] = listener;
        };
        api.removeEventListener = function (type, listener) {
            assert.strictEqual(listener, listeners[type], `the ${type} listener is removed by reference`);
            removed.push(type);
        };
        window.kiosked = api;

        driver.addEventListeners(eventTypes, function (event) {
            received.push(event);
        });

        listeners['lost-focus']({ reason: 'focus-lost' });

        assert.deepEqual(Object.keys(listeners), eventTypes, 'all requested events are registered');
        assert.strictEqual(received[0].type, 'lost-focus');
        assert.strictEqual(received[0].reason, 'focus-lost');
        assert.ok(received[0].occurredAt, 'the occurrence time is captured by the driver');

        driver.removeEventListeners();

        assert.deepEqual(removed, eventTypes, 'all registered events are removed');
        window.kiosked = originalKiosked;
    });

    QUnit.test('returns false when isLocked fails', function (assert) {
        const done = assert.async();
        const originalKiosked = window.kiosked;

        window.kiosked = createApi(function () {
            return Promise.reject(new Error('Native API failed'));
        });

        createDriver()
            .whenAvailable()
            .then(function (available) {
                assert.strictEqual(available, false);
                window.kiosked = originalKiosked;
                done();
            });
    });

    QUnit.test('waits for lockdown mode after the native API becomes available', function (assert) {
        const done = assert.async();
        const originalKiosked = window.kiosked;
        let locked = false;

        window.kiosked = createApi(function () {
            return Promise.resolve(locked);
        });

        createDriver()
            .whenAvailable()
            .then(function (available) {
                assert.strictEqual(available, false, 'lockdown is not active yet');
            })
            .then(function () {
                locked = true;

                return kioskedDriver.createKioskedDriver({
                    lockWaitMs: 500,
                    lockPollMs: 25
                }).whenAvailable();
            })
            .then(function (available) {
                assert.strictEqual(available, true, 'lockdown becomes active while waiting');
                window.kiosked = originalKiosked;
                done();
            });
    });

    QUnit.test('rejects when device info does not contain an app version', function (assert) {
        const originalKiosked = window.kiosked;
        const api = createApi();

        api.getDeviceInfo = function () {
            return Promise.resolve({ app: {} });
        };
        window.kiosked = api;

        return createDriver()
            .validateMinVersion()
            .then(
                function () {
                    assert.ok(false, 'validation should reject incomplete device info');
                },
                function (error) {
                    assert.equal(
                        error.message,
                        'Unable to get lock down browser device info: app version is missing'
                    );
                }
            )
            .finally(function () {
                window.kiosked = originalKiosked;
            });
    });

    QUnit.test('rejects when the installed version is below the configured minimum', function (assert) {
        const originalKiosked = window.kiosked;
        const api = createApi();

        api.getDeviceInfo = function () {
            return Promise.resolve({
                app: {
                    version: '1.2.3'
                }
            });
        };
        window.kiosked = api;

        return kioskedDriver
            .createKioskedDriver({
                minVersion: '2.0.0',
                lockWaitMs: 0
            })
            .validateMinVersion()
            .then(
                function () {
                    assert.ok(false, 'validation should reject an outdated version');
                },
                function (error) {
                    assert.equal(
                        error.message,
                        'Lock down browser version 1.2.3 is lower than required minimum version 2.0.0'
                    );
                }
            )
            .finally(function () {
                window.kiosked = originalKiosked;
            });
    });

    QUnit.test('uses the configured timeout when device info does not resolve', function (assert) {
        return assertDeviceInfoTimeout(assert, { ldbApiTimeout: 1234 }, 1234);
    });

    QUnit.test('defaults the device info timeout to ten seconds', function (assert) {
        return assertDeviceInfoTimeout(assert, { lockWaitMs: 0 }, 10000);
    });

    QUnit.test('calls native checkBlocklist with vendor map and showError flag', function (assert) {
        const done = assert.async();
        const originalKiosked = window.kiosked;
        let capturedVendorMap;
        let capturedShowError;
        const api = createApi();
        const processList = [
            {
                process: 'calculator',
                label: 'Calculator'
            }
        ];

        api.checkBlocklist = function (vendorMap, showError) {
            capturedVendorMap = vendorMap;
            capturedShowError = showError;

            return Promise.resolve(['calculator']);
        };

        window.kiosked = api;

        createDriver()
            .checkDeniedProcesses(processList)
            .then(function (blocked) {
                assert.deepEqual(capturedVendorMap, {
                    calculator: 'Calculator'
                });
                assert.strictEqual(capturedShowError, true);
                assert.deepEqual(blocked, ['Calculator']);
                window.kiosked = originalKiosked;
                done();
            });
    });
});
