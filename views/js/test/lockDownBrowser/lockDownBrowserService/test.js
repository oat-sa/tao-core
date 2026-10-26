define([
    'jquery',
    'tao/lockDownBrowser/lockDownBrowserEventBus',
    'tao/lockDownBrowser/lockDownBrowserService'
], function ($, eventBus, lockDownBrowserService) {
    'use strict';

    QUnit.module('lockDownBrowserService');

    QUnit.test('uses the client library configured driver', function (assert) {
        const service = lockDownBrowserService.createLockDownBrowserService({
            driver: lockDownBrowserService.DRIVER_KIOSKED_MOCK
        });

        assert.equal(service.getDriverId(), lockDownBrowserService.DRIVER_KIOSKED);
    });

    QUnit.test('shows and handles the header exit menu entry after dropdown rendering', function (assert) {
        let clicked = 0;
        const $exit = $('<li class="hidden" data-control="exit"><a href="#">Exit</a></li>')
            .appendTo('#qunit-fixture');

        lockDownBrowserService.mountExitMenu({
            onExitClick: function () {
                clicked++;
            }
        });
        $exit.find('a').trigger('click');

        assert.ok(eventBus.isExitMenuEnabled(), 'the kiosk menu state is persisted');
        assert.notOk($exit.hasClass('hidden'), 'the menu entry is visible');
        assert.equal(clicked, 1, 'the exit callback is called');

        const $dropdownExit = $('<div class="dropdown-container"><li class="hidden" data-control="exit">Exit</li></div>')
            .appendTo('#qunit-fixture')
            .find('[data-control="exit"]');
        eventBus.trigger('usermenurendered');

        assert.notOk($dropdownExit.hasClass('hidden'), 'a later-rendered dropdown entry is visible');
        lockDownBrowserService.removeExitMenu();
    });

    QUnit.test('recognizes mock and native Kiosked contexts', function (assert) {
        const originalKiosked = window.kiosked;
        const mockService = {
            isMockDriver: function () {
                return true;
            }
        };
        const nativeService = {
            isMockDriver: function () {
                return false;
            }
        };

        assert.ok(lockDownBrowserService.isLockDownBrowserContext(mockService), 'mock context is recognized');

        window.kiosked = null;
        assert.notOk(lockDownBrowserService.isLockDownBrowserContext(nativeService), 'ordinary browser is not Kiosked');

        window.kiosked = {
            exit: function () {}
        };

        assert.ok(lockDownBrowserService.isLockDownBrowserContext(nativeService), 'exit-capable Kiosked context is recognized');

        window.kiosked = originalKiosked;
    });
});
