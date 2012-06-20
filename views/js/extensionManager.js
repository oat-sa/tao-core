var ext_installed = [];
var toInstall = [];
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
		percentByExt = 100 / toInstall.length;

		//Show the dialog with the result
		$('#installProgress p').text(__('%s extensions to install').replace('%s', toInstall.length));
		$('#installProgress .bar').width(0);
		$('#installProgress').dialog({
			modal: true,
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
						$('.ui-dialog-buttonpane').remove();
						installError = 0;
						for (i in toInstall) {
							ext = toInstall[i];
							$('#installProgress p').text(__('Installing %s...').replace('%s', ext));
							$.ajax({
								type: "POST",
								url: root_url + "/tao/ExtensionsManager/install",
								data: 'id='+ext,
								dataType: 'json',
								async: false,
								success: function(data) {
									if (data.success) {
										$('#installProgress .bar').animate({width:'+='+percentByExt+'%'},0); //1000 - TODO: Worker ?
										$('tr#'+ext).hide(); //.slideUp();
										$tr = $('<tr></tr>').appendTo($('#installedExtension tbody')).hide();
										$tr.append('<td>'+$($('tr#'+ext+' td')[1]).text()+'</td>');
										$tr.append('<td>'+$($('tr#'+ext+' td')[2]).text()+'</td>');
										$tr.append('<td>'+$($('tr#'+ext+' td')[4]).text()+'</td>');
										$tr.append('<td><input type="checkbox" checked="" value="loaded" name="loaded['+ext+']" class="install"></td>');
										$tr.append('<td><input type="checkbox" checked="" value="loadAtStartUp" name="loadAtStartUp['+ext+']" class="install"></td>');
										$tr.shown(); //.slideDown();
									}
									else installError = 1;
									createInfoMessage(data.message);
								}
							});
							if (installError) {
								break;
							}
						}
						toInstall = [];
						$('#installProgress .bar').animate({backgroundColor:'#6b6',width:'100%'});
						$('#installProgress p').text(__('Install finished'));
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