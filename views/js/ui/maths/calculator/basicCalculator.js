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
 * Copyright (c) 2018 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'ui/maths/calculator/defaultCalculator',
    'tpl!ui/maths/calculator/tpl/basicKeyboard',
    'tpl!ui/maths/calculator/tpl/basicScreen'
], function (
    _,
    defaultCalculatorFactory,
    keyboardTpl,
    screenTpl
) {
    'use strict';

    /**
     * Creates a basic calculator component. Screen and keyboard layout are replaceable.
     * @param {Object} config - Some config entries (@see ui/dynamicComponent)
     * @param {Function} [config.keyboardLayout] - A Handlebars template for the keyboard
     * @param {Function} [config.screenLayout] - A Handlebars template for the screen
     * @param {Object} [config.calculator] - Config for the calculator (@see ui/maths/calculator/core/board)
     * @returns {dynamicComponent}
     */
    return function basicCalculatorFactory(config) {
        // The plugins config is directly built here instead of using a module variable to ensure the object is unique
        // to the instance. This wil avoid global polluting by successive instances, as nested objects and arrays might
        // be simply copied.
        return defaultCalculatorFactory(_.merge({
            calculator: {
                plugins: {
                    templateKeyboard: {
                        layout: keyboardTpl
                    },
                    simpleScreen: {
                        layout: screenTpl
                    }
                }
            }
        }, config));
    };
});
