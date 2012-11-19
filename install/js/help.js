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

	var inputId = $(event.currentTarget).attr('id');
	var msg = 'No help for input <strong>' + inputId + '</strong>.';

	if ((storeMsg = install.getHelp(inputId)) != null){
		msg = storeMsg;
	}

	var popupDocContext = parent.document;

	$(popupDocContext).find("#mainGenericPopup").show();
	$(popupDocContext).find("#screenShield").show();
	$(popupDocContext).find("#genericPopup h4").removeClass('error')
											   .addClass('help')
											   .html("Help");
	$(popupDocContext).find("#genericPopupContent").html(msg);
}

function displayTaoError(msg, title){

	if (typeof(title) == 'undefined'){
		var title = 'Error';
	}

	var popupDocContext = parent.document;

	$(popupDocContext).find("#mainGenericPopup").show();
	$(popupDocContext).find("#screenShield").show();
	$(popupDocContext).find("#genericPopup h4").removeClass('help')
											   .addClass('error')
											   .html(title);
	$(popupDocContext).find("#genericPopupContent").html(msg);
}