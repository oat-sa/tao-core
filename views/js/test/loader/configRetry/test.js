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

QUnit.module('Client config refresh dependencies');

QUnit.test('loads the translated dialog before client config', function(assert) {
    var done = assert.async();
    var observer;
    var timeout;
    var verifyDialog = function verifyDialog(){
        var overlay = document.querySelector('.config-retry-modal-bg');
        var modal;
        var refreshButton;

        if(!overlay){
            return;
        }

        observer.disconnect();
        window.clearTimeout(timeout);

        modal = overlay.querySelector('.config-retry-modal');
        refreshButton = modal.querySelector('button');

        assert.strictEqual(refreshButton.textContent, 'Refrescar', 'The frontend __ translation is used');
        assert.ok(
            document.querySelector('link[href*="configRetry.css"]'),
            'The dialog stylesheet is loaded through RequireJS'
        );
        assert.strictEqual(getComputedStyle(overlay).display, 'flex', 'The translated dialog is visible');

        overlay.parentNode.removeChild(overlay);
        done();
    };

    observer = new MutationObserver(verifyDialog);
    observer.observe(document.body, {childList: true});
    timeout = window.setTimeout(function(){
        observer.disconnect();
        assert.ok(false, 'The translated config failure dialog should load');
        done();
    }, 5000);
    verifyDialog();
});
