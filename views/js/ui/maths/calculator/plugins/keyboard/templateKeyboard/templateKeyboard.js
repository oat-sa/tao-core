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
    'ui/maths/calculator/plugin',
    'util/namespace',
    'tpl!ui/maths/calculator/plugins/keyboard/templateKeyboard/defaultTemplate'
], function ($, pluginFactory, nsHelper, defaultKeyboardTpl) {
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

            this.$layout = $(pluginConfig.layout(pluginConfig));
            areaBroker.getKeyboardArea().append(this.$layout);
            this.$layout.on(nsHelper.namespaceAll('click', pluginName), '.key', function() {
                var $key = $(this).closest('.key');
                var command = $key.data('command');
                var param = $key.data('param');
                if (command) {
                    calculator.useCommand(command, param);
                }
            });
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
