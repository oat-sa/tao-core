/*
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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Utility library that helps you to manipulate URLs.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([], function(){
    'use strict';

    /**
     * The Url util
     * @exports util/url
     */
    var urlUtil = {

        /*
         * The parse method is a adaptation of parseUri from
         * Steven Levithan <stevenlevithan.com> under the MIT License
         */

        /**
         * Parse the given URL and create an object with each URL chunks.
         *
         * BE CAREFUL! This util is different from UrlParser.
         * This one works only from the given string, when UrlParser work from window.location.
         * It means UrlParser will resolve the host of a relative URL using the host of the current window.
         *
         * @param {String} url - the URL to parse
         * @returns {Object} parsedUrl with the properties available in key below and query that contains query string key/values.
         */
        parse : function parse (url) {
            var	o   = {
                    key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","queryString","hash"],
                    q:   {
                        name:   "query",
                        parser: /(?:^|&)([^&=]*)=?([^&]*)/g
                    },
                    parser: {
                        strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                    }
                },
                m   = o.parser.strict.exec(url),
                parsed = {},
                i   = o.key.length;

            while (i--) {
                parsed[o.key[i]] = m[i] || "";
            }

            parsed[o.q.name] = {};
            parsed[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
                if ($1) {
                    parsed[o.q.name][$1] = $2;
                }
            });

            return parsed;
        }
    };

    return urlUtil;
});





