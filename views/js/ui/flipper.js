/**
 * thanks to http://jsfiddle.net/azizpunjani/JfUbW/
 * 
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires core/pluginifier
 * @requires core/dataattrhandler
 */
define(['jquery', 'core/pluginifier', 'core/dataattrhandler'], function($, Pluginifier, DataAttrHandler){
   'use strict';
   
   var ns = 'flipper';
   var dataNs = 'cards.' + ns;
   
   var defaults = {
       bindEvent   : 'click',
       stateClass : 'flipped',
       backStyle: {
            width: 0,
            opacity: 0.3
        },
        frontStyle: {
            marginLeft: 0,
            opacity: 1
        }
   };
   
   /** 
    * The Flipper component, that helps you to flip a component from front to back face.
    * @exports cards/flipper
    */
   var Flipper = {
       
        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').flipper({target : $('target') });
         * @public
         * 
         * @constructor
         * @param {Object} options - the plugin options
         * @param {string|boolean} [options.bindEvent = 'click'] - the event that trigger the toggling
         * @fires Flipper#create.flipper
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            
            //get options using default
            options = $.extend(true, {}, defaults, options);
           
            return this.each(function() {
                var $elt = $(this);
                
                if(!$elt.data(dataNs)){
                
                    var width = options.front.width();
                    options.backStyle.marginLeft = width / 2 + 'px';
                    options.frontStyle.width = width + 'px';

                    //add data to the element
                    $elt.data(dataNs, options);

                    options.back.css(options.backStyle);
                    options.back.css({
                        position: 'absolute',
                        display: 'block',
                        visibility: 'hidden',
                        top: options.front.position().top,
                        left: options.front.position().left
                    });
                    
                    if(options.unflip){
                        options.unflip.on('click', function(e){
                            e.preventDefault();
                            Flipper._unflip($elt);
                         });
                    }

                    //bind an event to trigger the toggling
                    if(options.bindEvent !== false){
                        $elt.on(options.bindEvent, function(e){
                            e.preventDefault();
                            Flipper._toggle($(this));
                         });
                    }

                    /**
                     * The plugin have been created.
                     * @event Flipper#create.flipper
                     */
                    $elt.trigger('create.' + ns);
                }
            });
       },
       
       toggle : function(){
           return this.each(function() {
                Flipper._toggle($(this));
           });
       },
       
       
        _toggle: function($elt){
            var options = $elt.data(dataNs);
            var $front = options.front;
            if($front.hasClass(options.stateClass)){
                this._unflip($elt);
            } else {
                this._flip($elt);
            }
        },
       
       
       flip : function(){
           return this.each(function() {
                Flipper._flip($(this));
           });
       },
               
       _flip: function($elt){
            var options = $elt.data(dataNs);
            var $front = options.front;
            var $back = options.back;
           
            //animate width to 0 and margin-left to 1/2 width
            $front.stop().animate(options.backStyle, 300, function() {
                $back.css('visibility', 'visible');
                $front.css('visibility', 'hidden')
                        .addClass(options.stateClass);
                // animate second card to full width and margin-left to 0  
                $back.animate(options.frontStyle, 300);
            });
        
            $elt.trigger('flip.' + ns);
       },
       
       unflip : function(){
           return this.each(function() {
                Flipper._unflip($(this));
           });
       },
               
       _unflip: function($elt){
            var options = $elt.data(dataNs);
            var $front = options.front;
            var $back = options.back;
            
            //animate width to 0 and margin-left to 1/2 width
            $back.stop().animate(options.backStyle, 300, function() {
                $back.css('visibility', 'hidden');
                $front.css('visibility', 'visible')
                        .removeClass(options.stateClass);
                // animate second card to full width and margin-left to 0  
                $front.animate(options.frontStyle, 300);
            });
        
            $elt.trigger('unflip.' + ns);
       },
               
       /**
        * Destroy completely the plugin.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').flipper('destroy');
        * @public
        */
       destroy : function(){
            this.each(function() {
                var $elt = $(this);
                var options = $elt.data(dataNs);
                if(options.unflip){
                    options.unflip.off('click');
                }
                if(options.bindEvent !== false){
                    $elt.off(options.bindEvent);
                }
                
                /**
                 * The plugin have been destroyed.
                 * @event Flipper#destroy.flipper
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
   };
   
   //Register the flipper to behave as a jQuery plugin.
   Pluginifier.register(ns, Flipper);
   
   /**
    * The only exposed function is used to start listening on data-attr
    * 
    * @public
    * @example define(['cards/flipper'], function(flipper){ flipper($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to listen in
    */
   return function listenDataAttr($container){
        new DataAttrHandler('flip', {
            container: $container,
            listenerEvent: 'click',
            namespace: dataNs
        }).init(function($elt, $target) {
            var options = {
                bindEvent: false
            };
            if($elt.data('flip-back')){
                options.front = $target;
                options.back = $($elt.data('flip-back'));
                
                if($elt.data('unflip')){
                    options.unflip = $($elt.data('unflip'));
                } 
                
            } else {
                $.error('Define either data-flip-back or data-flip-front, please!');
            }
            $elt.flipper(options);
        }).trigger(function($elt) {
            $elt.flipper('flip');
        });
    };
});

