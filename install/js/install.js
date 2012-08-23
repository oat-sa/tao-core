$(document).ready(function() {
	// API instanciation to be ready for template
	// injection.
	var apiInstance = new TaoInstall();
	apiInstance.frameId = 'mainFrame';
	apiInstance.setTemplate('step_1');

	// feedback popup show/hide
	$("#suppportTab").bind("click",openSupportTab);

	$("#supportPopupClose").bind("click",function(){
		$("#mainSupportPopup").hide();
		$("#mainSupportPopup").find("#supportFrameId").attr("src","supportFrameIndex.html");
		$("#suppportTab").bind("click",openSupportTab);
	});

	function openSupportTab(){
		$("#mainSupportPopup").show();
		$("#mainSupportPopup").find("#supportFrameId").attr("src","supportFrameIndex.html");
		$("#suppportTab").unbind("click");
	}
});