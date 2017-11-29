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
 * Copyright (c) 2015-2017 (original work) Open Assessment Technologies SA ;
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'tpl!ui/bulkActionPopup/tpl/layout',
    'ui/component',
    'ui/keyNavigation/navigator',
    'ui/keyNavigation/navigableDomElement',
    'util/shortcut/registry',
    'util/shortcut',
    'util/namespace',
    'ui/modal',
    'select2'
], function($, _, __, layoutTpl, component, keyNavigator, navigableDomElement, shortcutRegistry, globalShortcut, namespaceHelper){
    'use strict';

    /**
     * Namespace used in events and shortcuts
     * @type {String}
     * @private
     */
    var _ns = 'bulk-action-popup';

    /**
     * Builds an instance of the bulkActionPopup component
     *
     * @param {Object} config
     * @param {jQuery} config.renderTo - the jQuery container it should be rendered to
     * @param {String} config.actionName - the action name (use in the title text)
     * @param {String} config.resourceType - the name of the resource type (use in the text)
     * @param {Boolean} [config.allowShortcuts] - allow keyboard shortcuts (Esc to cancel, Enter to validate)
     * @param {String} [config.resourceTypes] - the name of the resource type in plural (use in the text)
     * @param {Boolean} [config.reason] - defines if the reason section should be displayed or not
     * @param {Function} [config.categoriesSelector] - callback renderer for categories
     * @param {Array} config.allowedResources - list of allowed resources to be displayed
     * @param {Array} [config.deniedResources] - list of denied resources to be displayed
     * @returns {bulkActionPopup}
     */
    return function bulkActionPopupFactory(config){

        //private object to hold the state of edition
        var state = {
            reasons : null,
            comment : ''
        };

        var instance = component({
            /**
             * Validates the dialog, and closes it (action performed when hitting the Ok button)
             * @returns {Boolean} Returns `true` if the dialog has been successively validated (and closed)
             */
            validate: function validate() {
                var $element = this.getElement();
                var $error;

                if ($element) {
                    $('.feedback-error', $element).remove();
                    if (!checkRequiredFields($element)) {
                        $error = $('<div class="feedback-error small"></div>').text(__('All fields are required'));
                        $element.find('.actions').prepend($error);
                        return false;
                    }
                }

                this.trigger('ok', state);
                this.destroy();
                return true;
            },

            /**
             * Cancels and closes the dialog
             */
            cancel: function cancel() {
                this.trigger('cancel');
                this.destroy();
            }
        })
            .setTemplate(layoutTpl)

            // uninstalls the component
            .on('destroy', function(){
                // allows all registered shortcuts to be triggered and disables the dialog shortcuts
                globalShortcut.enable();
                if (this.dialogShortcut) {
                    this.dialogShortcut.disable();
                    this.dialogShortcut.clear();
                    this.dialogShortcut = null;
                }
                if (this.navigator) {
                    this.navigator.destroy();
                    this.navigator = null;
                }

                this.getElement().removeClass('modal').modal('destroy');
            })

            // event triggered when the OK button has been clicked or the related shortcut has been used
            .on('action-ok', function() {
                this.validate();
            })

            // event triggered when the Cancel button has been clicked or the related shortcut has been used
            .on('action-cancel', function() {
                this.cancel();
            })

            // renders the component
            .on('render', function(){
                var self = this;
                var $element = this.getElement();
                var $reason;

                initModal({
                    disableEscape: true,
                    width : (this.config.single && !this.config.deniedResources.length && !this.config.reason) ? 600 : 800
                });

                if ( _.isObject(this.config.categoriesSelector)) {
                    $reason = $element.find('.reason').children('.categories');
                    this.config.categoriesSelector.render($reason);
                }

                $element
                    .on(namespaceHelper.namespaceAll('selected.cascading-combobox', _ns), function (e, reasons) {
                        state.reasons = reasons;
                        if (self.config.allowShortcuts) {
                            // ensure the keyboard navigation is taking care of the possible new fields
                            initNavigator();
                        }
                        self.trigger('change', state);
                    })
                    .on(namespaceHelper.namespaceAll('change', _ns), 'textarea', function () {
                        state.comment = $(this).val();
                        self.trigger('change', state);
                    })
                    .on(namespaceHelper.namespaceAll('click', _ns), '.actions .done', function (e) {
                        e.preventDefault();
                        self.trigger('action-ok');
                    })
                    .on(namespaceHelper.namespaceAll('click', _ns), '.actions .cancel', function (e) {
                        e.preventDefault();
                        self.trigger('action-cancel');
                    });

                if (this.config.allowShortcuts) {
                    // install the keyboard navigation
                    initNavigator();

                    // prevents all registered shortcuts to be triggered and activate the dialog shortcuts
                    globalShortcut.disable();
                    this.dialogShortcut = shortcutRegistry($('body'), {
                        avoidInput: true,
                        propagate: false,
                        prevent: true
                    })
                        // prevents the TAB key to be used to move outside the dialog box, but handles navigation
                        .add(namespaceHelper.namespaceAll('Tab', _ns, true), function() {
                            self.navigator.next();
                        }, {
                            avoidInput: false
                        })
                        .add(namespaceHelper.namespaceAll('Shift+Tab', _ns, true), function() {
                            self.navigator.previous();
                        }, {
                            avoidInput: false
                        })

                        // handles the dialog's shortcuts: just fire the action using the event loop
                        .add(namespaceHelper.namespaceAll('esc', _ns, true), function (e, shortcut) {
                            instance.trigger('action-cancel', shortcut);
                        })
                        .add(namespaceHelper.namespaceAll('enter', _ns, true), function (e, shortcut) {
                            instance.trigger('action-ok', shortcut);
                        });
                }
            });

        /**
         * Validates that all required fields have been filled
         * @param {jQuery} $container
         * @returns {Boolean}
         */
        function checkRequiredFields($container) {
            return $("select, textarea", $container).filter(function () {
                return $.trim($(this).val()).length === 0;
            }).length === 0;
        }

        /**
         * Adds the form into a popup and displays it
         * @param {Object} modalConfig
         */
        function initModal(modalConfig) {
            instance.getElement()
                .addClass('modal')
                .on('closed.modal', function () {
                    // always destroy the widget when closing
                    instance.destroy();
                })
                .modal(modalConfig)
                .focus();
        }

        /**
         * Sets a keyboard navigator on the dialog to take care of TAB navigation
         */
        function initNavigator() {
            var $element = instance.getElement();

            instance.navigator = keyNavigator({
                id : _ns,
                replace : true,
                loop : true,
                group: $element,

                // the dialog is always set as first component, so ensure to start on the first field
                defaultPosition: 1,

                // take all input fields and the dialog as navigable components
                // but ignore buttons and select2 hidden fields
                elements : navigableDomElement.createFromDoms($element.find(':input:not(button,select.select2-offscreen)').add($element))
            })
                .on('activate', function(cursor, target) {
                    if ($(target).is($element)) {
                        instance.validate();
                    }
                })

                // put the focus on the keyboard navigation, to ensure the first field is selected
                // otherwise the user will have to hit twice the tab key before selecting it
                .focus();
        }

        //compute extra config data (essentially for the template)
        return instance.init(_.defaults(config, {
            deniedResources : [],
            reason : false,
            allowShortcuts: true,
            reasonRequired: false,
            resourceCount : config.allowedResources.length,
            single : (config.allowedResources.length === 1),
            singleDenied : (config.deniedResources && config.deniedResources.length === 1),
            resourceTypes : config.resourceType + 's'
        }));
    };
});
