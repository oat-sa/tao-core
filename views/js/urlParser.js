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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define(['lodash'], function(_){
   
   var urlParts = ['hash', 'host', 'hostname', 'pathname', 'port', 'protocol', 'search'];
   
   /**
    * Parse an URL and gives you access to its parts
    * @exports urlParser
    * @constructor
    * @param {String} url
    */
   function UrlParser(url){
       this.url = url;
       
       //use the parser within the browser DOM engine
       //thanks to https://gist.github.com/jlong/2428561
       var detachedAnchor = document.createElement('a');
       detachedAnchor.href = url;
       this.data = _.pick(detachedAnchor, urlParts);
   }
   
   /**
    * Get a part of the url 
    * @memberOf UrlParser
    * @param {string} what - in 'hash', 'host', 'hostname', 'pathname', 'port', 'protocol', 'search'
    * @returns {String|Boolean} the requested url part or false
    */
   UrlParser.prototype.get = function(what){
       return urlParts.indexOf(what) > - 1 ? this.data[what] : false;
   };
    
   /**
    * Get an object that represents the URL's query params
    * @memberOf UrlParser
    * @returns {Object} key : value
    */
   UrlParser.prototype.getParams = function(){
       var params = {}; 
       this.data.search.replace(/^\?/, '').replace(/([^=&]+)=([^&]*)/g, function(m, key, value) {
            params[decodeURIComponent(key)] = decodeURIComponent(value);
        }); 
        return params;
   };
   
   /**
    * Get each paths chunk
    * @memberOf UrlParser
    * @returns {Array} - the paths
    */
   UrlParser.prototype.getPaths = function(){
       return this.data.pathname.replace(/^\/|\/$/g, '').split('/');
   };
    
   return UrlParser;
});
