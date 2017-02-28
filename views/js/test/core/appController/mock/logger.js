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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define(function () {
    'use strict';

    var listeners = {};

    function mockLoggerFactory() {
        return {
            log : function log(){
            },
            trace : function trace(){
            },
            error : function error(err){
                mockLoggerFactory.trigger('error', err);
            },
            child: function() {
                return mockLoggerFactory();
            }
        };
    }

    // do not use eventifier, otherwise can enter infinite loop as eventifier is using logger
    // use mock instead

    mockLoggerFactory.trigger = function trigger(name) {
        var callbacks = listeners[name] || [];
        var args = [].slice.call(arguments, 1);
        callbacks.forEach(function(cb) {
            cb.apply(null, args);
        });
    };

    mockLoggerFactory.on = function on(name, callback) {
        listeners[name] = listeners[name] || [];
        listeners[name].push(callback);
    };

    mockLoggerFactory.off = function off(name) {
        listeners[name] = [];
    };

    mockLoggerFactory.removeAllListeners = function removeAllListeners() {
        listeners = {};
    };

    return mockLoggerFactory;
});
