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
    'ui/maths/calculator/core/terms',
    'ui/maths/calculator/core/tokens',
    'ui/maths/calculator/core/plugin'
], function (_, __, nsHelper, registeredTerms, tokensHelper, pluginFactory) {
    'use strict';

    var pluginName = 'history';

    /**
     * Defines an entry in the history
     * @typedef {Object} historyEntry
     * @property {String} expression - The expression that has been extracted when the entry was created
     * @property {Object} variables - The list of variables that has been extracted when the entry was created
     * @property {String} [current] - The current edited expression if the entry is modified
     */

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
            var history, cursor, current;

            /**
             * Creates an entry from the current state
             * @returns {historyEntry}
             */
            function getCurrentState() {
                return {
                    expression: calculator.getExpression(),
                    variables: calculator.getVariables(),
                    current: null
                };
            }

            /**
             * Clears the entire history
             */
            function reset() {
                current = getCurrentState();
                history = [];
                cursor = 0;
            }

            /**
             * Retrieves a memory entry in the history
             * @param {Number} position
             * @returns {Object|null}
             */
            function getMemoryAt(position) {
                if (position >= 0 && position < history.length) {
                    return history[position];
                }
                else if (position === history.length) {
                    return current;
                }
                return null;
            }

            /**
             * Reminds an expression from the history
             * @param {Number} position
             */
            function remind(position) {
                var memory = getMemoryAt(position);

                // keep the current expression in the memory, in case the user goes back to it
                if (cursor === history.length && position !== cursor) {
                    current = getCurrentState();
                } else {
                    history[cursor].current = calculator.getExpression();
                }

                // restore an expression from the history at the wanted position
                if (memory) {
                    cursor = position;
                    if (memory.variables) {
                        calculator.setVariables(memory.variables);
                    }
                    calculator.replace(memory.current || memory.expression);
                    memory.current = null;
                }
            }

            /**
             * Adds a memory entry in the history from the current expression
             */
            function push() {
                var last = getMemoryAt(history.length - 1);
                var memory = getMemoryAt(cursor);
                if (!last || calculator.getExpression() !== last.expression) {
                    history.push(getCurrentState());
                }
                if (memory) {
                    memory.current = null;
                }
                cursor = history.length;
            }

            reset();
            calculator
                .on(nsHelper.namespaceAll('evaluate', pluginName), push)
                .on(nsHelper.namespaceAll('command-historyClear command-clearAll destroy', pluginName), reset)
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
                    calculator.trigger('history', _.pluck(history, 'expression'));
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
