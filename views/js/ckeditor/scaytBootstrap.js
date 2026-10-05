/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
/**
 * Native SCAYT (Spell Check As You Type) CKEditor 4 integration for authoring.
 *
 * Registers the vendored SCAYT plugin (tao/ckeditor/scayt, upstream
 * WebSpellChecker/ckeditor-plugin-scayt) as an external CKEditor plugin and
 * exposes its editor configuration. Same vendor backend as WProofreader;
 * the plugin drives checking per editor, so no global init race exists.
 *
 * Known limits vs the WProofreader integration: exclusions are tag-name only
 * (scayt_elementsToIgnore) — class/attribute islands (.math-tex, QTI widget
 * chrome) cannot be skipped. Demo service unless scayt_customerId is set.
 *
 * Configure via ClientLibConfigRegistry key `tao/ckeditor/scaytBootstrap`.
 */
define(['module', 'lodash', 'core/logger'], function (module, _, logger) {
    'use strict';

    var log = logger('tao/ckeditor/scaytBootstrap');
    var defaults = {
        enabled: true,
        scayt_autoStartup: true,
        scayt_sLang: 'en_US',
        scayt_customerId: '',
        scayt_elementsToIgnore: 'style,script,math,pre,code',
        scayt_minWordLength: 3
    };

    var config = _.defaults({}, module.config() || {}, defaults);
    var registered = false;

    // Extra plugins vendored locally (open-source CKEditor plugins missing
    // from TAO's custom build): the suggestion context menu needs menu,
    // contextmenu, floatpanel and panel.
    var extraVendorPlugins = ['panel', 'floatpanel', 'menu', 'contextmenu'];

    function pluginBase() {
        var file = window.require && window.require.toUrl('tao/ckeditor/scayt/plugin');
        return String(file)
            .split('?')[0]
            .replace(/\/scayt\/plugin(\.js)?$/, '/');
    }

    /**
     * Register the vendored plugin. Safe to call on every editor build; the
     * registration itself happens once. Returns false when CKEDITOR is not
     * available yet (caller builds editors right after, so it retries there).
     *
     * @returns {Boolean}
     */
    function registerScayt() {
        if (registered) {
            return true;
        }
        if (!config.enabled || !window.CKEDITOR) {
            return false;
        }
        try {
            var base = pluginBase();
            window.CKEDITOR.plugins.addExternal('scayt', base + 'scayt/');
            extraVendorPlugins.forEach(function (name) {
                window.CKEDITOR.plugins.addExternal(name, base + 'ckplugins/' + name + '/');
            });
            registered = true;
            return true;
        } catch (err) {
            log.warn('Unable to register SCAYT plugin: ' + (err && err.message));
            return false;
        }
    }

    function getCkeditorConfig() {
        if (!config.enabled) {
            return {};
        }
        var scaytConfig = {
            extraPlugins: 'scayt,contextmenu',
            scayt_autoStartup: config.scayt_autoStartup,
            scayt_sLang: config.scayt_sLang,
            scayt_elementsToIgnore: config.scayt_elementsToIgnore,
            scayt_minWordLength: config.scayt_minWordLength
        };
        if (config.scayt_customerId) {
            scaytConfig.scayt_customerId = config.scayt_customerId;
        }
        return scaytConfig;
    }

    return {
        registerScayt: registerScayt,
        getCkeditorConfig: getCkeditorConfig
    };
});
