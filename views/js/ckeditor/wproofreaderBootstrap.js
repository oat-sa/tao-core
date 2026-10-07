/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
/**
 * Loads WebSpellChecker WProofreader for CKEditor 4 (inline and iframe modes).
 * Configure via ClientLibConfigRegistry key `tao/ckeditor/wproofreaderBootstrap`
 * (see SetWProofreaderConfig install action).
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
    var boundInstanceReady = false;

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

    function getContainer(editor) {
        if (editor.window && editor.window.getFrame && editor.window.getFrame()) {
            return editor.window.getFrame().$;
        }
        return editor.element.$;
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

    function onInstanceReady(editor) {
        try {
            if (!editor || editor._wproofreaderInitDone) {
                return;
            }
            editor._wproofreaderInitDone = true;

            editor.on('destroy', function () {
                try {
                    if (!window.WEBSPELLCHECKER || typeof window.WEBSPELLCHECKER.getInstances !== 'function') {
                        return;
                    }
                    var myContainer = null;
                    try {
                        myContainer = getContainer(editor);
                    } catch (ignored) {
                        myContainer = editor.element && editor.element.$;
                    }
                    window.WEBSPELLCHECKER.getInstances().forEach(function (inst) {
                        var node = null;
                        try {
                            node = inst.getContainerNode();
                        } catch (ignored) {
                            return;
                        }
                        if (!node || !myContainer) {
                            return;
                        }
                        var mine = node === myContainer ||
                            (myContainer.tagName === 'IFRAME' && myContainer.contentDocument &&
                                node.ownerDocument === myContainer.contentDocument);
                        if (mine && typeof inst.destroy === 'function') {
                            try {
                                inst.destroy();
                            } catch (ignored) {
                                log.warn('Unable to destroy WProofreader instance.');
                            }
                        }
                    });
                } catch (err) {
                    log.warn('WProofreader instance cleanup failed: ' + (err && err.message));
                }
            });

            loadScript().catch(function (err) {
                log.error(err);
            });
        } catch (err) {
            log.error('WProofreader init failed for editor: ' + (err && err.message));
        }
    }

    function bindInstanceReady() {
        if (!enabled || boundInstanceReady || !window.CKEDITOR) {
            return;
        }
        boundInstanceReady = true;

        window.CKEDITOR.on('instanceReady', function (evt) {
            onInstanceReady(evt.editor);
        });

        var instances = window.CKEDITOR.instances || {};
        Object.keys(instances).forEach(function (name) {
            var editor = instances[name];
            if (editor && editor.status === 'ready') {
                onInstanceReady(editor);
            }
        });
    }

    var bindAttempts = 0;
    function scheduleBind() {
        if (!enabled || boundInstanceReady) {
            return;
        }
        if (window.CKEDITOR) {
            bindInstanceReady();
            return;
        }
        bindAttempts++;
        if (bindAttempts > 100) {
            log.error('WProofreader init skipped: window.CKEDITOR never appeared.');
            return;
        }
        setTimeout(scheduleBind, 100);
    }

    scheduleBind();

    return {
        enabled: enabled,
        getCkeditorConfig: function () {
            // CKEditor disables the native spell checker by default.
            // Keep it disabled only while the premium provider marks errors,
            // otherwise let the browser underline natively.
            return { disableNativeSpellChecker: enabled };
        }
    };
});
