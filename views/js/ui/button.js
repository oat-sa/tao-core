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
     * Builds a simple button component
     *
     * @param {Object} config
     * @param {String} config.id - The id of the button
     * @param {String} config.label - The id of the button
     * @param {String} [config.title] - An optional title of the button
     * @param {String} [config.icon] - An optional icon of the button
     * @param {String} [config.type] - The type of button to build
     * @param {Boolean} [config.small] - Whether build a small button (default: true)
     * @param {String} [config.cls] - An additional CSS class name
     * @returns {button}
     * @fires click - When the button is clicked
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

            /**
             * @todo We should support within the component async actions in order to disable the button while
             * the action is running. In order to do this we should support Promises within events (TT-36)
             * in order to do something like :
             *
             * ```
             *  component()
             *      .before('click', function(){
             *          this.disable();
             *      })
             *      .after('click', function(){
             *          this.enable();
             *      })
             * ```
             *
             * So when someone use the component with
             *
             * ```
             *  var myAwesomeButton = button().on('click', function(){
             *      return aPromise;
             *  });
             * ```
             *
             * the button state changes accordingly
             */

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
            })
            .init(config);
    }

    return buttonFactory;
});
