/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @requires jquery
 * @requires lodash
 */
define(['jquery', 'lodash'], function($, _){
   'use strict';
   
   var defaults = {
       container : false,
       listenerEvent : 'click',
       useTarget: true
   };
   
   /**
    * Loads the target element from the data-attr (and fallback to href or a named attribute).
    * The value of the data-attr is a CSS selector, it will be applied directly or with $elt as context.
    * 
    * @private
    * @param {String} attrName - the name of the attribute, ie. `toggle` for `data-toggle`
    * @param {jQueryElement} $elt - the element that holds the data attr
    * @returns {jQueryElement} the target
    */
   var getTarget = function getTarget(attrName, $elt){
        var $target = [];
        var targetSelector = $elt.attr('data-' + attrName) || $elt.attr('href') || $elt.attr('attrName');
        if(!_.isEmpty(targetSelector)){
            //try to contextualize from the current element before selcting globally
            if(/^(\+)|(>)/.test(targetSelector)){
                $target = $(targetSelector, $elt);
            } else if(/^:parent/.test(targetSelector)){
                $target = $elt.parent(targetSelector.replace(/^:parent/, ''));
            } else {
                $target = $(targetSelector);
            }
        }
        return $target;
   };
   
   var letDefaultOn = [
       ':radio', ':checkbox'
   ];
   
   /**
    * Some elements (listed in letDefaultOn) need the usual action to be triggered, check that
    * @param {jQueryElement} $elt
    * @returns {boolean}
    */
   var shouldPreventDefault = function shouldPreventDefault($elt){
       return !($elt.is(letDefaultOn.join(',')));
   };
   
   /**
    * This callback is used either to perform actions on data-attr element
    * @callback dataAttrCallback
    * @params {jQueryElmement} $elt - the element that contains the data-attr
    * @params {jQueryElmement} $target - the element targeted by the data-attr
    */
       
   /**
    * The DataAttrHandler helps you to listen events from data attribute elements 
    * and bound a jQuery plugin behavior.
    * @exports core/dataattrhandler
    * 
    * @contructor
    * @param {string} attrName - the name of the attribute, ie. `toggle` for `data-toggle`
    * @param {Object} options - the handler options
    * @param {string} options.namespace - the jQuery plugin namespace
    * @param {jQueryElement|boolean} [options.container = false] - the root context to listen in
    * @param {string} [options.listenerEvent = 'click'] - the event to listen on
    * @param {boolean} [options.preventDefault = true] - to prevent the default event to be fired
    * @param {string} [options.inner] - a selector inside the element to bind the event to
    * @param {boolean} [options.useTarget = true] - if the content of the data-attr is as target or not
    */
   var DataAttrHandler = function construct(attrName, options){
        
        var self = this;
        this.options = _.defaults(options, defaults);
        var selector = '[data-' + attrName + ']';
        
        //check namespace
        if(!_.has(this.options, 'namespace') || !_.isString(this.options.namespace)){
            return $.error('The plugin data namespace option is required');
        }
        
        if(this.options.container && this.options.container.selector){
            selector = this.options.container.selector + ' ' + selector;
        }
        
        if(this.options.inner){
            selector += ' ' + this.options.inner;
        }
        
        //listen for events on selector (the listening works even though the DOM changes).
        $(document)
            .off(this.options.listenerEvent, selector)
            .on(this.options.listenerEvent, selector, function(e){
            
            var $elt = $(e.target);
            
            if($elt.is(selector)){
                var $target, $outer;
                
                if (self.options.inner){
                    $outer = $elt;
                    $elt = $elt.parents('[data-' + attrName + ']');
                }
                
                $target =  (self.options.useTarget === true) ? getTarget(attrName, $elt) : (self.options.inner ? $outer : undefined);

                //check if the plugin is already bound to the element
                if(!$elt.data(self.options.namespace)){
                    if(typeof self.createPlugin === 'function'){
                        self.createPlugin($elt, $target);
                    }

                    //for radio bind also the method call to the group...
                    if($elt.is(':radio') && $elt.attr('name')){
                        $(':radio[name="' + $elt.attr('name') +'"]').not($elt).on(self.options.listenerEvent, function(e){
                           if(typeof self.callPluginMethod === 'function'){
                                self.callPluginMethod($elt, $target);
                            }
                            if(shouldPreventDefault($elt)){
                                e.preventDefault();
                            }
                        });
                    }
                }

                //call the method bound to this event
                if(typeof self.callPluginMethod === 'function'){
                    self.callPluginMethod($elt, $target);
                } /*else {
                    //if there is no action to call we top listening (init plugin only)
                    $(document).off(self.options.listenerEvent, selector);
                }*/

                if(shouldPreventDefault($elt)){
                    e.preventDefault();
                }
            }
        });
    };
               
    /**
     * Add the callback used to initialise the plugin, 
     * the cb will be executed only once
     * @param {dataAttrCallback} cb - callback
     * @returns {DataAttrHandler} for chaining
     */
    DataAttrHandler.prototype.init = function(cb){
            
        this.createPlugin = cb;

        return this;
    };

    /**
     * Add the callback used to trigger an action each time the event is fired.
     * @param {dataAttrCallback} cb - callback
     * @returns {DataAttrHandler} for chaining
     */
    DataAttrHandler.prototype.trigger = function(cb){

        this.callPluginMethod = cb;

        return this;
    };

    //expose the handler
    return DataAttrHandler;
});

