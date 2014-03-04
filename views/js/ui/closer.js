/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires core/pluginifier
 * @requires core/dataattrhandler
 */
define(['jquery', 'lodash', 'core/pluginifier', 'core/dataattrhandler'], function($, _, Pluginifier, DataAttrHandler){
   'use strict';
   
   var ns = 'closer';
   var dataNs = 'ui.' + ns;
   
   var defaults = {
       bindEvent : 'click',
       confirm : true,
       confirmMessage : 'Are you sure you want to close it?',
       disableClass : 'disabled'
   };
   
   /** 
    * The Closer component, that helps you to close a new element.
    * @exports ui/closer
    */
   var Closer = {
       
        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').closer({target : $('target')});
         * @public
         * 
         * @constructor
         * @param {Object} options - the plugin options
         * @param {jQueryElement} options.target - the element to close
         * @param {string|boolean} [options.bindEvent = 'click'] - the event that trigger the close
         * @param {boolean} [options.confirm = true] - diplay a popup to confirm the closing
         * @param {string} [optionsconfirmMessage = '...'] - the confirmation message
         * @fires Closer#create.closer
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            options = _.defaults(options, defaults);
           
            return this.each(function() {
                var $elt = $(this);
                
                if(!$elt.data(dataNs)){
                    //add data to the element
                    $elt.data(dataNs, options);

                     //bind an event to trigger the close
                    if(options.bindEvent !== false){
                        $elt.on(options.bindEvent, function(e){
                            e.preventDefault();
                             Closer._close($elt);
                         });
                    }

                    /**
                     * The plugin have been created.
                     * @event Closer#create.closer
                     */
                    $elt.trigger('create.' + ns);
                }
            });
       },
       
       /**
        * Trigger the close. 
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').closer('close');
        * @public
        * 
        * @returns {jQueryElement} for chaining
        */
       close : function(){
           this.each(function() {
                Closer._close($(this));
           });
       },
               
       /**
        * Internal close mechanism.
        * 
        * @private
        * @param {jQueryElement} $elt - plugin's element 
        * @fires Closer#close.closer
        * @fires close
        */
       _close : function($elt){
           var options = $elt.data(dataNs);
           if(options && !$elt.hasClass(options.disableClass)){
                var $target = options.target;
                var close = true;

                if(options.confirm === true){
                    close = confirm(options.confirmMessage);
                }
                if(close){

                    /**
                      * The plugin is closing the target. 
                      * Those eventes are fired just before the removal 
                      * to be able to listen them 
                      * (if $elt is inside the closed elt for instance)
                      * @event Closer#close.closer
                      * @param {jQueryElement} $target - the element being closed/removed
                      */
                    $elt.trigger('close.'+ ns, [$target]);
                    $target.trigger('close');            //global event for consistensy

                    $target.remove();

                    /**
                      * The target has been closed/removed. 
                      * @event Closer#closed.closer
                      */
                    $elt.trigger('closed.'+ ns);
                }
           }
       },
       
       /**
        * Disable the closer action. 
        * 
        * It can be called prior to the plugin initilization.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').closer('disable');
        * @public
        * 
        * @returns {jQueryElement} for chaining
        */
       disable : function(){
            this.each(function() {
                Closer._disable($(this));
           });
       },
       
       /**
        * Internal disabling mechanism.
        * 
        * @private
        * @param {jQueryElement} $elt - plugin's element 
        * @fires Closer#disabled.closer
        */
       _disable : function($elt){
            var options = $elt.data(dataNs);
            if(options){
                $elt.addClass(options.disableClass)
                    .trigger('disabled.'+ ns);
            }
       },
       
       /**
        * Enable the closer action. 
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').closer('enable');
        * @public
        * 
        * @returns {jQueryElement} for chaining
        */
       enable : function(){
            this.each(function() {
                Closer._enable($(this));
           });
       },
             
       /**
        * Internal enabling mechanism.
        * 
        * @private
        * @param {jQueryElement} $elt - plugin's element 
        * @fires Closer#enabled.closer
        */
       _enable : function($elt){
            var options = $elt.data(dataNs);
            if(options){
                $elt.removeClass(options.disableClass)
                   .trigger('enabled.'+ ns);
            }
       },
        
       /**
        * Destroy completely the plugin.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').closer('destroy');
        * @public
        * @fires Closer#destroy.closer
        */
       destroy : function(){
            this.each(function() {
                var $elt = $(this);
                var options = $elt.data(dataNs);
                if(options.bindEvent !== false){
                    $elt.off(options.bindEvent);
                }
                $elt.removeData(dataNs);
                
                /**
                 * The plugin have been destroyed.
                 * @event Closer#destroy.closer
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
   };
   
   //Register the toggler to behave as a jQuery plugin.
   Pluginifier.register(ns, Closer);
   
   /**
    * The only exposed function is used to start listening on data-attr
    * 
    * @public
    * @example define(['ui/closer'], function(closer){ closer($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to listen in
    */
   return function listenDataAttr($container){
       
        new DataAttrHandler('close', {
            container: $container,
            listenerEvent: 'click',
            namespace: dataNs,
            bubbled: true
        }).init(function($elt, $target) {
            var options = {
                target: $target,
                bindEvent: false
            };
            var confirm = $elt.data('confirm');
            if(confirm !== null){
                if(confirm === false){
                    options.confirm = false;
                } else {
                    options.confirmMessage = confirm;
                }
            }
            $elt.closer(options);
        }).trigger(function($elt) {
            $elt.closer('close');
        });
    };
});

