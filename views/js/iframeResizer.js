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
define(['jquery', 'jquery.sizechange'], function($){
    
    /**
     * Helps you to resize an iframe from it's content
     * 
     * todo migrate to a jQuery plugin ?
     * 
     * @author Bertrand Chevrier <betrand@taotesting.com>
     * @exports iframeResizer
     */
    var Resizer = {
            
        /**
         * Set the height of an iframe regarding it's content, on load and if the style changes.
         * 
         * @param {jQueryElement} $frame - the iframe to resize
         * @param {string} [restrict = '*'] - restrict the elements that can have a style change
         * @returns {jQueryElement} $frame for chaining 
         */
        autoHeight : function($frame, restrict){
            var self = this;
            restrict = restrict || 'body';
            $frame.load(function(){
                self._adaptHeight($frame);
                
                try{
                    $frame.contents().find(restrict).sizeChange(function(){
                        self._adaptHeight($frame);
                    });
                } catch(e){
                    //fallback to an interval mgt
                    setInterval(function(){
                        self._adaptHeight($frame);
                    }, 50);
                } 
            });
            
            return $frame;
        },
        
        _adaptHeight : function($frame){
            $frame.height($frame.contents().height());
        }
        
    };
    return Resizer;
;
});


