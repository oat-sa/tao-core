// import of the v2 UI components packages of Flash MX

import mx.controls.*;

import XML2Object;

import lu.tao.utils.tao_toolbox;
import lu.tao.utils.Event;
import com.xfactorstudio.xml.xpath.*;
import lu.tao.utils.tao_calculator;
import lu.tao.tao_sequence.tao_sequence;
import lu.tao.tao_scoring.tao_scoring;
import lu.tao.XUL2SWF.*;
import lu.tao.result.*;

class tao_test {
	private static var base_mc:MovieClip;
	private static var test_mc:MovieClip; // graphic UI container
	private static var xmlTestDescription_xml:XML;
	private static var xmlTestDescription_obj:Object;
	private static var xmlTestDescription_xml_array:Array = new Array();
	private static var xmlItemDescription_obj_array:Array = new Array();
	private static var currentTestLanguage_str:String; // language that is selected for the test (forwarded to items when possible)
//	private static var firstLangModifTime; // start of test time
//	private static var lastLangModifTime; // last language or item change time
	private static var testInitialCountDown_num; // in case of crash recovery
//	private static var itemByLangTime_array:Array = new Array();
	private static var currentLanguage_index:Number = 0;
	private static var languagesLookup_array:Array = new Array();
	private static var currentItem_index:Number = 0;
	private static var item_mcl:MovieClipLoader;
	private static var mclListener:Object;
	private static var callBackFunction_fct_array:Array;
	private static var itemContextHolders_array:Array = new Array();
	private static var savedItemSeq_num:Number;
//	private var communicationChannel_lc:LocalConnection;

	private static function xulLookupResolve(xpathTarget_xml:XML,initialXUL_str:String):String {
		var workString:String = new String();
		workString = initialXUL_str;
		var baseResult:String = new String();
		var finalResult:String = new String();
		var my_toolbox:tao_toolbox = new tao_toolbox();
		var workIndex1:Number;
		var workIndex2:Number;
		var workIndex3:Number;
//		trace("initialXUL_str = " + initialXUL_str);
		workIndex1 = workString.indexOf("#{");
//		trace("workIndex1 = " + workIndex1);
		if(workIndex1 == -1){
			finalResult = initialXUL_str;
		}
		else {
			while(workIndex1 != -1){
				workIndex2 = workString.indexOf("}#",workIndex1 + 2);
//			trace("workIndex2 = " + workIndex2);
				if(workIndex2 == -1){
					finalResult = initialXUL_str;
				}
				else{
					baseResult = workString.slice(workIndex1 + 2,workIndex2);
//			trace("baseResult sliced = " + baseResult);
					var tmpResult = new String(baseResult);
					tmpResult = tmpResult.toUpperCase();
					workIndex3 = tmpResult.indexOf("XPATH(");
//			trace("workIndex3 = " + workIndex3);
					if(workIndex3 != -1){
						trace("Cible = " + xpathTarget_xml.toString());//#{XPATH(/tao:TEST/rdfs:COMMENT)}#
						var xpathPart:String = my_toolbox.extractString(baseResult,baseResult.substr(workIndex3,6),")",0,false);
						var returnVal_str = XPath.selectNodes(xpathTarget_xml,xpathPart);
						var returnVal_xml:XML = new XML(returnVal_str);
						finalResult = returnVal_xml.firstChild.firstChild.nodeValue;
						trace("tao_test: XPath expression encountered " + xpathPart + " with link to " + returnVal_str); // + " and result " + finalResult);
						var prefix_str:String = new String(workString.slice(0,workIndex1));
						var suffix_str:String = new String(workString.slice(workIndex2 + 2));
						workString = prefix_str.concat(finalResult,suffix_str);
//			trace("new workString = " + workString);
					}
				}
				workIndex1 = workString.indexOf("#{",workIndex1 + finalResult.length);
			}
		}
		return(workString);
	}

	private static function buildTestDescription(xmlSource_xml:XML,xmlExtend_xml:XML):Void {
		var nodeExtend_xmlnode:XMLNode = xmlExtend_xml.firstChild.cloneNode(true);
		xmlSource_xml.appendChild(nodeExtend_xmlnode);
		trace("tao_test: xmlTestDescription enhanced");
	}

