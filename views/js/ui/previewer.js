/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'lodash', 'core/pluginifier'], function($, _, Pluginifier){
    'use strict';

    var ns = 'previewer';
    var dataNs = 'ui.' + ns;

    var defaults = {

    };

   //the previewer will show the resource regarding it's mime/type, willcards are supported for subtypes
   var mimeMapping = {
        'mediaElement'  : ['application/ogg', 'audio/*', 'video/*'],
        'image'         : ['image/*'],
        'object'        : ['application/pdf'], 
        'flash'         : ['application/x-shockwave-flash'],
        'mathml'        : ['application/mathml+xml']
    }; 

    /**
     * @exports ui/previewer
     */
    var previewer = {
     
        /**
         * Initialize the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').previewer({ file : 'test.mp4', type : 'video/mp4' });
         * @public
         *
         * @constructor
         * @param {Object} [options] - the plugin options
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
        


            //get options using default
            options = _.defaults(options || {}, defaults);

            return this.each(function(){
                var $elt = $(this);
                if(!$elt.data(dataNs)){

                    /**
                     * The plugin have been created.
                     * @event Incrementer#create.incrementer
                     */
                    $elt.trigger('create.' + ns);
                }
            });
        },

        update : function(file, type){
            return this.each(function(){
                previewer._update($(this), file, type);
            });
        },
       
        _update : function($elt, file, type){
            
        },
 
        /**
         * Destroy completely the plugin.
         *
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').filePreviewer('destroy');
         * @public
         */
        destroy : function(){
            this.each(function(){
                var $elt = $(this);
                var options = $elt.data(dataNs);

                /**
                 * The plugin have been destroyed.
                 * @event Incrementer#destroy.incrementer
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
    };

    //Register the incrementer to behave as a jQuery plugin.
    Pluginifier.register(ns, previewer);

    /**
     * The only exposed function is used to start listening on data-attr
     *
     * @public
     * @example define(['ui/previewer'], function(previewer){ previewer($('rootContainer')); });
     * @param {jQueryElement} $container - the root context to listen in
     */
    return function listenDataAttr($container){

        $container.find('[data-preview]').each(function(){
            var $elt = $(this);
            $elt.previewer({
                file : $elt.data('preview'),
                type : $elt.data('preview-type')
            });
        });
    };
});

