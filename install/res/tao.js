$(document).ready(function () {
	updateForm();
	
	$('#module_name, #module_host, #db_user, #db_pass').bind('keyup change', function () {
		updateForm();
	});
	
	$('.expander').click(foldUnfold)
				  .hover(function() { $(this).css('cursor', 'pointer'); });
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
		$('#db_test').click(function(e){
			e.preventDefault();
			
			$('#db_test').parent().find('span.status').remove();
			$('#db_test').after('<span class="status"><img src="img/throbber.gif" alt="testing" /></span>');

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
	if($.trim($('#db_user').val()) == ''){
		$('#db_test').attr('disabled', true);
		return false;
	}
	$('#db_test').attr('disabled', false);
	return true;
};


changeNamespace = function () {
	var namespace = buildNamespace();
	$('#module_namespace_lbl').text(namespace);
	$('#module_namespace').val(namespace);
};

getModuleName = function () {
	return $('#module_name').val();
};

foldUnfold = function () {
	$configList = $(this).parent().next();
	$configListHeader = $(this).parent();
	
	if($configList.css('display') == 'none') {
		$configList.css('display', 'block');
		$configListHeader.removeClass('ui-corner-bottom')
		$(this).attr('src', 'img/fold.png');
	} else {
		$configList.css('display', 'none');
		$(this).attr('src', 'img/unfold.png');
		$configListHeader.addClass('ui-corner-bottom');
	}
}