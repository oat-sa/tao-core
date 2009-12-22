// import all the as files wich are reauire in this application
// ie : all the classes
import lu.tao.utils.Event;
import lu.tao.utils.tao_toolbox;
import lu.tao.result.*;
import tao_test;
import lu.tao.utils.tao_calculator;
import lu.tao.tao_sequence.tao_sequence;
import lu.tao.tao_scoring.tao_scoring;
import com.xfactorstudio.xml.xpath.*;
import flash.external.ExternalInterface;
import mx.transitions.Tween;
import mx.transitions.easing.*;

#include "../core/include/tao_init.as"
#include "../core/include/tao_initWS.as"

#include "../core/include/tao_IRT.as"
#include "../core/include/tao_SEQ.as"

var pTestXmlFile_str:String;
var pSubject_str:String;
var pLabel_str:String;
var pComment_str:String;
var pPredicate_str:String;

var resultOutput_str:String = new String();

// breakoff Patch starts here
var breakOffNow_bool:Boolean;
var persistSO:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
if (persistSO.data.breakOffNow != undefined){
	_root.breakOffNow_bool = persistSO.data.breakOffNow;
	if((_root.breakoffUrl == undefined) && (persistSO.data.breakOffUrl != undefined)){
		_root.breakoffUrl = persistSO.data.breakOffUrl;
	}
}
_root.clickCatcher_mc = _root.createEmptyMovieClip("clickCatcher_mc", 47562);
_root.clickCatcher_mc.focusEnabled = true;
Selection.setFocus(_root.clickCatcher_mc);
_root.clickCatcher_mc._x = 0;
_root.clickCatcher_mc._y = 0;
_root.clickCatcher_mc.lineStyle(_root.clickCatcher_mc, 0xff0000, 0);
_root.clickCatcher_mc.beginFill(0xff0000, 0);
_root.clickCatcher_mc.moveTo(0, 0);
_root.clickCatcher_mc.lineTo(48, 0);
_root.clickCatcher_mc.lineTo(48, 49);
_root.clickCatcher_mc.lineTo(0, 49);
_root.clickCatcher_mc.lineTo(0, 0);
_root.clickCatcher_mc.useHandCursor = false;
var tImageEvent_listener = new Object();
tImageEvent_listener.click = function (){
	trace("Breakoff phase 1");
	if((Key.isDown(Key.SHIFT)) && (Key.isDown(Key.CONTROL))){
		trace("Breakoff phase 2");
		_root.breakOffNow_bool = true;
		persistSO.data.breakOffNow = true;
		persistSO.data.breakOffUrl = _root.breakoffUrl;
		_root.clickCatcher_mc.swapDepths(3000);
		_root.clickCatcher_mc.removeMovieClip();
		_root.currentItemRootLevel.feedTrace("BREAKOFF","REQUEST","taoHAWAI");
		_root.gotoItem(-1);
	};
}
_root.clickCatcher_mc.addEventListener("click", tImageEvent_listener);
_root.clickCatcher_mc.onRelease = tImageEvent_listener.click;
// breakoff Patch stops here

if (_root.TestXmlFile==undefined){
	pTestXmlFile_str = "Test.xml";
}
else {
	pTestXmlFile_str = _root.TestXmlFile;
}
if (_root.subject==undefined){
	pSubject_str = "#NOTDEFINED#";
	pLabel_str = "#NOTDEFINED#";
	pComment_str = "#NOTDEFINED#";
	pPredicate_str = "#NOTDEFINED#";
}
else {
	pSubject_str = _root["subject"];
	pLabel_str = _root["label"];
	pComment_str = _root["comment"];
	pPredicate_str = _root["predicate"];
}

var subjectNameWithoutSpace:String;
subjectNameWithoutSpace = escape(pSubject_str);

#include "./tao_result.as"

function getTestGlobalTimer(){
	return(getTimer());
}

function startTestTimer(){
	trace("test.fla : startTestTimer overlay invoqued");
	testTimer_ref.startTestTimer();
}

function stopTestTimer(){
	trace("test.fla : stopTestTimer overlay invoqued");
	testTimer_ref.stopTestTimer();
	testTimer_ref.startTestTimer();
}

function applyHideTransition(){
	trace("TOTO: applyHideTransition entered");
	var myResult_str;
	clearInterval(hideInterval);
	ExternalInterface.call("hideTransition", "");
	_level0.theMask_mc._width = 1;
	_level0.theMask_mc._height = 1;
}

var theMask_mc:MovieClip;

