function onLoad(){
	// Binding to API.
	install.onNextable = function(){
		displayLegend();
		
		$('#submitForm').removeClass('disabled')
						.addClass('enabled')
						.attr('disabled', false);
	}
	
	install.onUnnextable = function(){
		$('#submitForm').removeClass('enabled')
						.addClass('disabled')
						.attr('disabled', true);
	}
	
	// Binding to DOM.
	// What happens if you click 'Reload test'.
    $('#redoForm').bind('click', function(event){
    	checkConfig();
    });
    
    // What happens if you click on 'Proceed next step'.
    $('form').bind('submit', function(event){
    	if (install.isNextable()){
    		install.setTemplate('step_2');	
    	}
    	
    	return false;
    });
    
    // Feed install API help store.
    initHelp();

	checkConfig();
}

function checkConfig(){
	// Launch the configuration check procedure only if we can talk JSON
	// with the server side.
	install.sync(function(status, data){
		if (data.value.json == true){
			// Save useful information.
			install.addData('root_url', data.value.rootURL);
			install.addData('available_drivers', data.value.availableDrivers);
			install.addData('available_languages', data.value.availableLanguages);
			
			// Empty existing reports.
			var $list = $('#forms_check_content ul');
			$list.empty();
			install.clearRegisteredElements();
			
			// set a spinner up.
			
			var $target = $('<li id="loadingCheck"><label>Checking configuration. Please wait...</label></li>');
			$('#forms_check_content ul').prepend($target);
			var spinner = new Spinner(getSpinnerOptions('small')).spin($target[0]);
			
			setTimeout(function(){ // Fake a small processing time... -> 500ms
				var data = [{type: "CheckPHPRuntime", value: {min: "5.3", max: "5.3.18", optional:false}},
							{type: "CheckPHPExtension", value: {name: "PDO", optional: false}},
							{type: "CheckPHPExtension", value: {name: "curl", optional: false}},
			                {type: "CheckPHPExtension", value: {name: "zip", optional: false}},
			                {type: "CheckPHPExtension", value: {name: "json", optional: false}},
			                {type: "CheckPHPExtension", value: {name: "spl", optional: false}},
			                {type: "CheckPHPExtension", value: {name: "dom", optional: false}},
			                {type: "CheckPHPExtension", value: {name: "tidy", optional: false}},
			                {type: "CheckPHPExtension", value: {name: "mbstring", optional: false}},
			                {type: "CheckPHPExtension", value: {name: "svn", optional: true}},
			                {type: "CheckPHPExtension", value: {name: "suhosin", optional: true}},
			                {type: "CheckPHPINIValue", value: {name: "magic_quotes_gpc", value: "0", optional: false}},
			                {type: "CheckPHPINIValue", value: {name: "short_open_tag", value: "1", optional: false}},
			                {type: "CheckPHPINIValue", value: {name: "register_globals", value: "0", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: ".", rights: "rw", name: "fs_root", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "generis/data/cache", rights: "rw", name: "fs_generis_data_cache", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "generis/data/versioning", rights: "rw", name: "fs_generis_data_versionning", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "generis/common", rights: "rw", name: "fs_generis_common", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "generis/common/conf", rights: "rw", name: "fs_generis_common_conf", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "generis/common/conf/default", rights: "r", name: "fs_generis_common_conf_default", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "generis/common/conf/sample", rights: "r", name: "fs_generis_common_conf_sample", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "filemanager/views/data", rights: "rw", name: "fs_filemanager_views_data", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "filemanager/includes", rights: "r", name: "fs_filemanager_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "tao/views/export", rights: "rw", name: "fs_tao_views_export", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "tao/includes", rights: "r", name: "fs_tao_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "tao/data/cache", rights: "rw", name: "fs_tao_data_cache", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "tao/update/patches", rights: "rw", name: "fs_tao_update_patches", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "tao/update/bash", rights: "r", name: "fs_tao_update_bash", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "tao/locales", rights: "r", name: "fs_tao_locales", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoItems/data", rights: "rw", name: "fs_taoItems_data", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoItems/includes", rights: "r", name: "fs_taoItems_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoItems/views/runtime", rights: "rw", name: "fs_taoItems_views_runtime", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoDelivery/compiled", rights: "rw", name: "fs_taoDelivery_compiled", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoDelivery/includes", rights: "r", name: "fs_taoDelivery_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoGroups/includes", rights: "r", name: "fs_taoGroups_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoSubjects/includes", rights: "r", name: "fs_taoSubjects_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoTests/includes", rights: "r", name: "fs_taoTests_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "taoResults/includes", rights: "r", name: "fs_taoResults_includes", optional: false}},
			                {type: "CheckFileSystemComponent", value: {location: "wfEngine/includes", rights: "r", name: "fs_wfEngine_includes", optional: false}},
			                {type: "CheckCustom", value: {name: "mod_rewrite", extension: "tao", optional: false}},
			                {type: "CheckCustom", value: {name: "database_drivers", extension: "tao", optional: false}}];
			    
				install.checkConfiguration(data, function(status, data){
					if (status == 200){
						var $list = $('#forms_check_content ul');
						
						// Stop spinner.
						spinner.stop();
						$list.empty();
		
						// Append new reports.
						var mandatoryCount = 0;
				    	for (report in data.value){
				    		var r = data.value[report];
				    		if (r.value.status != 'valid'){
				    			var optional = r.value.optional;
				    			var kind = (optional == true) ? 'optional' : 'mandatory';
				    			var message;
				    			mandatoryCount += (r.value.optional == true) ? 0 : 1;
				    			
				    			switch (r.type){
				    				case 'PHPExtensionReport':
				    					var name = r.value.name;
				    					
				    					if (optional == true){
				    						message = "PHP Extension '" + name + "' is not loaded on your web server but is optional to run TAO.";
				    					}
				    					else{
				    						message = "PHP Extension '" + name + "' is not loaded on your web server but is mandatory to run TAO.";
				    					}
				    			break;
				    				
				    				case 'PHPINIValueReport':
				    					var expectedValue = r.value.expectedValue;
				    					var value = r.value.value;
				    					var name = r.value.name;
				    					
				    					if (optional == true){
				    						message = "PHP INI value '" + name + "' on your web server has not the expected value but is optional. Current value is '" + value + "' but should be '" + expectedValue + "'.";
				    					}
				    					else{
				    						message = "PHP INI value '" + name + "' on your web server has not the expected value but is mandatory. Current value is '" + value + "' but should be '" + expectedValue + "'.";
				    					}
				    				break;
				    				
				    				case 'FileSystemComponentReport':
				    					var expectedRights = r.value.expectedRights;
				    					var isReadable = r.value.isReadable;
				    					var isWritable = r.value.isWritable;
				    					var isExecutable = r.value.isExecutable;
				    					var location = r.value.location;
				    					
				    					var expectedRightsMessage = getExpectedRightsAsString(r.value.expectedRights);
				    					var currentRightsMessage = getCurrentRightsAsString(r);
				    					var nature = (r.value.isFile == true) ? 'file' : 'directory';
				    					
				    					message = "The " + nature + " located at '" + location + "' on your web server should be " + expectedRightsMessage + " but is currently " + currentRightsMessage + ' only.';
				    				break;
				    				
				    				default:
				    					message = r.value.message;
				    				break;
				    			}
				    			
				    			addReport(r.value.name, message, kind);
				    		}
				    	}
				    	
				    	if (mandatoryCount == 0){
				    		addReport('ready', 'Your system is compliant with the TAO requirements', 'ok', false, true);
				    	}
				    	
				    	install.stateChange();
					}
			    });
			}, 500);
			
		}
		else {
			// We cannot exchange data with the server side.
			var msg = "PHP Extension 'json' could not be found on the server-side.";
			addReport('json', msg, false);
		}
	});
}

function addReport(name, message, kind, prepend, noHelp){
	prepend = (typeof(prepend) != 'undefined') ? prepend : false;
	noHelp = (typeof(noHelp) != 'undefined') ? noHelp : false;
	var $list = $('#forms_check_content ul');
	var $input = $('<li/>').addClass('tao-input');
	$input.attr('id', 'input_' + name);
	$input.addClass('tao-' + kind);
	$label = $('<label/>').text(message);
	$input[0].isValid = function(){ return $(this).hasClass('tao-optional') || $(this).hasClass('tao-ok'); };
	$input.append($label);
	
	if (!noHelp){
		$help = $('<div title="learn more on this topic" class="icons ui-state-default ui-corner-all"></div>');
		$icon = $('<a id="hlp_' + name + '" class="ui-icon ui-icon-help"></a>');
		$icon.bind('click', function(event){
			displayTaoHelp(event);
		});
		$help.append($icon);
		$input.append($help);
	}
	
	install.register($input[0]);
	
	if (prepend == false){
		$list.append($input);	
	}
	else{
		$list.prepend($input);
	}
}

function displayLegend(){
	$('#formComment').empty().append('<p id="explMandatory">Mandatory requirement</p>')
							 .append('<p id="explOptional">Optional requirement</p>');
}

function initHelp(){
	install.addHelp('hlp_curl', 'PHP supports libcurl, a library created by Daniel Stenberg, that allows you to connect and communicate to many different types of servers with many different types of protocols. It is used by TAO to request resource files on the World Wide Web.');
	install.addHelp('hlp_zip', 'This extension enables you to transparently read or write ZIP compressed archives and the files inside them. TAO uses this extension to read/write import/export packages');
	install.addHelp('hlp_json', 'This PHP extension implements the JavaScript Object Notation (JSON) data-interchange format. It is used by various TAO extensions to enable web browsers to exchange data with the web server.');
	install.addHelp('hlp_spl', 'SPL is a collection of interfaces and classes that are meant to solve standard problems. TAO require these standard classes to run correctly.');
	install.addHelp('hlp_dom', 'The DOM extension allows you to operate on XML documents through the DOM API with PHP 5. TAO heavily uses XML to describe contents.');
	install.addHelp('hlp_mbstring', 'mbstring provides multibyte specific string functions that help you deal with multibyte encodings in PHP. As a cross-cultural application, TAO uses multibyte string to provide various symbols.');
	install.addHelp('hlp_svn', 'This extension implements PHP bindings for Subversion (SVN), a version control system, allowing PHP scripts to communicate with SVN repositories and working copies without direct command line calls to the svn executable. TAO uses SVN to version files. This feature is optional and for advanced users.');
	install.addHelp('hlp_suhosin', 'Suhosin is an advanced protection system for PHP installations. It was designed to protect servers and users from known and unknown flaws in PHP applications and the PHP core. The TAO team recommends the use of this extension for a safer PHP experience. Be sure that INI values for <em>suhosin.post.max_name_length</em> and <em>suhosin.request.max_varname_length</em> are set to <em>128.</em>');
	install.addHelp('hlp_magic_quotes_gpc', 'Magic Quotes is a process that automagically escapes incoming data to the PHP script. The value expected by TAO for this INI parameter is <em>Off</em>.');
	install.addHelp('hlp_register_globals', 'When on, register_globals will inject your scripts with all sorts of variables, like request variables from HTML forms. This coupled with the fact that PHP doesn\'t require variable initialization means writing insecure code is that much easier. For obvious security reasons, TAO requires this parameter to be set to <em>Off</em>.');
	install.addHelp('hlp_short_open_tag', 'Tells PHP whether the short form (<? ?>) of PHP\'s open tag should be allowed. The value of the <em>short_open_tag</em> INI parameter must be set to <em>On</em>.');
	install.addHelp('hlp_mod_rewrite', 'The mod_rewrite module uses a rule-based rewriting engine, based on a PCRE regular-expression parser, to rewrite requested URLs on the fly. It must be enabled to make TAO running properly.');
	install.addHelp('hlp_database_drivers', 'Database drivers supported by the TAO platform are MySQL and PostgreSQL.');
	install.addHelp('hlp_fs_root', 'The root directory of your installation must be readable and writable by the user running your web server.');
	install.addHelp('hlp_fs_generis_data_cache', "The '/generis/data/cache' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_generis_data_versionning', "The '/generis/data/versionning' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_generis_common', "The '/generis/common' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_generis_common_conf', "The '/generis/common/conf' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_generis_common_conf_default', "The '/generis/common/conf/default' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_generis_common_conf_sample', "The '/generis/common/conf/sample' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_filemanager_views_data', "The 'filemanager/views/data' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_filemanager_includes', "The 'filemanager/includes/' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_tao_views_export', "The '/tao/views/export' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_tao_data_cache', "The 'tao/data/cache' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_tao_update_patches', "The 'tao/update/patches' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_tao_update_bash', "The 'tao/update/bash' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_tao_locales', "The 'tao/locales' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_taoItems_data', "The 'taoItems/data' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_taoItems_includes', "The 'taoItems/includes' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_taoItems_views_export', "The 'taoItems/views/export' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_taoDelivery_compiled', "The 'taoDelivery/compiled' directory of your installation must be readable and writable by the user running your web server.");
	install.addHelp('hlp_fs_taoDelivery_includes', "The 'taoDelivery/includes' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_taoGroups_includes', "The 'taoGroups/includes' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_taoSubjects_includes', "The 'taoSubjects/includes' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_taoTests_includes', "The 'taoTests/includes' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_taoResults_includes', "The 'taoResults/includes' directory of your installation must be readable by the user running your web server.");
	install.addHelp('hlp_fs_wfEngine_includes', "The 'wfEngine/includes' directory of your installation must be readable by the user running your web server.");
}

function getExpectedRightsAsString(expectedRights){
	var tokens = [];
	
	for (var i = 0; i < expectedRights.length; i++){
		if (expectedRights.charAt(i) == 'r'){
			tokens.push('readable');
		}
		else if (expectedRights.charAt(i) == 'w'){
			tokens.push('writable');
		}
		else{
			tokens.push('executable');
		}
	}
	
	return tokens.join(', ');
}

function getCurrentRightsAsString(report){
	
	var tokens = [];
	
	if (report.value.isWritable == true){
		tokens.push('writable');
	}
	else if (report.value.isReadable == true){
		tokens.push('readable');
	}
	else{
		tokens.push('executable');
	}
	
	return tokens.join(', ');
}