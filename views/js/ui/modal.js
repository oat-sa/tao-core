define(['jquery', 'core/pluginifier', 'core/dataattrhandler'], function($, Pluginifier, DataAttrHandler){
    'use strict';
    
    /**
     * jQuery modal is an easy to use plugin 
     * which allows you to create modal windows
     * @example $('#modal-window').modal();
     * 
     * @require jquery >= 1.7.0 [http://jquery.com/]
     */

    var pluginName = 'modal';
    var dataNs = 'ui.' + pluginName;
    
    var defaults = {
        modalClose  : 'modal-close',
        modalOverlay: 'modal-bg',
        disableOverlayClose: false
    };


    var Modal = {
       /**
        * Initialize the modal dialog
        * @param {object} options - plugin options
        * @returns {jQuery object}
        */
       init: function(options){
          
          //extend the options using defaults
          options = $.extend(true, {}, defaults, options);
          
          return $(this).each(function() {
            var $modal = $(this);
            //add data to the element
            $modal.data(dataNs, options);
            
            //Initialize the overlay for the modal dialog
            if ($('#'+options.modalOverlay).length === 0) {
               $('<div/>').attr({'id':options.modalOverlay, 'class':'modal-bg'}).insertAfter($modal);
            }
            
            //Initialize the close button for the modal dialog
            if ($('#'+options.modalClose).length === 0) {
               $('<div/>').attr({'id':options.modalClose}).html('<span class="icon-close"></span>').appendTo($modal);
            }
            
            Modal._openModal($(this));
            
            /**
             * The plugin have been created.
             * @event Modal#create.modal
             */
            $modal.trigger('create.' + pluginName);
          });
       },

       /**
        * Bind events
        * @param {jQuery object} $element
        * @returns {undefined}
        */
       _bindEvents: function($element){
          var options = $element.data(dataNs);
          
          $(window).on('resize.'+pluginName, function(e){
             e.preventDefault();
             Modal._resizeModal($element);
          });

          $('#'+options.modalClose).on('click.'+pluginName, function(e){
             e.preventDefault();
             Modal._closeModal($element);
          });
          
          if(!options.disableOverlayClose){
            $('#'+options.modalOverlay).on('click.'+pluginName, function(e){
               e.preventDefault();
               Modal._closeModal($element);
            });
          }

          $(document).on('keydown.'+pluginName, function(e) {
             if(e.keyCode===27){
                e.preventDefault();
                Modal._closeModal($element);
             }
          });
       },

       /**
        * Unbind events
        * @param {jQuery object} $element
        * @returns {undefined}
        */
       _unBindEvents: function($element){
          var options = $element.data(dataNs);
          
          $(window).off('resize.'+pluginName);
          $element.off('click.'+pluginName);
          
          if(!options.disableOverlayClose){
              $('#'+options.modalOverlay).off('click.'+pluginName);
          }
          
          $(document).off('keydown.'+pluginName);
       },

       /**
        * Open the modal dialog
        * @returns {jQuery object}
        */
       openModal: function(){
           return this.each(function() {
                Modal._openModal($(this));
           });
       },
       
       /**
        * Private function for opening
        * @param {jQuery object} $element
        * @returns {jQuery object}
        */
       _openModal: function($element){
          var modalHeight = $element[0].clientHeight,
              windowHeight = $(window).height(),
              options = $element.data(dataNs);
      
          if (typeof options !== 'undefined'){
            //Calculate the top offset
            var topOffset = modalHeight>windowHeight?40:(windowHeight-modalHeight)/2;
            
            Modal._resizeModal($element);
            $element.css({
                'top': '-'+modalHeight+'px'
            });
            $('#'+options.modalOverlay).fadeIn(300);
            $element.animate({'opacity': '1', 'top':topOffset+'px'});
          
            Modal._bindEvents($element);
          }
       },

       /**
        * Close the modal dialog
        * @returns {undefined}
        */
       closeModal: function(){
           return this.each(function() {
               Modal._closeModal($(this));
           });
       },
       
       /**
        * Private function for closing
        * @param {jQuery object} $element
        * @returns {undefined}
        */
       _closeModal: function($element){
           var options = $element.data(dataNs);
       
           Modal._unBindEvents($element);
           
           $('#'+options.modalOverlay).fadeOut(300);
           $element.animate({'opacity': '0', 'top':'-1000px'}, 500);
           
           /**
            * The target has been closed/removed. 
            * @event Modal#closed.modal
            */
           $element.trigger('closed.'+ pluginName);
       },
       
       /**
        * Resize the modal window
        * @param {jQuery object} $element
        * @returns {undefined}
        */
       _resizeModal: function($element){
           var windowWidth = $(window).width();
           
           $element.css({
                'width': (windowWidth*0.7)+'px',
                'margin-left': (windowWidth*0.15)+'px'
           });
       }
    };


   //Register the modal to behave as a jQuery plugin.
   Pluginifier.register(pluginName, Modal);
   
   /**
    * The only exposed function is used to start listening on data-attr
    * 
    * @public
    * @example define(['ui/modal'], function(modal){ modal($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to listen in
    */
   return function listenDataAttr($container){
        new DataAttrHandler('modal', {
            container: $container,
            listenerEvent: 'click',
            namespace: dataNs
        }).init(function($elt, $target) {
            $target.modal();
        });
    };

});
