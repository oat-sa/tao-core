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
    'ui/component',
    'tpl!ui/form/validator/tpl/message',
    'tpl!ui/form/validator/tpl/validator',
    'css!ui/form/validator/css/validator'
], function (_, componentFactory, messageTpl, validatorTpl) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     */
    const defaults = {};

    /**
     * Builds a renderer component for the validation messages.
     *
     * @param {HTMLElement|String} container
     * @param {Object} config
     * @returns {validatorRenderer}
     * @fires ready - When the component is ready to work
     */
    function validatorRendererFactory(container, config) {
        const api = {
            /**
             * Displays messages
             * @param {String|String[]} messages
             * @returns {component}
             */
            display(messages) {
                const $element = this.getElement();

                if (this.is('rendered')) {
                    this.clear();
                    if (messages && !_.isArray(messages)) {
                        messages = [messages];
                    }
                    _.forEach(messages, message => $element.append(messageTpl({message})));
                }

                return this;
            },

            /**
             * Clears all messages
             * @returns {component}
             */
            clear() {
                if (this.is('rendered')) {
                    this.getElement().empty();
                }
                return this;
            }
        };

        const validatorRenderer = componentFactory(api, defaults)
            .setTemplate(validatorTpl)

            // auto render on init
            .on('init', function () {
                // auto render on init
                _.defer(() => this.render(container));
            })

            // renders the component
            .on('render', function () {
                if (this.getConfig().messages) {
                    this.display(this.getConfig().messages);
                }

                /**
                 * @event ready
                 */
                this.trigger('ready');
            });

        // initialize the component with the provided config
        // defer the call to allow to listen to the init event
        _.defer(() => validatorRenderer.init(config));

        return validatorRenderer;
    }

    return validatorRendererFactory;
});
