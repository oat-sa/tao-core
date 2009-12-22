System.security.allowDomain("*");

import flash.external.*;
import com.xfactorstudio.xml.xpath.*;
import item_hawai;
import it.sephiroth.XML2Object;
//import tao_item;
import lu.tao.utils.Event;
//import com.eXULiS.lib.Event;

var _nextInquiry_button_ref;
var _prevInquiry_button_ref;

var isHAWAIstimulusInitialized_bool:Boolean = false;

var _u16_BLACK_dt_marker:Boolean;
var _u16_BLACK_dt_marker_str:String;

var aBroadcaster = new Event();

// version of this item template
var itemVersion_str:String = "HAWAI b.20081217";
_level0.currentItemRootLevel = this;

// we must be able to listem msg from test
var communicationChannel_T2I_item_lc:LocalConnection = new LocalConnection();
trace("Connection channel test2item initialized in item");
// we must be able to send msg to test
var communicationChannel_I2T_item_lc:LocalConnection = new LocalConnection();
trace("Connection channel item2test initialized in item");

var lg_str:String;
var itemCountDown_num;
var itemXmlFile_str:String;
var item4tao;
var itemSequence_str:String;
var currentItemContext_xml:XML;
var tmpXML2Obj_ref:MovieClip = this.createEmptyMovieClip("tmpXML2Obj_mc", this.getNextHighestDepth());

var myPlaceInTheTestIs_num:Number = _level0.test4tao.getCurrentItem();

var tracerPosition_num:Number = 0;
var currentSubjectNameTracer_str:String = _level0.subjectNameWithoutSpace + "_t" + String(myPlaceInTheTestIs_num);

var baseTimer_num:Number;
var tracedEvents_array:Array;
var inquiryPlace_ref;
var era_stimulus_ref;
var era_stimulus_mc_ref;
var era_rte_itemScore_array:Array = new Array();
var era_rte_snapshotRef_str;
var era_rte_starting_frame;
var user_t_recovery;

function searchForPreviousTraces():Number{
	var localTracerPosition_num:Number = 0;
	_root.user_t_recovery = SharedObject.getLocal("/" + _root.currentSubjectNameTracer_str + "_p" + String(localTracerPosition_num), "/");
	while(_root.user_t_recovery.data.tracedEvents_array != undefined) {
		localTracerPosition_num++;
		_root.user_t_recovery = SharedObject.getLocal("/" + _root.currentSubjectNameTracer_str + "_p" + String(localTracerPosition_num), "/");
	}
	return(localTracerPosition_num);
}

tracerPosition_num = searchForPreviousTraces();

tracedEvents_array = new Array();

function getInfo(){
	era_stimulus_ref.this_mc.isEventDispatchingEnabled();
}

