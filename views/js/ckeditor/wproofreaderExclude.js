/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
/**
 * WProofreader exclusion lists for TAO CKEditor 4.
 */
define(['lodash'], function (_) {
    'use strict';

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
    var DEFAULT_IGNORE_ATTRIBUTES = [RESERVED_MARKER_ATTR];

    var DEFAULT_DISABLE_AUTO_SEARCH_IN = ['.cke_source', '.cke_source textarea'];
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
