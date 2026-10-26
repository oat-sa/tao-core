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
            onBreach: function () {},
            exit: function () {}
        };
    }

    QUnit.test('ignores the outdated window.kiosk API', function (assert) {
        var originalKiosked = window.kiosked;
        var originalKiosk = window.kiosk;

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

    QUnit.test('resolves only the window.kiosked API', function (assert) {
        var originalKiosked = window.kiosked;
        var api = createApi();

        window.kiosked = api;

        assert.deepEqual(kioskedDriver.resolveNativeApiHandle(), {
            kind: 'kiosked',
            api: api
        });

        window.kiosked = originalKiosked;
    });

    QUnit.test('rejects an incomplete window.kiosked API', function (assert) {
        var originalKiosked = window.kiosked;

        window.kiosked = {};

        assert.strictEqual(kioskedDriver.resolveNativeApiHandle(), null);

        window.kiosked = originalKiosked;
    });

    QUnit.test('uses an exit-only window.kiosked API to close the application', function (assert) {
        var originalKiosked = window.kiosked;
        var exitCalls = 0;

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

    QUnit.test('returns false when isLocked throws', function (assert) {
        var done = assert.async();
        var originalKiosked = window.kiosked;

        window.kiosked = createApi(function () {
            throw new Error('Native API failed');
        });

        kioskedDriver
            .createKioskedDriver()
            .whenAvailable()
            .then(function (available) {
                assert.strictEqual(available, false);
            })
            .then(function () {
                window.kiosked = originalKiosked;
                done();
            });
    });

    QUnit.test('returns false when isLocked rejects', function (assert) {
        var done = assert.async();
        var originalKiosked = window.kiosked;

        window.kiosked = createApi(function () {
            return Promise.reject(new Error('Native API failed'));
        });

        kioskedDriver
            .createKioskedDriver()
            .whenAvailable()
            .then(function (available) {
                assert.strictEqual(available, false);
            })
            .then(function () {
                window.kiosked = originalKiosked;
                done();
            });
    });

    QUnit.test('returns false when isLocked times out', function (assert) {
        var done = assert.async();
        var originalKiosked = window.kiosked;
        var originalSetTimeout = window.setTimeout;

        window.kiosked = createApi(function () {
            return new Promise(function () {});
        });
        window.setTimeout = function (callback) {
            return originalSetTimeout(callback, 0);
        };

        kioskedDriver
            .createKioskedDriver()
            .whenAvailable()
            .then(function (available) {
                assert.strictEqual(available, false);
            })
            .then(function () {
                window.setTimeout = originalSetTimeout;
                window.kiosked = originalKiosked;
                done();
            });
    });
});
