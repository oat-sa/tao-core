function onLoad(){
	// Binding to API.
	install.onNextable = function(){
		$('#explOk, #explOptional').css('display', 'inline');
		
		$('#submitForm').removeClass('disabled')
						.addClass('enabled')
						.attr('disabled', false);
	}
	
	install.onUnnextable = function(){
		$('#submitForm').removeClass('enabled')
						.addClass('disabled')
						.attr('disabled', true);
						
		$('#explMandatory, #explOptional').css('display', 'inline');
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
    
    // Display default captions.
    $('#explMandatory, #explOptional').css('visibility', 'visible');
    
    // Feed install API help store.
    initHelp();

	// Launch the configuration check procedure only if we can talk JSON
	// with the server side.
	install.sync(function(status, data){
		if (data.value.json == true){
			// Save useful information.
			install.addData('root_url', data.value.rootURL);
			checkConfig();	
		}
		else {
			// We cannot exchange data with the server side.
			var msg = "PHP Extension 'json' could not be found on the server-side.";
			addReport('json', msg, false);
		}
	});
}

function checkConfig(){
	// Remove captions.
	$('#explOk, #explMandatory, #explOptional').css('display', 'none');
	
	// Empty existing reports.
	var $list = $('#forms_check_content ul');
	$list.find('tao-input').each(function(){
		install.unregister(this);
	});
	$list.empty();
	
	// set a spinner up.
	
	var $target = $('<li id="loadingCheck"><label>Checking configuration. Please wait...</label></li>');
	$('#forms_check_content ul').prepend($target);
	var spinner = new Spinner(getSpinnerOptions('small')).spin($target[0]);
	
	setTimeout(function(){ // Fake a small processing time... -> 500ms
		var data = [{type: "CheckPHPRuntime", value: {min: "5.3", max: "5.3.13", optional:false}},
					{type: "CheckPHPExtension", value: {name: "curl", optional: false}},
	                {type: "CheckPHPExtension", value: {name: "zip", optional: false}},
	                {type: "CheckPHPExtension", value: {name: "json", optional: false}},
	                {type: "CheckPHPExtension", value: {name: "spl", optional: false}},
	                {type: "CheckPHPExtension", value: {name: "dom", optional: false}},
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
	                {type: "CheckFileSystemComponent", value: {location: "filemanager/includes", rights: "rw", name: "fs_filemanager_includes", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "tao/views/export", rights: "rw", name: "fs_tao_views_export", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "tao/includes", rights: "rw", name: "fs_tao_includes", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "tao/data/cache", rights: "rw", name: "fs_tao_data_cache", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "tao/update/patches", rights: "rw", name: "fs_tao_update_patches", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "tao/update/bash", rights: "rw", name: "fs_tao_update_bash", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoItems/data", rights: "rw", name: "fs_taoItems_data", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoItems/views/export", rights: "rw", name: "fs_taoItems_views_export", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoItems/includes", rights: "rw", name: "fs_taoItems_includes", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoDelivery/compiled", rights: "rw", name: "fs_taoDelivery_compiled", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoDelivery/views/export", rights: "rw", name: "fs_taoDelivery_views_export", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoDelivery/includes", rights: "rw", name: "fs_taoDelivery_includes", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoGroups/views/export", rights: "rw", name: "fs_taoGroups_views_export", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoGroups/includes", rights: "rw", name: "fs_taoGroups_includes", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoSubjects/views/export", rights: "rw", name: "fs_taoSubjects_views_export", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoSubjects/includes", rights: "rw", name: "fs_taoSubjects_includes", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoTests/views/export", rights: "rw", name: "fs_taoTests_views_export", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoTests/includes", rights: "rw", name: "fs_taoTests_includes", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "taoResults/views/export", rights: "w", name: "fs_taoResults_views_export", optional: false}},
	                {type: "CheckFileSystemComponent", value: {location: "wfEngine/includes", rights: "w", name: "fs_wfEngine_includes", optional: false}},
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
		    			var kind = (r.value.optional == true) ? 'optional' : 'mandatory';
		    			mandatoryCount += (r.value.optional == true) ? 0 : 1;
		    			addReport(r.value.name, r.value.message, kind);
		    		}
		    	}
		    	
		    	if (mandatoryCount == 0){
		    		// Add a fake report that states the config is OK.
		    		addReport('tao_config_ok', 'Your system is ready to deploy TAO.', 'ok', true, true);
		    	}
		    	
		    	install.stateChange();
			}
	    });
	}, 500);
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
		$icon = $('<a id="' + name + '" class="ui-icon ui-icon-help"></a>');
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

function initHelp(){
	install.addHelp('curl', 'PHP supports libcurl, a library created by Daniel Stenberg, that allows you to connect and communicate to many different types of servers with many different types of protocols.');
	install.addHelp('zip', 'This extension enables you to transparently read or write ZIP compressed archives and the files inside them.');
	install.addHelp('json', 'This PHP extension implements the JavaScript Object Notation (JSON) data-interchange format. The decoding is handled by a parser based on the JSON_checker by Douglas Crockford.');
	install.addHelp('spl', 'SPL is a collection of interfaces and classes that are meant to solve standard problems.');
	install.addHelp('dom', 'The DOM extension allows you to operate on XML documents through the DOM API with PHP 5.');
	install.addHelp('mbstring', 'mbstring provides multibyte specific string functions that help you deal with multibyte encodings in PHP.');
	install.addHelp('svn', 'This extension implements PHP bindings for Subversion (SVN), a version control system, allowing PHP scripts to communicate with SVN repositories and working copies without direct command line calls to the svn executable.');
	install.addHelp('suhosin', 'Suhosin is an advanced protection system for PHP installations. It was designed to protect servers and users from known and unknown flaws in PHP applications and the PHP core.');
	install.addHelp('magic_quotes_gpc', 'Magic Quotes is a process that automagically escapes incoming data to the PHP script. It\'s preferred to code with magic quotes off and to instead escape the data at runtime, as needed.');
	install.addHelp('register_globals', 'When on, register_globals will inject your scripts with all sorts of variables, like request variables from HTML forms. This coupled with the fact that PHP doesn\'t require variable initialization means writing insecure code is that much easier.');
	install.addHelp('short_open_tag', 'Tells PHP whether the short form (<? ?>) of PHP\'s open tag should be allowed. If you want to use PHP in combination with XML, you can disable this option in order to use <?xml ?> inline. Otherwise, you can print it with PHP, for example: <?php echo \'<?xml version="1.0"?>\'; ?>. Also, if disabled, you must use the long form of the PHP open tag (<?php ?>).');
	install.addHelp('mod_rewrite', 'The mod_rewrite module uses a rule-based rewriting engine, based on a PCRE regular-expression parser, to rewrite requested URLs on the fly.');
	install.addHelp('database_drivers', 'Database drivers supported by the TAO platform are mysql and pgsql.');
}
