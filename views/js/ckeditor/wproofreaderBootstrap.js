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
        minWordLength: 3,
        autocorrect: false,
        enableGrammar: false,
        disableStyleGuide: true,
        spellingSuggestions: true,
        grammarSuggestions: false,
        styleGuideSuggestions: false,
        settingsSections: ['languages'],
        detectLocalizationLanguage: true,
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
            _.omit(config, ['enabled', 'ignoreClasses', 'ignoreElements', 'ignoreAttributes', 'disableAutoSearchIn']),
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

        window.WEBSPELLCHECKER_CONFIG = _.assign({ autoSearch: true }, getWscConfig());

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

    function initEditor(editor) {
        if (!window.WEBSPELLCHECKER || typeof window.WEBSPELLCHECKER.init !== 'function') {
            log.error('WEBSPELLCHECKER.init is not available after loading the bundle');
            return;
        }
        wproofreaderExclude.markNonLanguageContentDom(getContainer(editor), markerOptions);
        window.WEBSPELLCHECKER.init({
            container: getContainer(editor),
            onErrorRequest: function (data) {
                log.error(data);
            }
        });
    }

    function bindInstanceReady() {
        if (!enabled || boundInstanceReady || !window.CKEDITOR) {
            return;
        }
        boundInstanceReady = true;

        window.CKEDITOR.on('instanceReady', function (evt) {
            var editor = evt.editor;
            wproofreaderExclude.bindEditorExcludeMarkers(editor, markerOptions);

            loadScript()
                .then(function () {
                    initEditor(editor);
                })
                .catch(function (err) {
                    log.error(err);
                });
        });
    }

    bindInstanceReady();

    return {
        enabled: enabled,
        getCkeditorConfig: function () {
            return enabled ? { disableNativeSpellChecker: true } : {};
        }
    };
});
