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

    const EXIT_MENU_SELECTOR = '[data-control="exit"]';
    const EXIT_MENU_EVENT = 'click.lockDownBrowserExit';
    const USER_MENU_READY_EVENT = 'usermenurendered.lockDownBrowserExit';

    function revealExitMenu(mountDocument) {
        const $exit = $(mountDocument).find(EXIT_MENU_SELECTOR);

        $exit.removeClass('hidden');

        return $exit;
    }

    function removeExitMenu(mountDocument) {
        const doc = mountDocument || document;

        $(doc).off(EXIT_MENU_EVENT, EXIT_MENU_SELECTOR);
        eventBus.off(USER_MENU_READY_EVENT).disableExitMenu();
        $(doc).find(EXIT_MENU_SELECTOR).addClass('hidden');
    }

    /**
     * @param {Object} [options]
     * @param {Document} [options.mountDocument]
     * @param {Function} [options.onExitClick]
     * @returns {jQuery|null}
     */
    function mountExitMenu(options) {
        options = options || {};
        const mountDocument = options.mountDocument || document;
        const $document = $(mountDocument);
        eventBus.enableExitMenu();
        const $exit = revealExitMenu(mountDocument);

        $document.off(EXIT_MENU_EVENT, EXIT_MENU_SELECTOR).on(EXIT_MENU_EVENT, EXIT_MENU_SELECTOR, function (event) {
            event.preventDefault();
            if (typeof options.onExitClick === 'function') {
                options.onExitClick();
            }
        });
        eventBus.off(USER_MENU_READY_EVENT).on(USER_MENU_READY_EVENT, function () {
            revealExitMenu(mountDocument);
        });

        return $exit.length ? $exit : null;
    }

    function resolveDriverId(config) {
        const moduleConfig = module.config() || {};
        const driver = moduleConfig.driver || (config && config.driver);

        if (driver === DRIVER_KIOSKED) {
            return DRIVER_KIOSKED;
        }

        return DRIVER_KIOSKED_MOCK;
    }

    function createLockDownBrowserService(config) {
        const driverId = resolveDriverId(config);
        const driverOptions = {
            minVersion: config && config.minVersion,
            mockActiveProcesses: config && config.mockActiveProcesses
        };
        const driver =
            driverId === DRIVER_KIOSKED
                ? kioskedDriverModule.createKioskedDriver(driverOptions)
                : mockDriverModule.createMockDriver(driverOptions);

        return {
            getDriverId: function getDriverId() {
                return driver.id;
            },

            isMockDriver: function isMockDriver() {
                return driver.id === DRIVER_KIOSKED_MOCK;
            },

            whenAvailable: function whenAvailable() {
                return driver.whenAvailable();
            },

            isSessionActive: function isSessionActive() {
                return driver.isSessionActive();
            },

            validateMinVersion: function validateMinVersion() {
                return driver.validateMinVersion();
            },

            addSecurityViolationListener: function addSecurityViolationListener(callback) {
                driver.addSecurityViolationListener(callback);
            },

            removeSecurityViolationListener: function removeSecurityViolationListener() {
                driver.removeSecurityViolationListener();
            },

            exitApplication: function exitApplication() {
                driver.exitApplication();
            },

            checkDeniedProcesses: function checkDeniedProcesses(processBlackList) {
                return driver.checkDeniedProcesses(processBlackList);
            },

            simulateSecurityViolation: function simulateSecurityViolation(detail) {
                return driver.simulateSecurityViolation(detail);
            }
        };
    }

    function isNativeLockDownBrowserPresent() {
        return !!kioskedDriverModule.resolveNativeExitApi();
    }

    function isLockDownBrowserContext(service) {
        return service.isMockDriver() || isNativeLockDownBrowserPresent();
    }

    return {
        DRIVER_KIOSKED: DRIVER_KIOSKED,
        DRIVER_KIOSKED_MOCK: DRIVER_KIOSKED_MOCK,
        uiProtocol: uiProtocol,
        mountExitMenu: mountExitMenu,
        removeExitMenu: removeExitMenu,
        createLockDownBrowserService: createLockDownBrowserService,
        isNativeLockDownBrowserPresent: isNativeLockDownBrowserPresent,
        isLockDownBrowserContext: isLockDownBrowserContext
    };
});
