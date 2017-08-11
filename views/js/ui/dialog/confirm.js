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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define(['lodash', 'i18n', 'ui/dialog'], function (_, __, dialog) {
    'use strict';

    /**
     * Displays a confirm message
     * @param {String} message - The displayed message
     * @param {Function} accept - An action called when the message is accepted
     * @param {Function} refuse - An action called when the message is refused
     * @param {Object} options - Dialog options
     * @param {Object} options.buttons - Dialog button options
     * @param {Object} options.buttons.labels - Dialog button labels
     * @param {String} options.buttons.labels.ok - "OK" button label
     * @param {String} options.buttons.labels.cancel - "Cancel" button label
     * @returns {dialog} - Returns the dialog instance
     */
    return function dialogConfirm(message, accept, refuse, options) {
        var accepted = false;
        var _options = {
            buttons: {
                labels: {
                    ok: __('Ok'),
                    cancel: __('Cancel')
                }
            }
        };
        var dialogOptions;
        var dlg;
        options = _.defaults(options || {}, _options);
        dialogOptions = {
            message: message,
            autoRender: true,
            autoDestroy: true,
            onOkBtn: function() {
                accepted = true;
                if (_.isFunction(accept)) {
                    accept.call(this);
                }
            },
            buttons: {
                ok: {
                    id : 'ok',
                    type : 'info',
                    label : options.buttons.labels.ok || __('Ok'),
                    close: true
                },
                cancel: {
                    id : 'cancel',
                    type : 'regular',
                    label : options.buttons.labels.cancel || __('Cancel'),
                    close: true
                }
            }
        };
        dlg = dialog(dialogOptions);

        if (_.isFunction(refuse)) {
            dlg.on('closed.modal', function() {
                if (!accepted) {
                    refuse.call(this);
                }
            });
        }
        return dlg;
    };
});
