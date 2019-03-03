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
 * Plugin that manages a keyboard for the calculator, with configurable layout.
 * Each key must declare the target command, using DOM attributes:
 * - data-command: the name of the command to call
 * - data-param: the optional parameter to apply to the command
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'util/namespace',
    'ui/maths/calculator/core/plugin',
    'ui/maths/calculator/core/labels',
    'tpl!ui/maths/calculator/plugins/keyboard/templateKeyboard/defaultTemplate'
], function ($, _, nsHelper, pluginFactory, labels, defaultKeyboardTpl) {
    'use strict';

    var pluginName = 'templateKeyboard';

    var defaultConfig = {
        layout: defaultKeyboardTpl
    };

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            // required by the plugin factory to validate this plugin
        },

        /**
         * Called when the plugin should be rendered.
         */
        render: function render() {
            var calculator = this.getCalculator();
            var areaBroker = calculator.getAreaBroker();
            var pluginConfig = this.getConfig();
            var templateConfig = _.merge({labels: labels}, pluginConfig);

            if (!_.isFunction(pluginConfig.layout)) {
                throw new TypeError('The keyboard plugin requires a template to render!');
            }

            this.$layout = $(pluginConfig.layout(templateConfig))
                .on(nsHelper.namespaceAll('click', pluginName), '.key', function () {
                    var $key = $(this).closest('.key');
                    var command = $key.data('command');
                    var param = $key.data('param');
                    if (command) {
                        calculator.useCommand(command, param);
                    }
                });

            areaBroker.getKeyboardArea().append(this.$layout);
        },

        /**
         * Called when the plugin is destroyed. Mostly when the host is destroyed itself.
         */
        destroy: function destroy() {
            var calculator = this.getCalculator();
            if (this.$layout) {
                this.$layout.off('.' + pluginName).remove();
                this.$layout = null;
            }
            calculator.off('.' + pluginName);
        }
    }, defaultConfig);
});
