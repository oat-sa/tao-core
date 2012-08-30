function onLoad(){
	
	// If we have available drivers, we set up
	// the list of db drivers.
	var availableDrivers = install.getData('available_drivers');
	
	if (availableDrivers != null && availableDrivers.length > 0){
		$databaseDriverElement = $('#database_driver');
		$databaseDriverElement.empty();
		
		for (var i in availableDrivers){
			$databaseDriverElement.append('<option value="' + availableDrivers[i] + '">' + getDriverLabel(availableDrivers[i]) + '</option>');
		}
	}
	
	install.onNextable = function(){
		$('#submitForm').removeClass('disable')
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
		// set a spinner up.
		$database = $('#database');
		$database.css('visibility', 'visible');
		var spinner = new Spinner(getSpinnerOptions('small')).spin($database[0]);
		
		setTimeout(function(){ // Fake additional delay for user - 500ms.
			var host = install.getData('database_host');
			var user = install.getData('database_user');
			var password = install.getData('database_password');
			var driver = install.getData('database_driver');
			var database = install.getData('database_name');
			var overwrite = install.getData('database_overwrite');
			
			var check = {host: host,
						 user: user,
						 password: password,
						 driver: driver,
						 database: database,
						 overwrite: overwrite,
						 optional: false};
			
			install.checkDatabaseConnection(check, function(status, data){
				$database.css('visibility', 'hidden');
				spinner.stop();

				if (data.value.status == 'valid'){
					// Great! We could connect with the provided data.
					if (install.isNextable()){
						install.setTemplate('step_4');
					}
				}
				else if (data.value.status == 'invalid-noconnection'){
					// No connection established.
					var dsn = driver + '://' + user + '@' + host;
					displayTaoError('Unable to connect to Relational Database Management System ' + dsn + '.');
				}
				else if (data.value.status == 'invalid-overwrite'){
					displayTaoError("A database with name '" + database + "' already exists. Check the corresponding check box to overwrite it.");
				}
				else if (data.value.status == 'invalid-nodriver'){
					displayTaoError("The database driver '" + driver + "' that should connect to your Relation Database Management System is not available on the server-side.");
				}
			});
		}, 500);
		
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
		
		// Get labelifed values from raw DOM.
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
			
			case 'database_host':
				install.getValidator(this, {dataType: 'dbhost'});
				validify(this);
			break;
			
			case 'database_user':
				install.getValidator(this, {dataType: 'string', min: 3, max: 30});
				validify(this);
			break;
			
			case 'database_password':
				// min = 0 to allow common root/[empty string] credential types.
				install.getValidator(this, {dataType: 'string', min: 0, max: 30, mandatory: false});
				validifyNotMandatory(this);
			break;
			
			case 'database_name':
				install.getValidator(this, {dataType: 'string', min: 1, max: 63});
				validify(this);
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
		
		$(".tao-input[type=checkbox]").bind("change", function(event){
			install.stateChange();
		});
		
		
	});
	
	// Populate form elements from API's data store.
	// (do not forget to restyle)
	$(install.populate()).each(function(){
		$(this).removeClass('helpTaoInputLabel');
	});
}

function getDriverLabel(driverId){
	switch(driverId){
		case 'mysql':
			return 'MySQL';
		break;
		
		case 'pgsql':
			return 'PostgreSQL';
		break;
		
		default:
			throw 'Unknown database driver (' + driverId + ').';
		break;
	}
}