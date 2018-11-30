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
 * Plugin that manages a simple value reminder in the calculator
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'util/namespace',
    'ui/maths/calculator/core/plugin'
], function (_, __, nsHelper, pluginFactory) {
    'use strict';

    var pluginName = 'remind';
    var varRemindName = 'mem';
    var varLastName = 'last';

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin is installing in its host.
         */
        install: function install() {
            var calculator = this.getCalculator();

            calculator
                .setCommand('remind', __('Remind'), __('Remind the recorded value'))
                .setCommand('remindLast', __('Remind Last'), __('Remind the last value'))
                .setCommand('remindStore', __('Store'), __('Store the value a variable'))
                .setCommand('remindClear', __('Clear'), __('Clear the stored variables'));
        },

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var calculator = this.getCalculator();

            calculator
                .on(nsHelper.namespaceAll('evaluate', pluginName), function (result) {
                    calculator.setVariable(varLastName, result);
                })
                .on(nsHelper.namespaceAll('command-remind', pluginName), function () {
                    if (calculator.hasVariable(varRemindName)) {
                        calculator.useVariable(varRemindName);
                    }
                })
                .on(nsHelper.namespaceAll('command-remindLast', pluginName), function () {
                    if (calculator.hasVariable(varLastName)) {
                        calculator.useVariable(varLastName);
                    }
                })
                .on(nsHelper.namespaceAll('command-remindStore', pluginName), function () {
                    if (calculator.hasVariable(varLastName)) {
                        calculator.setVariable(varRemindName, calculator.getVariable(varLastName));
                    }
                })
                .on(nsHelper.namespaceAll('command-remindClear command-clearAll destroy', pluginName), function () {
                    if (calculator.hasVariable(varRemindName)) {
                        calculator.deleteVariable(varRemindName);
                    }
                })
                .on(nsHelper.namespaceAll('destroy', pluginName), function () {
                    if (calculator.hasVariable(varLastName)) {
                        calculator.deleteVariable(varLastName);
                    }
                });
        },

        /**
         * Called when the plugin is destroyed. Mostly when the host is destroyed itself.
         */
        destroy: function destroy() {
            var calculator = this.getCalculator();
            calculator
                .deleteCommand('remind')
                .deleteCommand('remindLast')
                .deleteCommand('remindStore')
                .deleteCommand('remindClear')
                .off('.' + pluginName);
        }
    });
});
