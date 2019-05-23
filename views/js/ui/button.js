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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'ui/component',
    'tpl!ui/button/tpl/button'
], function (_, component, buttonTpl) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     */
    var defaults = {
        small: true
    };

    /**
     * @typedef {Object} buttonConfig Defines the config entries available to setup a button
     * @property {String} id - The identifier of the button
     * @property {String} label - The caption of the button
     * @property {String} [title] - An optional tooltip for the button
     * @property {String} [icon] - An optional icon for the button
     * @property {String} [type] - The type of button to build
     * @property {Boolean} [small] - Whether build a small button (default: true)
     * @property {String} [cls] - An additional CSS class name
     */

    /**
     * Builds a simple button component.
     *
     * @example
     *  // button with simple action
     *  var button = buttonFactory({
     *      id: 'foo',
     *      label: 'Foo',
     *      title: 'Foo Bar',
     *      icon: 'globe',
     *      type: 'info'
     *  })
     *      .on('click', function() {
     *          // do something
     *      })
     *      .render(container);
     *
     *  // button with handling of async action
     *  var button = buttonFactory({
     *      id: 'foo',
     *      label: 'Foo',
     *      title: 'Foo Bar',
     *      icon: 'globe',
     *      type: 'info'
     *  })
     *      .before('click', function(){
     *          this.disable();
     *      })
     *      .on('click', function() {
     *          return new Promise(function(resolve) {
     *              // do something
     *              resolve();
     *          });
     *      })
     *      .after('click', function(){
     *          this.enable();
     *      })
     *      .render(container);
     *
     * @param {buttonConfig} config
     * @param {String} config.id - The identifier of the button
     * @param {String} config.label - The caption of the button
     * @param {String} [config.title] - An optional tooltip for the button
     * @param {String} [config.icon] - An optional icon for the button
     * @param {String} [config.type] - The type of button to build
     * @param {Boolean} [config.small] - Whether build a small button (default: true)
     * @param {String} [config.cls] - An additional CSS class name
     * @returns {button}
     * @fires click - When the button is clicked
     * @fires ready - When the button is ready to work
     */
    function buttonFactory(config) {
        return component({
            /**
             * Gets the identifier of the button
             * @returns {String}
             */
            getId: function getId() {
                return this.config.id;
            }
        }, defaults)
            .setTemplate(buttonTpl)

            // renders the component
            .on('render', function () {
                var self = this;
                var $component = this.getElement();

                $component.on('click', function(e) {
                    e.preventDefault();

                    /**
                     * @event click
                     * @param {String} buttonId
                     */
                    self.trigger('click', self.config.id);
                });

                /**
                 * @event ready
                 */
                this.trigger('ready');
            })

            // take care of the disable state
            .on('disable', function () {
                this.getElement().prop('disabled', true);
            })
            .on('enable', function () {
                this.getElement().prop('disabled', false);
            })

            .init(config);
    }

    return buttonFactory;
});
