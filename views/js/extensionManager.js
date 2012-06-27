var ext_installed = [];
var toInstall = [];
var indexCurrentToInstall = -1;
var percentByExt = 0;
var installError = 0;

$(function(){
	$('#installProgress').hide();

	//Detect wich extension is already installed
	$('#installedExtension .ext-id').each(function() {
		ext = $(this).text();
		ext_installed.push(ext);
		$('.ext-id.ext-'+ext).addClass('installed');
	});

	$('#availlableExtension tr').click(function() {
		if ($('input:checked', $(this)).length) $('input:checked', $(this)).removeAttr('checked');
		else $('input', $(this)).attr('checked', 'checked');
	});
	$('#availlableExtension tr input').click(function(event){
		event.stopPropagation();
	});

	$('#availlableExtension form').submit(function(event) {
		//Prepare the list of extension to install in the order of dependency
		toInstall = [];
		$('#availlableExtension input:checked').each(function() {
			ext = $(this).attr('name').split('_')[1];
			deps = getDependencies(ext);
			if (deps.length) toInstall = toInstall.concat(deps);
			toInstall.push(ext);
		});
		toInstall = getUnique(toInstall);
		if (toInstall.length == 0) {
			alert(__('Nothing to install !'));
			return false;
		}
		//Let's go
		percentByExt = 100 / toInstall.length;

		//Show the dialog with the result
		$('#installProgress p.status').text(__('%s extensions to install').replace('%s', toInstall.length));
		$('#installProgress .bar').width(0);
		$('#installProgress .console').empty();
		progressConsole('Extensions to install : '+toInstall.join(', '));
		$('#installProgress').dialog({
			modal: true,
			width: 400,
			height: 300,
			buttons: [
				{
					text: __('No'),
					click: function() {
						$(this).dialog('close');
					}
				},
				{
					text: __('Yes'),
					click: function() {
						//Run the install one by one
						progressConsole('Prepare installation...');
						$('.ui-dialog-buttonpane').remove();
						installError = 0;
						indexCurrentToInstall = 0;
						installNextExtension();
					}
				}
			]
		});
		event.preventDefault();
	});
});

function getDependencies(extension) {
	var dependencies = [];
	$('#'+extension+' .dependencies li:not(.installed)').each(function() {
		var ext = $(this).attr('rel');
		var deps = getDependencies(ext);
		deps.push(ext);
		dependencies = dependencies.concat(deps);
	});
	return dependencies;
}

//Give an array with unique values
function getUnique(orig){
	var a = [];
	for (var i = 0; i < orig.length; i++) {
		if ($.inArray(orig[i], a) < 0) a.push(orig[i]);
	}
	return a;
}

function progressConsole(msg) {
	$('#installProgress .console').append('<p>'+msg+'</p>');
	//$('#installProgress .console').animate({ scrollTop: $('#installProgress .console').attr("scrollHeight") }, 500);
	$('#installProgress .console').attr({scrollTop: $('#installProgress .console').attr("scrollHeight")});
}

function installNextExtension() {
	ext = toInstall[indexCurrentToInstall];
	$('#installProgress p.status').text(__('Installing %s...').replace('%s', ext));
	progressConsole(__('Installing %s...').replace('%s', ext));
	$.ajax({
		type: "POST",
		url: root_url + "/tao/ExtensionsManager/install",
		data: 'id='+ext,
		dataType: 'json',
		success: function(data) {
			loaded();
			if (data.success) {
				progressConsole('Installation of '+ext+' success');
				$('tr#'+ext).slideUp('normal', function() {
					var $tr = $('<tr></tr>').appendTo($('#installedExtension tbody')).hide();
					var $orig = $('tr#'+ext+' td');
					$tr.append('<td>'+$($orig[1]).text()+'</td>');
					$tr.append('<td>'+$($orig[2]).text()+'</td>');
					$tr.append('<td>'+$($orig[4]).text()+'</td>');
					$tr.append('<td><input type="checkbox" checked="" value="loaded" name="loaded['+ext+']" class="install"></td>');
					$tr.append('<td><input type="checkbox" checked="" value="loadAtStartUp" name="loadAtStartUp['+ext+']" class="install"></td>');
					$tr.slideDown('normal', function() {
						$('tr#'+ext).remove();
						$('#installProgress .bar').animate({width:'+='+percentByExt+'%'}, 1000, function() {
							//Next
							indexCurrentToInstall++;
							hasNextExtensionToInstall();
						});
					});
				});
			} else {
				installError = 1;
				progressConsole('Installation of '+ext+' failed');
			}
			createInfoMessage(data.message);
			progressConsole('> '+data.message);
		}
	});

	if (installError) {
		progressConsole('Interrupt of installation');
	}
}

function hasNextExtensionToInstall() {
	if (indexCurrentToInstall >= toInstall.length) {
		progressConsole('Clear install preparation');
		toInstall = [];
		$('#installProgress p.status').text(__('Install finished'));
		progressConsole('Install finished');
		$('#installProgress .bar').animate({backgroundColor:'#bb6',width:'100%'}, 1000);
		progressConsole('Generating caches...');
		$.ajax({
			type: "GET",
			url: $($('#main-menu a')[0]).attr('href'),
			success: function(data) {
				loaded();
				progressConsole('Generating caches finished');
				$('#installProgress .bar').animate({backgroundColor:'#6b6'}, 1000);
			}
		});
	} else {
		installNextExtension();
	}
}