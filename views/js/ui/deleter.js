/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires core/pluginifier
 * @requires core/dataattrhandler
 */
define([
    'jquery', 
    'lodash', 
    'i18n', 
    'core/pluginifier', 
    'core/dataattrhandler',
    'tpl!ui/deleter/undo'
], function($, _, __, Pluginifier, DataAttrHandler, undoTmpl){
   'use strict';
   
   var ns = 'deleter';
   var dataNs = 'ui.' + ns;
   
   var defaults = {
       bindEvent        : 'click',
       undo             : false,
       undoTimeout      : 5000,
       undoMessage      : __('Element deleted.'),
       undoContainer    : 'body',
       confirm          : false,
       confirmMessage   : __('Are you sure you want to delete it?'),
       disableClass     : 'disabled'
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
         * @param {Boolean} [options.undo = false] - enable to undo the deletion
         * @param {Number}  [options.undoTimeout = 5000] - the time the undo remains available
         * @param {String} [options.undoMessage = '...'] - the message to display in the undo box
         * @param {String|jQueryElement} [options.undoContainer = 'body'] - the element that will contain the undo box
         * @param {boolean} [options.confirm = false] - diplay a popup to confirm the closing
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
                             deleter._delete($elt);
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
        * Trigger the delete. 
        * 
        * Called the jQuery way once registered by the Pluginifier.
        * @example $('selector').closer('close');
        * @param {jQueryElement} $elt - plugin's element 
        * @fires deleter#delete.deleter
        * @fires delete
        * @fires deleter#deleted.deleter
        * @fires deleted
        * @fires deleter#undo.deleter
        */
       _delete : function($elt){
           var self = deleter;
           var performDelete = true;
           var $target, 
               $parent,
               $evtTrigger,
               $placeholder,
               $undoBox;
           var options = $elt.data(dataNs);
           if(options && !$elt.hasClass(options.disableClass)){
                $target = options.target;

                if(options.confirm === true){
                    performDelete = window.confirm(options.confirmMessage);
                }

                if(performDelete){

                    $parent = $target.parent();

                    //if elt is inside target, we get the parent to simulate the bubbing
                    $evtTrigger = ($target.has($elt).length > 0) ? $parent  : $elt;

                    /**
                      * The plugin is removing the target. 
                      * Those eventes are fired just before the removal 
                      * to be able to listen them 
                      * (if $elt is inside the closed elt for instance)
                      * @event deleter#deleted.deleter
                      * @param {jQueryElement} $target - the element being closed/removed
                      */
                    $elt.trigger('delete.'+ ns, [$target]);
                    $target.trigger('delete', [options.undo]);            //global event for consistensy

                    //create a placeholder to retrieve the target position in case of undo
                    $placeholder = $('<span style="display:none;" />').insertAfter($target);
                    $target.detach();
                   
                    if(options.undo){
                        //show the feedback
                        $undoBox = self._createUndoBox(options);
                        $undoBox.find('.undo').click(function(e){
                            e.preventDefault();

                            performDelete = false;
                            $undoBox.remove();
                            $target.insertBefore($placeholder);
                            $placeholder.remove();
                            
                            /**
                              * The delete has been undone
                              * @event deleter#undo.deleter
                              */
                            $elt.trigger('undo.' + ns, [$target]);
                        });
                    } 

                    //remove the target once the atteched events may be terminated (no guaranty, this happens after in the event loop)
                    setTimeout(function(){
                        if(performDelete){ 
                            $target.remove();
                            
                            /**
                              * The target has been closed/removed. 
                              * @event deleter#deleted.deleter
                              */
                            $evtTrigger.trigger('deleted.'+ ns).trigger('deleted');
                        }
                        if($undoBox && $undoBox.length){
                            $undoBox.remove();
                            $placeholder.remove();
                        }
                    }, options.undo ? options.undoTimeout : 10);
                }
           }
       },
      
       /**
        * Create the undo message box
        * @private
        * @param {Object} options - the plugin options
        * @returns {jQueryElement} the undo box
        */ 
       _createUndoBox : function(options){
            return $(undoTmpl(options)).appendTo(options.undoContainer);
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
    Pluginifier.register(ns, deleter, {
        expose : ['delete']
    });
   
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
                bindEvent: false,
                undo : true
            };
            var confirm = $elt.data('delete-confirm');
            var undo = $elt.data('delete-undo');
            if(confirm){
                options.confirm = true;
                options.undo = false;
                if(confirm.length > 0){
                    options.confirmMessage = confirm;
                }
            }
            if(undo !== null && undo !== undefined){
                if(undo === false){
                    options.undo = false;
                } else {
                    options.confirm = false;
                    options.undo = true;
                    if(undo.length > 0){
                        options.undoMessage = undo;
                    }
                }
            }   
            $elt.deleter(options);
        }).trigger(function($elt) {
            $elt.deleter('delete');
        });
    };
});

