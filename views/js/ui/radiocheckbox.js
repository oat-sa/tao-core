define(['jquery'], function($){
    'use strict';
    
   /**
    * Manage radios and checkbox elements
    * 
    * @public
    * @param {jQueryElement} $container - the root context to lookup inside
    */
    return function lookupSelecter($container){
    
        //toggle radio/checkboes inside a pseudo label        
        $('.pseudo-label-box', $container).on('click', function (e) {
            e.preventDefault();
            var $box = $(this);
            var $radios =  $box.find('input:radio').not('[disabled]').not('.disabled');
            var $checkboxes = $box.find('input:checkbox').not('[disabled]').not('.disabled');
           
            if($radios.length){
                $radios.not(':checked').prop('checked', true);
                $radios.trigger('change');
            }
            if($checkboxes.length){
               $checkboxes.prop('checked', !$checkboxes.prop('checked')); 
               $checkboxes.trigger('change');
            }
        });
    };
});
