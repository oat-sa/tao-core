/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
/**
 * WProofreader exclusion lists for TAO CKEditor 4 (math, QTI widgets, code, source mode).
 *
 * Pure configuration builder. There is deliberately NO DOM marking pass and
 * NO getData stripping: every former marker target (.cke_widget_wrapper,
 * .math-tex, pre, code, iframe) is already covered by the ignoreClasses /
 * ignoreElements lists below, which the vendor evaluates live against the DOM
 * at scan time. Adding classes at runtime therefore changed nothing for the
 * premium checker, while native spell check (the only other consumer of
 * runtime flags) is disabled whenever marking could run.
 */
define(['lodash'], function (_) {
    'use strict';

    // Reserved marker class/attribute from the PoC phase. Nothing adds them
    // anymore and nothing strips them; they stay in the ignore lists so any
    // content marked during the PoC keeps being skipped instead of checked.
    var RESERVED_MARKER_CLASS = 'tao-wsc-ignore';
    var RESERVED_MARKER_ATTR = 'data-wsc-ignore-checking';

    var DEFAULT_IGNORE_CLASSES = [
        RESERVED_MARKER_CLASS,
        'math-tex',
        'widget-box',
        'hljs',
        'cke_widget_wrapper',
        'cke_widget_element',
        'cke_widget_drag_handler_container',
        'cke_widget_drag_handler'
    ];

    var DEFAULT_IGNORE_ELEMENTS = ['iframe', 'pre', 'code', 'svg', 'img', 'math'];

    // Only attributes that mark genuinely non-language islands. QTI structural
    // attributes (data-qti-class, data-widget, data-serial) are deliberately
    // NOT ignored: they sit on prose containers too (e.g. the choice prompt
    // editable root carries data-serial), and WSC prunes the whole subtree of
    // any element carrying an ignored attribute. Attribute *values* (serials,
    // class names) are never collected as text, so ignoring them protects
    // nothing while silencing real prose.
    var DEFAULT_IGNORE_ATTRIBUTES = [RESERVED_MARKER_ATTR];

    var DEFAULT_DISABLE_AUTO_SEARCH_IN = ['.cke_source', '.cke_source textarea'];

    // Allowlist for the vendor auto-search loop (WEBSPELLCHECKER autoSearch):
    // only CKEditor editables may spawn checker instances. Anything else on
    // the backoffice page (tree, properties panel, menus) is never eligible,
    // which keeps the global scan that used to crash authoring contained.
    // .cke_wysiwyg_frame matches iframe editors (the poll sees the iframe
    // element), .cke_editable matches inline editables, .text-container
    // matches plain response fields (e.g. Extended Text answer areas), which
    // the vendor checks through its text mirrored-field machinery.
    var DEFAULT_ENABLE_AUTO_SEARCH_IN = ['.cke_wysiwyg_frame', '.cke_editable', '.text-container'];

    function unionList(base, extra) {
        return _.compact(_.union(base, _.isArray(extra) ? extra : extra ? [extra] : []));
    }

    function normalizeIgnoreElements(value) {
        var custom = _.isArray(value)
            ? value
            : _.isString(value) && value.length
                ? value.split(',').map(function (part) {
                    return part.trim();
                })
                : [];
        // Custom entries MERGE with the defaults (same as ignoreClasses):
        // replacing them would silently re-enable checking of pre/code/math.
        return _.compact(_.union(DEFAULT_IGNORE_ELEMENTS, custom));
    }

    /**
     * @param {Object} clientConfig module.config() for wproofreaderBootstrap
     * @returns {Object} WSC options (ignore*, disableAutoSearchIn, enableAutoSearchIn)
     */
    function buildWscExcludeOptions(clientConfig) {
        clientConfig = clientConfig || {};

        return {
            ignoreClasses: unionList(DEFAULT_IGNORE_CLASSES, clientConfig.ignoreClasses),
            ignoreElements: normalizeIgnoreElements(clientConfig.ignoreElements),
            ignoreAttributes: unionList(DEFAULT_IGNORE_ATTRIBUTES, clientConfig.ignoreAttributes),
            disableAutoSearchIn: unionList(
                DEFAULT_DISABLE_AUTO_SEARCH_IN,
                clientConfig.disableAutoSearchIn
            ),
            enableAutoSearchIn: unionList(
                DEFAULT_ENABLE_AUTO_SEARCH_IN,
                clientConfig.enableAutoSearchIn
            )
        };
    }

    return {
        buildWscExcludeOptions: buildWscExcludeOptions
    };
});
