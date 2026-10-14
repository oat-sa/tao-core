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
 * Shared UI / cross-frame protocol for lock down browser (runner + delivery).
 */
define([], function () {
    'use strict';

    var MESSAGE_DEFER_FINISH = 'taoLockDownBrowserDeferFinish';
    var MESSAGE_EXIT = 'taoLockDownBrowserExit';
    var PENDING_EXIT_STORAGE_KEY = 'taoLockDownBrowserPendingExit';
    var LEGACY_MESSAGE_DEFER_FINISH = 'taoKioskLockdownDeferFinish';
    var LEGACY_MESSAGE_EXIT = 'taoKioskLockdownExit';
    var LEGACY_PENDING_EXIT_STORAGE_KEY = 'taoKioskLockdownPendingExit';

    return {
        MESSAGE_DEFER_FINISH: MESSAGE_DEFER_FINISH,
        MESSAGE_EXIT: MESSAGE_EXIT,
        PENDING_EXIT_STORAGE_KEY: PENDING_EXIT_STORAGE_KEY,
        EXIT_BAR_ELEMENT_ID: 'lockdown-browser-exit-bar',
        EXIT_BAR_CSS_CLASS: 'lockdown-browser-exit-bar',
        EXIT_BUTTON_CSS_CLASS: 'lockdown-browser-exit-button',
        BREACH_TRACE_KEY_PREFIX: 'lockdown-breach-',

        isDeferFinishMessage: function isDeferFinishMessage(type) {
            return type === MESSAGE_DEFER_FINISH || type === LEGACY_MESSAGE_DEFER_FINISH;
        },

        isExitMessage: function isExitMessage(type) {
            return type === MESSAGE_EXIT || type === LEGACY_MESSAGE_EXIT;
        },

        hasPendingExitInStorage: function hasPendingExitInStorage(storage) {
            try {
                if (storage.getItem(PENDING_EXIT_STORAGE_KEY) === '1') {
                    return true;
                }

                return storage.getItem(LEGACY_PENDING_EXIT_STORAGE_KEY) === '1';
            } catch (err) {
                return false;
            }
        },

        clearPendingExitInStorage: function clearPendingExitInStorage(storage) {
            try {
                storage.removeItem(PENDING_EXIT_STORAGE_KEY);
                storage.removeItem(LEGACY_PENDING_EXIT_STORAGE_KEY);
            } catch (err) {
                // ponytail: ignore
            }
        }
    };
});
