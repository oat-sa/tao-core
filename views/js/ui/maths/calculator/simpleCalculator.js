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
    'ui/maths/calculator/dynamicCalculator',
    'ui/maths/calculator/plugins/keyboard/templateKeyboard/templateKeyboard',
    'ui/maths/calculator/plugins/screen/simpleScreen/simpleScreen',
    'tpl!ui/maths/calculator/tpl/simpleKeyboard'
], function (
    _,
    dynamicCalculator,
    pluginKeyboardFactory,
    pluginScreenFactory,
    simpleKeyboardTpl
) {
    'use strict';

    /**
     * The list of UI plugins the simple calculator is using
     * @type {Object}
     */
    var simpleCalculatorPlugins = {
        keyboard: [
            pluginKeyboardFactory
        ],
        screen: [
            pluginScreenFactory
        ]
    };

    /**
     * Creates a simple calculator component. Screen and keyboard layout are replaceable.
     * @param {Object} config - Some config entries (@see ui/dynamicComponent)
     * @param {Function} [config.keyboardLayout] - A Handlebars template for the keyboard
     * @param {Function} [config.screenLayout] - A Handlebars template for the screen
     * @param {Object} [config.calculator] - Config for the calculator (@see ui/maths/calculator/core/board)
     * @returns {dynamicComponent}
     */
    return function simpleCalculatorFactory(config) {
        var defaultPluginsConfig = {
            templateKeyboard: {
                layout: config && config.keyboardLayout || simpleKeyboardTpl
            }
        };

        if (config && config.screenLayout) {
            defaultPluginsConfig.simpleScreen = {
                layout: config.screenLayout
            };
        }

        config = _.merge({
            loadedPlugins: simpleCalculatorPlugins,
            calculator: {
                plugins: defaultPluginsConfig
            }
        }, _.omit(config, ['keyboardLayout', 'screenLayout']));

        return dynamicCalculator(config);
    };
});
