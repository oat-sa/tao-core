System.security.allowDomain("*");

import lu.tao.utils.tao_toolbox;
import lu.tao.utils.Event;
var aBroadcaster = new Event();

// version of this test
var testVersion_str:String = "TEST b.$REVISION$";
trace(testVersion_str);
// version of the current item template
var itemVersion_str:String = "n.a.";
var versionDepthIndicator:Number;

var testDuration_num:Number;
var testUserCountDown_num:Number;

var currentItemRootLevel;
var resultRunning:Boolean;
var totInquiriesManifest:String;

var _currentResultFileName:String;

// we must be able to listem msg from nested item
var communicationChannel_I2T_test_lc:LocalConnection = new LocalConnection();
trace("Connection channel item2test initialized in test");

// we must be able to send msg to item
var communicationChannel_T2I_test_lc:LocalConnection = new LocalConnection();
trace("Connection channel test2item initialized in test");

var theMask_mc:MovieClip;

Stage.scaleMode = 'noScale';
Stage.align = 'TL';

trace("TOTO: mask creation entered");
if(_level0.theMask_mc == undefined){
	_level0.createEmptyMovieClip("theMask", 1050000);
	_level0.theMask_mc = _level0["theMask"];
	trace("TOTO: " + _level0.theMask_mc + "(" + _level0.theMask_mc.getDepth() + ")");
	_level0.theMask_mc.beginFill(0xFFFFFF);
	_level0.theMask_mc.moveTo(0, 0);
	_level0.theMask_mc.lineTo(1024, 0);
	_level0.theMask_mc.lineTo(1024, 768);
	_level0.theMask_mc.lineTo(0, 768);
	_level0.theMask_mc.lineTo(0, 0);
	_level0.theMask_mc.endFill();
}

totInquiriesManifest = "";

var testTimer_ref;
var _branchingMap_xml:XML;

resultRunning = false;

function doAction(){
	var originator_mc = arguments[0];
	var actionArg_str = arguments[1];
	trace("test doAction -> " + actionArg_str + " requested for " + originator_mc);
	switch (actionArg_str){
		case "maximize" :
		{
			var currentItem_obj = this;
			var vLimit:Number = currentItem_obj._widgetsRepository_array.length;
			for(var vCpt=0; vCpt<vLimit; vCpt++){
				var thisObj_obj:Object = currentItem_obj._widgetsRepository_array[vCpt];
				var vObjRef = thisObj_obj.objRef;
				var vObjType = thisObj_obj.objType;
				var vXulType = thisObj_obj.xulType;
				if((vObjRef._name != "xul") && (vObjRef._name != "testContainer_box") && (vObjRef._name != "itemContainer_box")){vObjRef._visible = false;};
			}
			originator_mc.width = 1000;
			originator_mc.height = 720;
//			aBroadcaster.dispatchXulEvent(originator_mc,"resize","");
			break;
		}
		case "restore" :
		{
			var currentItem_obj = this;
			var vLimit:Number = currentItem_obj._widgetsRepository_array.length;
			for(var vCpt=0; vCpt<vLimit; vCpt++){
				var thisObj_obj:Object = currentItem_obj._widgetsRepository_array[vCpt];
				var vObjRef = thisObj_obj.objRef;
				var vObjType = thisObj_obj.objType;
				var vXulType = thisObj_obj.xulType;
				if((vObjRef._name != "xul") && (vObjRef._name != "testContainer_box") && (vObjRef._name != "itemContainer_box")){vObjRef._visible = true;};
			}
			originator_mc._width = 800;
			originator_mc._height = 480;
//			aBroadcaster.dispatchXulEvent(originator_mc,"resize","");
			break;
		}
		case "blindItem" :
		{ // TODO blindItem
			// hide all components of item when testee request a break
			break;
		}
		case "unlockItem" :
		{ // TODO unlockItem
			// show all components -> student is back to test
			break;
		}
		case "RTrace" :
		{
			trace("RTrace (" + originator_mc + ") : " + arguments[2]);
		}
	}
}

actionListener_obj = new Object ();// the listener to be registered and triggered
actionListener_obj.xulEvent = function (evtObj : Object )
{
	// catches all the xulEvents based on the same broadcaster
	if (evtObj.subtype == "RTrace" )
	{
		trace("RTrace (" + originator_mc + ") : " + evtObj.target + " with arg = " + evtObj.args); //u4dipf
	}
}
_leve0.aBroadcaster.addEventListener("xulEvent", actionListener_obj ); // register our listener to xulEvent

var requestedLang_str:String;

var user_recovery; // share object for crash overrun

//var itemXMLfile:String;
var test4tao;

function restartTestAfterRobot(){
	play();
}

var starter_mc:MovieClip;
