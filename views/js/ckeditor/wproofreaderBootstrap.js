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

    var spellCheckConfig = _.defaults({}, module.config().spellCheckConfig || {}, {
        enabled: true,
        providerId: 'native',
        providerConfig: {}
    });
    var config = _.defaults({}, spellCheckConfig.providerConfig, defaults);
    var selectedLanguage = null;
    var disabledInstances = [];
    config.serviceId = _.trim(String(config.serviceId || ''));
    var enabled = spellCheckConfig.enabled !== false
        && spellCheckConfig.providerId === 'wproofreader'
        && !!config.serviceId;
    var scriptLoaded = false;
    var scriptLoading = null;

    function supportsLanguage(instance) {
        var languages;

        if (!selectedLanguage) {
            return false;
        }
        if (!_.isFunction(instance.getLangList)) {
            return true;
        }

        languages = instance.getLangList();
        if (!languages || (!languages.ltr && !languages.rtl)) {
            return true;
        }

        return _.has(languages.ltr, selectedLanguage) || _.has(languages.rtl, selectedLanguage);
    }

    function disableInstance(instance) {
        if (
            !_.isFunction(instance.disable)
            || _.includes(disabledInstances, instance)
            || (_.isFunction(instance.isDisabled) && instance.isDisabled())
        ) {
            return;
        }

        instance.disable({ ignoreCallback: true });
        disabledInstances.push(instance);
    }

    function enableInstance(instance) {
        if (_.includes(disabledInstances, instance) && _.isFunction(instance.enable)) {
            instance.enable({ ignoreCallback: true });
            disabledInstances = _.without(disabledInstances, instance);
        }
    }

    function setInstanceLanguage(instance) {
        if (!instance) {
            return;
        }

        try {
            if (!supportsLanguage(instance)) {
                disableInstance(instance);
                return;
            }

            if (_.isFunction(instance.setLang) && (!_.isFunction(instance.getLang) || instance.getLang() !== selectedLanguage)) {
                instance.setLang(selectedLanguage, { ignoreCallback: true });
                if (_.isFunction(instance.reloadMarkup)) {
                    instance.reloadMarkup();
                }
            }
            enableInstance(instance);
        } catch (err) {
            log.warn(`Unable to set WProofreader language: ${err && err.message}`);
        }
    }

    function updateInstancesLanguage() {
        if (
            !scriptLoaded
            || !window.WEBSPELLCHECKER
            || !_.isFunction(window.WEBSPELLCHECKER.getInstances)
        ) {
            return;
        }

        _.forEach(window.WEBSPELLCHECKER.getInstances(), setInstanceLanguage);
    }

    function getWscConfig() {
        var exclude;
        var wscConfig;
        var onLoad;

        exclude = wproofreaderExclude.buildWscExcludeOptions(config);
        wscConfig = _.assign(
            {},
            _.omit(config, ['ignoreClasses', 'ignoreElements', 'ignoreAttributes', 'disableAutoSearchIn', 'enableAutoSearchIn']),
            exclude
        );
        onLoad = wscConfig.onLoad;

        wscConfig.onLoad = function (instance, ...args) {
            setInstanceLanguage(instance);
            if (_.isFunction(onLoad)) {
                onLoad.call(this, instance, ...args);
            }
        };

        return wscConfig;
    }

    function loadScript() {
        if (scriptLoaded) {
            return Promise.resolve();
        }
        if (scriptLoading) {
            return scriptLoading;
        }

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
                    updateInstancesLanguage();
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

    function setLanguage(lang) {
        if (!enabled) {
            return;
        }

        lang = _.trim(String(lang || ''));
        selectedLanguage = lang || null;
        config.autoStartup = !!selectedLanguage;
        config.lang = selectedLanguage || '';

        if (window.WEBSPELLCHECKER_CONFIG) {
            window.WEBSPELLCHECKER_CONFIG.autoStartup = config.autoStartup;
            window.WEBSPELLCHECKER_CONFIG.lang = config.lang;
        }
        updateInstancesLanguage();
    }

    return {
        enabled: enabled,
        start: scheduleLoad,
        setLanguage: setLanguage,
        getCkeditorConfig: function () {
            return { disableNativeSpellChecker: false };
        }
    };
});
