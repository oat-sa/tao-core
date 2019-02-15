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
    'lib/expr-eval/expr-eval'
], function (_, Decimal, exprEval) {
    'use strict';

    var Parser = exprEval.Parser;

    /**
     * Good precision value of PI
     * @type {String}
     */
    var numberPI = '3.1415926535897932384626433832795028841971693993751058209749445923078164062862089986280348253421170679821480865132823066470938446095505822317253594081284811174502841027019385211055596446229489549303819644288109756659334461284756482337867831652712019091456485669234603486104543266482133936072602491412737245870066063155881748815209209628292540917153643678925903600113305305488204665213841469519415116094330572703657595919530921861173819326117931051185480744623799627495673518857527248912279381830119491298336733624406566430860213949463952247371907021798609437027705392171762931767523846748184676694051320005681271452635608277857713427577896091736371787214684409012249534301465495853710507922796892589235420199561121290219608640344181598136297747713099605187072113499999983729780499510597317328160963185950244594553469083026425223082533446850352619311881710100031378387528865875332083814206171776691473035982534904287554687311595628638823537875937519577818577805321712268066130019278766111959092164201989';

    /**
     * Good precision value of Euler's number
     * @type {String}
     */
    var numberE = '2.7182818284590452353602874713526624977572470936999595749669676277240766303535475945713821785251664274274663919320030599218174135966290435729003342952605956307381323286279434907632338298807531952510190115738341879307021540891499348841675092447614606680822648001684774118537423454424371075390777449920695517027618386062613313845830007520449338265602976067371132007093287091274437470472306969772093101416928368190255151086574637721112523897844250569536967707854499699679468644549059879316368892300987931277361782154249992295763514822082698951936680331825288693984964651058209392398294887933203625094431173012381970684161403970198376793206832823764648042953118023287825098194558153017567173613320698112509961818815930416903515988885193458072738667385894228792284998920868058257492796104841984443634632449684875602336248270419786232090021609902353043699418491463140934317381436405462531520961836908887070167683964243781405927145635490613031072085103837505101157477041718986106873969655212671546889570350354';

    /**
     * Defaults config for the evaluator
     * @type {Object}
     */
    var defaultConfig = {
        internalPrecision: 100,
        degree: false
    };

    /**
     * Defaults config for the Decimal constructor
     * @type {Object}
     */
    var defaultDecimalConfig = {
        defaults: true
    };

    /**
     * List of config entries the Decimal constructor accepts
     * @type {String[]}
     */
    var decimalConfigEntries = [
        'precision',
        'rounding',
        'toExpNeg',
        'toExpPos',
        'maxE',
        'minE',
        'modulo',
        'crypto'
    ];

    /**
     * List of config entries the Parser constructor accepts
     * @type {String[]}
     */
    var parserConfigEntries = [
        'operators'
    ];

    /**
     * Gets an arbitrary decimal precision number using a string representation.
     * @param {String} number
     * @param {Number} precision
     * @returns {String}
     */
    function toPrecisionNumber(number, precision) {
        var dot = number.indexOf('.');
        if (dot > 0) {
            number = number.substr(0, dot + precision + 1);
        }
        return number;
    }

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
     * @param {Object} [config] - Config entries, mostly for the Decimal constructor.
     * @param {Number} [config.precision=20] - The maximum number of significant digits of the result of an operation.
     * @param {Number} [config.internalPrecision=100] - Arbitrary decimal precision for some internal related computations (sin, cos, tan, ln).
     * @param {Number} [config.rounding=4] - The default rounding mode used when rounding the result of an operation to precision significant digits.
     * @param {Number} [config.toExpNeg=-7] - The negative exponent value at and below which toString returns exponential notation.
     * @param {Number} [config.toExpPos=21] - The positive exponent value at and above which toString returns exponential notation.
     * @param {Number} [config.maxE=9e15] - The positive exponent limit, i.e. the exponent value above which overflow to Infinity occurs.
     * @param {Number} [config.minE=-9e15] - The negative exponent limit, i.e. the exponent value below which underflow to zero occurs.
     * @param {Number} [config.modulo=1] - The modulo mode used when calculating the modulus: a mod n.
     * @param {Boolean} [config.crypto=false] - The value that determines whether cryptographically-secure pseudo-random number generation is used.
     * @param {Boolean} [config.degree=false] - Converts trigonometric values from radians to degrees.
     * @param {Object} [config.operators] - The list of operators to enable.
     * @returns {Function<expression, variables>} - The maths expression parser
     */
    function mathsEvaluatorFactory(config) {
        var localConfig = _.defaults({}, config, defaultConfig);
        var decimalConfig = _.pick(localConfig, decimalConfigEntries);
        var parserConfig = _.pick(localConfig, parserConfigEntries);
        var parser = new Parser(parserConfig);
        var ConfiguredDecimal = Decimal.set(_.isEmpty(decimalConfig) ? defaultDecimalConfig : decimalConfig);
        var EPSILON = (new ConfiguredDecimal(2)).pow(-52);
        var PI = new ConfiguredDecimal(toPrecisionNumber(numberPI, localConfig.internalPrecision));
        var E = new ConfiguredDecimal(toPrecisionNumber(numberE, localConfig.internalPrecision));

        /**
         * Map expr-eval API to decimal.js
         * @type {Object}
         */
        var mapAPI = {
            unary: [
                {
                    entry: 'sin',
                    action: function (a) {
                        return trigoOperator('sin', a);
                    }
                },
                {
                    entry: 'cos',
                    action: function (a) {
                        return trigoOperator('cos', a);
                    }
                },
                {
                    entry: 'tan',
                    action: function (a) {
                        return trigoOperator('tan', a);
                    }
                },
                {
                    entry: 'asin',
                    action: function (a) {
                        return inverseTrigoOperator('asin', a);
                    }
                },
                {
                    entry: 'acos',
                    action: function (a) {
                        return inverseTrigoOperator('acos', a);
                    }
                },
                {
                    entry: 'atan',
                    action: function (a) {
                        return inverseTrigoOperator('atan', a);
                    }
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
                    mapTo: 'log'
                },
                {
                    entry: 'log10',
                    mapTo: 'log'
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
                    action: decimalNumber
                },
                {
                    entry: 'exp',
                    mapTo: 'exp'
                },
                {
                    entry: 'not',
                    action: function (a) {
                        return !native(a);
                    }
                },
                {
                    entry: '!',
                    action: useOrigin
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
                    action: function (a, b) {
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
                    action: function (a, b) {
                        return Boolean(native(a) && native(b));
                    }
                },
                {
                    entry: 'or',
                    action: function (a, b) {
                        return Boolean(native(a) || native(b));
                    }
                },
                {
                    entry: 'in',
                    action: function (array, obj) {
                        obj = native(obj);
                        return 'undefined' !== typeof _.find(array, function (el) {
                            return native(el) === obj;
                        });
                    }
                }
            ],
            ternaryOps: [{
                entry: '?',
                action: useOrigin
            }],
            functions: [
                {
                    entry: 'random',
                    action: function (dp) {
                        return ConfiguredDecimal.random(dp);
                    }
                },
                {
                    entry: 'fac',
                    action: useOrigin
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
                    action: useOrigin
                },
                {
                    entry: 'pyt',
                    action: useOrigin
                },
                {
                    entry: 'pow',
                    mapTo: 'pow'
                },
                {
                    entry: 'atan2',
                    action: function (y, x) {
                        var result = functionOperator('atan2', y, x);
                        return localConfig.degree ? radianToDegree(result) : result;
                    }
                },
                {
                    entry: 'if',
                    action: useOrigin
                },
                {
                    entry: 'gamma',
                    action: useOrigin
                },
                {
                    entry: 'roundTo',
                    action: useOrigin
                },
                {
                    entry: 'nthrt',
                    action: function (n, x) {
                        x = decimalNumber(x);
                        n = parseInt(n, 10);
                        if (x.isNeg() && n % 2 !== 1) {
                            // not a real number (complex not supported)
                            return decimalNumber(NaN);
                        }
                        return x.abs().pow(decimalNumber(1).div(n)).mul(Decimal.sign(x));
                    }
                }
            ],
            consts: [{
                entry: 'PI',
                value: PI
            }, {
                entry: 'E',
                value: E
            }, {
                entry: 'EPSILON',
                value: EPSILON
            }]
        };

        /**
         * Takes care of zero-like values.
         * i.e. value smaller than the smallest double precision datatype value is considered equal to zero
         * @param {Decimal} number
         * @returns {Decimal}
         */
        function checkZero(number) {
            if (number.absoluteValue().lessThan(EPSILON)) {
                return new ConfiguredDecimal(0);
            }
            return number;
        }

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
         * Converts degrees to radians
         * @param {Number|String|Decimal} value
         * @returns {Decimal} - Always returns a Decimal
         */
        function degreeToRadian(value) {
            return decimalNumber(value).mul(PI).div(180);
        }

        /**
         * Converts radians to degrees
         * @param {Number|String|Decimal} value
         * @returns {Decimal} - Always returns a Decimal
         */
        function radianToDegree(value) {
            return decimalNumber(value).mul(180).div(PI);
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
         * Apply the mentioned trigonometric operator on an operand, taking care of the unit (degree or radian)
         * @param {String} operator - The operator to apply
         * @param {Number|String|Decimal} operand - The operand on which apply the operator
         * @returns {Decimal} - Always returns a Decimal
         */
        function trigoOperator(operator, operand) {
            if (!_.isFunction(Decimal[operator])) {
                throw new TypeError(operator + ' is not a valid operator!');
            }

            if (localConfig.degree) {
                operand = degreeToRadian(operand);
            } else {
                operand = decimalNumber(operand);
            }

            if (operator === 'tan' && operand.equals(PI.div(2))) {
                return new ConfiguredDecimal(NaN);
            }

            return checkZero(ConfiguredDecimal[operator](operand));
        }

        /**
         * Apply the mentioned inverse trigonometric operator on an operand, taking care of the unit (degree or radian)
         * @param {String} operator - The operator to apply
         * @param {Number|String|Decimal} operand - The operand on which apply the operator
         * @returns {Decimal} - Always returns a Decimal
         */
        function inverseTrigoOperator(operator, operand) {
            var result = checkZero(unaryOperator(operator, operand));
            return localConfig.degree ? radianToDegree(result) : result;
        }

        /**
         * Map the API
         * @param {Function} wrapper
         * @param {Object} origin
         * @param {Object} api
         */
        function mapping(wrapper, origin, api) {
            var fn;
            if (api.value) {
                fn = api.value;
            }
            else if (api.action) {
                fn = _.partialRight(api.action, origin[api.entry]);
            } else {
                fn = _.partial(wrapper, api.mapTo);
            }
            origin[api.entry] = fn;
        }

        /**
         * The exposed parser
         *
         * @param {String|mathsExpression} expression - The expression to evaluate
         * @param {Object} [variables] - Optional variables to use from the expression
         * @returns {mathsExpression}
         */
        function evaluate(expression, variables) {
            var parsedExpression, result, value;

            if (_.isPlainObject(expression)) {
                variables = variables || expression.variables;
                expression = expression.expression;
            }

            parsedExpression = parser.parse(expression);
            result = parsedExpression.evaluate(variables);
            value = native(result);

            /**
             * @typedef {Object} mathsExpression
             * @property {String} expression - The evaluated expression
             * @property {Object} variables - Optional variables used from the expression
             * @property {Decimal|Number|Boolean|String} result - The result of the expression, as returned by the evaluator
             * @property {Boolean|String} value - The result of the expression, as a native value
             */
            return {
                expression: expression,
                variables: variables,
                result: result,
                value: value
            };
        }

        // replace built-in operators and functions in expr-eval by those from decimal.js
        _.forEach(mapAPI.unary, _.partial(mapping, unaryOperator, parser.unaryOps));
        _.forEach(mapAPI.binary, _.partial(mapping, binaryOperator, parser.binaryOps));
        _.forEach(mapAPI.ternaryOps, _.partial(mapping, functionOperator, parser.ternaryOps));
        _.forEach(mapAPI.functions, _.partial(mapping, functionOperator, parser.functions));
        _.forEach(mapAPI.consts, _.partial(mapping, null, parser.consts));

        return evaluate;
    }

    return mathsEvaluatorFactory;
});
