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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

define([
    'jquery',
    'i18n',
    'layout/loading-bar',
    'ui/hider',
    'tao/provider/authSelector',
    'tpl!tao/controller/WebHooks/tpl/authContainer'
], function ($, __, loadingBar, hider, authSelectorProvider, authContainerTpl) {
    'use strict';

    /**
     * Get or create element for authorization form
     * @returns {*|jQuery|HTMLElement}
     */
    function getAuthContainer() {
        var $propertyContainer  = $('.content-block .wh-auth-container');

        if($propertyContainer.length) {
            return $propertyContainer;
        }

        $propertyContainer = $(authContainerTpl());
        $('.content-block input[name="classUri"][value="http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_WebHook"]')
            .before($propertyContainer);

        return $propertyContainer;
    }

    return {
        start: function start() {
            var $container = getAuthContainer();
            var $elId = $('.content-block input[name="classUri"][value="http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_WebHook"]')
                .siblings('#uri');

            var params = {};

            if($elId.length) {
                params = {
                    uri: $elId.val()
                };
            }

            /**
             * Enable/disable fields
             * @param {jQuery} $fields - selection of fields
             * @param {Boolean} enabled - enable or disable the fields
             */
            function toggleFields($fields, enabled) {
                if (enabled) {
                    $fields.removeAttr('disabled').removeProp('disabled');
                } else {
                    $fields.attr('disabled', true).prop('disabled', true);
                }
            }

            /**
             * Display the auth form part that complies to the selected auth method.
             * Will be applied on the auth method selection combo box.
             */
            function showAuthFormPart() {
                var $allForms = $container.find('.wh-auth-form-part');
                var $selectedForm = $container.find('[data-auth-method="' + this.value + '"]');

                // switch form visibility
                hider.hide($allForms);
                hider.show($selectedForm);

                // switch sendable fields
                toggleFields($allForms.find(':input'), false);
                toggleFields($selectedForm.find(':input'), true);
            }

            loadingBar.start();
            authSelectorProvider.getHtml(params)
                .then(function (html) {
                    // show the form, will all auth methods
                    $container.html(html);

                    // display the form parts according to the selected auth method
                    $container.find('.wh-auth-type-selector')
                        .each(showAuthFormPart)
                        .on('change', showAuthFormPart);
                })
                .catch(function() {
                    throw new Error( __('WebHook auth configuration can not be loaded'));
                })
                .then(function () {
                    loadingBar.stop();
                });
        }
    };
});
