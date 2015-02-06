define(['jquery', 'i18n', 'helpers', 'lock', 'layout/section', 'ui/feedback'], 
function($, __, helpers, Lock, sectionApi, feedback){
    'use strict';
	
    return {
        start : function(){

		    $("#release").click(function(e) {
		    	
		        e.preventDefault();
		        
		        var uri = $(this).data('id');
		        var dest = $(this).data('url');
		        
		        var successCallBack = function() {
		            sectionApi.current().loadContentBlock(dest);
		        };
		        
		        var errorBack = function() {
		            feedback().error(__('Unable to release the lock'));
		        };
		
		        new Lock(uri).release(successCallBack, errorBack);
		    });
        }
    }
});