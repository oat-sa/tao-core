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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'lib/decimal/decimal',
    'lib/expr-eval/expr-eval',
    'i18n'
], function (_, Decimal, exprEval) {
    'use strict';

    var Parser = exprEval.Parser;

    /**
     * Defaults config for the Decimal constructor
     * @type {Object}
     */
    var defaultConfig = {
        defaults: true
    };

    /**
     * Builds a maths expression parser.
     * For more info on the supported API:
     * - @see https://github.com/silentmatt/expr-eval
     * - @see https://github.com/MikeMcl/decimal.js
     *
     * @example
     * var evaluate = mathsEvaluatorFactory();
     *
     * // simple arithmetic
     * var result = evaluate("3*4+30"); // will return '42';
     *
     * // advanced expression
     * var result = evaluate("(10! - 5!) * 4 * (18 / 4) + sqrt(56^4)"); // will return '65319376';
     *
     * // parametric expression
     * var result = evaluate("2*a*x+b", {a:5, x:3, b:15}); // will return '45';
     *
     * @param {Object} [config] - Config for the Decimal constructor.
     * @param {Number} [config.precision=20] - The maximum number of significant digits of the result of an operation.
     * @param {Number} [config.rounding=4] - The default rounding mode used when rounding the result of an operation to precision significant digits.
     * @param {Number} [config.toExpNeg=-7] - The negative exponent value at and below which toString returns exponential notation.
     * @param {Number} [config.toExpPos=21] - The positive exponent value at and above which toString returns exponential notation.
     * @param {Number} [config.maxE=9e15] - The positive exponent limit, i.e. the exponent value above which overflow to Infinity occurs.
     * @param {Number} [config.minE=-9e15] - The negative exponent limit, i.e. the exponent value below which underflow to zero occurs.
     * @param {Number} [config.modulo=1] - The modulo mode used when calculating the modulus: a mod n.
     * @param {Number} [config.crypto=false] - The value that determines whether cryptographically-secure pseudo-random number generation is used.
     * @returns {Function<expression, variables>} - The maths expression parser
     */
    function mathsEvaluatorFactory(config) {
        var parser = new Parser();
        var ConfiguredDecimal = Decimal.set(config || defaultConfig);

        /**
         * Map expr-eval API to decimal.js
         * @type {Object}
         */
        var mapAPI = {
            unary: [
                {
                    entry: 'sin',
                    mapTo: 'sin'
                },
                {
                    entry: 'cos',
                    mapTo: 'cos'
                },
                {
                    entry: 'tan',
                    mapTo: 'tan'
                },
                {
                    entry: 'asin',
                    mapTo: 'asin'
                },
                {
                    entry: 'acos',
                    mapTo: 'acos'
                },
                {
                    entry: 'atan',
                    mapTo: 'atan'
                },
                {
                    entry: 'sinh',
                    mapTo: 'sinh'
                },
                {
                    entry: 'cosh',
                    mapTo: 'cosh'
                },
                {
                    entry: 'tanh',
                    mapTo: 'tanh'
                },
                {
                    entry: 'asinh',
                    mapTo: 'asinh'
                },
                {
                    entry: 'acosh',
                    mapTo: 'acosh'
                },
                {
                    entry: 'atanh',
                    mapTo: 'atanh'
                },
                {
                    entry: 'sqrt',
                    mapTo: 'sqrt'
                },
                {
                    entry: 'cbrt',
                    mapTo: 'cbrt'
                },
                {
                    entry: 'log',
                    mapTo: 'log'
                },
                {
                    entry: 'ln',
                    mapTo: 'ln'
                },
                {
                    entry: 'lg',
                    mapTo: 'log10'
                },
                {
                    entry: 'log10',
                    mapTo: 'log10'
                },
                {
                    entry: 'abs',
                    mapTo: 'abs'
                },
                {
                    entry: 'ceil',
                    mapTo: 'ceil'
                },
                {
                    entry: 'floor',
                    mapTo: 'floor'
                },
                {
                    entry: 'round',
                    mapTo: 'round'
                },
                {
                    entry: 'trunc',
                    mapTo: 'trunc'
                },
                {
                    entry: '-',
                    mapTo: 'neg'
                },
                {
                    entry: '+',
                    filter: decimalNumber
                },
                {
                    entry: 'exp',
                    mapTo: 'exp'
                },
                {
                    entry: 'not',
                    filter: function(a) {
                        return !native(a);
                    }
                },
                {
                    entry: '!',
                    filter: useOrigin
                }
            ],
            binary: [
                {
                    entry: '+',
                    mapTo: 'add'
                },
                {
                    entry: '-',
                    mapTo: 'sub'
                },
                {
                    entry: '*',
                    mapTo: 'mul'
                },
                {
                    entry: '/',
                    mapTo: 'div'
                },
                {
                    entry: '%',
                    mapTo: 'mod'
                },
                {
                    entry: '^',
                    mapTo: 'pow'
                },
                {
                    entry: '==',
                    mapTo: 'equals'
                },
                {
                    entry: '!=',
                    filter: function (a, b) {
                        return !binaryOperator('equals', a, b);
                    }
                },
                {
                    entry: '>',
                    mapTo: 'gt'
                },
                {
                    entry: '<',
                    mapTo: 'lt'
                },
                {
                    entry: '>=',
                    mapTo: 'gte'
                },
                {
                    entry: '<=',
                    mapTo: 'lte'
                },
                {
                    entry: 'and',
                    filter: function(a, b) {
                        return Boolean(native(a) && native(b));
                    }
                },
                {
                    entry: 'or',
                    filter: function(a, b) {
                        return Boolean(native(a) || native(b));
                    }
                },
                {
                    entry: 'in',
                    filter: function(array, obj) {
                        obj = native(obj);
                        return 'undefined' !== typeof _.find(array, function(el) {
                            return native(el) === obj;
                        });
                    }
                }
            ],
            ternaryOps: [{
                entry: '?',
                filter: useOrigin
            }],
            functions: [
                {
                    entry: 'random',
                    filter: function(dp) {
                        return ConfiguredDecimal.random(dp);
                    }
                },
                {
                    entry: 'fac',
                    filter: useOrigin
                },
                {
                    entry: 'min',
                    mapTo: 'min'
                },
                {
                    entry: 'max',
                    mapTo: 'max'
                },
                {
                    entry: 'hypot',
                    filter: useOrigin
                },
                {
                    entry: 'pyt',
                    filter: useOrigin
                },
                {
                    entry: 'pow',
                    mapTo: 'pow'
                },
                {
                    entry: 'atan2',
                    mapTo: 'atan2'
                },
                {
                    entry: 'if',
                    filter: useOrigin
                },
                {
                    entry: 'gamma',
                    filter: useOrigin
                },
                {
                    entry: 'roundTo',
                    filter: useOrigin
                },
                {
                    entry: 'nthrt',
                    filter: function (x, n) {
                        x = decimalNumber(x);
                        n = parseInt(n, 10);
                        if (x.isNeg() && n % 2 !== 1) {
                            // not a real number (complex not supported)
                            return decimalNumber(NaN);
                        }
                        return x.abs().pow(decimalNumber(1).div(n)).mul(Decimal.sign(x));
                    }
                }
            ]
        };

        /**
         * Cast a Decimal to native type
         * @param {Number|String|Decimal} number
         * @returns {Number|Boolean|String} - Always returns a native type
         */
        function native(number) {
            if (Decimal.isDecimal(number)) {
                return number.toNumber();
            }
            else if (number === 'true' || number === true) {
                return true;
            }
            else if (number === 'false' || number === false) {
                return false;
            }
            return number;
        }

        /**
         * Map an original function using possible Decimal arguments
         * @returns {*}
         */
        function useOrigin() {
            var args = [].slice.call(arguments);
            var origin = args.pop();
            return origin.apply(this, args.map(native));
        }

        /**
         * Cast a native number to Decimal
         * @param {Number|String|Decimal} number
         * @returns {Decimal} - Always returns a Decimal
         */
        function decimalNumber(number) {
            if (!Decimal.isDecimal(number)) {
                number = new ConfiguredDecimal(number);
            }
            return number;
        }

        /**
         * Apply the mentioned unary operator on an operand
         * @param {String} operator - The operator to apply
         * @param {Number|String|Decimal} operand - The operand on which apply the operator
         * @returns {Decimal} - Always returns a Decimal
         */
        function unaryOperator(operator, operand) {
            operand = decimalNumber(operand);
            if (!_.isFunction(operand[operator])) {
                throw new TypeError(operator + ' is not a valid operator!');
            }
            return operand[operator]();
        }

        /**
         * Apply the mentioned binary operator on the operands
         * @param {String} operator - The operator to apply
         * @param {Number|String|Decimal} left - Left operand
         * @param {Number|String|Decimal} right - Right operand
         * @returns {Decimal} - Always returns a Decimal
         */
        function binaryOperator(operator, left, right) {
            left = decimalNumber(left);
            if (!_.isFunction(left[operator])) {
                throw new TypeError(operator + ' is not a valid operator!');
            }
            return left[operator](decimalNumber(right));
        }

        /**
         * Apply the mentioned function operator on the operands
         * @param {String} operator - The operator to apply
         * @param {Number|String|Decimal} ... - operands
         * @returns {Decimal} - Always returns a Decimal
         */
        function functionOperator(operator) {
            var operands = [].slice.call(arguments, 1);
            if (!_.isFunction(ConfiguredDecimal[operator])) {
                throw new TypeError(operator + ' is not a valid function!');
            }

            return ConfiguredDecimal[operator].apply(ConfiguredDecimal, operands.map(decimalNumber));
        }

        /**
         * Map the API
         * @param {Function} wrapper
         * @param {Object} origin
         * @param {Object} api
         */
        function mapping(wrapper, origin, api) {
            var fn;
            if (api.filter) {
                fn = _.partialRight(api.filter, origin[api.entry]);
            } else {
                fn = _.partial(wrapper, api.mapTo);
            }
            origin[api.entry] = fn;
        }

        /**
         * The exposed parser
         *
         * @param {String} expression - The expression to evaluate
         * @param {Object} [variables] - Optional variables to use from the expression
         * @returns {String}
         */
        function evaluate(expression, variables) {
            var expr = parser.parse(expression);
            var result = expr.evaluate(variables);
            var value = native(result);
            if (typeof value === "boolean") {
                return value;
            }
            return String(value);
        }

        // replace built-in operators and functions in expr-eval by those from decimal.js
        _.forEach(mapAPI.unary, _.partial(mapping, unaryOperator, parser.unaryOps));
        _.forEach(mapAPI.binary, _.partial(mapping, binaryOperator, parser.binaryOps));
        _.forEach(mapAPI.ternaryOps, _.partial(mapping, functionOperator, parser.ternaryOps));
        _.forEach(mapAPI.functions, _.partial(mapping, functionOperator, parser.functions));

        return evaluate;
    }

    return mathsEvaluatorFactory;
});
