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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

/**
 * Turns a component into a window, with a title bar and a control area (close...)
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'tpl!tpl/window.tpl'
], function (_, windowTpl) {
    'use strict';

    var cssNs = '.window-component';

    var defaultConfig = {
        // minWidth: 50,
        // minHeight: 50,
        // edges: { left: true, right: true, bottom: true, top: true }
    };

    var windowedComponentAPI = {
        getTitleBar: function getTitleBar() {
            var $component = this.getElement();
            return $component.find(cssNs + '-title');
        },

        getBody: function getBody() {
            var $component = this.getElement();
            return $component.find(cssNs + '-body');
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     // * @param {Number} config.minWidth
     // * @param {Number} config.minHeight
     */
    return function makeResizable(component, config) {

        _.assign(component, windowedComponentAPI);

        return component
            .setTemplate(windowTpl)
            .off('.windowed')
            .on('init.windowed', function() {
                _.defaults(this.config, config || {}, defaultConfig);
            });
    };

});
