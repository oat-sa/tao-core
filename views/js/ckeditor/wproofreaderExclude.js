/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA ;
 */
/**
 * WProofreader exclusions for TAO CKEditor 4 (math, QTI widgets, code, source mode).
 */
define(['lodash'], function (_) {
    'use strict';

    var MARKER_CLASS = 'tao-wsc-ignore';
    var MARKER_ATTR = 'data-wsc-ignore-checking';
    var MARKER_ATTR_VALUE = '1';

    var DEFAULT_IGNORE_CLASSES = [
        MARKER_CLASS,
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
    var DEFAULT_IGNORE_ATTRIBUTES = [MARKER_ATTR];

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

    // Marker pass targets true non-language islands only. Never add QTI
    // structural hooks ([data-qti-class], [data-widget]) here: they wrap prose
    // (choice/option/prompt text) and marking them prunes it from checking.
    var DOM_MARK_SELECTORS = [
        '.cke_widget_wrapper',
        '.math-tex',
        'pre',
        'code',
        'iframe'
    ].join(',');

    function unionList(base, extra) {
        return _.compact(_.union(base, _.isArray(extra) ? extra : extra ? [extra] : []));
    }

    function normalizeIgnoreElements(value) {
        if (_.isArray(value)) {
            return value;
        }
        if (_.isString(value) && value.length) {
            return value.split(',').map(function (part) {
                return part.trim();
            });
        }
        return DEFAULT_IGNORE_ELEMENTS.slice();
    }

    /**
     * @param {Object} clientConfig module.config() for wproofreaderBootstrap
     * @returns {Object} WSC options (ignore*, disableAutoSearchIn)
     */
    function buildWscExcludeOptions(clientConfig) {
        clientConfig = clientConfig || {};

        return {
            ignoreClasses: unionList(DEFAULT_IGNORE_CLASSES, clientConfig.ignoreClasses),
            ignoreElements: normalizeIgnoreElements(clientConfig.ignoreElements).length
                ? normalizeIgnoreElements(clientConfig.ignoreElements)
                : DEFAULT_IGNORE_ELEMENTS.slice(),
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

    function markDomNode(node, markerClass, markerAttr) {
        if (!node || node.nodeType !== 1) {
            return;
        }
        node.classList.add(markerClass);
        node.setAttribute(markerAttr, MARKER_ATTR_VALUE);
        node.setAttribute('spellcheck', 'false');
    }

    /**
     * Strip runtime markers from serialized editor HTML so they never leak
     * into stored QTI. Only touches nodes carrying our marker class (the
     * spellcheck flag on those nodes is ours too, set together in
     * markDomNode); pre-existing author/TAO spellcheck flags are preserved.
     *
     * @param {String} html
     * @param {Object} [options]
     * @returns {String}
     */
    function stripMarkersFromHtml(html, options) {
        if (!html || html.indexOf(MARKER_CLASS) === -1 || typeof document === 'undefined') {
            return html;
        }
        options = options || {};
        var markerClass = options.markerClass || MARKER_CLASS;
        var markerAttr = options.markerAttribute || MARKER_ATTR;
        var holder = document.createElement('div');
        holder.innerHTML = html;
        var nodes = holder.querySelectorAll('.' + markerClass);
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].classList.remove(markerClass);
            nodes[i].removeAttribute(markerAttr);
            nodes[i].removeAttribute('spellcheck');
            if (nodes[i].className === '') {
                nodes[i].removeAttribute('class');
            }
        }
        return holder.innerHTML;
    }

    /**
     * Tag math/QTI/code islands so native spell check and WSC skip them.
     *
     * @param {HTMLElement} root editable root
     * @param {Object} [options]
     */
    function markNonLanguageContentDom(root, options) {
        if (!root || !root.querySelectorAll) {
            return;
        }

        options = options || {};
        var markerClass = options.markerClass || MARKER_CLASS;
        var markerAttr = options.markerAttribute || MARKER_ATTR;
        var selectors = options.selectors || DOM_MARK_SELECTORS;
        var nodes = root.querySelectorAll(selectors);
        var i;

        for (i = 0; i < nodes.length; i++) {
            markDomNode(nodes[i], markerClass, markerAttr);
        }
    }

    /**
     * @param {CKEDITOR.editor} editor
     * @param {Object} [options] same as markNonLanguageContentDom
     */
    function bindEditorExcludeMarkers(editor, options) {
        if (!editor) {
            return;
        }
        if (editor._taoWscExcludeBound) {
            return;
        }
        editor._taoWscExcludeBound = true;

        function applyMarkers() {
            if (editor.mode === 'source') {
                return;
            }
            var editable = editor.editable();
            if (editable && editable.$) {
                markNonLanguageContentDom(editable.$, options);
            }
        }

        editor.on('contentDom', applyMarkers);
        editor.on('afterInsertHtml', applyMarkers);
        editor.on('setData', function () {
            setTimeout(applyMarkers, 0);
        });
        editor.on('mode', function () {
            setTimeout(applyMarkers, 0);
        });

        if (editor.widgets) {
            editor.widgets.on('instanceCreated', applyMarkers);
        }

        editor.on('pluginContentModified', applyMarkers);
    }

    // ponytail: smallest runnable check — fails if exclude lists are emptied by mistake
    if (typeof console !== 'undefined' && console.assert) {
        console.assert(
            buildWscExcludeOptions({}).ignoreClasses.indexOf('math-tex') !== -1,
            'wproofreaderExclude: default ignoreClasses must include math-tex'
        );
        console.assert(
            buildWscExcludeOptions({}).enableAutoSearchIn.indexOf('.cke_editable') !== -1,
            'wproofreaderExclude: default enableAutoSearchIn must include .cke_editable'
        );
    }

    return {
        MARKER_CLASS: MARKER_CLASS,
        MARKER_ATTR: MARKER_ATTR,
        buildWscExcludeOptions: buildWscExcludeOptions,
        markNonLanguageContentDom: markNonLanguageContentDom,
        bindEditorExcludeMarkers: bindEditorExcludeMarkers,
        stripMarkersFromHtml: stripMarkersFromHtml
    };
});
