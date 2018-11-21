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
    'ui/maths/calculator/core/plugin',
    'tpl!ui/maths/calculator/plugins/screen/simpleScreen/term',
    'tpl!ui/maths/calculator/plugins/screen/simpleScreen/history',
    'tpl!ui/maths/calculator/plugins/screen/simpleScreen/defaultTemplate'
], function ($, _, __, nsHelper, scrollHelper, registeredTerms, pluginFactory, termTpl, historyTpl, defaultScreenTpl) {
    'use strict';

    var pluginName = 'simpleScreen';
    var varAnsName = 'ans';
    var reLeadingSpace = /^\s+/;
    var reErrorValue = /(NaN|[+-]?Infinity)/;
    var reAnsVar = new RegExp('\\b' + varAnsName + '\\b', 'g');
    var defaultValue = '0';

    var defaultConfig = {
        layout: defaultScreenTpl
    };

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var self = this;
            var calculator = this.getCalculator();

            /**
             * Checks if an expression contains an error value
             * @param {String} expression
             * @returns {Boolean}
             */
            function containsError(expression) {
                return reErrorValue.test(expression);
            }

            /**
             * Reset the current expression
             */
            function reset() {
                calculator.replace(defaultValue);
            }

            /**
             * Update the variable containing the last result, and keep track of the source expression.
             * @param {String} result
             */
            function store(result) {
                self.previous = calculator.getExpression();
                if (calculator.hasVariable(varAnsName)) {
                    self.previous = self.previous.replace(reAnsVar, calculator.getVariable(varAnsName));
                }
                calculator.setVariable(varAnsName, containsError(result) ? defaultValue : result);
            }

            /**
             * Erase the current expression and the history
             */
            function erase() {
                store(defaultValue);
                self.previous = defaultValue;
            }

            reset();
            erase();

            calculator
                .on(nsHelper.namespaceAll('termadd', pluginName), function (name, term) {
                    var expression, tokens;
                    // will replace the current term if:
                    // - it is a 0, and the term to add is not an operator nor a dot
                    // - it is the last result, and the term to add is not an operator
                    if (term.type !== 'operator') {
                        expression = calculator.getExpression().replace(reLeadingSpace, '');
                        tokens = calculator.getTokens();

                        if (tokens.length === 2 && tokens[0].type === 'NUM0' && name !== 'DOT') {
                            calculator.replace(expression.substr(1));
                        }
                        else if (
                            (tokens.length === 2 && tokens[0].value === varAnsName) ||
                            (tokens.length === 1 && tokens[0].type === 'term' &&
                                tokens[0].value !== varAnsName &&
                                tokens[0].value.substr(0, varAnsName.length) === varAnsName)) {
                            calculator.replace(expression.substr(varAnsName.length));
                        }
                    }
                })
                .after(nsHelper.namespaceAll('expressionchange', pluginName), function (expression) {
                    // ensure the displayed expression is at least a 0 (never be an empty string)
                    if (!expression.trim()) {
                        _.defer(reset);
                    }
                })
                .before(nsHelper.namespaceAll('evaluate', pluginName), function(ev, result) {
                    // when the expression is computed, we store the result as the last value
                    // then we replace the expression with a refined version (last value variable replaced)
                    store(result);
                    calculator.replace(self.previous);
                })
                .after(nsHelper.namespaceAll('evaluate', pluginName), function() {
                    // when the expression is computed, replace it with the result as a variable
                    calculator.replace(varAnsName);
                })
                .on(nsHelper.namespaceAll('clear', pluginName), reset)
                .on(nsHelper.namespaceAll('command-clearAll', pluginName), erase);
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
             * Transforms an tokenized expression, replacing values by the related labels.
             * @param {Array} tokens
             * @returns {String}
             */
            function transformTokens(tokens) {
                var expression = '';

                _.forEach(tokens, function (token) {
                    var term = {
                        type: token.type,
                        token: token.type,
                        value: token.value,
                        label: token.value
                    };

                    if (registeredTerms[token.type]) {
                        term.type = registeredTerms[token.type].type;
                        term.label = registeredTerms[token.type].label;
                    }
                    else if (token.type === 'term') {
                        if (calculator.hasVariable(token.value)) {
                            term.type = 'variable';
                            // always display the actual value of the last result variable
                            if (token.value === varAnsName) {
                                term.label = calculator.getVariable(varAnsName);
                            }
                        } else {
                            term.type = 'unknown';
                        }
                    }

                    expression += termTpl(term);
                });

                return expression;
            }

            /**
             * Auto scroll to the last child of a container
             * @param {jQuery} $container
             * @param {String} [sel]
             */
            function autoScroll($container, sel) {
                scrollHelper.scrollTo($container.find(':last-child ' + (sel || '')), $container);
            }

            /**
             * Updates the expression area
             * @param {Array} tokens
             */
            function showExpression(tokens) {
                self.controls.$expression.html(
                    transformTokens(tokens)
                );
                autoScroll(self.controls.$expression);
            }

            if (!_.isFunction(pluginConfig.layout)) {
                throw new TypeError('The screen plugin requires a template to render!');
            }

            this.$layout = $(pluginConfig.layout(_.defaults({
                expression: transformTokens(calculator.getTokens())
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
                        expression: transformTokens(tokenizer.tokenize(self.previous)),
                        result: transformTokens(tokenizer.tokenize(result))
                    }));
                    autoScroll(self.controls.$history, '.history-result');
                })
                .on(nsHelper.namespaceAll('syntaxerror', pluginName), function () {
                    calculator.setState('error', true);
                    showExpression(tokenizer.tokenize(calculator.getExpression() + '#'));
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
            calculator
                .off('.' + pluginName)
                .deleteVariable(varAnsName);
        }
    }, defaultConfig);
});
