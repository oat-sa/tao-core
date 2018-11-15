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
    'util/namespace',
    'ui/maths/calculator/core/terms',
    'ui/maths/calculator/core/plugin',
    'tpl!ui/maths/calculator/plugins/screen/simpleScreen/term',
    'tpl!ui/maths/calculator/plugins/screen/simpleScreen/simpleScreen'
], function ($, _, nsHelper, registeredTerms, pluginFactory, termTpl, simpleScreenTpl) {
    'use strict';

    var pluginName = 'simpleScreen';
    var varAnsName = 'ans';
    var reLeadingSpace = /^\s+/;

    var defaultConfig = {
        layout: simpleScreenTpl
    };

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var calculator = this.getCalculator();

            function reset() {
                calculator.replace('0');
            }

            function store(result) {
                calculator.setVariable(varAnsName, result);
            }

            function erase() {
                store('0');
            }

            reset();
            erase();

            calculator
                .on(nsHelper.namespaceAll('termadd', pluginName), function (name, term) {
                    var expression, tokens;
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
                .on(nsHelper.namespaceAll('evaluate', pluginName), store)
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

            /**
             * Transforms an expression, replacing values by the related labels.
             * @returns {String}
             */
            function transformExpression() {
                var tokens = calculator.getTokens();
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

            this.$layout = $(pluginConfig.layout(_.defaults({
                expression: transformExpression()
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
                    self.controls.$expression.html(transformExpression());
                })
                .on(nsHelper.namespaceAll('evaluate', pluginName), function () {
                    self.controls.$history.html(transformExpression());
                    calculator.replace(varAnsName);
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
