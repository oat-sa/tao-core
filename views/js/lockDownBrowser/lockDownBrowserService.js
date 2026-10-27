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
 * Lock down browser facade — selects vendor driver from client config.
 */
define([
    'jquery',
    'module',
    'tao/lockDownBrowser/drivers/kioskedDriver',
    'tao/lockDownBrowser/drivers/mockDriver',
    'tao/lockDownBrowser/lockDownBrowserEventBus',
    'tao/lockDownBrowser/lockDownBrowserUiProtocol'
], function ($, module, kioskedDriverModule, mockDriverModule, eventBus, uiProtocol) {
    'use strict';

    const DRIVER_KIOSKED = 'kiosked';
    const DRIVER_KIOSKED_MOCK = 'kioskedMock';
    const EVENT_TYPES = kioskedDriverModule.EVENT_TYPES;
    const EXIT_MENU_SELECTOR = '[data-control="exit"]';
    const EXIT_MENU_EVENT = 'click.lockDownBrowserExit';
    const USER_MENU_READY_EVENT = 'usermenurendered.lockDownBrowserExit';

    function revealExitMenu(mountDocument) {
        const $exit = $(mountDocument).find(EXIT_MENU_SELECTOR);

        $exit.removeClass('hidden');

        return $exit;
    }

    function removeExitMenu(mountDocument = document) {
        $(mountDocument).off(EXIT_MENU_EVENT, EXIT_MENU_SELECTOR);
        eventBus.off(USER_MENU_READY_EVENT).disableExitMenu();
        $(mountDocument).find(EXIT_MENU_SELECTOR).addClass('hidden');
    }

    /**
     * @param {Object} [options]
     * @param {Document} [options.mountDocument]
     * @param {Function} [options.onExitClick]
     * @returns {jQuery|null}
     */
    function mountExitMenu(options = {}) {
        const mountDocument = options.mountDocument || document;
        const $document = $(mountDocument);

        eventBus.enableExitMenu();
        const $exit = revealExitMenu(mountDocument);
        $document.off(EXIT_MENU_EVENT, EXIT_MENU_SELECTOR).on(EXIT_MENU_EVENT, EXIT_MENU_SELECTOR, event => {
            event.preventDefault();
            if (typeof options.onExitClick === 'function') {
                options.onExitClick();
            }
        });
        eventBus.off(USER_MENU_READY_EVENT).on(USER_MENU_READY_EVENT, () => revealExitMenu(mountDocument));

        return $exit.length ? $exit : null;
    }

    /**
     * Caller config wins over lib module.config. Explicit mock is always mock.
     * When a real driver is configured, use it; otherwise auto-detect native API.
     * @param {Object} [config]
     * @returns {string}
     */
    function resolveDriverId(config = {}) {
        const moduleConfig = module.config() || {};
        const configuredDriver = config.driver || moduleConfig.driver;

        if (configuredDriver === DRIVER_KIOSKED_MOCK) {
            return DRIVER_KIOSKED_MOCK;
        }

        if (configuredDriver === DRIVER_KIOSKED) {
            return DRIVER_KIOSKED;
        }

        if (kioskedDriverModule.resolveNativeApiHandle()) {
            return DRIVER_KIOSKED;
        }

        return DRIVER_KIOSKED_MOCK;
    }

    function createLockDownBrowserService(config = {}) {
        const driverId = resolveDriverId(config);
        const driverOptions = {
            minVersion: config.minVersion,
            mockActiveProcesses: config.mockActiveProcesses
        };
        const driver =
            driverId === DRIVER_KIOSKED
                ? kioskedDriverModule.createKioskedDriver(driverOptions)
                : mockDriverModule.createMockDriver(driverOptions);

        return {
            getDriverId: () => driver.id,

            isMockDriver: () => driver.id === DRIVER_KIOSKED_MOCK,

            whenAvailable: () => driver.whenAvailable(),

            isSessionActive: () => driver.isSessionActive(),

            validateMinVersion: () => driver.validateMinVersion(),

            addEventListeners: (eventTypes, callback) => driver.addEventListeners(eventTypes, callback),

            removeEventListeners: () => driver.removeEventListeners(),

            addSecurityViolationListener: callback => driver.addSecurityViolationListener(callback),

            removeSecurityViolationListener: () => driver.removeSecurityViolationListener(),

            exitApplication: () => driver.exitApplication(),

            checkDeniedProcesses: processBlackList => driver.checkDeniedProcesses(processBlackList),

            simulateSecurityViolation: detail => driver.simulateSecurityViolation(detail)
        };
    }

    function isNativeLockDownBrowserPresent() {
        return !!kioskedDriverModule.resolveNativeApiHandle();
    }

    function isLockDownBrowserContext(service) {
        return !!(service && service.isMockDriver()) || !!kioskedDriverModule.resolveNativeExitApi();
    }

    return {
        DRIVER_KIOSKED,
        DRIVER_KIOSKED_MOCK,
        EVENT_TYPES,
        uiProtocol,
        mountExitMenu,
        removeExitMenu,
        createLockDownBrowserService,
        isNativeLockDownBrowserPresent,
        isLockDownBrowserContext
    };
});
