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
 * Plugin that manages a simple screen for the calculator, with configurable layout.
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'util/namespace',
    'ui/scroller',
    'ui/maths/calculator/core/terms',
    'ui/maths/calculator/core/tokens',
    'ui/maths/calculator/core/expression',
    'ui/maths/calculator/core/plugin',
    'tpl!ui/maths/calculator/plugins/screen/simpleScreen/history',
    'tpl!ui/maths/calculator/plugins/screen/simpleScreen/defaultTemplate'
], function (
    $,
    _,
    __,
    nsHelper,
    scrollHelper,
    registeredTerms,
    tokensHelper,
    expressionHelper,
    pluginFactory,
    historyTpl,
    defaultScreenTpl
) {
    'use strict';

    var pluginName = 'simpleScreen';

    /**
     * Default displayed value
     * @type {String}
     */
    var defaultExpression = '0';

    /**
     * Default plugin config
     * @type {Object}
     */
    var defaultConfig = {
        // the layout of the screen
        layout: defaultScreenTpl,

        // number of decimal digits shown for decimal numbers
        decimalDigits: 5
    };

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var calculator = this.getCalculator();

            /**
             * Reset the current expression
             */
            function reset() {
                calculator.replace(calculator.getConfig().expression || defaultExpression);
            }

            reset();

            calculator
                .after(nsHelper.namespaceAll('expressionchange', pluginName), function (expression) {
                    // ensure the displayed expression is at least a 0 (never be an empty string)
                    if (!expression.trim()) {
                        _.defer(reset);
                    }
                })
                .after(nsHelper.namespaceAll('evaluate', pluginName), function() {
                    // when the expression is computed, replace it with the result as a variable
                    calculator.replace(registeredTerms.ANS.value);
                })
                .on(nsHelper.namespaceAll('clear', pluginName), reset);
        },

        /**
         * Called when the plugin should be rendered.
         */
        render: function render() {
            var self = this;
            var calculator = this.getCalculator();
            var areaBroker = calculator.getAreaBroker();
            var pluginConfig = this.getConfig();
            var tokenizer = calculator.getTokenizer();

            /**
             * Auto scroll to the last child of a container
             * @param {jQuery} $container
             * @param {String} [sel]
             */
            function autoScroll($container, sel) {
                scrollHelper.scrollTo($container.find(':last-child ' + (sel || '')), $container);
            }

            /**
             * Renders the expression into a string
             * @param {String|Object|token[]} expression
             * @returns {String}
             */
            function renderExpression(expression) {
                var variables = expressionHelper.roundLastResultVariable(
                    calculator.getVariables(),
                    pluginConfig.decimalDigits
                );
                return expressionHelper.render(expression, variables, tokenizer);
            }

            /**
             * Updates the expression area
             * @param {String|Object|token[]} tokens
             */
            function showExpression(tokens) {
                self.controls.$expression.html(renderExpression(tokens));
                autoScroll(self.controls.$expression);
            }

            if (!_.isFunction(pluginConfig.layout)) {
                throw new TypeError('The screen plugin requires a template to render!');
            }

            this.$layout = $(pluginConfig.layout(_.defaults({
                expression: renderExpression(calculator.getTokens())
            }, pluginConfig)));

            this.controls = {
                $history: this.$layout.find('.history'),
                $expression: this.$layout.find('.expression')
            };

            calculator
                .on(nsHelper.namespaceAll('command-clearAll', pluginName), function () {
                    self.controls.$history.empty();
                })
                .on(nsHelper.namespaceAll('expressionchange', pluginName), function () {
                    calculator.setState('error', false);
                    showExpression(calculator.getTokens());
                })
                .on(nsHelper.namespaceAll('evaluate', pluginName), function (result) {
                    self.controls.$history.html(historyTpl({
                        expression: renderExpression(calculator.getTokens()),
                        result: renderExpression(result)
                    }));
                    autoScroll(self.controls.$history, '.history-result');
                })
                .after(nsHelper.namespaceAll('evaluate', pluginName), function(result) {
                    if (expressionHelper.containsError(result.value)) {
                        showExpression(result);
                    }
                })
                .on(nsHelper.namespaceAll('syntaxerror', pluginName), function () {
                    calculator.setState('error', true);
                    showExpression(calculator.getExpression() + registeredTerms.ERROR.value);
                });

            areaBroker.getScreenArea().append(this.$layout);
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
            this.controls = null;
            calculator.off('.' + pluginName);
        }
    }, defaultConfig);
});