function showTransition(){
	trace("TOTO: showTransition entered");
	_level0.theMask_mc._width = 1024;
	_level0.theMask_mc._height = 768;
	ExternalInterface.call("showTransition", "");
}

var hideInterval;

function hideTransition(){
	trace("TOTO: hideTransition entered");
	new Tween(_level0.theMask_mc, "_alpha", Strong.easeIn, 100, 0, 2, true);
	hideInterval = setInterval(_level0.applyHideTransition, 2000);
	trace("TOTO: hideTransition returned " + hideInterval);
}

communicationChannel_I2T_test_lc.returnCurrentItemVersion = function(currentItemVersion_str:String){
	itemVersion_str = currentItemVersion_str;
}

var vCurrentItemContext_str:String = "";
communicationChannel_I2T_test_lc.saveItemContextString = function(itemContextChunk_str:String){
	trace("communicationChannel_I2T_test_lc.saveItemContextString invoqued");
	if(itemContextChunk_str.substr(0,11) == "#MoRe2cOmE#"){
		vCurrentItemContext_str += itemContextChunk_str.substr(11);
	}
	else{
		vCurrentItemContext_str += itemContextChunk_str;
		var vCurrentItemContext_xml:XML = new XML(vCurrentItemContext_str);
		vCurrentItemContext_str = "";
//		trace("saveItemContext with " + itemContext_xml.toString());
		test4tao.saveItemContext(vCurrentItemContext_xml);
	}
}

communicationChannel_I2T_test_lc.saveItemContext = function(itemContext_xml:XML){
	trace("communicationChannel_I2T_test_lc.saveItemContext invoqued");
//	trace("saveItemContext with " + itemContext_xml.toString());
	test4tao.saveItemContext(itemContext_xml);
}

communicationChannel_I2T_test_lc.saveItemResult = function(itemResult_xml:XML) {
	trace("communicationChannel_I2T_test_lc.saveItemResult invoqued");
	trace("saveItemResult with " + itemResult_xml.toString());
	test4tao.saveItemContext(itemResult_xml);
};

// these two methods allow the current item to trigger navigation between items
communicationChannel_I2T_test_lc.nextItem = function() {
	trace("test.fla: communicationChannel_I2T_test_lc.nextItem invoqued");
	_root.nextItem();
};

communicationChannel_I2T_test_lc.prevItem = function() {
	trace("test.fla: communicationChannel_I2T_test_lc.prevItem invoqued");
	_root.prevItem();
};

communicationChannel_I2T_test_lc.previousItem = function() {
	trace("communicationChannel_I2T_test_lc.previousItem invoqued");
	_root.prevItem();
};

communicationChannel_I2T_test_lc.allowPreviousItem = function(previousItemAllowed_boolean:Boolean) {
	trace("communicationChannel_I2T_test_lc.allowPreviousItem invoqued with " + previousItemAllowed_boolean);
	if(previousItemAllowed_boolean){
		test4tao.allowPreviousItem();
	}
	else {
		trace("communicationChannel_I2T_test_lc.allowPreviousItem: test is aware that previous item is not allowed");
	}
};

communicationChannel_I2T_test_lc.allowNextItem = function(nextItemAllowed_boolean:Boolean) {
	trace("communicationChannel_I2T_test_lc.allowNextItem invoqued with " + nextItemAllowed_boolean);
	if(nextItemAllowed_boolean){
		test4tao.allowNextItem();
	}
	else {
		trace("communicationChannel_I2T_test_lc.allowNextItem: test is aware that next item is not allowed");
	}
};

communicationChannel_I2T_test_lc.itemUnloadReady = function(itemUnloadReady_boolean:Boolean) {
	trace("communicationChannel_I2T_test_lc.itemUnloadReady invoqued");
	test4tao.afterItemUnloaded();
};

communicationChannel_I2T_test_lc.startTestTimer = function() {
	trace("test.fla: startTestTimer invoqued");
	_level0.startTestTimer();
};

communicationChannel_I2T_test_lc.stopTestTimer = function() {
	trace("test.fla: stopTestTimer invoqued");
	_level0.stopTestTimer();
};

communicationChannel_I2T_test_lc.connect("lc_item2test");
//communicationChannel_T2I_test_lc.connect("lc_test2item");
//var test4tao = tao_test.main(this,0,0,0,pTestXmlFile_str,pSubject_str);
test4tao = new tao_test(this,0,0,0,pTestXmlFile_str,pSubject_str);

function setLang(pLang:String){
	trace("test.fla: setLang call relay");
	itemVersion_str = "n.a.";
	test4tao.setLang(pLang);
}

