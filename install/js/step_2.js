function onLoad(){
	
	// Set up the list of available languages.
	var availableLanguages = install.getData('available_languages');
	if (availableLanguages != null){
		var $defaultLanguageElement = $('#default_language').empty();
		
		for (var i in availableLanguages){
			$defaultLanguageElement.append('<option value="' + i + '">' + availableLanguages[i] + '</option>');
		}
	}
	
	install.onNextable = function(){
		$('#submitForm').removeClass('disabled')
						.addClass('enabled')
						.attr('disabled', false);
		$('#submitForm').attr('value', 'Proceed next step');
	}
	
	install.onUnnextable = function(){
		$('#submitForm').removeClass('enabled')
						.addClass('disabled')
						.attr('disabled', true);
		$('#submitForm').attr('value', 'Awaiting mandatory information');
	}
	
	$('form').bind('submit', function(){
		if (install.isNextable()){
			install.setTemplate('step_3');
		}
		
		return false;
	});
	
	var firstValues = {};
	$('.tao-input').each(function(){
		$this = $(this);
		// Provide a data getter/setter for API handshake.
		install.getDataGetter(this);
		install.getDataSetter(this);
		
		// Get labelifed values from raw DOM.
		if ($this.prop('tagName').toLowerCase() == 'input'){
			firstValues[this.id] = this.getData();
		}
	});
	
	// Backward management.
	$('#install_seq li a').each(function(){
		$(this).bind('click', onBackward);
	});
	
	// Register inputs.
	$('.tao-input').each(function(){
		if (typeof(firstValues[this.id]) != 'undefined'){
			this.firstValue = firstValues[this.id];
		}
		
		switch (this.id){
			
			case 'host_name':
				install.getValidator(this, {dataType: 'host'});
				validify(this);
			break;
			
			case 'instance_name':
				install.getValidator(this);
				validify(this);
			break;
			
			default:
				install.getValidator(this);
			break;
		}

		install.register(this);
		
		// When data is changed, tell the Install API.
		$(this).bind('keyup click change paste blur', function(event){
			install.stateChange();
		});
	});
	
	// Populate form elements from API's data store.
	// (do not forget to restyle)
	$(install.populate()).each(function(){
		$(this).removeClass('helpTaoInputLabel');
	});
	
	// If after population, there is no value for host_name,
	// provide a default one if possible.
	if (install.getData('root_url') != null && install.getData('host_name') == null){
		$('#host_name').removeClass('helpTaoInputLabel')[0].setData(install.getData('root_url'));
	}
	
	initHelp();
	
	install.stateChange();
}

function initHelp(){
	install.addHelp('hlp_host_name', "This field must contain the entire URL (Uniform Resource Locator) that locates your TAO platform. The default value should work in any case.");
	install.addHelp('hlp_instance_name', "The instance name will be allocated to this installation to differentiate it from other accross your network.");
	install.addHelp('hlp_default_language', "The default language that will be used by TAO to display texts in the graphical user interface.");
	install.addHelp('hlp_deployment_mode', "The <em>production</em> deployment mode provide you a secure installation dedicated to production. On the other hand, the <em>development</em> mode is dedicated to developers where various debug modes are enabled.");
}