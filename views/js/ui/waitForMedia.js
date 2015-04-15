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
 * Copyright (c) 2015 (original work) Open Assessment Technlogies SA ;
 * 
 * @author Sam Sipasseuth <sam@taotesting.com>
 * @requires jquery
 */
define(['jquery'], function($){

    var _ns = '.wait';

    /**
     * Register a plugin that enable waiting for all media being loaded
     * 
     * @fires loaded.wait - fired when a media has been loaded
     * @fires all-loaded.wait - fired when all media has been loaded
     * @param {Function} [allLoadedCallback] - callback to be executed when all media has been loaded
     * @returns {jQueryElement} for chaingin
     */
    $.fn.waitForMedia = function(allLoadedCallback){
        
        function allLoaded($container) {
            $container.trigger('all-loaded' + _ns);
            if(typeof allLoadedCallback === 'function'){
                allLoadedCallback.call($container[0]);
            }
        } 
        
        return this.each(function(){

            var $container = $(this);
            var $img = $container.find('img');
            var count = $img.length;
            var loaded = 0;
            
            if (count === 0) {
                allLoaded($container);
            }
            
            /**
             * The function to be executed whenever an image is considered loaded
             */
            function imageLoaded(){

                $(this)
                    .trigger('loaded' + _ns)
                    .off('load' + _ns)
                    .off('error' + _ns);

                loaded++;
                if(loaded === count){
                    allLoaded($container);
                }
            }

            $img.each(function(){
                if(this.complete){
                    //the image is already loaded by the browser
                    imageLoaded.call(this);
                }else{
                    //the image is not yet loaded : add "load" listener
                    $(this).on('load' + _ns + ' error' + _ns, imageLoaded);
                }
            });

        });

    };

});