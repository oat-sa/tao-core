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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

/**
 * A minimal dialog used when the client config cannot be loaded.
 * Its minimal AMD dependencies are configured by bootstrap.js before the
 * client config request starts.
 *
 * @returns {Function} the dialog factory
 */
define([
    'i18n',
    'css!taoCss/configRetry.css'
], __ => {
    'use strict';

    return (options = {}) => {
        let overlay = null;
        let modal = null;
        let refreshButton = null;
        let previousFocus = null;

        const keepFocusInside = event => {
            if (event.key !== 'Tab') {
                return;
            }

            event.preventDefault();
            refreshButton.focus();
        };

        const render = () => {
            const container = document.body || document.documentElement;
            const modalBody = document.createElement('div');
            const message = document.createElement('p');
            const buttons = document.createElement('div');

            overlay = document.createElement('div');
            overlay.className = 'modal-bg config-retry-modal-bg';

            modal = document.createElement('div');
            modal.className = 'modal config-retry-modal';
            modal.setAttribute('role', 'alertdialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'config-retry-message');
            modal.addEventListener('keydown', keepFocusInside);

            modalBody.className = 'modal-body';
            message.id = 'config-retry-message';
            message.className = 'message';
            message.textContent = options.message
                || __('Unable to load the required configuration. Refresh the page to try again.');
            buttons.className = 'buttons';

            refreshButton = document.createElement('button');
            refreshButton.type = 'button';
            refreshButton.className = 'btn-info';
            refreshButton.textContent = options.refreshLabel || __('Refresh');
            refreshButton.addEventListener('click', () => {
                refreshButton.disabled = true;
                if (typeof options.onRefresh === 'function') {
                    options.onRefresh();
                } else {
                    window.location.reload();
                }
            });

            buttons.appendChild(refreshButton);
            modalBody.appendChild(message);
            modalBody.appendChild(buttons);
            modal.appendChild(modalBody);
            overlay.appendChild(modal);
            container.appendChild(overlay);
        };

        return {
            show() {
                if (!modal) {
                    previousFocus = document.activeElement;
                    render();
                }
                refreshButton.disabled = false;
                refreshButton.focus();
            },
            destroy() {
                const focusToRestore = previousFocus;

                if (modal) {
                    modal.removeEventListener('keydown', keepFocusInside);
                }
                if (modal && modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
                if (overlay && overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
                modal = null;
                overlay = null;
                refreshButton = null;
                previousFocus = null;

                if (focusToRestore && typeof focusToRestore.focus === 'function') {
                    focusToRestore.focus();
                }
            }
        };
    };
});
