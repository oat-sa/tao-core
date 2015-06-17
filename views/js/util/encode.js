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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 *
 */

/**
 *
 * @author dieter <dieter@taotesting.com>
 */
define([

], function () {
    'use strict';

    var _reQuotes = /(['"])/g;
    var _quotesEntities = {
        "'" : '&apos;',
        '"' : '&quot;'
    };

    /**
     * Encodes an HTML string to be safely displayed without code interpretation
     *
     * @param {String} html
     * @returns {String}
     */
    var encodeHTML = function encodeHTML(html) {
        // @see http://tinyurl.com/ko75kph
        return document.createElement('a').appendChild(
            document.createTextNode(html)).parentNode.innerHTML;
    };

    /**
     * Encodes an HTML string to be safely use inside an attribute
     *
     * @param {String} html
     * @returns {String}
     */
    var encodeAttribute = function encodeAttribute(html) {
        return encodeHTML(html).replace(_reQuotes, function(substr, $1) {
            return _quotesEntities[$1] || $1;
        });
    };

    return {
        html: encodeHTML,
        attribute: encodeAttribute
    };
});