	private static function buildItemDescription(testDescFile_str:String,xmlSource_xml:XML,lastDescFile_bool:Boolean){
		// load the XML root file
		var xmlLocalizedTest_xml:XML = new XML(); // XML content of the root file that is the table of content of the XML test files
		xmlLocalizedTest_xml.ignoreWhite = true;

		xmlLocalizedTest_xml.onLoad = function(success) {
			if(success){
				// file loading succeeded
				trace("tao_test: xml localized test file " + testDescFile_str + " successfully loaded");
				// build XML test description
				buildTestDescription(xmlSource_xml,xmlLocalizedTest_xml);
				// fill in the localized test description object array
				var vTmpObj:Object = new XML2Object(test_mc).parseXML(xmlLocalizedTest_xml);
				var vTmpIndex:Number = xmlItemDescription_obj_array.push(vTmpObj);
				xmlTestDescription_xml_array.push(xmlLocalizedTest_xml);
				if(lastDescFile_bool){
					trace("tao_test: initial test GUI container building");
					var langISOcode_str:String
					langISOcode_str = "";

// CRASH RECOVERY detection
					var shouldStopNow_bool:Boolean;
					shouldStopNow_bool = false;

					var sequenceResultVal_num:Number = 0;
					var user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_r", "/");
					if(user_recovery.data.currentLanguage_index != undefined){
						trace("CRASH RECOVERY entered");
// restore the itemContextHolders_array
						currentLanguage_index = user_recovery.data.currentLanguage_index;
						trace("CRASH RECOVERY currentLanguage_index = " + currentLanguage_index);
						sequenceResultVal_num = user_recovery.data.sequenceResultVal_num;
						trace("CRASH RECOVERY sequenceResultVal_num = " + sequenceResultVal_num);
						testInitialCountDown_num = user_recovery.data.testInitialCountDown_num;
						trace("CRASH RECOVERY testInitialCountDown_num = " + testInitialCountDown_num);
						var vCpt_so_num:Number;
						vCpt_so_num = 0;
						var user_recovery_c = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_c" + String(vCpt_so_num), "/");
						while(user_recovery_c.data.itemContext != undefined) {
							itemContextHolders_array.push(user_recovery_c.data.itemContext);
							vCpt_so_num++;
							user_recovery_c = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_c" + String(vCpt_so_num), "/");
						}
/*
						var user_recovery_l = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_l", "/");
						if(user_recovery_l.data.itemByLangTime != undefined) {
							itemByLangTime_array = user_recovery_l.data.itemByLangTime;
						}
*/
//		does next item exist?
						if(sequenceResultVal_num != -1){
							trace("CRASH RECOVERY next item exists");//		yes:
//			load next item
							currentItem_index = sequenceResultVal_num;
						}
						else {
							trace("CRASH RECOVERY shouldStopNow_bool set on TRUE");//		yes:
//		no:
//			goto result
							shouldStopNow_bool = true;
						}
					}
					else {
						trace("CRASH RECOVERY currentItem_index set on zero");//		yes:
						currentItem_index = 0;
					}
// CRASH RECOVERY detection ends here

					var sequenceMethod_str:String = new String(xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:HASSEQUENCEMODE"][0].data);
					if(sequenceMethod_str.toUpperCase() == "BRANCHING"){
						if(_level0._branchingMap_xml == undefined){
							_level0._branchingMap_xml = new XML();
							_level0._branchingMap_xml.onLoad = function (success){
								trace("[tao_SEQ] file branching.xpdl loaded with status: " + success);
							}
							_level0._branchingMap_xml.ignoreWhite = true;
							_level0._branchingMap_xml.load("branching.xpdl");
						}
					}

					var vMaxItems = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"].length - 1;
					var totInquiriesManifest_str:String;
					totInquiriesManifest_str = "";
					for(var vCpt:Number=0; vCpt<vMaxItems; vCpt++){
						var vComposition_str:String;
						vComposition_str = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["manifestInq"]
						totInquiriesManifest_str += vComposition_str + "/";
					}
					totInquiriesManifest_str = totInquiriesManifest_str.substr(0,-1);
					_level0.totInquiriesManifest = totInquiriesManifest_str;
					_level0.currentInquiry_num = 0;
					if(shouldStopNow_bool == true){
						gotoItem(vMaxItems);
					}
					else {
						_level0.test4tao.checkTestTimerPresence();
						var vTmpLanguageEntry_obj:Object = languagesLookup_array[currentLanguage_index];
						langISOcode_str = vTmpLanguageEntry_obj.iso;

						currentTestLanguage_str = langISOcode_str;
//						firstLangModifTime = getTimer();
//						lastLangModifTime = getTimer();

						var xulNode_str:String = xulLookupResolve(xmlTestDescription_xml_array[currentLanguage_index],xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:TESTPRESENTATION"][0]["xul"][0].data);
						var xulNode:XML = new XML(xulNode_str);
						var vTmpXULObj:Object = new XUL2Test(test_mc["test" + langISOcode_str],base_mc).parseXML(xulNode);

						if(_level0.test["test" + langISOcode_str].xul.testContainer_box.itemContainer_box != undefined){
							trace("tao_test: INFO: itemContainer_box exists and can nest items");
							// we create a movie clip to be replaced by the loaded item
							_level0.test["test" + langISOcode_str].xul.testContainer_box.itemContainer_box.createEmptyMovieClip("itemSWFcontainer_mc",0);
							_level0.test["test" + langISOcode_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc._lockroot=true;

							trace("tao_test: JUST BEFORE loadAnItem");
							_level0.test4tao.loadAnItem();
							trace("tao_test: JUST AFTER  loadAnItem");
						}
						else {
							trace("tao_test: Error: itemContainer_box does not exist");
						}
					}
				}
			}
			else {
				// file loading failed
				trace("tao_test: xml localized test file " + testDescFile_str + " loading failed");
			}
		};
		xmlLocalizedTest_xml.load(testDescFile_str);
	}

	public function checkTestTimerPresence(){
		var vTestDuration_str = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:DURATION"][0].data;
		var vTestDuration = Number(vTestDuration_str);
		trace("tao_test: checkTestTimerPresence entered with " + vTestDuration_str + " seconds");
		if(isNaN(vTestDuration) == false){
			if(testInitialCountDown_num == undefined){
				_level0.testUserCountDown_num = vTestDuration;
			}
			else{
				_level0.testUserCountDown_num = testInitialCountDown_num;
			}
			_level0.testDuration_num = vTestDuration;
/*
			var timerPlace_mc:MovieClip;
			_level0.createEmptyMovieClip("timerPlace",54300);
			timerPlace_mc = _level0.timerPlace;
			timerPlace_mc._childNextDepth = 1;
			timerPlace_mc._x = 900;
			timerPlace_mc._y = 0;
			trace("tao_test: checkTestTimerPresence - before counter creation");
			var xulTimerNode:XML = new XML("<xul><image disabled=\"false\" src=\"countdown.swf?countdownStart=" + String(_level0.testUserCountDown_num) + "&onEndAction=_level0.nextItem\" left=\"0\" top=\"0\" width=\"\" height=\"\"/></xul>");
			var vTmpTimerXULObj:Object = new XUL2SWF(timerPlace_mc,_level0).parseXML(xulTimerNode);
*/
		}
		else{
			trace("NO TEST DURATION detected");
		}
	}

	public function previousItem(Void):Void{
		trace("tao_test: Previous item button triggered");
// is previous item allowed
		_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "isPreviousItemAllowed");
	}

	public function allowPreviousItem(Void):Void{
		trace("tao_test: allow^PreviousItem called");
// is previous item allowed?
// yes:
//		get current item info and save them
		var callBackFunction_fct:String = "this.afterAllowPreviousItem";
		var callBackFunction_fct_arg:String = "";
		var callBack_obj:Object = {callBackFct:callBackFunction_fct, callBackArg:callBackFunction_fct_arg};
		callBackFunction_fct_array.push(callBack_obj);
		_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "getItemContext");
//			reload subject's response if possible
//		no:
//			do nothing
// no:
//		warning and nothing else
	}

	public function increaseCurrentItem_index(){
		currentItem_index++;
	}

	public function afterAllowPreviousItem(){
		trace("tao_test: afterAllowPreviousItem called");
//		does previous item exist?
		if(currentItem_index > 0){
//		yes:
//          trace elapsed time on the item we quit for the current language
/*
			var vTmpElapsedTime = getTimer() - lastLangModifTime;
			var vTmpItemLangTimeEntry_obj:Object = {index:currentItem_index, iso:currentTestLanguage_str, elapsedTime:vTmpElapsedTime};
			itemByLangTime_array.push(vTmpItemLangTimeEntry_obj);
			lastLangModifTime = getTimer();
*/
//			unload current item
			if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc != undefined){
				trace("tao_test: an item is already loaded and has to be unloaded with its consent")
				item_mcl.unloadClip(_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc);
				if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.item != undefined){
					removeMovieClip(_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.item);
				}
			}
//			load previous item
			currentItem_index-- ;
			_level0.test4tao.loadAnItem();
		}
		else {
//		no:
//			goto presentation
		}
	}

	public function gotoItem(itemSeq_num:Number):Void{
		trace("tao_test: Goto item triggered");
		var itemSeqFlag_num:Number;
		itemSeqFlag_num = itemSeq_num;
		if(itemSeq_num == -1){
			var vMaxItems = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"].length - 1;
			itemSeq_num = vMaxItems;
		}
		savedItemSeq_num = itemSeq_num;
		var currentWeight_str:String = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"][currentItem_index].attributes.weight;
		if((currentWeight_str == undefined) || (currentWeight_str == "0") || (itemSeqFlag_num == -1)){
			allowNextItem();
		}
		else{
// is next item allowed?
			_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "isNextItemAllowed");
		}
	}

	public function nextItem(Void):Void {
		trace("tao_test: Next item button triggered");
		var currentWeight_str:String = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"][currentItem_index].attributes.weight;
		if((currentWeight_str == undefined) || (currentWeight_str == "0")){
			allowNextItem();
		}
		else{
// is next item allowed?
			_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "isNextItemAllowed");
		}
	}

	public function allowNextItem(Void):Void{
		trace("tao_test: allowNextItem called");
// is next item allowed?
// yes:
//		get current item info and save them
		var callBackFunction_fct:String = "this.justBeforeNextItem";
		var callBackFunction_fct_arg:String = "";
		var callBack_obj:Object = {callBackFct:callBackFunction_fct, callBackArg:callBackFunction_fct_arg};
		callBackFunction_fct_array.push(callBack_obj);
		_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "getItemContext");
	}

	public function saveItemContext(itemContext_xml:XML):Void {
		trace("tao_test: saveItemContext called");
		var contextHolder_obj:Object = {contextIndex:currentItem_index, context:itemContext_xml};
		var contextExists_bool:Boolean = false;
		var vMaxItems = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"].length - 1;
trace("### saveItemContext ### with currentItem_index = " + currentItem_index + " and vMaxItems = " + vMaxItems);
if(currentItem_index != vMaxItems){
		for(var vCpt=0;vCpt<itemContextHolders_array.length;vCpt++){
			var vTmpContext_obj:Object = itemContextHolders_array[vCpt];
			var vIndex:Number = new Number(vTmpContext_obj.contextIndex);
			if(vIndex == currentItem_index){
				itemContextHolders_array[vCpt] = contextHolder_obj;
				contextExists_bool = true;
				break;
			}
		}
		if(!contextExists_bool){
			itemContextHolders_array.push(contextHolder_obj);
		}
}
		var callBack_obj:Object = callBackFunction_fct_array.pop();
		var callBackFunction_fct:String = callBack_obj.callBackFct;
		var callBackFunction_fct_arg:String = callBack_obj.callBackArg;
		eval(callBackFunction_fct)(callBackFunction_fct_arg);
	}

	public function justBeforeNextItem(Void):Void{
		trace("tao_test: justBeforeNextItem called");
		var callBackFunction_fct:String = "this.afterAllowNextItem";
		var callBackFunction_fct_arg:String = "";
		var callBack_obj:Object = {callBackFct:callBackFunction_fct, callBackArg:callBackFunction_fct_arg};
		callBackFunction_fct_array.push(callBack_obj);
		_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "beforeItemUnload");
	}

	public function afterItemUnloaded(Void):Void{
		trace("tao_test: afterItemUnloaded called");
		var callBack_obj:Object = callBackFunction_fct_array.pop();
		var callBackFunction_fct:String = callBack_obj.callBackFct;
		var callBackFunction_fct_arg:String = callBack_obj.callBackArg;
		if(callBackFunction_fct != undefined){
			eval(callBackFunction_fct)(callBackFunction_fct_arg);
		}
	}

	public function afterAllowNextItem(){
		trace("tao_test: afterAllowNextItem called");
		if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc != undefined){
			trace("tao_test: an item is already loaded and has to be unloaded with its consent")
			if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.nextItem_button != undefined){
				_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.nextItem_button.enabled = false;
			}
			item_mcl.unloadClip(_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc);
			removeMovieClip(_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.item);
		}
		var passedItemsDescription_obj_array:Array = new Array();
