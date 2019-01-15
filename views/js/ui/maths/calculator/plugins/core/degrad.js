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
 * Plugin that switch mode between degree and radian in the calculator
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'i18n',
    'util/namespace',
    'ui/maths/calculator/core/plugin'
], function (__, nsHelper, pluginFactory) {
    'use strict';

    var pluginName = 'degrad';

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin is installing in its host.
         */
        install: function install() {
            var calculator = this.getCalculator();

            calculator
                .setCommand('degree', __('Degree'), __('Set the trigonometric function to work in degrees'))
                .setCommand('radian', __('Radian'), __('Set the trigonometric function to work in radians'));
        },

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var calculator = this.getCalculator();

            function getMathsConfig() {
                var config = calculator.getConfig();
                if (!config.maths) {
                    config.maths = {};
                }
                return config.maths;
            }

            function setupMathsEvaluator() {
                var config = calculator.getConfig();
                var degree = config.maths && config.maths.degree;
                calculator
                    .setState('degree', degree)
                    .setState('radian', !degree)
                    .setupMathsEvaluator();
            }

            setupMathsEvaluator();

            calculator
                .on(nsHelper.namespaceAll('command-degree', pluginName), function () {
                    getMathsConfig().degree = true;
                    setupMathsEvaluator();
                })
                .on(nsHelper.namespaceAll('command-radian', pluginName), function () {
                    getMathsConfig().degree = false;
                    setupMathsEvaluator();
                });
        },

        /**
         * Called when the plugin is destroyed. Mostly when the host is destroyed itself.
         */
        destroy: function destroy() {
            var calculator = this.getCalculator();
            calculator
                .deleteCommand('degree')
                .deleteCommand('radian')
                .setState('degree', false)
                .setState('radian', false)
                .off('.' + pluginName);
        }
    });
});
