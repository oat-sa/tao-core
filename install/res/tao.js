$(document).ready(function () {
	updateForm();
	
	$('#module_name, #module_host, #db_user, #db_pass').bind('keyup change', function () {
		updateForm();
	});
});

updateForm = function () {
	changeNamespace();
	changeDatabaseName();
	if(checkDatabaseCredentials()){
		testDatabaseConnection();
	}
};

buildNamespace = function () {
	var moduleName = getModuleName();
	var moduleHost = $('#module_host').val();
	return 'http://' + moduleHost + '/' + moduleName + '.rdf';
};

changeDatabaseName = function () {
	$('#db_name').val(getModuleName());
	$('#db_name_lbl').text(getModuleName());
};

testDatabaseConnection = function(){
	if($('#db_test').data('testable') != true){
		$('#db_test').click(function(){
			var credentials = $('input,select', $('#db')).serializeArray();
			$.post('testDb.php', credentials, function(response){
				$('#db_test').parent().find('span.status').remove();
				if(response.connected == true){
					$('#db_test').after('<span class="status"><img src="img/accept.png" alt="ok" />Connected successfully</span>');
				}
				else{
					$('#db_test').after('<span class="status"><img src="img/exclamation.png" alt="!" />Connection has failed</span>');
				}
			}, 'json');
		}).data('testable', true);
	}
};

checkDatabaseCredentials = function(){
	var credentials = $('input', $('#db')).serializeArray();
	for(i in credentials){
		if(credentials[i]['name'] == 'db_user' && !credentials[i]['value']){
			$('#db_test').attr('disabled', true);
			return false;
			break;
		}
		if(credentials[i]['name'] == 'db_pass' && !credentials[i]['value']){
			$('#db_test').attr('disabled', true);
			return false;
			break;
		}
	}
	$('#db_test').attr('disabled', false);
	return true;
};


changeNamespace = function () {
	$('#module_namespace').text(buildNamespace());
};

getModuleName = function () {
	return $('#module_name').val();
};

