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
 * Defines the base component that will host the calculator UI and link it to the engine.
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/promise',
    'core/collections',
    'ui/component',
    'ui/maths/calculator/areaBroker',
    'ui/maths/calculator/terms',
    'util/mathsEvaluator',
    'util/namespace',
    'tpl!ui/maths/calculator/tpl/board'
], function ($, _, __, Promise, collections, componentFactory, areaBrokerFactory, registeredTerms, mathsEvaluatorFactory, nsHelper, boardTpl) {
    'use strict';

    /**
     * Default config values
     * @type {Object}
     */
    var defaultConfig = {
        expression: '',
        position: 0
    };

    /**
     * The internal namespace for built-in events listeners
     * @type {String}
     */
    var ns = 'calculator';

    /**
     * Build the basic UI for a calculator
     * @param {jQuery|HTMLElement|String} $container
     * @param {Function[]} pluginFactories
     * @param {Object} [config]
     * @param {String} [config.expression=''] - The initial expression
     * @param {Number} [config.position=0] - The initial position in the expression
     * @param {Object} [config.maths] - Optional config for the maths evaluator (@see util/mathsEvaluator)
     * @returns {calculator}
     */
    function calculatorBoardFactory($container, pluginFactories, config) {
        /**
         * Maths expression parser.
         * @type {Function}
         */
        var mathsEvaluator;

        /**
         * Keep the area broker instance
         * @see ui/maths/calculator/areaBroker
         */
        var areaBroker;

        /**
         * @type {Object} the registered plugins
         */
        var plugins = {};

        /**
         * The current expression
         * @type {String}
         */
        var expression = '';

        /**
         * A list of variables that can be used in the expression
         * @type {Map}
         */
        var variables = new collections.Map();

        /**
         * A list of registered commands that can be used inside the calculator
         * @type {Map}
         */
        var commands = new collections.Map();

        /**
         * The current position in the current expression (i.e. the position of the caret)
         * @type {Number}
         */
        var position = 0;

        /**
         *
         * @type {Object}}
         */
        var calculatorApi = {
            /**
             * Returns the current expression
             * @returns {String}
             */
            getExpression: function getExpression() {
                return expression;
            },

            /**
             * Changes the current expression
             * @param {String} expr
             * @returns {calculator}
             * @fires expressionchange after the expression has been changed
             */
            setExpression: function setExpression(expr) {
                expression = String(expr || '');
                /**
                 * @event expressionchange
                 * @param {String} expression
                 */
                this.trigger('expressionchange', expression);
                return this;
            },

            /**
             * Gets the current position inside the expression
             * @returns {Number}
             */
            getPosition: function getPosition() {
                return position;
            },

            /**
             * Sets the current position inside the expression
             * @param {Number|String} pos
             * @returns {calculator}
             * @fires positionchange after the position has been changed
             */
            setPosition: function setPosition(pos) {
                position = Math.max(0, Math.min(parseInt(pos, 10) || 0, expression.length));
                /**
                 * @event positionchange
                 * @param {Number} position
                 */
                this.trigger('positionchange', position);
                return this;
            },

            /**
             * Gets a variable defined for the expression.
             * @param {String} name - The variable name
             * @returns {String} The value. Can be another expression.
             */
            getVariable: function getVariable(name) {
                return variables.get(name);
            },

            /**
             * Sets a variable that can be used by the expression.
             * @param {String} name - The variable name
             * @param {String} value - The value. Can be another expression.
             * @returns {calculator}
             * @fires variableadd after the variable has been set
             */
            setVariable: function setVariable(name, value) {
                variables.set(name, value);
                /**
                 * @event variableadd
                 * @param {String} name
                 * @param {String} value
                 */
                this.trigger('variableadd', name, value);
                return this;
            },

            /**
             * Deletes a variable defined for the expression.
             * @param {String} name - The variable name
             * @returns {calculator}
             * @fires variabledelete after the variable has been deleted
             */
            deleteVariable: function deleteVariable(name) {
                variables.delete(name);
                /**
                 * @event variabledelete
                 * @param {String} name
                 */
                this.trigger('variabledelete', name);
                return this;
            },

            /**
             * Gets the list of variables defined for the expression.
             * @returns {Object} The list of defined variables.
             */
            getVariables: function getVariables() {
                var defs = {};
                variables.forEach(function (value, name) {
                    defs[name] = value;
                });
                return defs;
            },

            /**
             * Sets a list of variables that can be used by the expression.
             * @param {Object} defs - A list variables to set.
             * @returns {calculator}
             * @fires variableadd after each variable has been set
             */
            setVariables: function setVariables(defs) {
                var self = this;
                _.forEach(defs, function (value, name) {
                    self.setVariable(name, value);
                });
                return this;
            },

            /**
             * Deletes all variables defined for the expression.
             * @returns {calculator}
             * @fires variabledelete after the variables has been deleted
             */
            deleteVariables: function deleteVariables() {
                variables.clear();
                /**
                 * @event variabledelete
                 * @param {null} name
                 */
                this.trigger('variabledelete', null);
                return this;
            },

            /**
             * Registers a command
             * @param {String} name
             * @param {String} [label]
             * @param {String} [description]
             * @returns {calculator}
             * @fires commandadd after the command has been set
             */
            setCommand: function setCommand(name, label, description) {
                commands.set(name, {
                    name: name,
                    label: label,
                    description: description
                });
                /**
                 * @event commandadd
                 * @param {String} name
                 */
                this.trigger('commandadd', name);
                return this;
            },

            /**
             * Gets the definition of a registered command
             * @returns {Object} The registered command
             */
            getCommand: function getCommand(name) {
                return commands.get(name);
            },

            /**
             * Gets the list of registered commands
             * @returns {Object} The list of registered commands
             */
            getCommands: function getCommands() {
                var defs = {};
                commands.forEach(function (value, name) {
                    defs[name] = value;
                });
                return defs;
            },

            /**
             * Checks if a command is registered
             * @param {String} name
             * @returns {Boolean}
             */
            hasCommand: function hasCommand(name) {
                return commands.has(name);
            },

            /**
             * Delete a registered command
             * @param {String} name
             * @returns {calculator}
             * @fires commanddelete after the command has been deleted
             */
            deleteCommand: function deleteCommand(name) {
                commands.delete(name);
                /**
                 * @event commanddelete
                 * @param {String} name
                 */
                this.trigger('commanddelete', name);
                return this;
            },

            /**
             * Inserts a term in the expression at the current position
             * @param {String} name - The name of the term to insert
             * @param {Object} term - The definition of the term to insert
             * @returns {calculator}
             * @fires termerror if the term to add is invalid
             * @fires termadd when the term has been added
             * @fires termadd-<name> when the term has been added
             */
            addTerm: function addTerm(name, term) {
                var value;
                if (!_.isPlainObject(term) || 'undefined' === typeof term.value) {
                    /**
                     * @event termerror
                     * @param {TypeError} err
                     */
                    return this.trigger('termerror', new TypeError('Invalid term: ' + name));
                }

                value = term.value;
                if (term.type === 'function') {
                    value += ' ';
                }
                this.setExpression(expression.substr(0, position) + value + expression.substr(position));
                this.setPosition(position + value.length);

                /**
                 * @event termadd
                 * @param {String} name - The name of the added term
                 * @param {Object} term - The descriptor of the added term
                 */
                this.trigger('termadd', name, term);

                /**
                 * @event termadd-<name>
                 * @param {Object} term - The descriptor of the added term
                 */
                this.trigger('termadd-' + name, term);

                return this;
            },

            /**
             * Inserts a term in the expression at the current position
             * @param {String} name - The name of the term to insert
             * @returns {calculator}
             * @fires termerror if the term to add is invalid
             * @fires termadd when the term has been added
             */
            useTerm: function useTerm(name) {
                var term = registeredTerms[name];
                if ('undefined' === typeof term) {
                    /**
                     * @event termerror
                     * @param {TypeError} err
                     */
                    return this.trigger('termerror', new TypeError('Invalid term: ' + name));
                }

                return this.addTerm(name, term);
            },

            /**
             * Inserts a variable as a term in the expression at the current position
             * @param {String} name - The name of the variable to insert
             * @returns {calculator}
             * @fires termerror if the term to add is invalid
             * @fires termadd when the term has been added
             */
            useVariable: function useVariable(name) {
                if (!variables.has(name)) {
                    /**
                     * @event termerror
                     * @param {TypeError} err
                     */
                    return this.trigger('termerror', new TypeError('Invalid variable: ' + name));
                }

                return this.addTerm('VAR_' + name.toUpperCase(), {
                    label: name,
                    value: name,
                    type: 'variable',
                    description: __('Variable %s', name)
                });
            },

            /**
             * Calls a command
             * @param {String} name - The name of the called command
             * @param {*} ... - additional params for the command
             * @returns {calculator}
             * @fires command with the name and the parameters of the command
             * @fires command-<name> with the parameters of the command
             * @fires commanderror if the command is invalid
             */
            useCommand: function useCommand(name) {
                if (!commands.has(name)) {
                    /**
                     * @event commanderror
                     * @param {TypeError} err
                     */
                    return this.trigger('commanderror', new TypeError('Invalid command: ' + name));
                }

                /**
                 * @event command
                 * @param {String} name - The name of the called command
                 * @param {*} ... - additional params for the command
                 */
                this.trigger.apply(this, ['command'].concat([].slice.call(arguments)));

                /**
                 * @event command-<name>
                 * @param {*} ... - additional params for the command
                 */
                this.trigger.apply(this, ['command-' + name].concat([].slice.call(arguments, 1)));

                return this;
            },

            /**
             * Replaces the expression and move the cursor at the end.
             * @param {String} expr - The new expression to set
             * @returns {calculator}
             * @fires replace after the expression has been replaced
             */
            replace: function replace(expr) {
                var oldExpression = this.getExpression();
                var oldPosition = this.getPosition();

                this.setExpression(expr)
                    .setPosition(this.getExpression().length);

                /**
                 * @event replace
                 * @param {String} expression - the replaced expression
                 * @param {Number} position - the replaced position
                 */
                this.trigger('replace', oldExpression, oldPosition);

                return this;
            },

            /**
             * Clears the expression
             * @returns {calculator}
             * @fires clear after the expression has been cleared
             */
            clear: function clear() {
                this.setExpression('')
                    .setPosition(0);

                /**
                 * @event clear
                 */
                this.trigger('clear');

                return this;
            },

            /**
             * Evaluates the current expression
             * @returns {String|null}
             * @fires evaluate when the expression has been evaluated
             * @fires syntaxerror when the expression contains an error
             */
            evaluate: function evaluate() {
                var result = null;
                try {
                    result = mathsEvaluator(this.getExpression(), this.getVariables());
                    /**
                     * @event evaluate
                     * @param {String} result
                     */
                    this.trigger('evaluate', result);
                } catch (e) {
                    /**
                     * @event syntaxerror
                     * @param {Error} err
                     */
                    this.trigger('syntaxerror', e);
                }
                return result;
            },

            /**
             * Runs a method in all plugins
             *
             * @param {String} method - the method to run
             * @returns {Promise} once that resolve when all plugins are done
             */
            runPlugins: function runPlugins(method) {
                var execStack = [];

                _.forEach(plugins, function (plugin) {
                    if (_.isFunction(plugin[method])) {
                        execStack.push(plugin[method]());
                    }
                });

                return Promise.all(execStack);
            },

            /**
             * Gets the calculator plugins
             * @returns {Array} the plugins
             */
            getPlugins: function getPlugins() {
                return _.toArray(plugins);
            },

            /**
             * Gets a plugin
             * @param {String} name - the plugin name
             * @returns {plugin} the plugin
             */
            getPlugin: function getPlugin(name) {
                return plugins[name];
            },

            /**
             * Gets access to the areaBroker
             * @returns {areaBroker}
             */
            getAreaBroker: function getAreaBroker() {
                return areaBroker;
            },

            /**
             * Setups the maths evaluator
             * @returns {calculator}
             */
            setupMathsEvaluator: function setupMathsEvaluator() {
                mathsEvaluator = mathsEvaluatorFactory(this.getConfig().maths);
                return this;
            }
        };

        /**
         * @typedef {component} calculator
         */
        var calculator = componentFactory(calculatorApi, defaultConfig)
            .setTemplate(boardTpl)
            .before('init', function () {
                this.setupMathsEvaluator();
                if (this.config.expression) {
                    this.setExpression(this.config.expression);
                }
                if (this.config.position) {
                    this.setPosition(this.config.position);
                }

                // built-in commands
                this.setCommand('clear', __('Clear'), __('Clear expression'))
                    .setCommand('clearAll', __('Clear All'), __('Clear all data'))
                    .setCommand('execute', __('Execute'), __('Compute the expression'))
                    .on(nsHelper.namespaceAll('command-execute', ns), this.evaluate.bind(this))
                    .on(nsHelper.namespaceAll('command-clear command-clearAll', ns), this.clear.bind(this));
            })
            .after('init', function () {
                this.render($container);
            })
            .before('render', function () {
                var self = this;
                var $element = this.getElement();

                areaBroker = areaBrokerFactory($element, {
                    screen: $element.find('.screen'),     // where the expressions and their result are rendered
                    input: $element.find('.input'),       // where the expressions are input
                    keyboard: $element.find('.keyboard')  // the keyboard area that should provide a way to interact with the calculator
                });

                _.forEach(pluginFactories, function (pluginFactory) {
                    var plugin = pluginFactory(self, self.getAreaBroker());
                    plugins[plugin.getName()] = plugin;
                });

                return this.runPlugins('install')
                    .then(function () {
                        self.runPlugins('init');
                    })
                    .then(function () {
                        self.runPlugins('render');
                    })
                    .then(function () {
                        /**
                         * @event ready
                         */
                        self.trigger('ready');
                    });
            })
            .on('destroy', function () {
                var self = this;
                return this.runPlugins('destroy')
                    .then(function () {
                        self.off('.' + ns);
                        areaBroker = null;
                        mathsEvaluator = null;
                        variables.clear();
                        plugins = {};
                    });
            });

        _.defer(function () {
            calculator.init(config);
        });

        return calculator;
    }

    return calculatorBoardFactory;
});
