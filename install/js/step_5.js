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
    		// Install...
    		var inputs = {
    			'module_url': install.getData('host_name'),
    			'module_namespace': install.getData('host_name') + '/' + install.getData('instance_name') + '.rdf',
    			'module_lang': install.getData('default_language'),
    			'module_mode': install.getData('deployment_mode'),
    			'import_local': install.getData('sample_data'),
    			'user_login': install.getData('superuser_login'),
    			'user_pass1': install.getData('superuser_password1'),
    			'user_lastname': install.getData('superuser_lastname'),
    			'user_firstname': install.getData('superuser_firstname'),
    			'user_email': install.getData('superuser_email'),
    			'db_host': install.getData('database_host'),
    			'db_user': install.getData('database_user'),
    			'db_pass': install.getData('database_password'),
    			'db_driver': install.getData('database_driver'),
    			'db_name': install.getData('database_name')
    		};
    		
    		$('#deployment').css('visibility', 'visible');
    		install.install(inputs, function(status, data){
				var success = false;
				
				if (typeof(data) != 'undefined'){
					// We received an HTTP 200 code...
					if (data.value.status = 'valid'){
						// This a success. In any other case, we fail gracefuly below.
						success = true;
					}
				}
				
				if (success == true){
					$('#deployment').css('visibility', 'hidden');
					if (install.getData('deployment_mode') == 'debug'){
						// In debug mode, we still have access to the /tao/install
						// folder. We can thus display a new template.
						install.setTemplate('step_6');	
					}
					else{
						// We are in 'production' deployment mode. Thus, the /tao/install
						// cannot be accessed anymore because of more restrictive .htaccess
						// files created at installation time on the server-side.
						// Thus, we go directly to the TAO portal at the root of the install
						// directory.
						install.redirect('../../');
					}
				}
    		});
    	}
    	
    	return false;
    });
    
    // Spin
    var spinner = new Spinner(getSpinnerOptions('small')).spin($('#deployment')[0]);
}