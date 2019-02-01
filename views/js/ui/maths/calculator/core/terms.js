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

    return {
        // Digits definition
        NUM0: {
            label: labels.NUM0,
            value: '0',
            type: 'digit',
            description: __('Digit 0')
        },
        NUM1: {
            label: labels.NUM1,
            value: '1',
            type: 'digit',
            description: __('Digit 1')
        },
        NUM2: {
            label: labels.NUM2,
            value: '2',
            type: 'digit',
            description: __('Digit 2')
        },
        NUM3: {
            label: labels.NUM3,
            value: '3',
            type: 'digit',
            description: __('Digit 3')
        },
        NUM4: {
            label: labels.NUM4,
            value: '4',
            type: 'digit',
            description: __('Digit 4')
        },
        NUM5: {
            label: labels.NUM5,
            value: '5',
            type: 'digit',
            description: __('Digit 5')
        },
        NUM6: {
            label: labels.NUM6,
            value: '6',
            type: 'digit',
            description: __('Digit 6')
        },
        NUM7: {
            label: labels.NUM7,
            value: '7',
            type: 'digit',
            description: __('Digit 7')
        },
        NUM8: {
            label: labels.NUM8,
            value: '8',
            type: 'digit',
            description: __('Digit 8')
        },
        NUM9: {
            label: labels.NUM9,
            value: '9',
            type: 'digit',
            description: __('Digit 9')
        },
        DOT: {
            label: labels.DOT,
            value: '.',
            type: 'digit',
            description: __('Dot')
        },
        EXP10: {
            label: labels.EXP10,
            value: 'e',
            type: 'digit',
            description: __('Power of 10')
        },

        // Aggregators
        LPAR: {
            label: labels.LPAR,
            value: '(',
            type: 'aggregator',
            description: __('Left parenthesis')
        },
        RPAR: {
            label: labels.RPAR,
            value: ')',
            type: 'aggregator',
            description: __('Right parenthesis')
        },

        // Separator
        COMMA: {
            label: labels.COMMA,
            value: ',',
            type: 'separator',
            description: __('Arguments separator')
        },

        // Operators
        SUB: {
            label: labels.SUB,
            value: '-',
            type: 'operator',
            description: __('Binary operator -')
        },
        NEG: {
            label: labels.NEG,
            value: '\u207B',
            type: 'operator',
            description: __('Unary operator -')
        },
        ADD: {
            label: labels.ADD,
            value: '+',
            type: 'operator',
            description: __('Binary operator +')
        },
        MUL: {
            label: labels.MUL,
            value: '*',
            type: 'operator',
            description: __('Binary operator *')
        },
        DIV: {
            label: labels.DIV,
            value: '/',
            type: 'operator',
            description: __('Binary operator /')
        },
        MOD: {
            label: labels.MOD,
            value: '%',
            type: 'operator',
            description: __('Binary operator modulo')
        },
        POW: {
            label: labels.POW,
            value: '^',
            type: 'operator',
            description: __('Power of')
        },
        FAC: {
            label: labels.FAC,
            value: '!',
            type: 'operator',
            description: __('Factorial')
        },
        ASSIGN: {
            label: labels.ASSIGN,
            value: '=',
            type: 'operator',
            description: __('Assign')
        },

        // Variables
        ANS: {
            label: labels.ANS,
            value: 'ans',
            type: 'variable',
            description: __('Last result')
        },

        // Constants
        PI: {
            label: labels.PI,
            value: 'PI',
            type: 'constant',
            description: __('Value of PI')
        },
        E: {
            label: labels.E,
            value: 'E',
            type: 'constant',
            description: __('Value of E')
        },

        // Errors
        NAN: {
            label: labels.NAN,
            value: 'NaN',
            type: 'error',
            description: __('Error in value')
        },
        INFINITY: {
            label: labels.INFINITY,
            value: 'Infinity',
            type: 'error',
            description: __('Error in result')
        },
        ERROR: {
            label: labels.ERROR,
            value: '#',
            type: 'error',
            description: __('Error in syntax')
        },

        // Functions
        EXP: {
            label: labels.EXP,
            value: 'exp',
            type: 'function',
            description: __('Exponent')
        },
        SQRT: {
            label: labels.SQRT,
            value: 'sqrt',
            type: 'function',
            description: __('Square root')
        },
        CBRT: {
            label: labels.CBRT,
            value: 'cbrt',
            type: 'function',
            description: __('Cube root')
        },
        NTHRT: {
            label: labels.SQRT,
            value: 'nthrt',
            type: 'function',
            description: __('Nth root')
        },
        FLOOR: {
            label: labels.FLOOR,
            value: 'floor',
            type: 'function',
            description: __('Round to lower whole number')
        },
        CEIL: {
            label: labels.CEIL,
            value: 'ceil',
            type: 'function',
            description: __('Round to upper whole number')
        },
        ROUND: {
            label: labels.ROUND,
            value: 'round',
            type: 'function',
            description: __('Round to closest whole number')
        },
        TRUNC: {
            label: labels.TRUNC,
            value: 'trunc',
            type: 'function',
            description: __('Whole number part')
        },
        SIN: {
            label: labels.SIN,
            value: 'sin',
            type: 'function',
            description: __('Sine')
        },
        COS: {
            label: labels.COS,
            value: 'cos',
            type: 'function',
            description: __('Cosine')
        },
        TAN: {
            label: labels.TAN,
            value: 'tan',
            type: 'function',
            description: __('Tangent')
        },
        ASIN: {
            label: labels.ASIN,
            value: 'asin',
            type: 'function',
            description: __('Arc sine')
        },
        ACOS: {
            label: labels.ACOS,
            value: 'acos',
            type: 'function',
            description: __('Arc cosine')
        },
        ATAN: {
            label: labels.ATAN,
            value: 'atan',
            type: 'function',
            description: __('Arc tangent')
        },
        SINH: {
            label: labels.SINH,
            value: 'sinh',
            type: 'function',
            description: __('Hyperbolic sine')
        },
        COSH: {
            label: labels.COSH,
            value: 'cosh',
            type: 'function',
            description: __('Hyperbolic cosine')
        },
        TANH: {
            label: labels.TANH,
            value: 'tanh',
            type: 'function',
            description: __('Hyperbolic tangent')
        },
        ASINH: {
            label: labels.ASINH,
            value: 'asinh',
            type: 'function',
            description: __('Hyperbolic arc sine')
        },
        ACOSH: {
            label: labels.ACOSH,
            value: 'acosh',
            type: 'function',
            description: __('Hyperbolic arc cosine')
        },
        ATANH: {
            label: labels.ATANH,
            value: 'atanh',
            type: 'function',
            description: __('Hyperbolic arc tangent')
        },
        LN: {
            label: labels.LN,
            value: 'ln',
            type: 'function',
            description: __('Natural logarithm')
        },
        LOG: {
            label: labels.LN,
            value: 'log',
            type: 'function',
            description: __('Natural logarithm')
        },
        LG: {
            label: labels.LOG,
            value: 'lg',
            type: 'function',
            description: __('Base-10 logarithm')
        },
        LOG10: {
            label: labels.LOG,
            value: 'log10',
            type: 'function',
            description: __('Base-10 logarithm')
        },
        ABS: {
            label: labels.ABS,
            value: 'abs',
            type: 'function',
            description: __('Absolute value')
        },
        RAND: {
            label: labels.RAND,
            value: 'random',
            type: 'function',
            description: __('Random value')
        }
    };
});
