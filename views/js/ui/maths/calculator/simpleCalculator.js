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
    'util/namespace',
    'ui/maths/calculator/dynamicCalculator',
    'ui/maths/calculator/plugins/keyboard/templateKeyboard/templateKeyboard',
    'ui/maths/calculator/plugins/screen/simpleScreen/simpleScreen'
], function (
    _,
    nsHelper,
    dynamicCalculator,
    pluginKeyboardFactory,
    pluginScreenFactory
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
     * The internal namespace for built-in events listeners
     * @type {String}
     */
    var ns = 'simpleCalculator';

    /**
     * Creates a simple calculator component. Screen and keyboard layout are replaceable.
     * @param {Object} config - Some config entries (@see ui/dynamicComponent)
     * @param {Function} [config.keyboardLayout] - A Handlebars template for the keyboard
     * @param {Function} [config.screenLayout] - A Handlebars template for the screen
     * @param {Object} [config.calculator] - Config for the calculator (@see ui/maths/calculator/core/board)
     * @returns {dynamicComponent}
     */
    return function simpleCalculatorFactory(config) {
        var defaultPluginsConfig = {};

        if (config && config.keyboardLayout) {
            defaultPluginsConfig.templateKeyboard = {
                layout: config.keyboardLayout
            };
        }

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

        return dynamicCalculator(config)
            .on(nsHelper.namespaceAll('ready', ns), function () {
                var initialWidth = this.getElement().width();
                var initialHeight = this.getElement().height();
                var initialFontSize = parseInt(this.getCalculator().getElement().css('fontSize'), 10) || 10;
                this.on(nsHelper.namespaceAll('resize', ns), function (position) {
                    var areaBroker;
                    if (this.getElement()) {
                        this.getCalculator().getElement().css('fontSize', initialFontSize * Math.min(
                            this.getElement().width() / initialWidth,
                            this.getElement().height() / initialHeight
                        ));

                        areaBroker = this.getCalculator().getAreaBroker();
                        areaBroker.getKeyboardArea().height(position.contentHeight - areaBroker.getScreenArea().outerHeight());
                    }
                });
            })
            .on('destroy', function () {
                this.off('.' + ns);
            });
    };
});
