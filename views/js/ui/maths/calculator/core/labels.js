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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'i18n',
    'tpl!ui/maths/calculator/core/tpl/historyUp',
    'tpl!ui/maths/calculator/core/tpl/historyDown',
    'tpl!ui/maths/calculator/core/tpl/backspace'
], function (
    __,
    historyUpTpl,
    historyDownTpl,
    backspaceTpl
) {
    'use strict';

    return {
        // Digits definition
        NUM0: '0',
        NUM1: '1',
        NUM2: '2',
        NUM3: '3',
        NUM4: '4',
        NUM5: '5',
        NUM6: '6',
        NUM7: '7',
        NUM8: '8',
        NUM9: '9',
        DOT: '.',
        EXP10: 'e',
        POW10: '10<sup>x</sup>',

        // Aggregators
        LPAR: '(',
        RPAR: ')',

        // Separator
        COMMA: ',',
        SPACER: '',

        // Operators
        SUB: '-',
        ADD: '+',
        POS: '\u207A',
        NEG: '\u207B',
        MUL: '\u00D7',
        DIV: '\u00F7',
        MOD: 'modulo',
        POW: '^',
        POW2: 'x<sup>2</sup>',
        POW3: 'x<sup>3</sup>',
        POWY: 'x<sup>y</sup>',
        POWMINUSONE: 'x<sup>\u207B' + '1</sup>',
        FAC: '!',
        ASSIGN: '=',

        // Variables
        ANS: 'Ans',

        // Constants
        PI: '\u03C0',
        E: 'e',

        // Errors
        NAN: 'Error',
        INFINITY: 'Infinity',
        ERROR: 'Syntax error',

        // Functions
        EXP: 'exp',
        EXPX: 'e<sup>x</sup>',
        SQRT: '\u221A',
        CBRT: '<sup>3</sup>\u221A',
        NTHRT: '<sup>x</sup>\u221A',
        FLOOR: 'floor',
        CEIL: 'ceil',
        ROUND: 'round',
        TRUNC: 'trunc',
        SIN: 'sin',
        COS: 'cos',
        TAN: 'tan',
        ASIN: 'sin<sup>\u207B1</sup>',
        ACOS: 'cos<sup>\u207B1</sup>',
        ATAN: 'tan<sup>\u207B1</sup>',
        SINH: 'sinh',
        COSH: 'cosh',
        TANH: 'tanh',
        ASINH: 'sinh<sup>\u207B1</sup>',
        ACOSH: 'cosh<sup>\u207B1</sup>',
        ATANH: 'tanh<sup>\u207B1</sup>',
        LN: 'ln',
        LOG: 'log<sub>10</sub>',
        ABS: 'abs',
        RAND: 'random',

        // Actions
        CLEAR: 'C',
        CLEARALL: 'AC',
        EXECUTE: '=',
        HISTORYUP: historyUpTpl(),
        HISTORYDOWN: historyDownTpl(),
        BACKSPACE: backspaceTpl(),
        DEGREE: __('Deg'),
        RADIAN: __('Rad'),
        SIGN: '&plusmn;'
    };
});
