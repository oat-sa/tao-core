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
