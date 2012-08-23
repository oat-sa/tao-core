function onLoad(){
	
	// Always nextable because no inputs in this template.
	install.setNextable(true);
	
	// Backward management.
	$('#install_seq li a').each(function(){
		$(this).bind('click', onBackward);
	});
	
    // What happens if you click on 'Proceed next step'.
    $('form').bind('submit', function(event){
    	$('#submitForm').attr('disabled', 'disabled')
    		   			.removeClass('enabled')	
    		   			.addClass('disabled');
    	
    	if (install.isNextable()){
    		window.open('../');
    	}
    	
    	return false;
    });
}