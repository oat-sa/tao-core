define(['tao/lockDownBrowser/lockDownBrowserService'], function (lockDownBrowserService) {
    'use strict';

    QUnit.module('lockDownBrowserService');

    QUnit.test('uses the client library configured driver', function (assert) {
        var service = lockDownBrowserService.createLockDownBrowserService({
            driver: lockDownBrowserService.DRIVER_KIOSKED_MOCK
        });

        assert.equal(service.getDriverId(), lockDownBrowserService.DRIVER_KIOSKED);
    });
});
