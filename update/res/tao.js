/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
$(document).ready(function () {
	updateForm();
	
	$('#module_name, #module_host, #db_user, #db_pass').bind('keyup change input', function () {
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