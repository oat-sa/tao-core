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
        // Vendor auto-search (focus polling + instance backstop) stays ON but is
        // contained by enableAutoSearchIn: only CKEditor editables are eligible,
        // so the backoffice chrome that used to crash the global scan is excluded
        // by construction. autoSearch:false put ITS instances to sleep entirely.
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

    var markerOptions = {
        markerClass: wproofreaderExclude.MARKER_CLASS,
        markerAttribute: wproofreaderExclude.MARKER_ATTR
    };

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
            script.src = config.srcUrl;
            script.async = true;
            script.onload = function () {
                scriptLoaded = true;
                resolve();
            };
            script.onerror = function () {
                reject(new Error('Failed to load WProofreader bundle from ' + config.srcUrl));
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
     * Backoffice page CSS leaks into the WSC suggestion balloon (stray list
     * bullets, decorative icon font overlapping the suggestion text). The vendor
     * stylesheets stay enabled on purpose: they also position the balloon next
     * to the word and style the proofreading dialog. We only neutralize the two
     * broken decorative bits (both verified live to compute correctly); layout,
     * positioning and dialog styling remain vendor-owned.
     * Injected per document (page + every editor container, which may be an
     * iframe). Premium path only.
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

    function initEditor(editor) {
        if (!window.WEBSPELLCHECKER || typeof window.WEBSPELLCHECKER.init !== 'function') {
            log.error('WEBSPELLCHECKER.init is not available after loading the bundle');
            return;
        }
        wproofreaderExclude.markNonLanguageContentDom(getContainer(editor), markerOptions);
        var container = getContainer(editor);
        if (container && container.ownerDocument) {
            injectBalloonStyle(container.ownerDocument);
        }
        window.WEBSPELLCHECKER.init({
            container: container,
            onErrorRequest: function (data) {
                log.error(data);
            }
        });
    }

    function onInstanceReady(editor) {
        // The module may evaluate after some editors are already ready (the
        // instanceReady event does not replay). The per-editor flag keeps a
        // re-fired event or a sweep from initialising the same container twice.
        if (!editor || editor._wproofreaderInitDone) {
            return;
        }
        editor._wproofreaderInitDone = true;
        wproofreaderExclude.bindEditorExcludeMarkers(editor, markerOptions);

        // Runtime markers must never leak into stored content: strip them at
        // the single serialization chokepoint every save path goes through.
        editor.on('getData', function (evt) {
            if (evt && evt.data && typeof evt.data.dataValue === 'string') {
                evt.data.dataValue = wproofreaderExclude.stripMarkersFromHtml(evt.data.dataValue, markerOptions);
            }
        });

        loadScript()
            .then(function () {
                initEditor(editor);
            })
            .catch(function (err) {
                log.error(err);
            });
    }

    function bindInstanceReady() {
        if (!enabled || boundInstanceReady || !window.CKEDITOR) {
            return;
        }
        boundInstanceReady = true;

        window.CKEDITOR.on('instanceReady', function (evt) {
            onInstanceReady(evt.editor);
        });

        // Editors created before this module evaluated already fired
        // instanceReady; initialise those synchronously-ready ones now.
        var instances = window.CKEDITOR.instances || {};
        Object.keys(instances).forEach(function (name) {
            var editor = instances[name];
            if (editor && editor.status === 'ready') {
                onInstanceReady(editor);
            }
        });
    }

    bindInstanceReady();

    return {
        enabled: enabled,
        getCkeditorConfig: function () {
            // CKEditor disables the native spell checker by default.
            // Keep it disabled only while the premium provider marks errors,
            // otherwise let the browser underline natively (FR1 default path).
            return { disableNativeSpellChecker: enabled };
        }
    };
});
