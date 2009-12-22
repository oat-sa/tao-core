// here come the methods invoqued by XUL oncommand tags

function gotoInquiry(inquiryNumber:Number){
	item4tao.gotoInquiry(inquiryNumber);
}
function prevInquiry(){
//	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "stopTestTimer");
	_root._prevInquiry_button_ref._visible = false;
	_root._nextInquiry_button_ref._visible = false;
	_root.isHAWAIstimulusInitialized_bool = false;
	_root.feedTrace("PREVIOUS_INQUIRY","REQUEST","taoHAWAI");
	item4tao.prevInquiry();
}
function nextInquiry(){
//	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "stopTestTimer");
	_root._prevInquiry_button_ref._visible = false;
	_root._nextInquiry_button_ref._visible = false;
	_root.isHAWAIstimulusInitialized_bool = false;
	_root.feedTrace("NEXT_INQUIRY","REQUEST","taoHAWAI");
	_root.feedTrace("NEXT_BUTTON","id=" + _root._nextInquiry_button_ref.id,"taoHAWAI");
	item4tao.nextInquiry();
}
function goUp(){
	item4tao.goUp();
}
function goDown(){
	item4tao.goDown();
}
