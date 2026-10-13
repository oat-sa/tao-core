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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */
define(['tao/ckeditor/wproofreaderBootstrap'], function (wproofreaderBootstrap) {
    'use strict';

    QUnit.module('wproofreaderBootstrap language');

    QUnit.test('applies the current language to existing and future instances', function (assert) {
        var originalAppendChild = document.head.appendChild;
        var originalCkeditor = window.CKEDITOR;
        var originalWproofreader = window.WEBSPELLCHECKER;
        var originalConfig = window.WEBSPELLCHECKER_CONFIG;
        var instances = [];
        var currentLanguage = 'en_US';
        var existingReloads = 0;
        var disabled = false;
        var disableOptions;
        var enableOptions;
        var existingInstance = {
            getLangList: function () {
                return {
                    ltr: {
                        fr_FR: 'French',
                        de_DE: 'German'
                    },
                    rtl: {}
                };
            },
            getLang: function () {
                return currentLanguage;
            },
            setLang: function (lang, options) {
                currentLanguage = lang;
                assert.deepEqual(options, { ignoreCallback: true }, 'language callbacks are not emitted');
            },
            reloadMarkup: function () {
                existingReloads++;
            },
            isDisabled: function () {
                return disabled;
            },
            disable: function (options) {
                disabled = true;
                disableOptions = options;
            },
            enable: function (options) {
                disabled = false;
                enableOptions = options;
            }
        };
        var futureLanguage = 'en_US';
        var futureReloads = 0;
        var futureInstance = {
            getLang: function () {
                return futureLanguage;
            },
            setLang: function (lang) {
                futureLanguage = lang;
            },
            reloadMarkup: function () {
                futureReloads++;
            }
        };

        assert.expect(16);

        window.CKEDITOR = {};
        document.head.appendChild = function (element) {
            if (element.tagName === 'SCRIPT') {
                assert.strictEqual(window.WEBSPELLCHECKER_CONFIG.lang, 'fr_FR', 'pending language configures the bundle');
                window.WEBSPELLCHECKER = {
                    getInstances: function () {
                        return instances;
                    }
                };
                element.onload();
                return element;
            }
            return originalAppendChild.call(this, element);
        };

        try {
            wproofreaderBootstrap.setLanguage('fr_FR');
            wproofreaderBootstrap.start();

            instances.push(existingInstance);
            wproofreaderBootstrap.setLanguage('de_DE');
            assert.strictEqual(currentLanguage, 'de_DE', 'existing instance receives the changed language');
            assert.strictEqual(existingReloads, 1, 'existing markup is rechecked');
            assert.strictEqual(window.WEBSPELLCHECKER_CONFIG.lang, 'de_DE', 'global config tracks the changed language');

            window.WEBSPELLCHECKER_CONFIG.onLoad(futureInstance);
            assert.strictEqual(futureLanguage, 'de_DE', 'future instance receives the latest language');
            assert.strictEqual(futureReloads, 1, 'markup is rechecked when a future instance has a stale language');

            wproofreaderBootstrap.setLanguage('pl_PL');
            assert.deepEqual(disableOptions, { ignoreCallback: true }, 'unsupported languages disable checking silently');
            assert.strictEqual(disabled, true, 'instance is disabled for an unsupported language');
            assert.strictEqual(currentLanguage, 'de_DE', 'unsupported language is not applied');
            assert.strictEqual(existingReloads, 1, 'unsupported language does not recheck with another dictionary');

            wproofreaderBootstrap.setLanguage('de_DE');
            assert.deepEqual(enableOptions, { ignoreCallback: true }, 'compatible languages re-enable checking silently');
            assert.strictEqual(disabled, false, 'instance is re-enabled for a compatible language');

            wproofreaderBootstrap.setLanguage('');
            assert.strictEqual(disabled, true, 'instance is disabled when the item has no language');
            assert.strictEqual(window.WEBSPELLCHECKER_CONFIG.autoStartup, false, 'future instances do not start without a language');
            assert.strictEqual(window.WEBSPELLCHECKER_CONFIG.lang, '', 'missing language does not retain or invent a fallback');
        } finally {
            document.head.appendChild = originalAppendChild;
            window.CKEDITOR = originalCkeditor;
            window.WEBSPELLCHECKER = originalWproofreader;
            window.WEBSPELLCHECKER_CONFIG = originalConfig;
        }
    });
});
