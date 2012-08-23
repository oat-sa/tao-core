$(document).ready(function(){
	// bind click events on question mark
	$(".ui-icon-help").bind("click", displayTaoHelp);
	// display help inside inout fields
	$("input:text, textarea").labelify({labelledClass: "helpTaoInputLabel"});
});


function displayTaoHelp(event){
	var inputId = $(event.currentTarget).attr('id');
	var msg = 'No help for input <strong>' + inputId + '</strong>.';

	if ((storeMsg = install.getHelp(inputId)) != null){
		msg = storeMsg;
	}

	$("#helpPopUp, .ui-overlay").remove();

	$("body").append('<div class="ui-overlay"><div class="ui-widget-overlay  ui-corner-all"></div></div>');
	$("body").append('<div id="helpPopUp" class="ui-widget ui-widget-content ui-corner-all"><div id="helpPopUpClose" title="Close the Help Topic">X</div><h4>Help</h4><p id="helpPopupContent">' + msg + '</p></div>');
	
	$("#helpPopUpClose").bind("click",function(){
		$(".ui-overlay").remove();
		$("#helpPopUp").remove();
	});
}

function displayTaoError(msg, title){

	if (typeof(title) == 'undefined'){
		var title = 'Error';
	}

	$("#errorPopUp, #helpPopUp, .ui-overlay").remove();

	$("body").append('<div class="ui-overlay"><div class="ui-widget-overlay  ui-corner-all"></div></div>');
	$("body").append('<div id="errorPopUp" class="ui-widget ui-widget-content ui-corner-all"><div id="errorPopUpClose" title="Close">X</div><h4>' + title + '</h4><p id="errorPopupContent">' + msg + '</p></div>');
	
	$("#errorPopUpClose").bind("click",function(){
		$(".ui-overlay").remove();
		$("#errorPopUp").remove();
	});
}

/*function displaySupport(){


	$("#errorPopUp, #helpPopUp, .ui-overlay").remove();

	$("body").append('<div class="ui-overlay"><div class="ui-widget-overlay  ui-corner-all"></div></div>');
	$("body").append('<div id="errorPopUp" class="ui-widget ui-widget-content ui-corner-all"><div id="errorPopUpClose" title="Close">X</div><h4>' + title + '</h4><p id="errorPopupContent">' + msg + '</p></div>');
	
	$("#errorPopUpClose").bind("click",function(){
		$(".ui-overlay").remove();
		$("#errorPopUp").remove();
	});
}*/