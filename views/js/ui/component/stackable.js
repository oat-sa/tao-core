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
    'ui/stacker'
], function(stackerFactory) {
    'use strict';

    /**
     * @param {Component} - an instance of ui/component
     */
    return function makeStackable(component) {
        var stacker;

        return component
            .on('init', function () {
                var scope = this.config && this.config.stackingScope;
                stacker = stackerFactory(scope);
            })
            .on('show', function () {
                stacker.bringToFront(this.getElement());
            })
            .on('render', function () {
                var $element = this.getElement();

                stacker.reset($element);
                stacker.bringToFront($element);
                stacker.autoBringToFront($element);
            });
    };

});