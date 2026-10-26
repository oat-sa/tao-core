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

QUnit.module('Client config bootstrap');

QUnit.test('offers a translated refresh action when config loading fails', function(assert) {
    var initialRequest = window.configBootstrapRequests[0];
    var overlay;
    var modal;
    var message;
    var refreshButton;

    assert.strictEqual(
        initialRequest.dependencies[0],
        '/tao/ClientConfig/config?extension=tao',
        'The client config is requested during bootstrap'
    );
    assert.strictEqual(
        window.bootstrapRequireConfig.paths.i18ntr,
        '../locales/it-IT',
        'The translation bundle matches the page language'
    );

    initialRequest.error(new Error('500'));
    overlay = document.querySelector('.config-retry-modal-bg');
    modal = document.querySelector('.config-retry-modal');
    message = modal.querySelector('.message');
    refreshButton = modal.querySelector('button');

    assert.ok(overlay, 'A background overlay is displayed');
    assert.strictEqual(getComputedStyle(overlay).display, 'flex', 'The background uses a flex layout');
    assert.strictEqual(getComputedStyle(overlay).alignItems, 'center', 'The modal is vertically centered');
    assert.strictEqual(getComputedStyle(overlay).justifyContent, 'center', 'The modal is horizontally centered');
    assert.strictEqual(getComputedStyle(modal).position, 'static', 'The modal participates in the flex layout');
    assert.strictEqual(getComputedStyle(modal).opacity, '1', 'The modal is visible');
    assert.strictEqual(getComputedStyle(message).fontSize, '16px', 'The message is displayed at 1.6rem');
    assert.strictEqual(
        message.textContent,
        'Impossibile caricare la configurazione richiesta. Aggiorna la pagina per riprovare.',
        'The frontend translated message is displayed'
    );
    assert.strictEqual(refreshButton.textContent, 'Aggiorna', 'The translated refresh label is displayed');
    assert.strictEqual(document.activeElement, refreshButton, 'The Refresh button receives focus');
    assert.strictEqual(window.configBootstrapRequests.length, 1, 'The assessment does not continue after failure');

    initialRequest.success();
    assert.notOk(document.querySelector('.config-retry-modal'), 'The modal is removed after config loads');
    assert.notOk(document.querySelector('.config-retry-modal-bg'), 'The background is removed after config loads');
});

QUnit.test('refresh action requests a page reload', function(assert) {
    var refreshRequested = false;
    var dialog = window.configRetryDialog({
        onRefresh: function(){
            refreshRequested = true;
        }
    });
    var refreshButton;

    dialog.show();
    refreshButton = document.querySelector('.config-retry-modal button');
    refreshButton.click();

    assert.ok(refreshRequested, 'Clicking Refresh invokes the page refresh action');
    assert.ok(refreshButton.disabled, 'The button is disabled while the page is refreshing');

    dialog.destroy();
});
