define(['select2'], function(){
    'use strict';
    
    /**
    * Look up for element in the container that will be used as select2 widgets
    * 
    * @public
    * @example define(['ui/selecter'], function(selecter){ selecter($('rootContainer')); });
    * @param {jQueryElement} $container - the root context to lookup inside
    */
    return function lookupSelecter($container){
        $('.select2', $container).each(function() {
            var $elt = $(this);
            var hasSearch = false === $elt.data('has-search'); 
            var options = {
                width: '100%'
            };
            if(hasSearch) {
                options.minimumResultsForSearch = -1;
            }

            $elt.select2(options);
      });
    };
});