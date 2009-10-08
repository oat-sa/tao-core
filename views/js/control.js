/**
 * initialize the naviguation into the ui components 
 */
function uiNaviguate(){
	
	//tab navigation
	$('a.nav', $('.ui-tabs-panel')).click(function() {
		$('.ui-tabs-panel').load(_href(this.href));
		return false;
	});
	
	//load the forms into the form container
	$('a.form-nav', $('.ui-tabs-panel')).click(function() {
		 $("#form-container").load(_href(this.href));
		 return false;
	});
	
	//submit the form by ajax into the form container
	$("form").submit(function(){
		try{
			$("#form-container").load(
				_href($(this).attr('action')),
				$(this).serializeArray()
			);
		}
		catch(exp){}
		return false;
	});
}

function _href(ref){
	return  (ref.indexOf('?') > -1) ? ref + '&nc='+new Date().getTime() : ref + '?nc='+new Date().getTime(); 
}


var $tabs = null;
$(function(){
	
	//create tabs
	$tabs = $('#tabs').tabs({load: uiNaviguate});
	/*$tabs = $('#tabs').tabs({
	    load: function(event, ui) {
	        $('a.nav', ui.panel).click(function() {
	            $(ui.panel).load(this.href);
	            return false;
	        });
	    }
	});*/
	
	
	
		
	

});