/* // special Sustainability -> time sparing
		for(var vCpt=0;vCpt<itemContextHolders_array.length;vCpt++){
*/ // replaced by following line
		for(var vCpt=itemContextHolders_array.length - 1;vCpt>=itemContextHolders_array.length - 1;vCpt--){
			var contextHolder_obj:Object;
			var itemContextData_xml:XML;
			var vTmpContext_obj:Object = itemContextHolders_array[vCpt];
			var vIndex:Number = new Number(vTmpContext_obj.contextIndex);
			contextHolder_obj = itemContextHolders_array[vCpt];
			itemContextData_xml = contextHolder_obj.context;
			var tmpXML2Obj_ref:MovieClip = _root.createEmptyMovieClip("tmpXML2Obj_mc", _root.getNextHighestDepth());

			if(itemContextData_xml != undefined){
				duplicateMovieClip(_root.tmpXML2Obj_mc, "vTmp_mc", _root.getNextHighestDepth());
				var vTmpObj:Object = new XML2Object(_root.vTmp_mc).parseXML(itemContextData_xml);
//				vTmpObj.itemContext[0].attributes.index = vCpt;
				passedItemsDescription_obj_array.push(vTmpObj);
				removeMovieClip(_root.vTmp_mc);
			}
		}
		var futureItemsDescription_obj_array:Array = new Array();
		var vAlreadyDone_bool:Boolean = false;
/* // special Sustainability -> time sparing
		for(var vCpt=0;vCpt<xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"].length;vCpt++){
			var contextHolder_obj:Object;
			var itemContextData_xml:XML;
			for(var vCptContext=0;vCptContext<itemContextHolders_array.length;vCptContext++){
				var vTmpContext_obj:Object = itemContextHolders_array[vCptContext];
				var vIndex:Number = new Number(vTmpContext_obj.contextIndex);
				if(vIndex == vCpt){
					vAlreadyDone_bool = true;
				}
			}
			if(!vAlreadyDone_bool){
				futureItemsDescription_obj_array.push(xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"][vCpt]);
			}
			vAlreadyDone_bool = false;
		}
*/
		var sequenceResultVal_num:Number = 0;
		var oSequence_obj:tao_sequence = new tao_sequence();

		if(savedItemSeq_num == undefined){
			sequenceResultVal_num = oSequence_obj.getNextIndex(xmlItemDescription_obj_array,passedItemsDescription_obj_array,futureItemsDescription_obj_array,currentItem_index);
		}
		else{
			sequenceResultVal_num = savedItemSeq_num;
			savedItemSeq_num = undefined;
		}
//      trace elapsed time on the item we quit for the current language
/*
		var vTmpElapsedTime = getTimer() - lastLangModifTime;
		var vTmpItemLangTimeEntry_obj:Object = {index:currentItem_index, iso:currentTestLanguage_str, elapsedTime:vTmpElapsedTime};
		itemByLangTime_array.push(vTmpItemLangTimeEntry_obj);
		lastLangModifTime = getTimer();
*/
		if(_level0.testUserCountDown_num != undefined){
			if(_level0.testTimer_ref.getTestTimerRemaining() < 0){
//				sequenceResultVal_num = -1;
				var vMaxItems = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"].length - 1;
				sequenceResultVal_num = vMaxItems;
			}
		}

// CRASH RECOVERY DUMP starts here
	var user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_r", "/");

// itemContextHolders_array must absolutely be backuped
	user_recovery.data.currentLanguage_index = currentLanguage_index;
	user_recovery.data.sequenceResultVal_num = sequenceResultVal_num;
	user_recovery.data.testInitialCountDown_num = _level0.testTimer_ref.getTestTimerRemaining();
	user_recovery.flush();

	var user_recovery_c = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_c" + String(itemContextHolders_array.length - 1), "/");
	user_recovery_c.data.itemContext = itemContextHolders_array[itemContextHolders_array.length - 1];
	user_recovery_c.flush();
/* // it was for Monique -> not needed any more for the moment
	var user_recovery_l = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_l", "/");
	user_recovery_l.data.itemByLangTime = itemByLangTime_array;
	user_recovery_l.flush();
*/
// CRASH RECOVERY DUMP stops here

