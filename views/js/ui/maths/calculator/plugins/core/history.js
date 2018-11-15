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
 * Plugin that manages an history of evaluated expressions in the calculator
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'util/namespace',
    'ui/maths/calculator/core/plugin'
], function (_, __, nsHelper, pluginFactory) {
    'use strict';

    var pluginName = 'history';

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin is installing in its host.
         */
        install: function install() {
            var calculator = this.getCalculator();

            calculator
                .setCommand('historyClear', __('Clear History'), __('Clear history'))
                .setCommand('historyGet', __('Get History'), __('Get the history list'))
                .setCommand('historyUp', __('Previous'), __('Remind the previous expression in the history'))
                .setCommand('historyDown', __('Next'), __('Remind the next expression in the history'));
        },

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var calculator = this.getCalculator();
            var history, cursor;

            function reset() {
                history = [];
                cursor = 0;
            }

            function remind(position) {
                var expression;
                if (position >= 0 && position <= history.length) {
                    cursor = position;
                    expression = history[cursor] || '';
                    calculator.replace(expression);
                }
            }

            reset();
            calculator
                .before(nsHelper.namespaceAll('evaluate', pluginName), function () {
                    var expression = calculator.getExpression();
                    if (cursor < history.length) {
                        history[cursor] = expression;
                    } else if (expression !== history[history.length - 1]) {
                        history.push(expression);
                    }
                    cursor = history.length;
                })
                .on(nsHelper.namespaceAll('command-historyClear command-clearAll', pluginName), reset)
                .on(nsHelper.namespaceAll('command-historyUp', pluginName), function () {
                    remind(cursor - 1);
                })
                .on(nsHelper.namespaceAll('command-historyDown', pluginName), function () {
                    remind(cursor + 1);
                })
                .on(nsHelper.namespaceAll('command-historyGet', pluginName), function () {
                    /**
                     * @event history
                     * @param {Array} history - The current history list
                     */
                    calculator.trigger('history', history.slice());
                })
                .on(nsHelper.namespaceAll('destroy', pluginName), function () {
                    reset();
                });
        },

        /**
         * Called when the plugin is destroyed. Mostly when the host is destroyed itself.
         */
        destroy: function destroy() {
            var calculator = this.getCalculator();
            calculator
                .deleteCommand('historyClear')
                .deleteCommand('historyUp')
                .deleteCommand('historyDown')
                .deleteCommand('historyGet')
                .off('.' + pluginName);
        }
    });
});
