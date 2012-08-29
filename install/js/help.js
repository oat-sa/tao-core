$(document).ready(function(){
	// bind click events on question mark
	$(".ui-icon-help").bind("click", displayTaoHelp);
	// display help inside inout fields
	$("input:text, textarea").labelify({labelledClass: "helpTaoInputLabel"});

	// feedback popup show/hide
	$("#suppportTab").bind("click",openSupportTab);

	$("#supportPopupClose").bind("click",function(){
		$("#mainSupportPopup").hide();
		$("#screenShield").hide();
		$("#mainSupportPopup").find("#supportFrameId").attr("src","supportFrameIndex.html");
	});

	var popupDocConcext=parent.document

	$("#genericPopupClose").bind("click",function(){
		$("#mainGenericPopup").hide();
		$("#screenShield").hide();
	});

	function openSupportTab(){
		$("#mainSupportPopup").show();
		$("#screenShield").show();
		$("#mainSupportPopup").find("#supportFrameId").attr("src","supportFrameIndex.html");
	}
});


function displayTaoHelp(event){

	console.log("ok");

	var inputId = $(event.currentTarget).attr('id');
	var msg = 'No help for input <strong>' + inputId + '</strong>.';

	if ((storeMsg = install.getHelp(inputId)) != null){
		msg = storeMsg;
	}

	var popupDocConcext=parent.document

	$(popupDocConcext).find("#mainGenericPopup").show();
	$(popupDocConcext).find("#screenShield").show();
	$(popupDocConcext).find("#genericPopupContent h4").html("");
	$(popupDocConcext).find("#genericPopupContent").html(msg);
}

function displayTaoError(msg, title){

	if (typeof(title) == 'undefined'){
		var title = 'Error';
	}

	$("#mainGenericPopup").show();
	$("#screenShield").show();
	$("#genericPopupContent h4").html(title);
	$("#genericPopupContent").html(msg);
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