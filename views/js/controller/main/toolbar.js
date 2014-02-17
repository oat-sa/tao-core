define(['jquery'], function($){
    'use strict';
    
    var $toolbarContainer = $('#main-menu > .right-menu');
    
    var taoToolbar = {
        setUp : function(){
            $toolbarContainer.find('[data-action]').click(function(e){
                e.preventDefault();
                
                var $elt = $(this);
                var action = $elt.data('action');
                require([action], function(controller){
                    if(controller &&  typeof controller.start === 'function'){
                        controller.start();
                    }
                });
                
            });
        }
    };
    
    return taoToolbar;
});
