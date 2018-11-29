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
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define(['i18n'], function (__) {
    'use strict';

    return {
        // Operand definition
        NUM0: {
            label: '0',
            value: '0',
            type: 'operand',
            description: __('Digit 0')
        },
        NUM1: {
            label: '1',
            value: '1',
            type: 'operand',
            description: __('Digit 1')
        },
        NUM2: {
            label: '2',
            value: '2',
            type: 'operand',
            description: __('Digit 2')
        },
        NUM3: {
            label: '3',
            value: '3',
            type: 'operand',
            description: __('Digit 3')
        },
        NUM4: {
            label: '4',
            value: '4',
            type: 'operand',
            description: __('Digit 4')
        },
        NUM5: {
            label: '5',
            value: '5',
            type: 'operand',
            description: __('Digit 5')
        },
        NUM6: {
            label: '6',
            value: '6',
            type: 'operand',
            description: __('Digit 6')
        },
        NUM7: {
            label: '7',
            value: '7',
            type: 'operand',
            description: __('Digit 7')
        },
        NUM8: {
            label: '8',
            value: '8',
            type: 'operand',
            description: __('Digit 8')
        },
        NUM9: {
            label: '9',
            value: '9',
            type: 'operand',
            description: __('Digit 9')
        },
        DOT: {
            label: '.',
            value: '.',
            type: 'operand',
            description: __('Dot')
        },
        POW10: {
            label: 'e',
            value: 'e',
            type: 'operand',
            description: __('Power of 10')
        },

        // Modificators
        LPAR: {
            label: '(',
            value: '(',
            type: 'modificator',
            description: __('Left parenthesis')
        },
        RPAR: {
            label: ')',
            value: ')',
            type: 'modificator',
            description: __('Right parenthesis')
        },

        // Operators
        ADD: {
            label: '+',
            value: '+',
            type: 'operator',
            description: __('Binary operator +')
        },
        SUB: {
            label: '-',
            value: '-',
            type: 'operator',
            description: __('Binary operator -')
        },
        MUL: {
            label: '\u00D7',
            value: '*',
            type: 'operator',
            description: __('Binary operator *')
        },
        DIV: {
            label: '\u00F7',
            value: '/',
            type: 'operator',
            description: __('Binary operator /')
        },
        MOD: {
            label: 'mod',
            value: '%',
            type: 'operator',
            description: __('Binary operator modulo')
        },
        POW: {
            label: '^',
            value: '^',
            type: 'operator',
            description: __('Power of')
        },
        FAC: {
            label: '!',
            value: '!',
            type: 'operator',
            description: __('Factorial')
        },
        ASSIGN: {
            label: '=',
            value: '=',
            type: 'operator',
            description: __('Assign')
        },

        // Constants
        PI: {
            label: '\u03C0',
            value: 'PI',
            type: 'constant',
            description: __('Value of PI')
        },
        E: {
            label: 'e',
            value: 'E',
            type: 'constant',
            description: __('Value of E')
        },

        // Errors
        NAN: {
            label: 'Error',
            value: 'NaN',
            type: 'error',
            description: __('Error in value')
        },
        INFINITY: {
            label: 'Infinity',
            value: 'Infinity',
            type: 'error',
            description: __('Error in result')
        },
        ERROR: {
            label: 'Syntax error',
            value: '#',
            type: 'error',
            description: __('Error in syntax')
        },

        // Functions
        EXP: {
            label: 'exp',
            value: 'exp',
            type: 'function',
            description: __('Exponent')
        },
        SQRT: {
            label: '\u221A',
            value: 'sqrt',
            type: 'function',
            description: __('Square root')
        },
        CBRT: {
            label: '\u221B',
            value: 'cbrt',
            type: 'function',
            description: __('Cube root')
        },
        NTHRT: {
            label: '\u221A',
            value: 'nthrt',
            type: 'function',
            description: __('Nth root')
        },
        FLOOR: {
            label: 'floor',
            value: 'floor',
            type: 'function',
            description: __('Round to lower whole number')
        },
        CEIL: {
            label: 'ceil',
            value: 'ceil',
            type: 'function',
            description: __('Round to upper whole number')
        },
        ROUND: {
            label: 'round',
            value: 'round',
            type: 'function',
            description: __('Round to closest whole number')
        },
        TRUNC: {
            label: 'trunc',
            value: 'trunc',
            type: 'function',
            description: __('Whole number part')
        },
        SIN: {
            label: 'sin',
            value: 'sin',
            type: 'function',
            description: __('Sine')
        },
        COS: {
            label: 'cos',
            value: 'cos',
            type: 'function',
            description: __('Cosine')
        },
        TAN: {
            label: 'tan',
            value: 'tan',
            type: 'function',
            description: __('Tangent')
        },
        ASIN: {
            label: 'sin<sup>-1</sup>',
            value: 'asin',
            type: 'function',
            description: __('Arc sine')
        },
        ACOS: {
            label: 'cos<sup>-1</sup>',
            value: 'acos',
            type: 'function',
            description: __('Arc cosine')
        },
        ATAN: {
            label: 'tan<sup>-1</sup>',
            value: 'atan',
            type: 'function',
            description: __('Arc tangent')
        },
        SINH: {
            label: 'sinh',
            value: 'sinh',
            type: 'function',
            description: __('Hyperbolic sine')
        },
        COSH: {
            label: 'cosh',
            value: 'cosh',
            type: 'function',
            description: __('Hyperbolic cosine')
        },
        TANH: {
            label: 'tanh',
            value: 'tanh',
            type: 'function',
            description: __('Hyperbolic tangent')
        },
        ASINH: {
            label: 'sinh<sup>-1</sup>',
            value: 'asinh',
            type: 'function',
            description: __('Hyperbolic arc sine')
        },
        ACOSH: {
            label: 'cosh<sup>-1</sup>',
            value: 'acosh',
            type: 'function',
            description: __('Hyperbolic arc cosine')
        },
        ATANH: {
            label: 'tanh<sup>-1</sup>',
            value: 'atanh',
            type: 'function',
            description: __('Hyperbolic arc tangent')
        },
        LN: {
            label: 'ln',
            value: 'ln',
            type: 'function',
            description: __('Natural logarithm')
        },
        LOG: {
            label: 'ln',
            value: 'log',
            type: 'function',
            description: __('Natural logarithm')
        },
        LG: {
            label: 'log',
            value: 'lg',
            type: 'function',
            description: __('Base-10 logarithm')
        },
        LOG10: {
            label: 'log',
            value: 'log10',
            type: 'function',
            description: __('Base-10 logarithm')
        },
        ABS: {
            label: 'abs',
            value: 'abs',
            type: 'function',
            description: __('Absolute value')
        },
        RAND: {
            label: 'random',
            value: 'random',
            type: 'function',
            description: __('Random value')
        }
    };
});
