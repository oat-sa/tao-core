$(function(){
	$("#settings-loader").click(function(){
		try {
			var settingForm = $("#settings-form");
			if (settingForm) {
				//settings dialog
				settingForm.dialog({
					width: 500,
					height: 300,
					autoOpen: false
				});
				settingForm.bind('dialogclose', function(event, ui){
					settingForm.dialog('destroy');
				});
				settingForm.dialog('option', 'title', $(this).text());
				settingForm.load(this.href, {}, function(){
					settingForm.dialog('open');
				});
				
			}
		}
		catch(exp){ console.log(exp) }
		return false;
	});
})
