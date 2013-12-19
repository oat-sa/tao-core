/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires lodash
 */
define(['jquery', 'lodash'], function($, _){
   'use strict';
   
   /** 
    * Helps you to create a jQuery plugin, the Cards way
    * @exports core/pluginifer
    */
    var Pluginifier = {
        
        /**
         * Regsiter a new jQuery plugin, the Cards way
         * @param {string} pluginName - the name of the plugin to regsiter. ie $('selector').pluginName();
         * @param {Object} plugin - the plugin as a plain object 
         * @param {Function} plugin.init - the entry point of the plugin is always an init method
         */
        register : function(pluginName, plugin){
            if(typeof $.fn[pluginName] === 'function'){
                return $.error('A plugin named ' + pluginName + ' is already registered');
            }
            if(!_.isPlainObject(plugin) || !(typeof plugin.init  === 'function')){
                return $.error('The object to register as a jQuery plugin must be a plain object with an `init` method.');
            }

            $.fn[pluginName] = function(method){
                if(plugin[method]){
                     if(/^_/.test(method)){
                         $.error( 'Trying to call a private method `' + method + '`' );
                     } else {
                         return plugin[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
                     }
                } else if ( typeof method === 'object' || ! method) {
                     return plugin.init.apply( this, arguments );
                } 
                $.error( 'Method ' + method + ' does not exist on plugin' );
            };
        }
    };
   
    return Pluginifier;
});

