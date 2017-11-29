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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
define([
    'lodash',
    'core/encoder/boolean',
    'core/encoder/number',
    'core/encoder/float',
    'core/encoder/time',
    'core/encoder/array2str',
    'core/encoder/str2array',
    'core/encoder/entity'
],
function(
    _,
    boolean,
    number,
    float,
    time,
    array2str,
    str2array,
    entity
){
    'use strict';

    /**
     * Extract the argument in parenthesis from a function name:  "foo(a,b)" return [a,b]
     * @param {string} name - the declaration : array(a,b)
     * @returns {array} of extracted args
     */
    var extractArgs = function extractArgs(name){
        var args = [];
        var matches = [];
        if(name.indexOf('(') > -1){
            matches = /\((.+?)\)/.exec(name);
            if(matches && matches.length >= 1){
                args = matches[1].split(',');
            }
        }
        return args;
    };

    /**
     * Extract the name from a function declaration:   "foo(a,b)" return foo
     * @param {string} name - the declaration : foo(a,b)
     * @returns {string} the name
     */
    var extractName = function extractName(name){
        if(name.indexOf('(') > -1){
            return name.substr(0, name.indexOf('('));
        }
        return name;
    };

   /**
    * Provides multi sources encoding decoding
    * @exports core/encoder/encoders
    */
    var encoders =  {
        number : number,
        float: float,
        time : time,
        boolean : boolean,
        array2str : array2str,
        str2array : str2array,
        entity : entity,

        register : function(name, encode, decode){
            if(!_.isString(name)){
                throw new Error('An encoder must have a valid name');
            }
            if(!_.isFunction(encode)){
                throw new Error('Encode must be a function');
            }
            if(!_.isFunction(decode)){
                throw new Error('Decode must be a function');
            }
            this[name] = { encode : encode, decode : decode };
        },

        encode : function(name, value){
            var encoder, args;

            name = extractName(name);
            if(this[name]){
                encoder = this[name];
                args = [value];
                return encoder.encode.apply(encoder, args.concat(extractArgs(name)));
            }
            return value;
        },

        decode : function(name, value){
            var decoder, args;

            name = extractName(name);
            if(this[name]){
                decoder = this[name];
                args = [value];
                return decoder.decode.apply(decoder, args.concat(extractArgs(name)));
            }
            return value;
        }
    };

    return encoders;
});

