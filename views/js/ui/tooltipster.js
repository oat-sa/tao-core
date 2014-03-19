define(['jquery', 'core/dataattrhandler', 'tooltipster'], function($, DataAttrHandler){
    'use strict';
    
    /**
    * Look up for tooltips and initialize them
    * 
    * @public
    * @param {jQueryElement} $container - the root context to lookup inside
    */
    return function lookupSelecter($container){
       
        $('[data-tooltip]', $container).each(function(){
            var $elt = $(this);
            var $target = DataAttrHandler.getTarget('tooltip', $elt);
            $elt.tooltipster({
                theme: 'tao-tooltip',
                content: $target,          
                contentAsHTML: $target.children().length > 0,
                delay: 350,
                trigger: 'hover'
            });
        });
    };
});