function prevItem(){
	trace("test.fla: prevItem call relay");
	_level0.currentItemRootLevel.feedTrace("PREVIOUS_ITEM","REQUEST","taoHAWAI");
	itemVersion_str = "n.a.";
	test4tao.previousItem();
}

function nextItem(){
	trace("test.fla: nextItem call relay");
	_level0.currentItemRootLevel.feedTrace("NEXT_ITEM","REQUEST","taoHAWAI");
	itemVersion_str = "n.a.";
	test4tao.nextItem();
}

function getHelp(vURLtoHelpPage_str){
	trace("test.fla: getHelp call with " + vURLtoHelpPage_str);
	_level0.currentItemRootLevel.feedTrace("GET_HELP","REQUEST","taoHAWAI");
// TODO: trace the Help request
	getURL(vURLtoHelpPage_str,"_blank");
}

var currentItemChangedListeners_array:Array = new Array();
function subscribeOnCurrentItemChanged(listenerRef_obj:Object){
	trace("test.fla: subscribeOnCurrentItemChanged call relay");
	test4tao.subscribeOnCurrentItemChanged(listenerRef_obj);
}

function getCurrentItem(){
	trace("test.fla: getCurrentItem call relay");
	return test4tao.getCurrentItem();
}

function getItemsList(){
	trace("test.fla: getItemsList call relay");
	return test4tao.getItemsList();
}

function gotoItem(itemSeq_num:Number){
	trace("test.fla: gotoItem call relay with " + itemSeq_num);
	itemVersion_str = "n.a.";
	test4tao.gotoItem(itemSeq_num);
}

function getItemContextHolders(){
	return(test4tao.getItemContextHolders());
}

var currentInquiry_num:Number = 0;

function getCurrentItemInquiry(){
	var vCurrentItemInquiry_str:String;
	vCurrentItemInquiry_str = "";
	vCurrentItemInquiry_str = String(test4tao.getCurrentItem());
	vCurrentItemInquiry_str += "/" + String(currentInquiry_num);
	trace("test.fla: getCurrentItemInquiry returned " + vCurrentItemInquiry_str);
	return(vCurrentItemInquiry_str);
}


function collectResult(){
	trace("test.fla: result gathering");
//	gotoAndPlay(4);
	if(resultRunning == false){
		resultRunning = true;
		res = test4tao.collectResult(pTestXmlFile_str,pSubject_str,pLabel_str,pComment_str);
		recordCookies(res);
		sendXML(res);
	}
	stop();
}

function endTest(){
	if(_root.endTestConfirmation_bool){
		_level0.endTestConfirmControl();
	}
	else{
		_level0.collectResult();
	}
}

function endTestConfirmControl(){
	function endTestCancel(){
		endTestWindow.deletePopUp();
		test4tao.increaseCurrentItem_index();
		test4tao.afterAllowPreviousItem();
	}
	function endTestOk(){
		endTestWindow.deletePopUp();
		_level0.collectResult();
	}
/*
	var winTitle = "Fin du Test";
	var winMessage = "Voulez-vous terminer le test ?";
	var vOkLabel = "Oui";
	var vCancelLabel = "Non";
	var endTestWindow = mx.managers.PopUpManager.createPopUp(_root, mx.containers.Window, true, {title:winTitle, contentPath:"warningDetail", closeButton:true, messageToShow:winMessage, callerOk:endTestOk, callerCancel:endTestCancel, okLabel:vOkLabel, cancelLabel:vCancelLabel});
*/
	var winTitle = "End of Test";
	var winMessage = "The test is now finished. Your result will display on next page. Press the 'Print Test Result' button to obtain an hard copy of this result. Thank you."; //"Voulez-vous terminer le test ?";
	var vOkLabel = "Ok";
	var endTestWindow = mx.managers.PopUpManager.createPopUp(_root, mx.containers.Window, true, {title:winTitle, contentPath:"warningDetail", closeButton:true, messageToShow:winMessage, callerOk:endTestOk, okLabel:vOkLabel}); //callerCancel:endTestCancel,

	endTestWindow.setSize(200,200);
	endTestWindow._x = 280;
	endTestWindow._y = 150;
	var endTestWindow_lo = new Object();
	endTestWindow_lo.click = function(){
		endTestWindow.deletePopUp();
		test4tao.increaseCurrentItem_index();
		test4tao.afterAllowPreviousItem();
	}
	endTestWindow.addEventListener("click", endTestWindow_lo);
}

hideTransition();
stop();