//		does next item exist?
		if(sequenceResultVal_num != -1){
//		yes:
//			load next item
			currentItem_index = sequenceResultVal_num;
//			_level0.test4tao.loadAnItem(); // debug version... useful to keep trace valid when going from one item to another
//			getURL("index.php","_self"); // FTL first attempt
			getURL("start.php","_self"); // Felix version
		}
		else {
//		no:
//			goto result
//			_root.endTest();
			var vMaxItems = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"].length - 1;
			gotoItem(vMaxItems);

		}
	}

	public function getItemContextHolders(){
		return(itemContextHolders_array);
	}

	public function collectResult(pTestXmlFile_str:String,pSubject_str:String,pLabel_str:String,pComment_str:String):Cresult {
		trace("collectResult entered");
       // We create an instance of result by ginig : rdfid, rdfs_Label, rdfs_Comment
		var testName_str = xmlItemDescription_obj_array[0]["tao:TEST"][0]["rdfs:LABEL"][0].data;
        var res = new Cresult(xmlItemDescription_obj_array[0]["tao:TEST"][0].attributes["rdf:ID"],xmlItemDescription_obj_array[0]["tao:TEST"][0]["rdfs:LABEL"][0].data,xmlItemDescription_obj_array[0]["tao:TEST"][0]["rdfs:COMMENT"][0].data);

        // for output on screen in the last frame
		var result2Display_str:String = new String("");
		//first create a subject
		var tester=new Csubject(pSubject_str,pLabel_str,pComment_str);
        //associate this subject to the test
        res.subject=tester;

        // associate scoring(Name, parameter, value) to the test
		var scoringMethod_str:String = new String(xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:HASSCORINGMETHOD"][0].data);
        res.addScoring("HASSCORINGMETHOD","NAME",scoringMethod_str);
		var cumulModel_str:String = new String(xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CUMULMODEL"][0].data);
        res.addScoring("CUMULMODEL","NAME",cumulModel_str);

		result2Display_str = result2Display_str.concat("<b>Test Results:</b><br /><br />Subject: ",pLabel_str,"<br />Test: ",testName_str,"<br />Scoring Method: ",scoringMethod_str,"<br />Cumul Method: ",cumulModel_str,"<br /><br />");

        //associate behavior (parameter, value) to the test
//        res.addTestbehavior("NumberClicks","15");
        res.addTestbehavior("SError",String(_root.lastTheta_num));
//        res.addTestbehavior("ChangeMindsoccurs","12");

		var totWeight_num:Number = new Number(0);
		var passedItemsDescription_obj_array:Array = new Array();
		var endorsment4Scoring_array:Array = new Array();
		var isThereCTest_bool:Boolean;
		isThereCTest_bool = false;

		for(var vCpt=0;vCpt<xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"].length - 1;vCpt++){
			// create new item
			var it = new Citem();
			// add properties (name, value) to res item
			var currentWeight_num:Number = new Number();
			var currentItemModel_str:String;
			var currentItemDefinitionFile_str:String;

			currentWeight_num = xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["weight"];
			if((currentWeight_num == undefined) || (isNaN(number(currentWeight_num)))){
				currentWeight_num = 0;
			}
			totWeight_num += number(currentWeight_num);

			currentItemModel_str = xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["model"];
			currentItemModel_str = (currentItemModel_str == undefined) ? "tao_item" : currentItemModel_str;
			currentItemDefinitionFile_str = xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].data;


			it.addPoperty("tao:WEIGHT",currentWeight_num);
			it.addPoperty("tao:DIFFICULTY",xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["DIFFICULTY"]);
			it.addPoperty("tao:DISCRIMINATION",xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["DISCRIMINATION"]);
			it.addPoperty("tao:GUESSING",xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["GUESSING"]);
			it.addPoperty("tao:MODEL",currentItemModel_str);
			it.addPoperty("tao:DEFINITIONFILE",currentItemDefinitionFile_str);
			it.addPoperty("tao:SEQUENCE",xmlItemDescription_obj_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["Sequence"]);

			var contextHolder_obj:Object;
			var itemContextData_xml:XML;
			for(var vCptContext=0;vCptContext<itemContextHolders_array.length;vCptContext++){
				var vTmpContext_obj:Object = itemContextHolders_array[vCptContext];
				var vIndex:Number = new Number(vTmpContext_obj.contextIndex);
				if(vIndex == vCpt){
					contextHolder_obj = itemContextHolders_array[vCptContext];
					itemContextData_xml = contextHolder_obj.context;
					break;
				}
			}

			var tmpXML2Obj_ref:MovieClip = _root.createEmptyMovieClip("tmpXML2Obj2_mc", _root.getNextHighestDepth());
			var vRDFid:String;
			var vLabel:String;
			var vComment:String;
			var vEndorsment:String;
			var vItemUsage:String;

			if(vCptContext < itemContextHolders_array.length){
				duplicateMovieClip(_root.tmpXML2Obj2_mc, "vTmp2_mc", 20333);
				var vTmp2Obj:Object;
				vTmp2Obj = new XML2Object(_root.vTmp2_mc).parseXML(itemContextData_xml);

				vTmp2Obj.itemContext[0].attributes.index = vCpt;
				passedItemsDescription_obj_array.push(vTmp2Obj);

				vRDFid = vTmp2Obj.itemContext[0].itemRDFid[0].data;
				vLabel = vTmp2Obj.itemContext[0].itemLabel[0].data;
				vComment = vTmp2Obj.itemContext[0].itemComment[0].data;
				vEndorsment = vTmp2Obj.itemContext[0].itemEndorsmentsResult[0].data;
				vItemUsage = "VISITED";
				var behaviorListeners_array = new Array();
				var tmpMaxInq = 0;
				for (var nam in vTmp2Obj.itemContext[0].inquiries[0]) {
					tmpMaxInq++ ;
				}
//				var tmpMaxInq = vTmpObj.itemContext[0].inquiries.length;
				for(var vInquiryCpt=0;vInquiryCpt<tmpMaxInq;vInquiryCpt++){
					if(vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt]==undefined){
						break;
					}
					else{ //inquiryListener
						var vRowResultEndors_obj:Object = {listenerName:"inquiryEndorsment", listenerValue:vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryEndorsment[0].data};
						behaviorListeners_array.push(vRowResultEndors_obj);
						var tmpMaxInqLstnrs = 0;
						for (var nam in vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryListeners[0]) {
							if (nam == "data"){
								tmpMaxInqLstnrs++ ;
							}
						}
						for(var vLstnrsCpt=0;vLstnrsCpt<tmpMaxInqLstnrs;vLstnrsCpt++){
							if(vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryListeners[vLstnrsCpt].inquiryListenerName[0].data != undefined){
//							var vRowResult_obj:Object = {listenerName:vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryValues[0].inquiryValue[vLstnrsCpt].inquiryName[0].data,listenerValue:vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryValues[0].inquiryValue[vLstnrsCpt].propValue[0].data};
								var vRowResult_obj:Object = {
									listenerName:vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryListeners[vLstnrsCpt].inquiryListenerName[0].data,
									listenerValue:vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryListeners[vLstnrsCpt].inquiryListenerValue[0].data
								};
								behaviorListeners_array.push(vRowResult_obj);
							}
						}
					}
				}
				var vRowResultContext_obj:Object = {listenerName:"ItemContext", listenerValue:escape(itemContextData_xml.toString())};
				behaviorListeners_array.push(vRowResultContext_obj);
				removeMovieClip(_root.vTmp2_mc);
			}
			else{
				trace("item not visited");
				vRDFid = "";
				vLabel = "";
				vComment = "";
				vEndorsment = "undefined";
				vItemUsage = "NOT_VISITED";
			}

			it.addPoperty("tao:ENDORSMENT",vEndorsment);
			it.addPoperty("tao:ITEMUSAGE",vItemUsage);

			if(vEndorsment != undefined){
				switch(xmlItemDescription_obj_array[0]["tao:TEST"][0].attributes["rdf:ID"]){
					case "http://localhost/middleware/romTests.rdf#11290228946964":
					case "http://localhost/middleware/romTests.rdf#112911346134932":
					{
						result2Display_str = result2Display_str.concat("  - Problem Result ", string(vCpt + 1)," (weight ", string(currentWeight_num),"): ", vEndorsment,"<br /><img src='" + vTmp2Obj.itemContext[0].itemListeners[0].currentImage[0].data + "' width='246' height='96' align='left' hspace='5' vspace='5' /><br /><b>Your answer:</b> " + vTmp2Obj.itemContext[0].inquiries[0]["inquiry0"][0].inquiryValues[0].inquiryValue[0].propValue[0].data + "<br /><b>Correct answer:</b> " + vTmp2Obj.itemContext[0].inquiries[0]["inquiry0"][0].inquiryHasAnswerLabel[0].data + "<br /><br /><br /><br /><br />");
						break;
					}
					case "http://localhost/middleware/romTests.rdf#11291248991062":
					case "http://localhost/middleware/romTests.rdf#112869619751070":
					{
						var vHasAnswer_tmp_str:String = new String(vTmp2Obj.itemContext[0].inquiries[0]["inquiry0"][0].inquiryHasAnswerLabel[0].data);
						var vHasAnswer_result_txt = "";
						if(vHasAnswer_tmp_str.indexOf(".jpg") != -1){
							vHasAnswer_result_txt = "<br /><img src='" + vTmp2Obj.itemContext[0].inquiries[0]["inquiry0"][0].inquiryHasAnswerLabel[0].data + "' width='90' height='90' align='left' hspace='5' vspace='5' /><br /><br />";
						}
						else{
							var my_toolbox:tao_toolbox = new tao_toolbox();
							vHasAnswer_result_txt = "<br />     " + my_toolbox.replaceString(vHasAnswer_tmp_str,"'","\'");
						}
						var indexLastListener_num = vTmp2Obj.itemContext[0].inquiries[0]["inquiry0"][0].inquiryListeners[0].inquiryListener.length - 1;
						var vYourAnswer_tmp_str:String = new String(vTmp2Obj.itemContext[0].inquiries[0]["inquiry0"][0].inquiryListeners[0].inquiryListener[indexLastListener_num].listenerValue[0].data);
						var vYourAnswer_result_txt = "";
						if(vYourAnswer_tmp_str.indexOf(".jpg") != -1){
							vYourAnswer_result_txt = "<br /><img src='" + vTmp2Obj.itemContext[0].inquiries[0]["inquiry0"][0].inquiryListeners[0].inquiryListener[indexLastListener_num].listenerValue[0].data + "' width='90' height='90' align='left' hspace='5' vspace='5' /><br /><br />";
						}
						else{
							var my_toolbox:tao_toolbox = new tao_toolbox();
							vYourAnswer_result_txt = "<br />     " + my_toolbox.replaceString(vYourAnswer_tmp_str,"'","\'");
						}
						result2Display_str = result2Display_str.concat("  - Problem Result ", string(vCpt + 1)," (weight ", string(currentWeight_num),"): ", vEndorsment,"<br /><img src='" + vTmp2Obj.itemContext[0].itemListeners[0].currentImage[0].data + "' width='120' height='120' align='left' hspace='5' vspace='5' /><br /><br /><br /><br /><br /><br /><br /><br /><b>Your answer:</b>" + vYourAnswer_result_txt + "<br /><br /><br /><br /><br /><b>Correct answer:</b>" + vHasAnswer_result_txt + "<br /><br /><br /><br /><br />");
						break;
					}
					default:{
/*
						if(vTmp2Obj.itemContext[0].itemTrace[0].CTevent[0].data != undefined){
							isThereCTest_bool = true;
							var tmpResultDenom = 0;
							var vEndorsment_num = 0;
							for(var vInquiryCpt=0;vInquiryCpt<tmpMaxInq;vInquiryCpt++){
								if(vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt]==undefined){
									break;
								}
								else{
									var vGapResultEndors_str:String = vTmp2Obj.itemContext[0].inquiries[0]["inquiry" + vInquiryCpt][0].inquiryEndorsment[0].data;
									tmpResultDenom++;
									if(!(isNaN(parseInt(vGapResultEndors_str,10)))){
										vEndorsment_num += parseInt(vGapResultEndors_str,10);
									}
								}
							}
							vEndorsment_num = Math.round((100 * vEndorsment_num) / tmpResultDenom);
							vEndorsment_num = vEndorsment_num / 100;
							vEndorsment = String(vEndorsment_num);
						}
						if(vTmp2Obj.itemContext[0].itemTrace[0].taoOpenevent[0].data != undefined){
							trace("TAO_OPEN DETECTED");
							isThereCTest_bool = true;
							var tmpResultDenom = 0;
							var vEndorsment_num = 0;
							var tmpMaxEvt = 0;
							for (var nam in vTmp2Obj.itemContext[0].itemTrace[0]) {
								tmpMaxEvt++ ;
								trace("tmpMaxEvt: " + tmpMaxEvt);
							}
							for(var vInquiryCpt=0;vInquiryCpt<tmpMaxEvt;vInquiryCpt++){
								var tEvtContent_str:String;
								tEvtContent_str = vTmp2Obj.itemContext[0].itemTrace[0].taoOpenevent[vInquiryCpt].data;
								if(tEvtContent_str.substr(0,7)=="answer_"){
									tmpResultDenom++;
									if(tEvtContent_str.indexOf("#1#=")!=-1){
										vEndorsment_num++;
									}
								}
							}
							vEndorsment_num = Math.round((100 * vEndorsment_num) / tmpResultDenom);
							vEndorsment_num = vEndorsment_num / 100;
							vEndorsment = String(vEndorsment_num);
						}
						result2Display_str = result2Display_str.concat("  - Problem Result ", string(vCpt + 1)," (weight ", string(currentWeight_num),"): ", vEndorsment,"<br />");
*/
					}
				}
			}
			endorsment4Scoring_array.push(vEndorsment);

			it.addPoperty("rdfid",vRDFid);
			it.addPoperty("rdfs:Label",vLabel);
			it.addPoperty("rdfs:Comment",vComment);

			if(vItemUsage == "VISITED"){
				//associate behavior (parameter, value) to the item
				for(var vBehaviorCpt=0;vBehaviorCpt<behaviorListeners_array.length;vBehaviorCpt++){
					var vRowBehavior_obj:Object = behaviorListeners_array[vBehaviorCpt];
					it.addBehavior(vRowBehavior_obj.listenerName,vRowBehavior_obj.listenerValue);
				}
			}
			res.addItem(it);
		}

		var scoringResultVal_str:String = new String("");
		var oScoring_obj:tao_scoring = new tao_scoring();
		scoringResultVal_str = oScoring_obj.calculateScoring(xmlItemDescription_obj_array,passedItemsDescription_obj_array,"theta");
		res.addScoring("SCORE","VALUE",scoringResultVal_str);
		if(isThereCTest_bool){
			var totalRecomputedResult_num:Number;
			totalRecomputedResult_num = 0;
			for(var vCptRecompute_num=0;vCptRecompute_num<endorsment4Scoring_array.length;vCptRecompute_num++){
				totalRecomputedResult_num += parseFloat(endorsment4Scoring_array[vCptRecompute_num]);
			}
			totalRecomputedResult_num = Math.round((totalRecomputedResult_num * 1000)/endorsment4Scoring_array.length)/10;
			scoringResultVal_str = String(totalRecomputedResult_num) + "%";
		}

//		gotoAndPlay(9);

		var miniSubIndicator_num:Number;
		var miniSubIndicatorEnd_num:Number;
		var miniSubIndicator_str:String;
		miniSubIndicator_str = xmlItemDescription_obj_array[0]["tao:TEST"][0].attributes["rdf:ID"];
		miniSubIndicator_num = miniSubIndicator_str.indexOf("middleware/");
		if(miniSubIndicator_num != -1){
			miniSubIndicator_num += 11;
			miniSubIndicatorEnd_num = miniSubIndicator_str.indexOf(".rdf", miniSubIndicator_num);
			if(miniSubIndicatorEnd_num != -1){
				miniSubIndicator_str = miniSubIndicator_str.substr(miniSubIndicator_num, miniSubIndicatorEnd_num - miniSubIndicator_num);
			}
			else{
				miniSubIndicator_str = miniSubIndicator_str.substr(miniSubIndicator_num);
			}
		}
		switch(miniSubIndicator_str){
/*
			case "MoniqueReichertTests":
			{
//{index:currentItem_index, iso:currentTestLanguage_str, elapsedTime:vTmpElapsedTime};
				var ItemByLangTimeStat_array:Array = new Array();
				for(var vCpt=0;vCpt<itemByLangTime_array.length;vCpt++){
					var itemFound_bool:Boolean = false;
					var vTmpItemLangTimeEntry2_obj:Object = itemByLangTime_array[vCpt];
					for(var vRowCpt=0;vRowCpt<ItemByLangTimeStat_array.length;vRowCpt++){
						var vTmpItemLangTimeEntry1_obj:Object = ItemByLangTimeStat_array[vRowCpt];
						if(vTmpItemLangTimeEntry1_obj.index==vTmpItemLangTimeEntry2_obj.index){
							if(vTmpItemLangTimeEntry1_obj.iso==vTmpItemLangTimeEntry2_obj.iso){
								itemFound_bool = true;
								var vIndex_tmp = vTmpItemLangTimeEntry1_obj.index;
								var vIso_tmp = vTmpItemLangTimeEntry1_obj.iso;
								var vElapsTime = vTmpItemLangTimeEntry1_obj.elapsedTime + vTmpItemLangTimeEntry2_obj.elapsedTime;
								var vNbVisit = vTmpItemLangTimeEntry1_obj.visit + 1;
								var newRecord_obj = {index:vIndex_tmp, iso:vIso_tmp, elapsedTime:vElapsTime, visit:vNbVisit};
								ItemByLangTimeStat_array[vRowCpt] = newRecord_obj;
							}
						}
					}
					if(itemFound_bool == false){
						var vNewIndex_tmp = vTmpItemLangTimeEntry2_obj.index;
						var vNewIso_tmp = vTmpItemLangTimeEntry2_obj.iso;
						var vNewElapsTime = vTmpItemLangTimeEntry2_obj.elapsedTime;
						var vNewNbVisit = 1;
						var newRecordToAdd_obj = {index:vNewIndex_tmp, iso:vNewIso_tmp, elapsedTime:vNewElapsTime, visit:vNewNbVisit};
						ItemByLangTimeStat_array.push(newRecordToAdd_obj);
					}
				}
				var ItemByLangTimeFinal_array:Array = new Array();
				for(var vRowCpt=0;vRowCpt<ItemByLangTimeStat_array.length;vRowCpt++){
					var vTmpItemLangTimeEntry1_obj:Object = ItemByLangTimeStat_array[vRowCpt];
					var vIndex_tmp = vTmpItemLangTimeEntry1_obj.index;
					var vNewIndex_txt:String = new String("");
					vNewIndex_txt = vNewIndex_txt.concat("000",String(vIndex_tmp));
					vNewIndex_txt = vNewIndex_txt.slice(-3);
					var vIso_tmp = vTmpItemLangTimeEntry1_obj.iso;
					var vElapsTime = vTmpItemLangTimeEntry1_obj.elapsedTime;
					var vNbVisit = vTmpItemLangTimeEntry1_obj.visit;
					var newRecord_obj = {index:vNewIndex_txt, iso:vIso_tmp, elapsedTime:vElapsTime, visit:vNbVisit};
					ItemByLangTimeFinal_array[vRowCpt] = newRecord_obj;
				}
				result2Display_str = result2Display_str.concat("<br /><b>Some Statistics: </b><br />");
				ItemByLangTimeFinal_array.sortOn(["index","iso"]);
				for(var vRowCpt=0;vRowCpt<ItemByLangTimeFinal_array.length;vRowCpt++){
					var vTmpItemLangTimeEntry1_obj:Object = ItemByLangTimeFinal_array[vRowCpt]
					var vDisplayIndex_tmp:Number = parseInt(vTmpItemLangTimeEntry1_obj.index,10);
					vDisplayIndex_tmp = vDisplayIndex_tmp + 1; // display human index instead of computer array index
					var vDisplayIso_tmp = vTmpItemLangTimeEntry1_obj.iso;
					var vDisplayElapsTime = Math.round(vTmpItemLangTimeEntry1_obj.elapsedTime/1000);
					var vDisplayNbVisit = vTmpItemLangTimeEntry1_obj.visit;
					result2Display_str = result2Display_str.concat("<br />  - <b>Question:</b> ", vDisplayIndex_tmp,", ", string(vDisplayNbVisit)," visit(s) for the language <b>", vDisplayIso_tmp,"</b> that lasted ", string(vDisplayElapsTime)," s.");
				}
				result2Display_str = result2Display_str.concat("<br /><br /><b>Elapsed Time by Language: </b><br /><br />");
				ItemByLangTimeFinal_array.sortOn(["iso"]);
				var totTimeElapsedOnOneLang_num = 0;
				var lastISOcodeReviewed_str = "";
				for(var vRowCpt=0;vRowCpt<ItemByLangTimeFinal_array.length;vRowCpt++){
					var vTmpItemLangTimeEntry1_obj:Object = ItemByLangTimeFinal_array[vRowCpt]
					var vDisplayIso_tmp = vTmpItemLangTimeEntry1_obj.iso;
					var vDisplayElapsTime = vTmpItemLangTimeEntry1_obj.elapsedTime;
					if((vDisplayIso_tmp != lastISOcodeReviewed_str) && (lastISOcodeReviewed_str != "")){
						totTimeElapsedOnOneLang_num = Math.round(totTimeElapsedOnOneLang_num/1000);
						result2Display_str = result2Display_str.concat("  - Testee spent <b>", totTimeElapsedOnOneLang_num," s.</b> in <b>", lastISOcodeReviewed_str,"</b> language.<br />");
						lastISOcodeReviewed_str = vDisplayIso_tmp;
						totTimeElapsedOnOneLang_num = vDisplayElapsTime;
					}
					else{
						lastISOcodeReviewed_str = vDisplayIso_tmp;
						totTimeElapsedOnOneLang_num += vDisplayElapsTime;
					}
				}
				totTimeElapsedOnOneLang_num = Math.round(totTimeElapsedOnOneLang_num/1000);
				result2Display_str = result2Display_str.concat("  - Testee spent <b>", totTimeElapsedOnOneLang_num," s.</b> in <b>", lastISOcodeReviewed_str,"</b> language.<br />");
				result2Display_str = result2Display_str.concat("<br /><br /><b>Final Score: </b>", scoringResultVal_str, "<br />");
				break;
			}
*/
			default:
			{
				result2Display_str = result2Display_str.concat("<br /><b>Final Score: </b>", scoringResultVal_str);
			}
		}
		var result2Display_tmp_txt = "";
		result2Display_tmp_txt += result2Display_str;
		trace("Result: " + result2Display_tmp_txt);

		_root.resultOutput_str = result2Display_str;
		_root.finalScore_str = scoringResultVal_str;
        res.addTestbehavior("displayedResult",escape(result2Display_str));
//		if(posFormIT_num > -1){
//			_level0.noresult=1;
//			_level0.formit=1;
//		}
//		removeMovieClip(_level0.test["test" + currentTestLanguage_str].xul);
        return res;
	}

	public function setLang(pLang:String):Void{
		trace("tao_test: Change language requested for " + pLang);
		var callBackFunction_fct:String = "this.afterSetLang";
		var callBackFunction_fct_arg:String = pLang;
		_level0.requestedLang_str = pLang;
		var callBack_obj:Object = {callBackFct:callBackFunction_fct, callBackArg:callBackFunction_fct_arg};
		callBackFunction_fct_array.push(callBack_obj);
		_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "getItemContext");
	}

	public function afterSetLang(){
		trace("tao_test: afterSetLang called");
		var pLang:String = arguments[0];
/*
		var vTmpElapsedTime = getTimer() - lastLangModifTime;
		var vTmpItemLangTimeEntry_obj:Object = {index:currentItem_index, iso:currentTestLanguage_str, elapsedTime:vTmpElapsedTime};
		itemByLangTime_array.push(vTmpItemLangTimeEntry_obj);
		lastLangModifTime = getTimer();
*/
		if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc != undefined){
			trace("tao_test: an item is already loaded and has to be unloaded with its consent")
			item_mcl.unloadClip(_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc);
			if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.item != undefined){
			    removeMovieClip(_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.item);
			}
		}
		if (_level0.test["test" + currentTestLanguage_str].xul != undefined){
			trace("tao_test: a test GUI is already loaded for language " + currentTestLanguage_str + " and has to be hidden")
			_level0.test["test" + currentTestLanguage_str]._visible = false;
		}
		// finding the index of the requested language
		for(var vCpt=0;vCpt<getLanguages();vCpt++){
			var vTmpLanguageEntry_obj:Object = languagesLookup_array[vCpt];
			var vLangISOcode_str:String = new String(vTmpLanguageEntry_obj.iso);
			if(vLangISOcode_str == pLang){
				currentLanguage_index = vCpt;
				break;
			}
		}
		currentTestLanguage_str = pLang;
		trace("tao_test: the test GUI for the requested language has to be shown")
		_level0.test["test" + currentTestLanguage_str]._visible = true;
		if (_level0.test["test" + currentTestLanguage_str].xul == undefined){
			trace("tao_test: the test GUI for the requested language does not exist yet and so has to be built on language index " + currentLanguage_index)
			var xulNode_str:String = xulLookupResolve(xmlTestDescription_xml_array[currentLanguage_index],xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:TESTPRESENTATION"][0]["xul"][0].data);
			var xulNodeTmp:XML = new XML(xulNode_str);
			var vNewGUIObj:Object = new XUL2Test(test_mc["test" + currentTestLanguage_str],base_mc).parseXML(xulNodeTmp);
			_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.createEmptyMovieClip("itemSWFcontainer_mc",0);
			_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc._lockroot=true;
		}
		_level0.test4tao.loadAnItem();
	}

	public function getCurrentItem(){
		trace("tao_test: ENTERING getCurrentItem");
		return currentItem_index;
	}

	public function getItemsList(){
		trace("tao_test: ENTERING getItemsList");
		return xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"];
	}

	public function subscribeOnCurrentItemChanged(listenerRef_obj:Object){
		trace("tao_test: ENTERING subscribeOnCurrentItemChanged");
		var alreadySubscribed_bool:Boolean;
		alreadySubscribed_bool = false;
		for(var vCpt=0;vCpt<_root.currentItemChangedListeners_array.length;vCpt++){
			if(_root.currentItemChangedListeners_array[vCpt] == listenerRef_obj){
				alreadySubscribed_bool = true;
			}
		}
		if(!alreadySubscribed_bool){
			_root.currentItemChangedListeners_array.push(listenerRef_obj);
		}
	}

	public function loadAnItem(){
		trace("tao_test: ENTERING loadAnItem");
		var listenerRef_obj;
		trace("Ready for dispatch with currentItemChangedListeners_array.length = " + _root.currentItemChangedListeners_array.length);
		for(var vCpt=0;vCpt<_root.currentItemChangedListeners_array.length;vCpt++){
			listenerRef_obj = _root.currentItemChangedListeners_array[vCpt];
			trace("Dispatch currentItemChangedListeners_array[" + vCpt + "] = " + listenerRef_obj);
			listenerRef_obj.somethingChanged();
		}
		_level0.currentInquiry_num = 0;
		_level0.aBroadcaster.dispatchXulEvent(this,"updateProgressStatusDisplay", "");
		// we create a movie clip to be replaced by the loaded item
		mclListener = new Object();
//		var mclListener:Object = new Object();
		mclListener.onLoadError = function(target_mc:MovieClip, errorCode:String, httpStatus:Number) {
			trace("tao_test: ERROR LOADING");
			_level0.test4tao.loadAnItem();
		}
		mclListener.onLoadInit = function(target_mc:MovieClip) {
			var itemXMLfile:String;
			item_mcl.removeListener(mclListener);
			itemXMLfile = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"][currentItem_index].data;
			trace("tao_test: itemXmlFile " + itemXMLfile + " information sent thru communication channel");

			var contextHolder_obj:Object;
			var itemContextData_xml:XML;
			for(var vCpt=0;vCpt<itemContextHolders_array.length;vCpt++){
				var vTmpContext_obj:Object = itemContextHolders_array[vCpt];
				var vIndex:Number = new Number(vTmpContext_obj.contextIndex);
				if(vIndex == currentItem_index){
					contextHolder_obj = itemContextHolders_array[vCpt];
					itemContextData_xml = contextHolder_obj.context;
					break;
				}
			}

			if(itemContextData_xml != undefined){
				_level0.persistItemContextData = itemContextData_xml;
				var vItemContext_str:String = itemContextData_xml.toString();
				if(vItemContext_str.length > 40000){
					trace("tao_test: setItemContextString needed : " + vItemContext_str.length);
					var vItemContextChunk_str:String = "";
					var vItemContextLen_num:Number = vItemContext_str.length;
					var vItemContextLenLoop_num:Number = 0;
					vItemContextLenLoop_num = Math.ceil(vItemContextLen_num / 40000);
					for(var vCpt_num:Number = 0;vCpt_num < (vItemContextLenLoop_num - 1);vCpt_num++){
						trace("tao_test: setItemContextString loop " + vCpt_num);
						vItemContextChunk_str = vItemContext_str.substr(vCpt_num * 40000,40000);
						vItemContextChunk_str = "#MoRe2cOmE#" + vItemContextChunk_str;
						_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "setItemContextString", vItemContextChunk_str);
					}
					trace("tao_test: setItemContextString loop ends");
					vItemContextChunk_str = vItemContext_str.substr(vCpt_num * 40000,40000);
					_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "setItemContextString", "hello");
				}
				else{
					trace("tao_test: setItemContextString NOT needed : " + vItemContext_str.length);
					_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "setItemContext", itemContextData_xml);
				}
			}
			var currentItemSequence_str:String = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"][currentItem_index].attributes.Sequence;
			_level0.persistItemXMLfile = itemXMLfile;
			_level0.persistItemSequence = currentItemSequence_str;
			_level0.communicationChannel_T2I_test_lc.send("lc_test2item", "setItemXmlFile", itemXMLfile, currentItemSequence_str);
			var vMaxItems = (xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"].length - 1);
			if(currentItem_index == vMaxItems){
                _level0.finishTestTimer();
			}
			if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.nextItem_button != undefined){
				_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.nextItem_button.enabled = true;
				if(currentItem_index == vMaxItems){
					_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.nextItem_button.visible = false;
					_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.testLanguages_box._visible = false;
				}
			}
			if (_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.prevItem_button != undefined){
				if(currentItem_index == 0){
					_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.prevItem_button.visible = false;
				}
				else{
					_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.prevItem_button.visible = true;
				}
				if(currentItem_index == vMaxItems){
					_level0.test["test" + currentTestLanguage_str].xul.testContainer_box.prevItem_button.visible = false;
				}
			}
		};
		item_mcl = new MovieClipLoader();
		item_mcl.addListener(mclListener);
		var itemModel_str = xmlItemDescription_obj_array[currentLanguage_index]["tao:TEST"][0]["tao:CITEM"][currentItem_index].attributes["itemModel"];
		if(itemModel_str == undefined){
			itemModel_str = "tao_item.swf";
		}
		item_mcl.loadClip(itemModel_str, _level0.test["test" + currentTestLanguage_str].xul.testContainer_box.itemContainer_box.itemSWFcontainer_mc);
	}

	public function tao_test(target_mc:MovieClip, depth_num:Number, x_num:Number, y_num:Number, testDescFile_str:String, testDB_str:String){
		callBackFunction_fct_array = new Array();
		// load the XML root file
		var xmlRootTest_xml:XML = new XML(); // XML content of the root file that is the table of content of the XML test files
		xmlRootTest_xml.ignoreWhite = true;
		base_mc = target_mc;
		savedItemSeq_num = undefined;
		xmlRootTest_xml.onLoad = function(success) {
			if(success){
				// file loading succeeded
				trace("tao_test: xml root test file successfully loaded");
				// build XML test description
				xmlTestDescription_xml = new XML();
//				buildTestDescription(xmlTestDescription_xml,xmlRootTest_xml);
				buildTestDescription(xmlTestDescription_xml,this);
				// fill in the test description object
				test_mc = target_mc.createEmptyMovieClip("test",depth_num);
				test_mc._x = x_num;
				test_mc._y = y_num;
//				xmlTestDescription_obj = new XML2Object(test_mc).parseXML(xmlRootTest_xml);
				xmlTestDescription_obj = new XML2Object(test_mc).parseXML(this);
				// complete the test description with the localized definition (different languages)
				xmlItemDescription_obj_array = new Array();
				var vLimit = xmlTestDescription_obj["tao:TEST"][0]["tao:TESTcontent"].length;
				for(var vCpt=0;vCpt<vLimit;vCpt++){
					// filling the structure containing the different available languages
					var vLangISOcode_str:String;
					vLangISOcode_str = "";
					vLangISOcode_str = getLanguage(vCpt);
					var vTmpLanguageEntry_obj:Object = {index:vCpt, iso:vLangISOcode_str};
					languagesLookup_array.push(vTmpLanguageEntry_obj);
					// creation of a particular test movie clip for each language
					test_mc.createEmptyMovieClip("test" + vLangISOcode_str,vCpt);
					test_mc["test" + vLangISOcode_str]._x = 0;
					test_mc["test" + vLangISOcode_str]._y = 0;
					// get the localized test data that have to be appended to the test data structure
					var xmlLocalizedTest_str:String = xmlTestDescription_obj["tao:TEST"][0]["tao:TESTcontent"][vCpt].data;
					for(var vAdjustmentCpt=0;vAdjustmentCpt<getLanguages();vAdjustmentCpt++){
						var aTestLangISOcode_str:String;
						aTestLangISOcode_str = xmlTestDescription_xml.childNodes[0].childNodes[vAdjustmentCpt].attributes["lang"];
						if(vLangISOcode_str == aTestLangISOcode_str){
							break;
						}
					}
					xmlLocalizedTest_str = xmlLocalizedTest_str.concat(".xml");
					// updating the test data structure; if it is the last update, the GUI must be built for the first language
					buildItemDescription(xmlLocalizedTest_str,xmlTestDescription_xml.childNodes[0].childNodes[vAdjustmentCpt].childNodes[0],(vCpt == (vLimit - 1)));
				}
			}
			else {
				// file loading failed
				trace("tao_test: xml root test file loading failed");
			}
		};
		xmlRootTest_xml.load(testDescFile_str);
	}

	private static function getLanguages():Number{
		return xmlTestDescription_obj["tao:TEST"][0]["tao:TESTcontent"].length;
	}

	private static function getLanguage(index_num:Number):String{
		return xmlTestDescription_obj["tao:TEST"][0]["tao:TESTcontent"][index_num].attributes["lang"];
	}

	public static function main(target_mc:MovieClip, depth_num:Number, x_num:Number, y_num:Number, testDescFile_str:String, testDB_str:String):tao_test{
		var test:tao_test = new tao_test(target_mc, depth_num, x_num, y_num, testDescFile_str, testDB_str);
		return(test);
	}
}
