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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
/**
 * This can be used whenever you want to make a component stackable, meaning that its z-index will be controlled by the module ui/stacker
 * If you want to use a stacking scope, the component should be initialised with a stackingScope property
 *
 * @example
 * var component = componentFactory({}, { stackingScope: 'myScope' });
 * makeStackable(component);
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'ui/stacker'
], function(_, stackerFactory) {
    'use strict';

    var defaultConfig = {
        stackingScope: ''
    };

    var stackableComponent = {
        bringToFront: function bringToFront() {
            var $element = this.getElement();
            this._stacker.bringToFront($element);
        }
    };

    /**
     * @param {Component} - an instance of ui/component
     * @param {Object} config
     * @param {String} config.stackingScope - scope id for the stacker
     */
    return function makeStackable(component, config) {
        _.assign(component, stackableComponent);

        return component
            .off('.makeStackable')
            .on('init.makeStackable', function () {
                _.defaults(this.config, config || {}, defaultConfig);

                this._stacker = stackerFactory(this.config.stackingScope);
            })
            .on('show.makeStackable', function () {
                this.bringToFront();
            })
            .on('render.makeStackable', function () {
                var $element = this.getElement();

                this._stacker.reset($element);
                this._stacker.autoBringToFront($element);
                this.bringToFront();
            });
    };

});