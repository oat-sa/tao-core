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
    'tao/lockDownBrowser/drivers/kioskedDriver',
    'tao/lockDownBrowser/drivers/mockDriver',
    'tao/lockDownBrowser/lockDownBrowserUiProtocol'
], function ($, __, kioskedDriverModule, mockDriverModule, uiProtocol) {
    'use strict';

    var DRIVER_KIOSKED = 'kiosked';
    var DRIVER_KIOSKED_MOCK = 'kioskedMock';

    var exitBarStyles = {
        position: 'fixed',
        bottom: 0,
        left: 0,
        right: 0,
        zIndex: 10000,
        padding: '12px 16px',
        textAlign: 'center',
        background: 'rgba(0, 0, 0, 0.85)'
    };

    var exitButtonStyles = {
        minWidth: '240px'
    };

    function removeExitBar(mountDocument) {
        var doc = mountDocument || document;

        $(doc.getElementById(uiProtocol.EXIT_BAR_ELEMENT_ID)).remove();
    }

    /**
     * @param {Object} [options]
     * @param {Document} [options.mountDocument]
     * @param {string} [options.label]
     * @param {Function} [options.onExitClick] ({ $bar, mountDocument })
     */
    function mountExitBar(options) {
        options = options || {};
        var mountDocument = options.mountDocument || document;

        if (mountDocument.getElementById(uiProtocol.EXIT_BAR_ELEMENT_ID)) {
            return null;
        }

        var label = options.label != null ? options.label : __('Exit the application');
        var $bar = $('<div id="' + uiProtocol.EXIT_BAR_ELEMENT_ID + '" class="' + uiProtocol.EXIT_BAR_CSS_CLASS + '">' +
            '<button type="button" class="' + uiProtocol.EXIT_BUTTON_CSS_CLASS + ' btn btn-primary">' +
            label +
            '</button></div>');

        $bar.css(exitBarStyles);
        $bar.find('button').css(exitButtonStyles).on('click', function () {
            if (typeof options.onExitClick === 'function') {
                options.onExitClick({ $bar: $bar, mountDocument: mountDocument });
            }
        });

        $(mountDocument.body).append($bar);

        return $bar;
    }

    function resolveDriverId(config) {
        var driver = config && config.driver;

        if (driver === DRIVER_KIOSKED) {
            return DRIVER_KIOSKED;
        }

        return DRIVER_KIOSKED_MOCK;
    }

    function createLockDownBrowserService(config) {
        var driverId = resolveDriverId(config);
        var driverOptions = {
            minVersion: config && config.minVersion,
            mockActiveProcesses: config && config.mockActiveProcesses
        };
        var driver;

        if (driverId === DRIVER_KIOSKED) {
            driver = kioskedDriverModule.createKioskedDriver(driverOptions);
        } else {
            driver = mockDriverModule.createMockDriver(driverOptions);
        }

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
        return !!kioskedDriverModule.resolveNativeApiHandle();
    }

    return {
        DRIVER_KIOSKED: DRIVER_KIOSKED,
        DRIVER_KIOSKED_MOCK: DRIVER_KIOSKED_MOCK,
        uiProtocol: uiProtocol,
        mountExitBar: mountExitBar,
        removeExitBar: removeExitBar,
        createLockDownBrowserService: createLockDownBrowserService,
        isNativeLockDownBrowserPresent: isNativeLockDownBrowserPresent
    };
});