function feedTrace(){	
	var tracedKeyAction_str:String;
	var tracedMessage_str:String;
	var tracedName_str:String;
	tracedKeyAction_str = arguments[0];
	tracedMessage_str = arguments[1];
	tracedName_str = arguments[2];
	trace("itemHAWAI: feed from " + tracedName_str + " : event " + tracedKeyAction_str + " with message " + tracedMessage_str);
   	var currentTimer_num = _level0.getTestGlobalTimer() - _root.baseTimer_num;
   	var currentTimer_str = String(currentTimer_num);

	if(tracedKeyAction_str == "START"){
		tracedKeyAction_str = (tracerPosition_num > 0) ? "RESTART" : "START";
	}

	var anElement_str:String = "<taoEvent Name='" + tracedName_str + "' Type='"  + tracedKeyAction_str + "' Time='" + currentTimer_str;
	if(tracedMessage_str.indexOf("<") != -1){
		var cbaTmp_str:String = escape(tracedMessage_str);
		var cbaTmp_num:Number = cbaTmp_str.indexOf("%3Ccba");
		cbaTmp_str = (cbaTmp_num == -1) ? cbaTmp_str : cbaTmp_str.substr(cbaTmp_num);
		anElement_str += "'>" + cbaTmp_str + "</taoEvent>";		
	}
	else{
		anElement_str += ((tracedMessage_str == undefined) || (tracedMessage_str == "undefined") || (tracedMessage_str == "")) ? "'/>" : "'>" + /*escape(*/tracedMessage_str/*)*/ + "</taoEvent>";
	}

	//trace("taoEvent: " + anElement_str);
	_root.tracedEvents_array.push(anElement_str);

	if((_root.tracedEvents_array.length >= 10) || (tracedKeyAction_str == "END")){
		trace("ten more - start");
		var elemToDel_str:String = "";
		var tracedEvents_str:String = "";
		var maxTracedEvents_num:Number = _root.tracedEvents_array.length;
		for(var vCpt_ta_num:Number = 0;vCpt_ta_num<maxTracedEvents_num;vCpt_ta_num++){
			elemToDel_str = String(_root.tracedEvents_array.shift());
			trace("ten more - " + elemToDel_str);
			tracedEvents_str += elemToDel_str;
		}
		delete elemToDel_str;
		_root.user_t_recovery = SharedObject.getLocal("/" + _root.currentSubjectNameTracer_str + "_p" + String(_root.tracerPosition_num), "/");
		_root.user_t_recovery.data.tracedEvents_array = tracedEvents_str;
		var retFlush_val = _root.user_t_recovery.flush();
		trace("ten more - retFlush_val = " + retFlush_val);
		_root.tracerPosition_num = _root.tracerPosition_num + 1;
	}
//	if((_root.era_rte_starting_frame != undefined) && (tracedKeyAction_str == "gotoFrameInitialized") && (_root._nextInquiry_button_ref._visible == false)){
//	if((tracedKeyAction_str == "interactionAllowed") && (_root._nextInquiry_button_ref._visible == false)){
	if((tracedKeyAction_str == "gotoFrame") && (_root._nextInquiry_button_ref._visible == false)){
		trace("feed from tao_HAWAI : complex initialized");
		_root.isHAWAIstimulusInitialized_bool = true;
		_root._nextInquiry_button_ref._visible = true;
		_root._prevInquiry_button_ref._visible = true;
	}
}
function ConfirmationPopupClosed(vAnswer_str:String){
	trace("confirmationPopupClosed(returnCode='" + vAnswer_str + "') called");
	_root._prevInquiry_button_ref._visible = true;
	_root._nextInquiry_button_ref._visible = true;
	if(vAnswer_str == "ok"){
		item4tao.nextInquiry_confirmed();
	}
	else{
		// do nothing
	}
}
function doAction(){
	var originator_mc = arguments[0];
	var actionArg_str = arguments[1];
	var supplArg_str = arguments[2];
	var separator_num;
	var frameId_str:String;
	separator_num = originator_mc.indexOf(",");
	if((separator_num != -1) && !isNaN(separator_num)){
		actionArg_str = originator_mc.substr(0,separator_num);
		supplArg_str = originator_mc.substr(separator_num + 1);
		originator_mc = _root;
	}
	trace("item doAction -> " + actionArg_str + " requested for " + originator_mc + " with actionArg_str = " + actionArg_str + " and supplArg_str = " + supplArg_str);
	trace("item doAction -> arguments[0]:" + arguments[0]);
	trace("item doAction -> arguments[1]:" + arguments[1]);
	trace("item doAction -> arguments[2]:" + arguments[2]);
	trace("item doAction -> arguments[3]:" + arguments[3]);
	trace("item doAction -> arguments[4]:" + arguments[4]);
	var tmpSnapshotRef_str;
	var tmpStartingFrame_str;

	switch (actionArg_str){
		case "notify" :
		{
			era_stimulus_ref = originator_mc._stimulus;
			era_stimulus_mc_ref = originator_mc;
			trace("tao_HAWAI::doAction(notify): notify is considered for STIMULUS = " + era_stimulus_ref.this_mc + " with frame = " + _root.era_rte_starting_frame);
			if((_root.era_rte_starting_frame != undefined) && (supplArg_str == "initialized")){
// tao_HAWAI : set focus on the right frame in the stimulus
				trace("tao_HAWAI::doAction(notify:initialized): let's notify the GotoFrame event for STIMULUS = " + era_stimulus_ref.this_mc + " with frame = " + _root.era_rte_starting_frame);
				_root.feedTrace(arguments[2],_root.era_rte_starting_frame,"stimulus");
//				aBroadcaster.dispatchXulEvent(era_stimulus_ref.this_mc,"gotoFrame",_root.era_rte_starting_frame);
				trace("tao_HAWAI::doAction(stimulusGotoFrame): STIMULUS StartTask called with EntryPoint=" + _root.era_rte_starting_frame + " and SnapshotRef=" + _root.era_rte_snapshotRef_str);
// ## START ######################################################################################################################
				era_stimulus_ref.StartTask(_root.era_rte_starting_frame,301,52,722,715,_root.era_rte_snapshotRef_str);
//				era_stimulus_ref.StartTask(_root.era_rte_starting_frame,301,52,722,715,"");
// ### END #######################################################################################################################
			}
			else{
				if(supplArg_str.indexOf("ItemScore") != -1){
					var currentInquiryIndex_num:Number = item4tao.getCurrentInquiryIndex() - 1;
					var localSupplArg_str:String = escape(supplArg_str);
					var localSupplArgResult_num:Number = localSupplArg_str.indexOf("%20result%3D%22");
					var localResult_str:String = localSupplArg_str.substring(localSupplArgResult_num + 15,localSupplArg_str.indexOf("%22",localSupplArgResult_num + 15));
// tao_HAWAI : stimulus sends back scoring and snapshot ref
					trace("tao_HAWAI::doAction(notify - score): TAO was notified by STIMULUS on inquiry " + currentInquiryIndex_num);
					trace("tao_HAWAI::doAction(notify - score): TAO was notified by STIMULUS with ItemScore = " + localSupplArg_str + " and SnapshotRef = " + arguments[3]);
					trace("tao_HAWAI::doAction(notify - score): STIMULUS returned '" + localResult_str + "'");
					_root.era_rte_itemScore_array[currentInquiryIndex_num] = localResult_str;
					item4tao.saveScoreAndSnapshotRef(currentInquiryIndex_num,supplArg_str,arguments[3]); // arguments[4] should contain currentInquiryIndex_num - but cba_rte does not return it properly
					_root.feedTrace("ItemScore",supplArg_str,"stimulus");
					_root.feedTrace("SnapshotRef",arguments[3],"stimulus");
				}
				else{
					_root.feedTrace(arguments[2],arguments[3],"stimulus");
				}
			}
			break;
		}
		case "recordTrace" :
		{
			trace("tao_HAWAI::doAction(recordTrace): TAO called by STIMULUS with arguments[2] = " + arguments[2] + " and arguments[3] = " + escape(arguments[3]));
			_root.feedTrace(arguments[2],arguments[3],"stimulus");
			break;
		}
		case "stimulusGotoFrame" :
		{
			trace("tao_HAWAI::doAction(stimulusGotoFrame): let's dispatch the GotoFrame event for era_stimulus_ref = " + era_stimulus_ref + " and STIMULUS " + era_stimulus_ref.this_mc);
			_root.feedTrace("gotoFrame",arguments[2],"stimulus");
//			aBroadcaster.dispatchXulEvent(era_stimulus_ref.this_mc,"gotoFrame",supplArg_str);
			trace("tao_HAWAI::doAction(stimulusGotoFrame): STIMULUS StartTask called with EntryPoint=" + _root.era_rte_starting_frame + " and SnapshotRef=" + _root.era_rte_snapshotRef_str);
// ## START ######################################################################################################################
			era_stimulus_ref.StartTask(_root.era_rte_starting_frame,301,52,722,715,_root.era_rte_snapshotRef_str);
//			era_stimulus_ref.StartTask(_root.era_rte_starting_frame,301,52,722,715,"");
// ### END #######################################################################################################################
			break;
		}
	}
}
/*
	function _nextInquiryOk():Void{
		trace("_nextInquiryOk invoqued");
		sendNotification ("confirm_event", "go_on_next_item" , "_click" );
		_root.item4tao.nextInquiry_confirmed();
	}
	function _nextInquiryCancel():Void{
		trace("_nextInquiryCancel invoqued");
		sendNotification("confirm_event", "stay_on_item" , "_click" );
		_root.communicationChannel_I2T_item_lc.send("lc_item2test", "startTestTimer");
	}
*/
// here come the methods invoqued by
function getCurrentItemIndex(Void):Number{
	trace("tao_item.fla: getCurrentItemIndex invoqued");
	var vCurrentItemIndex:Number = 0;
	if(currentItemContext_xml != undefined){
		duplicateMovieClip(tmpXML2Obj_mc, "vTmp_mc", this.getNextHighestDepth());
		var vTmpObj:Object = new XML2Object(vTmp_mc).parseXML(currentItemContext_xml);
		vCurrentItemIndex = Number(vTmpObj.itemContext[0].inquiries[0].currentInquiry[0].data);
		removeMovieClip(vTmp_mc);
	}
	trace("tao_item.fla: currentItemContext_xml is " + currentItemContext_xml.toString());
	trace("tao_item.fla: getCurrentItemIndex returned " + vCurrentItemIndex);
	return(vCurrentItemIndex);
}
function getInquiryValues(pIndex:Number):Array  {
	trace("tao_item.fla: getInquiryValues invoqued with " + pIndex);
	var vCurrentInquiryValue:Array = new Array();
	if(currentItemContext_xml != undefined){
		trace("ContextStr : " + currentItemContext_xml.toString());
		duplicateMovieClip(tmpXML2Obj_mc, "vTmp_mc", this.getNextHighestDepth());
		var vTmp_mc:MovieClip;
		var vTmpObj:Object = new XML2Object(vTmp_mc).parseXML(currentItemContext_xml);
		for(var vCpt=0;vCpt<vTmpObj.itemContext[0].inquiries[0]["inquiry" + pIndex][0].inquiryValues[0].inquiryValue.length;vCpt++){
			var vName:String = new String(vTmpObj.itemContext[0].inquiries[0]["inquiry" + pIndex][0].inquiryValues[0].inquiryValue[vCpt].name[0].data);
			var vSelected:String = new String(vTmpObj.itemContext[0].inquiries[0]["inquiry" + pIndex][0].inquiryValues[0].inquiryValue[vCpt].selected[0].data);
			var vGroupName:String = new String(vTmpObj.itemContext[0].inquiries[0]["inquiry" + pIndex][0].inquiryValues[0].inquiryValue[vCpt].groupName[0].data);
			var vRow_obj:Object = {name:vName,selected:vSelected,groupName:vGroupName};
			vCurrentInquiryValue.push(vRow_obj);
		}
		removeMovieClip(vTmp_mc);
	}
	trace("tao_item.fla: currentItemContext_xml is " + currentItemContext_xml.toString());
	trace("tao_item.fla: getInquiryValue returned " + vCurrentInquiryValue);
	return(vCurrentInquiryValue);
}
// here starts the item communication core API
_root.communicationChannel_T2I_item_lc.getCurrentItemVersion = function(){
	trace("tao_item.fla: communicationChannel_T2I_item_lc.getCurrentItemVersion invoqued");
	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "returnCurrentItemVersion", itemVersion_str);
}

