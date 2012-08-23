function onLoad(){
	
	install.onNextable = function(){
		$('#submitForm').removeClass('disabled')
						.addClass('enabled')
						.attr('disabled', false);
		$('#submitForm').attr('value', 'Proceed next step');
	};
	
	install.onUnnextable = function(){
		$('#submitForm').removeClass('enabled')
						.addClass('disabled')
						.attr('disabled', true);
		$('#submitForm').attr('value', 'Awaiting mandatory information');
	}
	
	$('form').bind('submit', function(){
		if (install.isNextable()){
			install.setTemplate('step_5');
		}
		
		return false;
	});
	
	// Backward management.
	$('#install_seq li a').each(function(){
		$(this).bind('click', onBackward);
	});
	
	// Initialize 'tao-input's.
	
	var firstValues = {};
	$('.tao-input').each(function(){
		$this = $(this);
		// Provide a data getter/setter for API handshake.
		install.getDataGetter(this);
		install.getDataSetter(this);
		
		// Get labelifed values from raw DOM for further comparison.
		if ($this.prop('tagName').toLowerCase() == 'input' && $this.attr('type') == 'text'){
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
			
			case 'superuser_firstname':
				install.getValidator(this, {dataType: 'string', min: 0, max: 30, mandatory: false});
			break;
			
			case 'superuser_lastname':
				install.getValidator(this, {dataType: 'string', min:0, max: 30, mandatory: false});
			break;
			
			case 'superuser_email':
				install.getValidator(this, {dataType: 'string', min: 0, max: 30, mandatory: false});
			break;
			
			case 'superuser_login':
				install.getValidator(this, {dataType: 'string', min: 1, max: 30});
			break;
			
			case 'superuser_password1':
				install.getValidator(this, {dataType: 'string', min: 5});
			break;
			
			case 'superuser_password2':
				install.getValidator(this, {dataType: 'string', min: 5, sameAs: 'superuser_password1'});
			break;
			
			default:
				install.getValidator(this);
			break;
		}
		
		install.register(this);
		
		// When data is changed, tell the Install API.
		$(".tao-input[type=text], .tao-input[type=password]").bind('keyup click change paste blur', function(event){
			install.stateChange();
		});
		
	});
	
	// Populate form elements from API's data store.
	// (do not forget to restyle)
	$(install.populate()).each(function(){
		$(this).removeClass('helpTaoInputLabel');
	});
}
