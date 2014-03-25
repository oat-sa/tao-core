/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires core/pluginifier
 * @requires core/dataattrhandler
 */
define(['jquery', 'lodash', 'core/pluginifier', 'core/dataattrhandler'], function($, _, Pluginifier, DataAttrHandler){
   'use strict';
   
   var ns = 'deleter';
   var dataNs = 'ui.' + ns;
   
   var defaults = {
       bindEvent : 'click',
       confirm : true,
       confirmMessage : 'Are you sure you want to close it?',
       disableClass : 'disabled'
   };
   
   /** 
    * The deleter component, that helps you to close a new element.
    * @exports ui/deleter
    */
   var deleter = {
        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').deleter({target : $('target')});
         * @public
         * 
         * @constructor
         * @param {Object} options - the plugin options
         * @param {jQueryElement} options.target - the element to close
         * @param {string|boolean} [options.bindEvent = 'click'] - the event that trigger the close
         * @param {boolean} [options.confirm = true] - diplay a popup to confirm the closing
         * @param {string} [optionsconfirmMessage = '...'] - the confirmation message
         * @fires deleter#create.deleter
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
                             deleter._close($elt);
                         });
                    }

                    /**
                     * The plugin have been created.
                     * @event deleter#create.deleter
                     */
                    $elt.trigger('create.' + ns);
                }
            });
       },
       
       /**
        * Trigger the close. 
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').deleter('close');
        * @public
        * 
        * @returns {jQueryElement} for chaining
        */
       close : function(){
           this.each(function() {
                deleter._close($(this));
           });
       },
               
       /**
        * Internal close mechanism.
        * 
        * @private
        * @param {jQueryElement} $elt - plugin's element 
        * @fires deleter#close.deleter
        * @fires close
        */
       _close : function($elt){
           var options = $elt.data(dataNs);
           if(options && !$elt.hasClass(options.disableClass)){
                var $target = options.target;
                var close = true;
                var $evtTrigger;

                if(options.confirm === true){
                    close = confirm(options.confirmMessage);
                }
                if(close){

                    //if elt is inside target, we get the parent to simulate the bubbing
                    $evtTrigger = ($target.has($elt).length > 0) ? $target.parent()  : $elt;

                    /**
                      * The plugin is closing the target. 
                      * Those eventes are fired just before the removal 
                      * to be able to listen them 
                      * (if $elt is inside the closed elt for instance)
                      * @event deleter#deleted.deleter
                      * @param {jQueryElement} $target - the element being closed/removed
                      */
                    $elt.trigger('delete.'+ ns, [$target]);
                    $target.trigger('delete');            //global event for consistensy

                    $target.detach();

                    //remove the target once the atteched events may be terminated (no guaranty, this happens after in the event loop)
                    setTimeout(function(){
                        
                        $target.remove();
                        
                        /**
                          * The target has been closed/removed. 
                          * @event deleter#deleted.deleter
                          */
                        $evtTrigger.trigger('deleted.'+ ns).trigger('deleted');

                    }, 10);
                }
           }
       },
       
       /**
        * Disable the deleter action. 
        * 
        * It can be called prior to the plugin initilization.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').deleter('disable');
        * @public
        * 
        * @returns {jQueryElement} for chaining
        */
       disable : function(){
            this.each(function() {
                deleter._disable($(this));
           });
       },
       
       /**
        * Internal disabling mechanism.
        * 
        * @private
        * @param {jQueryElement} $elt - plugin's element 
        * @fires deleter#disabled.deleter
        */
       _disable : function($elt){
            var options = $elt.data(dataNs);
            if(options){
                $elt.addClass(options.disableClass)
                    .trigger('disabled.'+ ns);
            }
       },
       
       /**
        * Enable the deleter action. 
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').deleter('enable');
        * @public
        * 
        * @returns {jQueryElement} for chaining
        */
       enable : function(){
            this.each(function() {
                deleter._enable($(this));
           });
       },
             
       /**
        * Internal enabling mechanism.
        * 
        * @private
        * @param {jQueryElement} $elt - plugin's element 
        * @fires deleter#enabled.deleter
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
        * @example $('selector').deleter('destroy');
        * @public
        * @fires deleter#destroy.deleter
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
                 * @event deleter#destroy.deleter
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
   };
   
   //Register the toggler to behave as a jQuery plugin.
   Pluginifier.register(ns, deleter);
   
   /**
    * The only exposed function is used to start listening on data-attr
    * 
    * @public
    * @example define(['ui/deleter'], function(deleter){ deleter($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to listen in
    */
   return function listenDataAttr($container){
       
        new DataAttrHandler('delete', {
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
            $elt.deleter(options);
        }).trigger(function($elt) {
            $elt.deleter('close');
        });
    };
});

