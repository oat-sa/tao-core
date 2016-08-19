/**
 * Gamp v0.1.0 - A simple arithmetic API with workaround to floating-point issue
 *
 * https://github.com/jsconan/gamp
 *
 * Copyright (c) 2016 Jean-Sébastien CONAN
 * Released under the MIT license.
 */
(function (global, alias) {
    'use strict';

    var define;

    if (global.define && global.define.amd) {
        define = window.define;
    } else if (global.module) {
        define = function (exports) {
            module.exports = exports();
        };
    } else {
        define = function (exports) {
            global[alias] = exports();
        };
    }

    define(function () {
        /**
         * Computes the precision of a decimal number.
         * This precision will be then used as a correction factor to normalize
         * the value in order to prevent the floating-point round error.
         * @param {number} val
         * @returns {number}
         */
        function precision(val) {
            var digits = String(val);
            var point = digits.indexOf('.');
            return point < 0 ? 1 : Math.pow(10, digits.length - point - 1);
        }

        /**
         * Computes the approached precision for a list of decimal numbers.
         * This precision will be then used as a correction factor to normalize
         * the values in order to prevent the floating-point round error.
         * @param {number} ...
         * @returns {number}
         */
        function gamp() {
            var i = arguments.length - 1;
            var factor = -Infinity;
            while (i >= 0) {
                factor = Math.max(factor, precision(arguments[i--]));
            }
            return Math.abs(factor);
        }

        /**
         * Makes the translation of a floating point number to an integer value using a precision factor
         * @param {number} val
         * @param {number} factor
         * @returns {number}
         */
        gamp.normalize = function normalize(val, factor) {
            return Math.round(factor * Number(val));
        };

        /**
         * Formats a number to prevent round-off error
         * @param {number} val
         * @param {number} [precision=16]
         * @returns {number}
         */
        gamp.format = function format(val, precision) {
            return Number(Number(val).toPrecision('undefined' === typeof precision ? 16 : precision));
        };

        /**
         * Computes the addition of two decimal values
         * @param {number} a
         * @param {number} b
         * @returns {number}
         */
        gamp.add = function add(a, b) {
            var factor = gamp(a, b);
            return gamp.format((gamp.normalize(a, factor) + gamp.normalize(b, factor)) / factor);
        };

        /**
         * Computes the subtraction of two decimal values
         * @param {number} a
         * @param {number} b
         * @returns {number}
         */
        gamp.sub = function sub(a, b) {
            var factor = gamp(a, b);
            return gamp.format((gamp.normalize(a, factor) - gamp.normalize(b, factor)) / factor);
        };

        /**
         * Computes the multiplication of two decimal values
         * @param {number} a
         * @param {number} b
         * @returns {number}
         */
        gamp.mul = function mul(a, b) {
            var factor = gamp(a, b);
            return gamp.format((gamp.normalize(a, factor) * gamp.normalize(b, factor)) / (factor * factor), 15);
        };

        /**
         * Computes the division of two decimal values
         * @param {number} a
         * @param {number} b
         * @returns {number}
         */
        gamp.div = function div(a, b) {
            var factor = gamp(a, b);
            return gamp.format(gamp.normalize(a, factor) / gamp.normalize(b, factor));
        };

        /**
         * Computes the power of a decimal value
         * @param {number} a
         * @param {number} b
         * @returns {number}
         */
        gamp.pow = function pow(a, b) {
            var factor = gamp(a);
            var ta = gamp.normalize(a, factor);
            var ib = Math.floor(b);
            var fb = b - ib;
            var res = ib ? Math.pow(ta, ib) / Math.pow(factor, ib) : 1;
            if (fb) {
                res = gamp.div(gamp.mul(res, Math.pow(ta, fb)), Math.pow(factor, fb));
            }
            return gamp.format(res, 15);
        };

        return gamp;
    });
})(this, 'gamp');