_root.communicationChannel_T2I_item_lc.setItemXmlFile = function(itemXmlFile:String, currentItemSequence_str:String){
	trace("tao_item.fla: communicationChannel_T2I_item_lc.setItemXmlFile entered with currentItemSequence_str: " + currentItemSequence_str);
	var pItemXmlFile_str:String;
	if (itemXmlFile==undefined){
		pItemXmlFile_str = "Item.xml";
	}
	else {
		pItemXmlFile_str = itemXmlFile + ".xml";
	}
	_root.lg_str = pItemXmlFile_str.substr(pItemXmlFile_str.indexOf(".") - 2,2);
	_root.itemXmlFile_str = pItemXmlFile_str;
	if(currentItemSequence_str==undefined){
	   itemSequence_str = "-1";
	}
	else{
	   itemSequence_str = currentItemSequence_str;
	}
	trace("tao_item.fla: communicationChannel_T2I_item_lc.setItemXmlFile invoqued and pItemXmlFile_str: " + pItemXmlFile_str);
	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "returnCurrentItemVersion", itemVersion_str);
	item4tao = new item_hawai(_root,0,0,0,pItemXmlFile_str,itemSequence_str);
	item4tao.main();
}

_root.communicationChannel_T2I_item_lc.getItemContext = function(){
	trace("tao_item.fla: communicationChannel_T2I_item_lc.getItemContext invoqued");
	// clear the timer on item if it exists
	clearInterval(_root.wIntervalItemLength);
	_root.itemCountDown_num = undefined;
	delete _root.itemCountDown_num;
	// define the item context xml container
	var itemContext_xml:XML;// = new XML();
	// collect all what matters for the context of the item and for the final result
	itemContext_xml = item4tao.getItemContext();
	var vItemContext_str:String = itemContext_xml.toString();
	if(vItemContext_str.length > 40000){
		var vItemContextChunk_str:String = "";
		var vItemContextLen_num:Number = vItemContext_str.length;
		var vItemContextLenLoop_num:Number = 0;
		vItemContextLenLoop_num = Math.ceil(vItemContextLen_num / 40000);
		for(var vCpt_num:Number = 0;vCpt_num < (vItemContextLenLoop_num - 1);vCpt_num++){
			vItemContextChunk_str = vItemContext_str.substr(vCpt_num * 40000,40000);
			vItemContextChunk_str = "#MoRe2cOmE#" + vItemContextChunk_str;
			_root.communicationChannel_I2T_item_lc.send("lc_item2test", "saveItemContextString", vItemContextChunk_str);
		}
		vItemContextChunk_str = vItemContext_str.substr(vCpt_num * 40000,40000);
		_root.communicationChannel_I2T_item_lc.send("lc_item2test", "saveItemContextString", vItemContextChunk_str);
	}
	else{
		_root.communicationChannel_I2T_item_lc.send("lc_item2test", "saveItemContext", itemContext_xml);
		trace("tao_item.fla: communicationChannel_T2I_item_lc.getItemContext returned " + vItemContext_str);
	}
}

