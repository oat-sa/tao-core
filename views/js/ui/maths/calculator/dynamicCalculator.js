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
    'i18n',
    'core/promise',
    'util/namespace',
    'ui/dynamicComponent',
    'ui/maths/calculator/core/board',
    'ui/maths/calculator/pluginsLoader'
], function (
    _,
    __,
    Promise,
    nsHelper,
    dynamicComponent,
    calculatorBoardFactory,
    loadPlugins
) {
    'use strict';

    /**
     * Default config values
     * @type {Object}
     */
    var _defaults = {
        title: __('Calculator'),
        preserveAspectRatio: false,
        width: 240,
        height: 360,
        alternativeTemplate: null
    };

    /**
     * The internal namespace for built-in events listeners
     * @type {String}
     */
    var ns = 'dynamicCalculator';

    /**
     * Creates a dynamic panel containing a calculator.
     * @param {Object} config - Some config entries (@see ui/dynamicComponent)
     * @param {Object} [config.calculator] - Config for the calculator (@see ui/maths/calculator/core/board)
     * @param {Object} [config.loadedPlugins] - a collection of already loaded plugins
     * @param {Object} [config.dynamicPlugins] - a collection of plugins to load
     * @returns {dynamicComponent}
     */
    return function dynamicCalculatorFactory(config) {
        var calculator, dynamicCalculator;

        var api = {
            /**
             * Gets the nested calculator
             * @returns {calculator}
             */
            getCalculator: function getCalculator() {
                return calculator;
            }
        };

        config = _.defaults(config || {}, _defaults);

        dynamicCalculator = dynamicComponent(api)
            .on('rendercontent', function ($content) {
                var self = this;
                return loadPlugins(config.loadedPlugins, config.dynamicPlugins)
                    .then(function (loadedPlugins) {
                        return new Promise(function (resolve) {
                            calculator = calculatorBoardFactory($content, loadedPlugins, config.calculator)
                                .on(nsHelper.namespaceAll('ready', ns), function () {
                                    self
                                        .on(nsHelper.namespaceAll('resize', ns), function (position) {
                                            self.off(nsHelper.namespaceAll('resize', ns));
                                            // keep the initial size as the minimal
                                            self.config.minWidth = position.width;
                                            self.config.minHeight = position.height;
                                        })
                                        .setContentSize(calculator.getElement().outerWidth(), calculator.getElement().outerHeight())
                                        .setState('ready')
                                        .trigger('ready');
                                    resolve();
                                });
                        });
                    });
            })
            .on('ready', function () {
                var initialWidth = this.getElement().width();
                var initialHeight = this.getElement().height();
                var initialFontSize = parseInt(this.getCalculator().getElement().css('fontSize'), 10) || 10;
                this.on('resize', function (position) {
                    var areaBroker;
                    if (this.getElement()) {
                        this.getCalculator().getElement().css('fontSize', initialFontSize * Math.min(
                            this.getElement().width() / initialWidth,
                            this.getElement().height() / initialHeight
                        ));

                        areaBroker = this.getCalculator().getAreaBroker();
                        areaBroker.getKeyboardArea().height(
                            position.contentHeight
                            - areaBroker.getScreenArea().outerHeight()
                            - areaBroker.getInputArea().outerHeight()
                        );
                    }
                });
            })
            .on('destroy', function () {
                var self = this;
                return new Promise(function (resolve) {
                    if (calculator) {
                        calculator
                            .after(nsHelper.namespaceAll('destroy', ns), function () {
                                self.off('.' + ns);
                                calculator = null;
                                resolve();
                            })
                            .destroy();
                    } else {
                        resolve();
                    }
                });
            });

        _.defer(function () {
            dynamicCalculator.init(config);
        });

        return dynamicCalculator;
    };
});
