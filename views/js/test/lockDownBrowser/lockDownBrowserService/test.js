define([
    'jquery',
    'tao/lockDownBrowser/lockDownBrowserEventBus',
    'tao/lockDownBrowser/lockDownBrowserService'
], function ($, eventBus, lockDownBrowserModule) {
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

    QUnit.test('shows and handles the header exit menu entry after dropdown rendering', function (assert) {
        let clicked = 0;
        const $exit = $('<li class="hidden" data-control="exit"><a href="#">Exit</a></li>')
            .appendTo('#qunit-fixture');

        lockDownBrowserModule.mountExitMenu({
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
        lockDownBrowserModule.removeExitMenu();
    });

    QUnit.test('recognizes mock and exit-capable Kiosked contexts', function (assert) {
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

        assert.ok(lockDownBrowserModule.isLockDownBrowserContext(mockService), 'mock context is recognized');

        window.kiosked = null;
        assert.notOk(lockDownBrowserModule.isLockDownBrowserContext(nativeService), 'ordinary browser is not Kiosked');

        window.kiosked = {
            exit: function () {}
        };

        assert.ok(lockDownBrowserModule.isLockDownBrowserContext(nativeService), 'exit-capable Kiosked context is recognized');

        window.kiosked = originalKiosked;
    });
});
