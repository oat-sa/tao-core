/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires core/pluginifier
 * @requires core/dataattrhandler
 */
define(['jquery', 'core/pluginifier', 'core/dataattrhandler'], function($, Pluginifier, DataAttrHandler){
   'use strict';
   
   var ns = 'toggler';
   var dataNs = 'ui.' + ns;
   
   var defaults = {
       bindEvent   : 'click',
       openedClass : 'opened',
       closedClass : 'closed'
   };
   
   /** 
    * The Toggler component, that helps you to show/hide an element
    * @exports ui/toggler
    */
   var Toggler = {
       
        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').toggler({target : $('target') });
         * @public
         * 
         * @constructor
         * @param {Object} options - the plugin options
         * @param {jQueryElement} options.target - the element to be toggled
         * @param {string|boolean} [options.bindEvent = 'click'] - the event that trigger the toggling
         * @param {string} [options.openedClass = 'opened'] - the css added to element (not the target) for the opened state
         * @param {string} [options.closedClass = 'closed'] - the css added to element (not the target) for the closed state
         * @fires Toggler#create.toggler
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            
            //get options using default
            options = $.extend(true, {}, defaults, options);
           
            return this.each(function() {
                var $elt = $(this);
               
                if(!$elt.data(dataNs)){
                    //add data to the element
                    $elt.data(dataNs, options);

                    //add the default class if not set
                    if(!$elt.hasClass(options.closedClass) && !$elt.hasClass(options.openedClass)){
                        $elt.addClass(options.closedClass);
                    }

                    //bind an event to trigger the toggling
                    if(options.bindEvent !== false){
                        $elt.on(options.bindEvent, function(e){
                            e.preventDefault();
                            Toggler._toggle($(this));
                         });
                    }

                    /**
                     * The plugin have been created.
                     * @event Toggler#create.toggler
                     */
                    $elt.trigger('create.' + ns);
                }
            });
       },
       
       /**
        * Toggle the target.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').toggler('toggle');
        * @public
        * 
        * @returns {jQueryElement} for chaining
        */
       toggle : function(){
           return this.each(function() {
                Toggler._toggle($(this));
           });
       },
               
       /**
        * Internal toggling mechanism.
        * 
        * @private
        * @param {jQueryElement} $elt - plugin's element 
        * @fires Toggler#toggle.toggler
        * @fires Toggler#open.toggler
        * @fires Toggler#close.toggler
        */
       _toggle: function($elt){
            var options = $elt.data(dataNs);
            var $target = options.target;

           var action;
           if( $elt.is(':radio,:checkbox') ){
                action =  $elt.prop('checked') ?  'open' : 'close';
            } else {
                action =  $elt.hasClass(options.closedClass) ?  'open' : 'close';
                $elt.toggleClass(options.closedClass)
                    .toggleClass(options.openedClass);
            }
            
            if(action === 'open'){
                $target.show();
            } else {
                $target.hide();
            }
        
           /**
            * The target has been toggled. 
            * Trigger 2 events : toggle and open or close.
            * @event Toggler#toggle.toggler
            * @event Toggler#open.toggler
            * @event Toggler#close.toggler
            */
            $elt.trigger('toggle.' + ns, [$target])
                .trigger(action + '.' + ns, [$target]);
       },
               
       /**
        * Destroy completely the plugin.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').toggler('destroy');
        * @public
        */
       destroy : function(){
            this.each(function() {
                var $elt = $(this);
                var options = $elt.data(dataNs);
                if(options.bindEvent !== false){
                    $elt.off(options.bindEvent);
                }
                
                /**
                 * The plugin have been destroyed.
                 * @event Toggler#destroy.toggler
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
   };
   
   //Register the toggler to behave as a jQuery plugin.
   Pluginifier.register(ns, Toggler);
   
   /**
    * The only exposed function is used to start listening on data-attr
    * 
    * @public
    * @example define(['ui/toggler'], function(toggler){ toggler($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to listen in
    */
   return function listenDataAttr($container){
       
        new DataAttrHandler('toggle', {
            container: $container,
            listenerEvent: 'click',
            namespace: dataNs,
        }).init(function($elt, $target) {
            $elt.toggler({
                target: $target,
                bindEvent: false
            });
        }).trigger(function($elt) {
            $elt.toggler('toggle');
        });
    };
});

