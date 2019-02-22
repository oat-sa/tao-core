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
 * Copyright (c) 2018-2019 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'i18n',
    'ui/maths/calculator/core/labels'
], function (__, labels) {
    'use strict';

    /**
     * @typedef {Object} term - Represents a tokenizable term
     * @property {String} label - The displayable text
     * @property {String} value - The related text that should be found in the expression
     * @property {String} type - The type of token
     * @property {String} description - A description of what represent the term
     * @property {String|null} exponent - Some terms introduce exponent notation, and this property tells on which side
     */

    /**
     * Defines the terms that can be tokenized from an expression
     * @type {term[]}
     */
    return {
        // Digits definition
        NUM0: {
            label: labels.NUM0,
            value: '0',
            type: 'digit',
            description: __('Digit 0'),
            exponent: null
        },
        NUM1: {
            label: labels.NUM1,
            value: '1',
            type: 'digit',
            description: __('Digit 1'),
            exponent: null
        },
        NUM2: {
            label: labels.NUM2,
            value: '2',
            type: 'digit',
            description: __('Digit 2'),
            exponent: null
        },
        NUM3: {
            label: labels.NUM3,
            value: '3',
            type: 'digit',
            description: __('Digit 3'),
            exponent: null
        },
        NUM4: {
            label: labels.NUM4,
            value: '4',
            type: 'digit',
            description: __('Digit 4'),
            exponent: null
        },
        NUM5: {
            label: labels.NUM5,
            value: '5',
            type: 'digit',
            description: __('Digit 5'),
            exponent: null
        },
        NUM6: {
            label: labels.NUM6,
            value: '6',
            type: 'digit',
            description: __('Digit 6'),
            exponent: null
        },
        NUM7: {
            label: labels.NUM7,
            value: '7',
            type: 'digit',
            description: __('Digit 7'),
            exponent: null
        },
        NUM8: {
            label: labels.NUM8,
            value: '8',
            type: 'digit',
            description: __('Digit 8'),
            exponent: null
        },
        NUM9: {
            label: labels.NUM9,
            value: '9',
            type: 'digit',
            description: __('Digit 9'),
            exponent: null
        },
        DOT: {
            label: labels.DOT,
            value: '.',
            type: 'digit',
            description: __('Dot'),
            exponent: null
        },
        EXP10: {
            label: labels.EXP10,
            value: 'e',
            type: 'digit',
            description: __('Power of 10'),
            exponent: 'right'
        },

        // Aggregators
        LPAR: {
            label: labels.LPAR,
            value: '(',
            type: 'aggregator',
            description: __('Left parenthesis'),
            exponent: null
        },
        RPAR: {
            label: labels.RPAR,
            value: ')',
            type: 'aggregator',
            description: __('Right parenthesis'),
            exponent: null
        },

        // Separator
        COMMA: {
            label: labels.COMMA,
            value: ',',
            type: 'separator',
            description: __('Arguments separator'),
            exponent: null
        },

        // Operators
        SUB: {
            label: labels.SUB,
            value: '-',
            type: 'operator',
            description: __('Binary operator -'),
            exponent: null
        },
        NEG: {
            label: labels.NEG,
            value: labels.NEG,
            type: 'operator',
            description: __('Unary operator -'),
            exponent: null
        },
        ADD: {
            label: labels.ADD,
            value: '+',
            type: 'operator',
            description: __('Binary operator +'),
            exponent: null
        },
        POS: {
            label: labels.POS,
            value: labels.POS,
            type: 'operator',
            description: __('Unary operator +'),
            exponent: null
        },
        MUL: {
            label: labels.MUL,
            value: '*',
            type: 'operator',
            description: __('Binary operator *'),
            exponent: null
        },
        DIV: {
            label: labels.DIV,
            value: '/',
            type: 'operator',
            description: __('Binary operator /'),
            exponent: null
        },
        MOD: {
            label: labels.MOD,
            value: '%',
            type: 'operator',
            description: __('Binary operator modulo'),
            exponent: null
        },
        POW: {
            label: labels.POW,
            value: '^',
            type: 'operator',
            description: __('Power of'),
            exponent: 'right'
        },
        FAC: {
            label: labels.FAC,
            value: '!',
            type: 'operator',
            description: __('Factorial'),
            exponent: null
        },
        ASSIGN: {
            label: labels.ASSIGN,
            value: '=',
            type: 'operator',
            description: __('Assign'),
            exponent: null
        },

        // Variables
        ANS: {
            label: labels.ANS,
            value: 'ans',
            type: 'variable',
            description: __('Last result'),
            exponent: null
        },

        // Constants
        PI: {
            label: labels.PI,
            value: 'PI',
            type: 'constant',
            description: __('Value of PI'),
            exponent: null
        },
        E: {
            label: labels.E,
            value: 'E',
            type: 'constant',
            description: __('Value of E'),
            exponent: null
        },

        // Errors
        NAN: {
            label: labels.NAN,
            value: 'NaN',
            type: 'error',
            description: __('Error in value'),
            exponent: null
        },
        INFINITY: {
            label: labels.INFINITY,
            value: 'Infinity',
            type: 'error',
            description: __('Error in result'),
            exponent: null
        },
        ERROR: {
            label: labels.ERROR,
            value: '#',
            type: 'error',
            description: __('Error in syntax'),
            exponent: null
        },

        // Functions
        EXP: {
            label: labels.EXP,
            value: 'exp',
            type: 'function',
            description: __('Exponent'),
            exponent: 'right'
        },
        SQRT: {
            label: labels.SQRT,
            value: 'sqrt',
            type: 'function',
            description: __('Square root'),
            exponent: null
        },
        CBRT: {
            label: labels.CBRT,
            value: 'cbrt',
            type: 'function',
            description: __('Cube root'),
            exponent: null
        },
        NTHRT: {
            label: labels.SQRT,
            value: 'nthrt',
            type: 'function',
            description: __('Nth root'),
            exponent: 'left'
        },
        FLOOR: {
            label: labels.FLOOR,
            value: 'floor',
            type: 'function',
            description: __('Round to lower whole number'),
            exponent: null
        },
        CEIL: {
            label: labels.CEIL,
            value: 'ceil',
            type: 'function',
            description: __('Round to upper whole number'),
            exponent: null
        },
        ROUND: {
            label: labels.ROUND,
            value: 'round',
            type: 'function',
            description: __('Round to closest whole number'),
            exponent: null
        },
        TRUNC: {
            label: labels.TRUNC,
            value: 'trunc',
            type: 'function',
            description: __('Whole number part'),
            exponent: null
        },
        SIN: {
            label: labels.SIN,
            value: 'sin',
            type: 'function',
            description: __('Sine'),
            exponent: null
        },
        COS: {
            label: labels.COS,
            value: 'cos',
            type: 'function',
            description: __('Cosine'),
            exponent: null
        },
        TAN: {
            label: labels.TAN,
            value: 'tan',
            type: 'function',
            description: __('Tangent'),
            exponent: null
        },
        ASIN: {
            label: labels.ASIN,
            value: 'asin',
            type: 'function',
            description: __('Arc sine'),
            exponent: null
        },
        ACOS: {
            label: labels.ACOS,
            value: 'acos',
            type: 'function',
            description: __('Arc cosine'),
            exponent: null
        },
        ATAN: {
            label: labels.ATAN,
            value: 'atan',
            type: 'function',
            description: __('Arc tangent'),
            exponent: null
        },
        SINH: {
            label: labels.SINH,
            value: 'sinh',
            type: 'function',
            description: __('Hyperbolic sine'),
            exponent: null
        },
        COSH: {
            label: labels.COSH,
            value: 'cosh',
            type: 'function',
            description: __('Hyperbolic cosine'),
            exponent: null
        },
        TANH: {
            label: labels.TANH,
            value: 'tanh',
            type: 'function',
            description: __('Hyperbolic tangent'),
            exponent: null
        },
        ASINH: {
            label: labels.ASINH,
            value: 'asinh',
            type: 'function',
            description: __('Hyperbolic arc sine'),
            exponent: null
        },
        ACOSH: {
            label: labels.ACOSH,
            value: 'acosh',
            type: 'function',
            description: __('Hyperbolic arc cosine'),
            exponent: null
        },
        ATANH: {
            label: labels.ATANH,
            value: 'atanh',
            type: 'function',
            description: __('Hyperbolic arc tangent'),
            exponent: null
        },
        LN: {
            label: labels.LN,
            value: 'ln',
            type: 'function',
            description: __('Natural logarithm'),
            exponent: null
        },
        LOG: {
            label: labels.LN,
            value: 'log',
            type: 'function',
            description: __('Natural logarithm'),
            exponent: null
        },
        LG: {
            label: labels.LOG,
            value: 'lg',
            type: 'function',
            description: __('Base-10 logarithm'),
            exponent: null
        },
        LOG10: {
            label: labels.LOG,
            value: 'log10',
            type: 'function',
            description: __('Base-10 logarithm'),
            exponent: null
        },
        ABS: {
            label: labels.ABS,
            value: 'abs',
            type: 'function',
            description: __('Absolute value'),
            exponent: null
        },
        RAND: {
            label: labels.RAND,
            value: 'random',
            type: 'function',
            description: __('Random value'),
            exponent: null
        }
    };
});
