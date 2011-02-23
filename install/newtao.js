$(document).ready(function () {
	updateForm();
	
	$('#module_name, #module_host').bind('keyup change', function () {
		updateForm();
	});
});

updateForm = function () {
	changeNamespace();
	changeDatabaseName();
}

buildNamespace = function () {
	var moduleName = getModuleName();
	var moduleHost = $('#module_host').val();
	return 'http://' + moduleHost + '/' + moduleName + '.rdf';
}

changeDatabaseName = function () {
	$('#db_name').val(getModuleName());
	$('#db_name_lbl').text(getModuleName());
}

changeNamespace = function () {
	$('#module_namespace').text(buildNamespace());
}

getModuleName = function () {
	return $('#module_name').val();
}