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
 * Plugin that manages a navigation by tokens within the current expression
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'i18n',
    'util/namespace',
    'ui/maths/calculator/core/plugin'
], function (_, __, nsHelper, pluginFactory) {
    'use strict';

    var pluginName = 'stepNavigation';

    return pluginFactory({
        name: pluginName,

        /**
         * Called when the plugin is installing in its host.
         */
        install: function install() {
            var calculator = this.getCalculator();

            calculator
                .setCommand('stepMoveLeft', __('Move Left'), __('Move the cursor one step on the left'))
                .setCommand('stepMoveRight', __('Move Right'), __('Move the cursor one step on the right'))
                .setCommand('stepDeleteLeft', __('Delete Left'), __('Delete the term on the left side of the cursor'))
                .setCommand('stepDeleteRight', __('Delete Right'), __('Delete the term on the right side of the cursor'));
        },

        /**
         * Called when the plugin should be initialized.
         */
        init: function init() {
            var calculator = this.getCalculator();

            /**
             * Remove a sub-expresion from the expression
             * @param expression
             * @param position
             * @param length
             */
            function remove(expression, position, length) {
                if (length) {
                    expression = calculator.getExpression();
                    calculator
                        .setExpression(expression.substr(0, position) + expression.substr(position + length))
                        .setPosition(position);
                }
            }

            /**
             * Remove a token from the expression
             * @param expression
             * @param token
             */
            function removeToken(expression, token) {
                var from, length;
                if (token) {
                    expression = calculator.getExpression();
                    from = token.offset;
                    length = token.value.length;
                    while(from + length < expression.length && expression.charAt(from + length) === ' ') {
                        length ++;
                    }

                    remove(expression, from, length);
                }
            }

            calculator
                .on(nsHelper.namespaceAll('command-stepMoveLeft', pluginName), function () {
                    var position = calculator.getPosition();
                    var tokens = calculator.getTokens();
                    var index = calculator.getTokenIndex();
                    var token = calculator.getToken();
                    var offset = position;

                    if (token && position > 0) {
                        if (position === token.offset) {
                            if (index > 0) {
                                token = tokens[index - 1];
                            } else {
                                token = null;
                            }
                        }
                    } else {
                        token = null;
                    }
                    if (token) {
                        offset = token.offset;
                    } else {
                        offset = 0;
                    }

                    if (offset !== position) {
                        calculator.setPosition(offset);
                    }
                })
                .on(nsHelper.namespaceAll('command-stepMoveRight', pluginName), function () {
                    var expression = calculator.getExpression();
                    var position = calculator.getPosition();
                    var tokens = calculator.getTokens();
                    var index = calculator.getTokenIndex();
                    var token = calculator.getToken();
                    var offset = expression.length;

                    if (token && index < tokens.length - 1) {
                        token = tokens[index + 1];
                        if (token) {
                            offset = token.offset;
                        }
                    }

                    if (offset !== position) {
                        calculator.setPosition(offset);
                    }
                })
                .on(nsHelper.namespaceAll('command-stepDeleteLeft', pluginName), function () {
                    var expression = calculator.getExpression();
                    var position = calculator.getPosition();
                    var tokens = calculator.getTokens();
                    var index = calculator.getTokenIndex();
                    var token = calculator.getToken();

                    if (token) {
                        if (position > token.offset) {
                            removeToken(expression, token);
                        } else {
                            if (index > 0 ) {
                                removeToken(expression, tokens[index - 1]);
                            } else if (position > 0) {
                                remove(expression, 0, token.offset);
                            }
                        }
                    }
                })
                .on(nsHelper.namespaceAll('command-stepDeleteRight', pluginName), function () {
                    var expression = calculator.getExpression();
                    var position = calculator.getPosition();
                    var token = calculator.getToken();

                    if (token && position >= token.offset && position < token.offset + token.value.length) {
                        removeToken(expression, token);
                    }
                });
        },

        /**
         * Called when the plugin is destroyed. Mostly when the host is destroyed itself.
         */
        destroy: function destroy() {
            var calculator = this.getCalculator();
            calculator
                .deleteCommand('stepMoveLeft')
                .deleteCommand('stepMoveRight')
                .deleteCommand('stepDeleteLeft')
                .deleteCommand('stepDeleteRight')
                .off('.' + pluginName);
        }
    });
});
