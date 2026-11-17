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
/**
 * Skip redundant selectInstance content loads after post-save tree refresh.
 */
define(['jquery', 'uri'], function ($, uri) {
    'use strict';

    /**
     * @param {Object} nodeContext
     * @returns {String}
     */
    function getInstanceUriFromNodeContext(nodeContext) {
        return nodeContext.id || (nodeContext.uri ? uri.decode(nodeContext.uri) : '');
    }

    /**
     * @param {jQuery} $form
     * @returns {String}
     */
    function readContentInstanceUri($form) {
        var contentUri = $form.find('#id').val();

        if (!contentUri) {
            var $uriInput = $form.find('#uri');
            if ($uriInput.length) {
                contentUri = uri.decode($uriInput.val());
            }
        }

        return contentUri || '';
    }

    /**
     * @param {jQuery} $form
     * @param {Object} selectInstanceAction
     * @returns {Boolean}
     */
    function formActionMatchesLoadAction($form, selectInstanceAction) {
        if (!selectInstanceAction || !selectInstanceAction.url) {
            return true;
        }

        var formAction = ($form.attr('action') || '').split('?')[0];
        var actionUrl = selectInstanceAction.url.split('?')[0];

        return formAction.indexOf(actionUrl) !== -1 || actionUrl.indexOf(formAction) !== -1;
    }

    /**
     * Instance edit forms expose the plain resource URI in #id (#uri is encoded).
     *
     * @param {Object} nodeContext
     * @param {Object} selectInstanceAction
     * @param {jQuery|null} $contentBlock
     * @returns {Boolean}
     */
    function contentShowsSameInstanceForm(nodeContext, selectInstanceAction, $contentBlock) {
        var $form;
        var contentUri;
        var nodeUri;

        if (!$contentBlock || !$contentBlock.length) {
            return false;
        }

        $form = $contentBlock.find('.xhtml_form form').first();
        if (!$form.length) {
            $form = $contentBlock.find('form').first();
        }
        if (!$form.length) {
            return false;
        }

        contentUri = readContentInstanceUri($form);
        nodeUri = getInstanceUriFromNodeContext(nodeContext);

        if (!contentUri || !nodeUri || contentUri !== nodeUri) {
            return false;
        }

        return formActionMatchesLoadAction($form, selectInstanceAction);
    }

    /**
     * @param {Object} options
     * @param {Object} options.nodeContext
     * @param {Object} options.selectInstanceAction
     * @param {Boolean} options.postSaveRefresh
     * @param {String} [options.postSaveUri]
     * @param {String} [options.activeContentActionId]
     * @param {jQuery|null} [options.$contentBlock]
     * @returns {Boolean}
     */
    function shouldSkipTreeDrivenInstanceLoad(options) {
        var nodeUri = getInstanceUriFromNodeContext(options.nodeContext);

        if (
            options.postSaveRefresh &&
            options.postSaveUri &&
            nodeUri &&
            options.postSaveUri === nodeUri
        ) {
            return true;
        }

        if (contentShowsSameInstanceForm(
            options.nodeContext,
            options.selectInstanceAction,
            options.$contentBlock
        )) {
            return true;
        }

        if (
            options.postSaveRefresh &&
            options.selectInstanceAction &&
            options.activeContentActionId &&
            options.activeContentActionId !== options.selectInstanceAction.id
        ) {
            return true;
        }

        return false;
    }

    return {
        getInstanceUriFromNodeContext: getInstanceUriFromNodeContext,
        readContentInstanceUri: readContentInstanceUri,
        formActionMatchesLoadAction: formActionMatchesLoadAction,
        contentShowsSameInstanceForm: contentShowsSameInstanceForm,
        shouldSkipTreeDrivenInstanceLoad: shouldSkipTreeDrivenInstanceLoad
    };
});