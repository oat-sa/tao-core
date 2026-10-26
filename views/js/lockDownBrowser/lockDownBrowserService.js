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
    'i18n',
    'module',
    'tao/lockDownBrowser/drivers/kioskedDriver',
    'tao/lockDownBrowser/drivers/mockDriver',
    'tao/lockDownBrowser/lockDownBrowserUiProtocol'
], function ($, __, module, kioskedDriverModule, mockDriverModule, uiProtocol) {
    'use strict';

    const DRIVER_KIOSKED = 'kiosked';
    const DRIVER_KIOSKED_MOCK = 'kioskedMock';
    const EVENT_TYPES = kioskedDriverModule.EVENT_TYPES;

    const exitBarStyles = {
        position: 'fixed',
        bottom: 0,
        left: 0,
        right: 0,
        zIndex: 10000,
        padding: '12px 16px',
        textAlign: 'center',
        background: 'rgba(0, 0, 0, 0.85)'
    };

    const exitButtonStyles = {
        minWidth: '240px'
    };

    function removeExitBar(mountDocument = document) {
        $(mountDocument.getElementById(uiProtocol.EXIT_BAR_ELEMENT_ID)).remove();
    }

    /**
     * @param {Object} [options]
     * @param {Document} [options.mountDocument]
     * @param {string} [options.label]
     * @param {Function} [options.onExitClick] ({ $bar, mountDocument })
     */
    function mountExitBar(options = {}) {
        const mountDocument = options.mountDocument || document;

        if (mountDocument.getElementById(uiProtocol.EXIT_BAR_ELEMENT_ID)) {
            return null;
        }

        const label = options.label != null ? options.label : __('Exit the application');
        const $bar = $(`<div id="${uiProtocol.EXIT_BAR_ELEMENT_ID}" class="${uiProtocol.EXIT_BAR_CSS_CLASS}">` +
            `<button type="button" class="${uiProtocol.EXIT_BUTTON_CSS_CLASS} btn btn-primary">${label}</button>` +
            '</div>');

        $bar.css(exitBarStyles);
        $bar.find('button').css(exitButtonStyles).on('click', () => {
            if (typeof options.onExitClick === 'function') {
                options.onExitClick({ $bar, mountDocument });
            }
        });

        $(mountDocument.body).append($bar);

        return $bar;
    }

    /**
     * Caller config wins over lib module.config. Explicit mock is always mock.
     * When a real driver is configured, use it; otherwise auto-detect native API.
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

    return {
        DRIVER_KIOSKED,
        DRIVER_KIOSKED_MOCK,
        EVENT_TYPES,
        uiProtocol,
        mountExitBar,
        removeExitBar,
        createLockDownBrowserService,
        isNativeLockDownBrowserPresent
    };
});