var vCurrentItemContext_str:String = "";
_root.communicationChannel_T2I_item_lc.setItemContextString = function(itemContextChunk_str:String){
	trace("communicationChannel_T2I_item_lc.setItemContextString invoqued");
	if(itemContextChunk_str.substr(0,11) == "#MoRe2cOmE#"){
		vCurrentItemContext_str += itemContextChunk_str.substr(11);
	}
	else{
		vCurrentItemContext_str += itemContextChunk_str;
		currentItemContext_xml = new XML(vCurrentItemContext_str);
		vCurrentItemContext_str = "";
	}
}

_root.communicationChannel_T2I_item_lc.setItemContext = function(itemContext_xml:XML){
	trace("tao_item.fla: communicationChannel_T2I_item_lc.setItemContext invoqued with " + itemContext_xml.toString());
	// set back the context of the item (including the result structure)
	currentItemContext_xml = itemContext_xml;
}
_root.communicationChannel_T2I_item_lc.getItemResult = function(){
	trace("tao_item.fla: communicationChannel_T2I_item_lc.getItemResult invoqued");
	// define the item result xml container
	var itemResult_xml:XML = new XML();
	// collect all what matters for the result
	// and then send back the information
	trace("tao_item.fla: communicationChannel_T2I_item_lc.getItemResult returned " + itemResult_xml.toString());
	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "saveItemResult", itemResult_xml);
}
_root.communicationChannel_T2I_item_lc.isPreviousItemAllowed = function(){
	// evaluate the situation regarding questions and inquiries and then
	var previousItemAllowed_boolean:Boolean = true;
	// call back test with lc_item2test to say allowPreviousItem (true or false)
	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "allowPreviousItem", previousItemAllowed_boolean);
}
_root.communicationChannel_T2I_item_lc.isNextItemAllowed = function(){
	trace("tao_item.fla: communicationChannel_T2I_item_lc.isNextItemAllowed invoqued");
	// evaluate the situation regarding questions and inquiries and then
	var nextItemAllowed_boolean:Boolean = true;
//	var nextItemAllowed_boolean:Boolean = item4tao.isNextItemAllowed();
	// call back test with lc_item2test to say allowNextItem (true or false)
	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "allowNextItem", nextItemAllowed_boolean);
}
_root.communicationChannel_T2I_item_lc.beforeItemUnload = function(){
	trace("tao_item.fla: communicationChannel_T2I_item_lc.beforeItemUnload entered");
	var itemUnloadReady_boolean:Boolean = false;
	// free all what has to be fred
//	itemUnloadReady_boolean = item4tao.garbageCollectAll();

//	if((_root.era_stimulus_ref != undefined) && (_root.era_stimulus_ref != null)){	
//		trace("tao_item.fla: beforeItemUnload STIMULUS call to EndTask with inquiry:" + (_level0.currentInquiry_num - 1));
//		_root.era_stimulus_ref.EndTask(_level0.currentInquiry_num - 1);
//	}
//	else{
		_root.finishMePlease();  //moved to cba_rte.as -> receiveEndTaskResponse() //ft
//	}

}

