define(['tao/lockDownBrowser/lockDownBrowserService'], function (lockDownBrowserModule) {
    'use strict';

    QUnit.module('lockDownBrowserService');

    function stubNativeKioskedApi() {
        return {
            getDeviceInfo: function () {},
            lock: function () {},
            unlock: function () {},
            isLocked: function () {
                return Promise.resolve(true);
            },
            exit: function () {},
            checkBlocklist: function () {
                return Promise.resolve([]);
            }
        };
    }

    QUnit.test('uses configured kiosked driver when requested', function (assert) {
        const service = lockDownBrowserModule.createLockDownBrowserService({
            driver: 'kiosked'
        });

        assert.strictEqual(service.getDriverId(), 'kiosked');
        assert.notOk(service.isMockDriver());
    });

    QUnit.test('uses mock when requested even if native API is present', function (assert) {
        const originalKiosked = window.kiosked;

        window.kiosked = stubNativeKioskedApi();

        const service = lockDownBrowserModule.createLockDownBrowserService({
            driver: 'kioskedMock'
        });

        assert.strictEqual(service.getDriverId(), 'kioskedMock');
        assert.ok(service.isMockDriver());

        window.kiosked = originalKiosked;
    });

    QUnit.test('selects kiosked when native API is present and no driver is configured', function (assert) {
        const originalKiosked = window.kiosked;

        window.kiosked = stubNativeKioskedApi();

        const service = lockDownBrowserModule.createLockDownBrowserService({});

        assert.strictEqual(service.getDriverId(), 'kiosked');
        assert.notOk(service.isMockDriver());

        window.kiosked = originalKiosked;
    });
});
