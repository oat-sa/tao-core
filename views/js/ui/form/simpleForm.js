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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'ui/form/form'
], function (_, __, formFactory) {
    'use strict';

    /**
     * @typedef {formConfig} simpleFormConfig Defines the config entries available to setup a form
     * @property {String} [submitText] - The caption of the submit button
     * @property {String} [submitIcon] - The icon of the submit button
     * @property {String} [resetText] - The caption of the reset button
     * @property {String} [resetIcon] - The icon of the reset button
     * @property {Boolean} [reset] - Activate the reset button
     */

    /**
     * Default config values
     * @type {simpleFormConfig}
     */
    var defaultConfig = {
        submitText: __('Save'),
        submitIcon: 'save',
        resetText: __('Reset'),
        resetIcon: 'reset',
        reset: true
    };

    /**
     * Builds a simple form component, that contains at least a submit button
     *
     * @example
     *  var container = $('.my-container', $container);
     *
     *  var config = {
     *      title: 'My fancy form',
     *      submitText: 'Publish',
     *      widgets: [{
     *          widget: widgetDefinitions.TEXTBOX
     *          uri: 'nickname',
     *          label: 'Name',
     *          required: true
     *      }, {
     *          widget: widgetDefinitions.TEXTAREA
     *          uri: 'comment',
     *          label: 'Comment',
     *          required: true
     *      }]
     *  };
     *
     *  var form = formFactory(container, config)
     *      .on('submit', function(values) {
     *          // ...
     *      });
     *
     * @param {HTMLElement|String} container
     * @param {simpleFormConfig} config
     * @param {String} [config.submitText] - The caption of the submit button
     * @param {String} [config.submitIcon] - The icon of the submit button
     * @param {String} [config.resetText] - The caption of the reset button
     * @param {String} [config.resetIcon] - The icon of the reset button
     * @param {Boolean} [config.reset] - Activate the reset button
     * @param {String} [config.title] - An optional title for the form (default none)
     * @param {String} [config.formAction] - The url the form is targeting (default '#')
     * @param {String} [config.formMethod] - The HTTP method the form should use (default 'get')
     * @param {widgetConfig[]} [config.widgets] - The list of widgets to set in the form (default none)
     * @param {buttonConfig[]} [config.buttons] - The list of buttons to set in the form (default none)
     * @param {Object} [config.values] - Initial values for the widgets
     * @param {Object} [config.ranges] - An optional list of ranges for the widgets (generis related, default none)
     * @returns {form}
     * @fires ready - When the component is ready to work
     */
    return function simpleFormFactory(container, config) {
        config = _.merge({}, defaultConfig, config);
        config.buttons = config.buttons || [];

        if (config.reset) {
            config.buttons.push({
                type: 'neutral',
                id: 'reset',
                label: config.resetText,
                icon: config.resetIcon
            });
        }

        config.buttons.push({
            type: 'info',
            id: 'submit',
            label: config.submitText,
            icon: config.submitIcon
        });

        return formFactory(container, config)
            .on('button-submit', function() {
                this.submit();
            })
            .on('button-reset', function() {
                this.reset();
            });
    };
});