delayTimerListener = new Object();
delayTimerListener.xulEvent = function( evtObj:Object )
{
	// catches all the xulEvents based on the same broadcaster
	trace("tao_item.fla: xulEvent triggered by " + evtObj.target + " with " + evtObj.subtype); //u4dipf
	if( evtObj.subtype == "startTestTimer" ){
		_root.communicationChannel_I2T_item_lc.send("lc_item2test", "startTestTimer");
	}
	if( evtObj.subtype == "stopTestTimer" ){
		_root.communicationChannel_I2T_item_lc.send("lc_item2test", "stopTestTimer");
	}
}
_root.aBroadcaster.addEventListener( "xulEvent", _root.delayTimerListener );

_root.finishMePlease = function(){
	trace("tao_item.fla: _root.finishMePlease entered");
	var vResult_bool:Boolean;
	if((_root.era_stimulus_ref != undefined) && (_root.era_stimulus_ref != null)){	
		vResult_bool = _root.era_stimulus_ref.destroyAll();
		delete(_root.era_stimulus_ref);
	}
	trace("tao_item.fla: _root.finishMePlease continues");
	var currentItem_obj = this;
	var vLimit:Number = currentItem_obj._widgetsRepository_array.length;
	for(var vCpt=0; vCpt<vLimit; vCpt++){
		var thisObj_obj:Object = currentItem_obj._widgetsRepository_array[vCpt];
		var vObjRef = thisObj_obj.objRef;
		var vObjType = thisObj_obj.objType;
		var vXulType = thisObj_obj.xulType;
		if(vObjRef._name == "problem_stimulus"){
			trace("found problem_stimulus");
			vObjRef.onUnload = function () {
				trace("   *-> " + this + " unloaded");
				var vRef = this;
				vRef.destroyObject();
				removeMovieClip(vRef);
				delete(vRef);
			};
			vObjRef.unloadMovie();
		}
	}
	_root.finishAll();
}

