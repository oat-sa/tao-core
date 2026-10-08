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
define([
    'module',
    'lodash',
    'core/logger',
    'tao/ckeditor/wproofreaderExclude'
], function (module, _, logger, wproofreaderExclude) {
    'use strict';

    var log = logger('tao/ckeditor/wproofreaderBootstrap');
    var defaults = {
        enabled: false,
        serviceId: '',
        srcUrl: 'https://svc.webspellchecker.net/spellcheck31/wscbundle/wscbundle.js',
        lang: 'auto',
        autoSearch: true,
        localization: 'en',
        theme: 'gray',
        minWordLength: 3,
        autocorrect: false,
        enableGrammar: false,
        disableStyleGuide: true,
        spellingSuggestions: true,
        grammarSuggestions: false,
        styleGuideSuggestions: false,
        settingsSections: ['languages'],
        detectLocalizationLanguage: false,
        ignoreAllCapsWords: true,
        enableBadgeButton: false,
        requestTokensCount: 3,
        disableDialog: true,
        disableOptionsStorage: ['lang'],
        actionItems: ['ignoreAll', 'settings'],
        cache: true
    };

    var config = _.defaults({}, module.config() || {}, defaults);
    var enabled = config.enabled === true && !!config.serviceId;
    var scriptLoaded = false;
    var scriptLoading = null;

    if (config.enabled && !config.serviceId) {
        log.warn('WProofreader is enabled but serviceId is missing; spell check will not start.');
    }

    function getWscConfig() {
        var exclude = wproofreaderExclude.buildWscExcludeOptions(config);
        return _.assign(
            {},
            _.omit(config, ['enabled', 'ignoreClasses', 'ignoreElements', 'ignoreAttributes', 'disableAutoSearchIn', 'enableAutoSearchIn']),
            exclude
        );
    }

    function loadScript() {
        if (scriptLoaded) {
            return Promise.resolve();
        }
        if (scriptLoading) {
            return scriptLoading;
        }

        injectBalloonStyle();

        window.WEBSPELLCHECKER_CONFIG = getWscConfig();

        scriptLoading = new Promise(function (resolve, reject) {
            var script = document.createElement('script');

            function fail(err) {
                scriptLoading = null;
                if (script.parentNode) {
                    script.parentNode.removeChild(script);
                }
                reject(err);
            }

            script.src = config.srcUrl;
            script.async = true;
            script.onload = function () {
                if (window.WEBSPELLCHECKER) {
                    scriptLoaded = true;
                    resolve();
                } else {
                    fail(new Error('WProofreader bundle loaded without defining WEBSPELLCHECKER: ' + config.srcUrl));
                }
            };
            script.onerror = function () {
                fail(new Error('Failed to load WProofreader bundle from ' + config.srcUrl));
            };
            document.head.appendChild(script);
        });

        return scriptLoading;
    }

    /**
     * Backoffice page CSS leaks into the WSC suggestion balloon.
     */
    var BALLOON_STYLE_ID = 'tao-wsc-balloon-fix';
    var BALLOON_CSS = [
        'html body .wsc-contextmenu__list,html body .wsc-contextmenu__list .wsc-contextmenu__item{list-style:none !important;}',
        'html body .wsc-contextmenu__list .wsc-contextmenu__icon{display:none !important;}'
    ].join('\n');

    function injectBalloonStyle(targetDocument) {
        var doc = targetDocument || document;
        if (!doc || !doc.head || !doc.createElement || doc.getElementById(BALLOON_STYLE_ID)) {
            return;
        }
        try {
            var style = doc.createElement('style');
            style.id = BALLOON_STYLE_ID;
            style.textContent = BALLOON_CSS;
            doc.head.appendChild(style);
        } catch (err) {
            log.warn('Unable to inject WProofreader balloon style: ' + (err && err.message));
        }
    }

    var bindAttempts = 0;
    var loadAttempts = 0;
    function scheduleLoad() {
        if (!enabled || scriptLoaded || scriptLoading) {
            return;
        }
        if (!window.CKEDITOR) {
            if (bindAttempts++ < 100) {
                setTimeout(scheduleLoad, 100);
            }
            return;
        }
        loadAttempts++;
        loadScript().catch(function (err) {
            log.error(err);
            if (loadAttempts < 3) {
                setTimeout(scheduleLoad, 5000);
            }
        });
    }

    return {
        enabled: enabled,
        start: scheduleLoad,
        getCkeditorConfig: function () {
            return { disableNativeSpellChecker: false };
        }
    };
});
