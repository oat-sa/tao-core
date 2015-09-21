/*
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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/validator',
    'ui/formValidator/highlighters/highlighter'
], function($, _, __, validator, Highlighter){
    'use strict';

    var highlighter;

    /**
     * @param {Object} options
     * @param {jQuery} [options.container] - container which contains elements to validate
     * @param {Object} [options.selector = '[data-validate]'] - selector to find elements to validate
     * @param {string|Array} [options.events = ['change', 'blur']] - the default event that triggers the validation
     * @param {Object} [options.call = '[data-validate]'] - selector to find elements to validate
     * @constructor
     */
    function FormValidator(options) {
        var self = this,
            state,
            $toValidate;
        //options.container.groupValidator();

        this.options = {
            highlighter : {
                type : 'message',
                errorClass : 'error',
                errorMessageClass : 'validate-error'
            },
            container : $(document),
            selector : '[data-validate]',
            onsubmit : true, // Validate the form on submit. Set to false to use only other events for validation.
            validateOnInit : false,
            events : ['change', 'blur']
        };

        this.init = function init() {
            self.options = $.extend(self.options, options);

            $toValidate = getFieldsToValidate();

            $toValidate.validator({
                event : self.options.events
            });

            if (options.validateOnInit) {
                self.validate();
            }
        };

        this.validate = function () {
            var $toValidate = getFieldsToValidate();
            $toValidate.validator('validate', afterValidate);
        };

        this.destroy = function () {

        };

        function afterValidate() {
            console.log(arguments);
        }

        function highlightField($field, success) {
            var highlighter = getHighlighter();
            if (success) {
                highlighter.highlight($field);
            } else {
                highlighter.unhighlight($field);
            }
        }

        function getHighlighter() {
            if (highlighter === undefined) {
                highlighter = new Highlighter(self.options.highlighter);
            }
        }

        /**
         * Get fields to validate
         * @returns {jQuery}
         */
        function getFieldsToValidate() {
            var $container;
            if ($toValidate === undefined) {
                $container = getContainer();
                $toValidate = $container.find(self.options.selector);
            }
            return $toValidate;
        }

        /**
         * Get container which contains fields to validate
         * @returns {jQuery}
         */
        function getContainer() {
            var $container;

            if (self.options.container && self.options.container.length) {
                $container = self.options.container;
            } else {
                $container = $(document);
            }

            return $container;
        }

        this.init();
    }

    return FormValidator;
});