_root.finishAll = function(){
	trace("tao_item.fla: _root.finishAll entered");
	trace("-------------------------------------");
	_root.era_stimulus_ref = undefined;
	_root.era_stimulus_mc_ref.swapDepths(2900);
	removeMovieClip(era_stimulus_mc_ref);
	_root.era_stimulus_mc_ref = undefined;
	// warn the test that the item is ready to be unloaded or not
	clearInterval(_root.wIntervalItemLength);
	_root.itemCountDown_num = undefined;
	delete _root.itemCountDown_num;
	delete _root.wIntervalItemLength;
	removeMovieClip(timerPlace_mc);
	delete timerPlace_mc;
	delete itemCountDown_num;
//	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "itemUnloadReady", itemUnloadReady_boolean);
	delete currentItemContext_xml;
	removeMovieClip(tmpXML2Obj_ref);
	delete tmpXML2Obj_ref;
	delete item4tao;
	_root.communicationChannel_T2I_item_lc.close();
	_root.communicationChannel_T2I_item_lc.destroyObject();
	delete _root.communicationChannel_T2I_item_lc;
	_root.communicationChannel_I2T_item_lc.close();
	_root.communicationChannel_I2T_item_lc.destroyObject();
	delete _root.communicationChannel_I2T_item_lc;
	_global.setTimeout(function(){_level0.test4tao.afterItemUnloaded();},500);
}

function prevItem(){
	trace("item.fla: prevItem call relay");
	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "prevItem");
}
function nextItem(){
	trace("item.fla: nextItem call relay");
	_root.communicationChannel_I2T_item_lc.send("lc_item2test", "nextItem");
}

_root.communicationChannel_T2I_item_lc.connect("lc_test2item");
_root.communicationChannel_I2T_item_lc.connect("lc_item2test");

// for debug
//localXmlFile = "essai.xml";

// interface for Authoring
if(localXmlFile != undefined){
	Stage.scaleMode = "noScale";
    Stage.align     = "LT";
	item4tao = new item_hawai(_root,0,0,0,localXmlFile,itemSequence_str);
	item4tao.main();
}