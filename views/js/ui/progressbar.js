/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires lodash
 * @requires core/pluginifier
 * @requires core/dataattrhandler
 */
define(['jquery', 'lodash', 'handlebars', 'core/pluginifier', 'core/dataattrhandler'], 
function($, _, Handlebars, Pluginifier, DataAttrHandler){
   'use strict';
   
   var ns = 'progressbar';
   var dataNs = 'ui.' + ns;
   
   
   var defaults = {
       disableClass : 'disabled',
       value : 0
   };
   
   /** 
    * The Progressbar component. 
    * @exports ui/progressbar
    */
   var progressBar = {
       
        /**
         * Initialize the plugin.
         * 
         * Called the jQuery way once registered by the Pluginifier.
         * @example $('selector').progressbar({ value : 15 });
         * @public
         * 
         * @constructor
         * @param {Object} options - the plugin options
         * @param {jQueryElement} options.value - the progress value in %
         * @fires progressBar#create.progressbar
         * @returns {jQueryElement} for chaining
         */
        init : function(options){
            options = _.defaults(options || {}, defaults);
            
            return this.each(function() {
                var $elt = $(this);
                
                if(!$elt.data(dataNs)){
                    //add data to the element
                    $elt.data(dataNs, options);


                    /**
                     * The plugin have been created.
                     * @event progressBar#create.progressbar
                     */
                    $elt.trigger('create.' + ns);
                }
            });
       },
         
       /**
        * Trigger the adding. 
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').progressbar('add');
        * @param {jQueryElement} $elt - plugin's element 
        * @fires progressBar#add.progressbar
        * @fires progressBar#add
        */
       _add : function($elt){
           var options = $elt.data(dataNs);
           var $target = options.target;
           
           //call appendTo, prependTo, etc.
           var position = options.position + 'To';
           
           var applyTemplate = function applyTemplate($content, position, $target, data){
               $content[position]($target);

               /**
                * The target has received content.
                * @event progressBar#add
                * @param {jQueryElement} - the added content
                * @param {Object} - the data bound to the added content
                */
               $target.trigger('add', [$content, data]);
               
               /**
                * The content has been added.
                * @event progressBar#add.progressbar
                * @param {jQueryElement} - the target
                * @param {jQueryElement} - the added content
                * @param {Object} - the data bound to the added content
                */
               $elt.trigger('add.'+ns, [$target, $content, data]);

           };
           
           //DOM element or template
           if(typeof options._template === 'function'){

               options.templateData(function templateDataCallback(data){
                    applyTemplate($(options._template(data)), position, $target, data);
               });
             
           } else {
               applyTemplate($(options._html), position, $target);
           }
       },
               
       /**
        * Destroy completely the plugin.
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').progressbar('destroy');
        * @public
        * @fires progressBar#destroy.progressbar
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
                 * @event progressBar#destroy.progressbar
                 */
                $elt.trigger('destroy.' + ns);
            });
        }
   };
   
   //Register the toggler to behave as a jQuery plugin.
   Pluginifier.register(ns, progressBar, {
        expose : ['add']
   });
   
   /**
    * The only exposed function is used to start listening on data-attr
    * 
    * @public
    * @example define(['ui/progressbar'], function(progressbar){ progressbar($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to listen in
    */
   return function listenDataAttr($container){
       
        new DataAttrHandler('add', {
            container: $container,
            listenerEvent: 'click',
            namespace: dataNs
        }).init(function($elt, $target) {
            $elt.progressbar({
                target: $target,
                bindEvent: false,
                content: $($elt.attr('data-content'))
            });
        }).trigger(function($elt) {
            $elt.progressbar('add');
        });
    };
});

