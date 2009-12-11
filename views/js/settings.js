$(function(){
	$("a#settings-loader").click(function(){
		
		var settingForm = $("#settings-form");
		if(settingForm){
			//settings dialog
			settingForm.dialog({
				width: 500,
				height: 300,
				autoOpen: false
			});
			settingFormbind('dialogclose', function(event, ui){
				settingForm.dialog('destroy');
			});
			settingForm.dialog('option', 'title', $(this).text());
			settingForm.load(this.href, {}, function(){
				settingForm.dialog('open');
			});
			
		}
		
		return false;
	});
})
