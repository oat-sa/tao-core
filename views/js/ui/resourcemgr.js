define([
    'jquery',
    'lodash', 
    'core/pluginifier', 
    'core/dataattrhandler', 
    'ui/modal',
    'ui/resourcemgr/file-browser',
    'ui/resourcemgr/file-preview',
    'ui/resourcemgr/file-selector',
    'tpl!ui/resourcemgr/layout'
], function($, _, Pluginifier, DataAttrHandler, modal, fileBrowser, filePreview, fileSelector, layout){

    'use strict';
   
   var ns = 'resourcemgr';
   var dataNs = 'ui.' + ns;
   
   var defaults = {
        bindEvent   : 'click',
        appendContainer : '.tao-scope:first'
   };
   
   /** 
    * The ResourceMgr component helps you to browse and select external resources.
    * @exports ui/resourcemgr
    */
   var resourceMgr = {

        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').resourcemgr({
         *
         *  });
         * 
         * @constructor
         * @param {Object} options - the plugin options
         * @param {Sring|Boolean} [options.bindEvent = 'click'] - the event that trigger the toggling
         * @param {String} options.url - the URL of the service used to retrieve the resources.
         * @fires ResourceMgr#create.resourcemgr
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            var self = resourceMgr;
            
            //get options using default
            options = _.defaults(options, defaults);
           
            return this.each(function() {
                var $elt = $(this);
                var $target; 

                if(!$elt.data(dataNs)){

                    //add data to the element
                    $elt.data(dataNs, options);

                    //auto bind events configured in options
                    _.functions(options).forEach(function(eventName){
                        $elt.on(eventName + '.' + ns, function(){
                            options[eventName].apply($elt, arguments);
                        });
                    });
                   
                    $target = options.$target || self._createTarget($elt);
            
                    $target.modal({
                        startClosed: true,
                        minWidth : 900,
                        disableClosing: true
                    });

                    $target.on('select.' + ns, function(e, uris){
                        self._close($elt);
                        $elt.trigger(e, [uris]);
                    });

                    fileBrowser($target, '/');
                    fileSelector($target, '/');
                    filePreview($target, '/');
        
                     //bind an event to trigger the addition
                    if(options.bindEvent !== false){
                        $elt.on(options.bindEvent, function(e){
                            e.preventDefault();
                            self._open($elt);
                         });
                    }

                    /**
                     * The plugin have been created.
                     * @event ResourceMgr#create.resourcemgr
                     */
                    $elt.trigger('create.' + ns);
                }
            });
       },
      
       _createTarget : function($elt){
            var options = $elt.data(dataNs);
            if(options){
                //create an identifier to the target content
                options.targetId = 'resourcemgr-' + $(document).find('.resourcemgr').length;
                
                //generate
                options.$target  = $(layout())
                    .attr('id', options.targetId)
                    .css('display', 'none')
                    .appendTo(options.appendContainer);             
 
                $elt.data(dataNs, options);
            }
            return options.$target;
       },

       _open : function($elt){
            var options = $elt.data(dataNs);
            if(options && options.$target){
                options.$target.modal('open');
            }
       }, 
               
       _close : function($elt){
            var options = $elt.data(dataNs);
            if(options && options.$target){
                options.$target.modal('close');
            }
       }, 
       /**
        * Destroy completely the plugin.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').resourcemgr('destroy');
        * @public
        */
       destroy : function(){
            this.each(function() {
                var $elt = $(this);
                var options = $elt.data(dataNs);
                if(options.bindEvent !== false){
                    $elt.off(options.bindEvent);
                }
                if(options.targetId){
                    $('#' + options.targetId).remove();
                } 
                /**
                 * The plugin have been destroyed.
                 * @event ResourceMgr#destroy.resourcemgr
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
   };
   
   //Register the resourcemgr to behave as a jQuery plugin.
   Pluginifier.register(ns, resourceMgr);
   
   /**
    * The only exposed function is used to start listening on data-attr
    * 
    * @public
    * @example define(['ui/resourcemgr'], function(resourcemgr){ resourcemgr($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to listen in
    */
   return function listenDataAttr($container){
       
    };
});

