// import of the v2 UI components packages of Flash MX
import mx.controls.*;
import mx.managers.PopUpManager;
import flash.geom.Rectangle;
import flash.external.*;
import lu.tao.utils.tao_toolbox;
import com.xfactorstudio.xml.xpath.*;
import it.sephiroth.XML2Object;
import lu.tao.XUL2SWF.XUL2Item;
import lu.tao.utils.Event;
import lu.tao.tao_scoring.tao_COMPLEX;

class item_hawai{
	private var base_mc:MovieClip;
	private var itemDescFile_str:String;
	private var itemSequence_str:String;
	private var item:MovieClip; // graphic UI container
	private var xmlItemDescription_xml:XML;
	private var xmlItemDescription_obj:Object;
	private var currentItemLanguage_str:String; // language that is selected for the item (forwarded to items when possible)
	private var currentLanguage_index:Number;
	private var languagesLookup_array:Array;
	private var currentItem_index:Number;
	private var vTmpIndex:Number;
	private var inquiryPlace_rect:Rectangle;

	private function xulLookupResolve(xpathTarget_xml:XML,initialXUL_str:String):String {
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
						var xpathPart:String = my_toolbox.extractString(baseResult,baseResult.substr(workIndex3,6),")",0,false);
						var returnVal_array = XPath.selectNodes(xpathTarget_xml,xpathPart);
						var returnVal_xml:XML = returnVal_array[0];
						if(returnVal_xml.firstChild.nodeType == 3){
							finalResult = returnVal_xml.firstChild.nodeValue;
						}
						else {
							for(var vNodesCpt = 0;vNodesCpt<returnVal_xml.childNodes.length;vNodesCpt++){
								finalResult = finalResult.concat(returnVal_xml.childNodes[vNodesCpt].toString());
							}
						}
						trace("tao_item: XPath expression encountered " + xpathPart + " with link to " + returnVal_array); // + " and result " + finalResult);
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

	public function buildItemDescription (xmlSource_xml:XML,xmlExtend_xml:XML):Void {
		var nodeExtend_xmlnode:XMLNode = xmlExtend_xml.firstChild.cloneNode(true);
		xmlSource_xml.appendChild(nodeExtend_xmlnode);
		trace("tao_item: xmlItemDescription enhanced");
		trace("tao_item: initial item GUI container building");
		var xulNode_str:String = this.xulLookupResolve(xmlItemDescription_xml,xmlItemDescription_obj["tao:ITEM"][0]["tao:ITEMPRESENTATION"][0]["xul"][0].data);
		var xulNode:XML = new XML(xulNode_str);
		var vTmpXULObj:Object = new XUL2Item(item,base_mc).parseXML(xulNode);
		// send to inquiryContainer_box the current xul inquiry to load
//		var vTmpIndex:Number = 0;
		var currentItem_obj = this;
		var inquiryPlace = this;
		if(currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box != undefined){
			trace("tao_item: INFO: inquiryContainer_box exists and can nest inquiries");
			// we save the reference to the mc object where we want to nest
			inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		}

//		var startDepth:Number = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box._childNextDepth;
		var lastMaxYBound_num:Number = 0;
		var lastMaxXBound_num:Number = 0;
// all the values of the following variables may be overwritten by the attributes (width, height, spanWidth)
// specified in tao:DISPLAYALLINQUIRIES tag - for each variable, the attribute's name is included the comment
		var rectDisplayWidth_num:Number = 885; // width - of visible rectangle
		var rectDisplayHeight_num:Number = 550; // height - "   "       "
		var rectSpanWidth_num:Number = 885; // spanWidth - scrollable width (inside the rect)
		var rectSpanHeight_num:Number = 550; // spanHeight -    "     height    "      "
		var vScrollWidth_num:Number = 15; // vScrollWidth - vertical scrollbar width
		var vScrollHeight_num:Number = 550; // vScrollHeight -  "        "     height
		var vScrollLeft_num:Number = 895; // vScrollLeft -      "        "     x position
		var vScrollTop_num:Number = 110; // vScrollTop -        "        "     y    "
		var hScrollWidth_num:Number = 885; // hScrollWidth - idem above
		var hScrollHeight_num:Number = 15; // hScrollHeight
		var hScrollLeft_num:Number = 10; // hScrollLeft
		var hScrollTop_num:Number = 658; // hScrollTop
		var rectVerticalDisplayMode_str:String = "auto"; // verticalMode - "auto" = position of inquiries is automatic computed; "manual" = do it yourself (DIY)
		var rectHorizontalDisplayMode_str:String = "manual"; // horizontalMode - idem above
		var rectVerticalScroll_bool:Boolean = true; // verticalScroll - set to "false" if you don't want scrollbar
		var rectHorizontalScroll_bool:Boolean = false; // horizontalScroll - idem for horizontal scrollbar
// NB1: for verticalMode and horizontalMode = "auto" -> nearly everything is computed recursively in XUL2Item
// NB2:to be compliant with taotab sent by ppl, the line 181 is placed wrongly (it should be 2 lines before)

		var result_str:String;
//		var ggbFileURL_str:String;
//		var flashID_str:String;
//		result_str = "" + ExternalInterface.call("getFlashId");
//		flashID_str = result_str;
//		ggbFileURL_str = "" + xmlItemDescription_obj["tao:ITEM"][0]["tao:GEOGEBRA"][0].attributes.stimulus;
//		ExternalInterface.call("resizeElement", flashID_str, 1000, 300);
//		ExternalInterface.call("toGeogebraOpenFile", ggbFileURL_str);
		//result_str = String();

		if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].data == "on"){
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.width != undefined){
				var rectDisplayWidth_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.width;
				rectDisplayWidth_num = parseInt(rectDisplayWidth_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.height != undefined){
				var rectDisplayHeight_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.height;
				rectDisplayHeight_num = parseInt(rectDisplayHeight_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.verticalMode != undefined){
				rectVerticalDisplayMode_str = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.verticalMode;
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.horizontalMode != undefined){
				rectHorizontalDisplayMode_str = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.horizontalMode;
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.spanWidth != undefined){
				var rectSpanWidth_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.spanWidth;
				rectSpanWidth_num = parseInt(rectSpanWidth_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.spanHeight != undefined){
				var rectSpanHeight_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.spanHeight;
				rectSpanHeight_num = parseInt(rectSpanHeight_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.verticalScroll != undefined){
				var verticalScroll_str:String = new String(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.verticalScroll);
				rectVerticalScroll_bool = (verticalScroll_str.toUpperCase() == "FALSE")?false:true;
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.horizontalScroll != undefined){
				var horizontalScroll_str:String = new String(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.horizontalScroll);
				rectHorizontalScroll_bool = (horizontalScroll_str.toUpperCase() == "TRUE")?true:false;
			}
			inquiryPlace_rect = new Rectangle(0, 0, rectDisplayWidth_num, rectDisplayHeight_num);
			inquiryPlace.scrollRect = inquiryPlace_rect;
			_root.inquiryPlace_ref = inquiryPlace;
		}
		for(var vCpt=0;vCpt<getInquiries();vCpt++){
//			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vCpt].attributes["order"] == currentItem_index){  //currentItem_obj.getCurrentItemIndex()){
//				trace("=================== currentItem_index = " + currentItem_index);
//				vTmpIndex = currentItem_index;
//			}
			var local_mc:MovieClip;
			inquiryPlace.createEmptyMovieClip("inquiry" + vCpt,inquiryPlace._childNextDepth);
			inquiryPlace._childNextDepth += 1;
			local_mc = inquiryPlace["inquiry" + vCpt];
			local_mc._result_array = new Array();
			var tmpResult = base_mc.getInquiryValues(vCpt);
//			local_mc._result_array.push(tmpResult);
			local_mc._result_array = tmpResult;
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].data != "on"){
				local_mc._visible = false;
				trace("tao_item: Inquiry" + vCpt + " created but invisible");
			}
			local_mc._childNextDepth = 1; // local XUL depth (levels) management
			var xulInquiryNode_str:String = xulLookupResolve(xmlItemDescription_xml,xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vCpt]["tao:INQUIRYDESCRIPTION"][0]["tao:HASPRESENTATIONLAYER"][0]["xul"][0].data);
			var xulInquiryNode:XML = new XML(xulInquiryNode_str);
			var vTmpInquiryXULObj:Object = new XUL2Item(local_mc,base_mc).parseXML(xulInquiryNode);
			local_mc._y = 0;
			local_mc._x = 0;
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].data == "on"){
				if(rectVerticalDisplayMode_str == "auto"){
					lastMaxYBound_num += local_mc.xul._maxYBound;
					trace("Alerte! _maxYBound = " + local_mc.xul._maxYBound + " -> lastMaxYBound_num = " + lastMaxYBound_num);
					local_mc._y = lastMaxYBound_num;
				}
				if(rectHorizontalDisplayMode_str == "auto"){
					local_mc._x = lastMaxXBound_num;
					lastMaxXBound_num += local_mc.xul._maxXBound;
					trace("Alerte! _maxXBound = " + local_mc.xul._maxXBound + " -> lastMaxXBound_num = " + lastMaxXBound_num);
				}
			}
		}
		if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].data == "on"){
			lastMaxYBound_num += 100;
			lastMaxXBound_num += 100;
			var scrollPlace_mc = inquiryPlace._parent;
			var scrollPlaceLayer_num = scrollPlace_mc._parent._childNextDepth + 123;
			trace("Scrollbar level = " + scrollPlaceLayer_num);
			trace("inquiryPlace = " + inquiryPlace);
			trace("inquiryPlace height = " + inquiryPlace._height);
			trace("scrollPlace_mc = " + scrollPlace_mc);
			trace("scrollPlace_mc height = " + scrollPlace_mc._height);

			scrollPlace_mc._parent._childNextDepth = scrollPlaceLayer_num + 1;
			scrollPlace_mc.createClassObject(mx.controls.UIScrollBar, "my_vsb", scrollPlaceLayer_num++);
			if((lastMaxYBound_num - rectDisplayHeight_num) > 0){
				scrollPlace_mc.my_vsb.setScrollProperties(20, 0, lastMaxYBound_num - rectDisplayHeight_num);
				scrollPlace_mc.my_vsb._visible = rectVerticalScroll_bool;
				scrollPlace_mc._maxVScrollVal = lastMaxYBound_num - rectDisplayHeight_num;
			}
			else{
trace("PING: " + rectSpanHeight_num + " - " + rectDisplayHeight_num + " + " + rectVerticalScroll_bool);
    			if((rectSpanHeight_num - rectDisplayHeight_num) > 0){
    				scrollPlace_mc.my_vsb.setScrollProperties(20, 0, rectSpanHeight_num - rectDisplayHeight_num);
    				scrollPlace_mc.my_vsb._visible = rectVerticalScroll_bool;
    				scrollPlace_mc._maxVScrollVal = rectSpanHeight_num - rectDisplayHeight_num;
    			}
    			else{
    				scrollPlace_mc.my_vsb._visible = false;
    			}
			}
			scrollPlace_mc._parent._childNextDepth = scrollPlaceLayer_num + 1;
			scrollPlace_mc.createClassObject(mx.controls.UIScrollBar, "my_hsb", scrollPlaceLayer_num++);
			if((lastMaxXBound_num - rectDisplayWidth_num) > 0){
				scrollPlace_mc.my_hsb.setScrollProperties(20, 0, lastMaxXBound_num - rectDisplayWidth_num);
				scrollPlace_mc.my_hsb._visible = rectHorizontalScroll_bool;
				trace("H Scrollbar visible = " + rectHorizontalScroll_bool);
			}
			else{
    			if((rectSpanWidth_num - rectDisplayWidth_num) > 0){
    				scrollPlace_mc.my_hsb.setScrollProperties(20, 0, rectSpanWidth_num - rectDisplayWidth_num);
    				scrollPlace_mc.my_hsb._visible = rectHorizontalScroll_bool;
    				scrollPlace_mc._maxHScrollVal = rectSpanWidth_num - rectDisplayWidth_num;
    			}
    			else{
    				scrollPlace_mc.my_hsb._visible = false;
    				trace("H Scrollbar visible = " + rectHorizontalScroll_bool + " but lastMaxXBound_num = " + lastMaxXBound_num + " and rectDisplayWidth_num = " + rectDisplayWidth_num);
    			}
			}
			
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollWidth != undefined){
				var vScrollWidth_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollWidth;
				vScrollWidth_num = parseInt(vScrollWidth_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollHeight != undefined){
				var vScrollHeight_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollHeight;
				vScrollHeight_num = parseInt(vScrollHeight_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollLeft != undefined){
				var vScrollLeft_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollLeft;
				vScrollLeft_num = parseInt(vScrollLeft_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollTop != undefined){
				var vScrollTop_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.vScrollTop;
				vScrollTop_num = parseInt(vScrollTop_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollWidth != undefined){
				var hScrollWidth_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollWidth;
				hScrollWidth_num = parseInt(hScrollWidth_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollHeight != undefined){
				var hScrollHeight_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollHeight;
				hScrollHeight_num = parseInt(hScrollHeight_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollLeft != undefined){
				var hScrollLeft_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollLeft;
				hScrollLeft_num = parseInt(hScrollLeft_str);
			}
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollTop != undefined){
				var hScrollTop_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.hScrollTop;
				hScrollTop_num = parseInt(hScrollTop_str);
			}
			var wheelable_bool:Boolean = false;
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.wheelable != undefined){
				var wheelable_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:DISPLAYALLINQUIRIES"][0].attributes.wheelable;
				if(wheelable_str == "on"){
					wheelable_bool = true;
				}
			}
			scrollPlace_mc.my_vsb.move(vScrollLeft_num,vScrollTop_num);
			scrollPlace_mc.my_vsb.setSize(vScrollWidth_num,vScrollHeight_num);
			scrollPlace_mc.my_vsb._inquiryPlaceRef = inquiryPlace;
			scrollPlace_mc.my_hsb.horizontal = true;
			scrollPlace_mc.my_hsb.move(hScrollLeft_num,hScrollTop_num);
			scrollPlace_mc.my_hsb.setSize(hScrollWidth_num,hScrollHeight_num);
			scrollPlace_mc.my_hsb._inquiryPlaceRef = inquiryPlace;

			var vscroll_listener = new Object();
			vscroll_listener.scroll = function(eventObject:Object) {
//				trace("Scrollbar V Pos = " + eventObject.target.scrollPosition);
				var inquiryPlace = eventObject.target._inquiryPlaceRef;
				var scrollBox = inquiryPlace.scrollRect;
				scrollBox.y = eventObject.target.scrollPosition;
				inquiryPlace.scrollRect = scrollBox;
			};
			scrollPlace_mc.my_vsb.addEventListener("scroll", vscroll_listener);
			
			var hscroll_listener = new Object();
			hscroll_listener.scroll = function(eventObject:Object) {
//				trace("Scrollbar H Pos = " + eventObject.target.scrollPosition);
				var inquiryPlace = eventObject.target._inquiryPlaceRef;
				var scrollBox = inquiryPlace.scrollRect;
				scrollBox.x = eventObject.target.scrollPosition;
				inquiryPlace.scrollRect = scrollBox;
			};
			scrollPlace_mc.my_hsb.addEventListener("scroll", hscroll_listener);

			if(wheelable_bool){
				var mouseListener:Object = new Object();
				mouseListener.onMouseWheel = function(delta) {
					trace("onMouseWheel Pos progress " + delta);
					trace("onMouseWheel _root.inquiryPlace_ref.scrollRect " + _root.inquiryPlace_ref.scrollRect);
					if(_root.inquiryPlace_ref.scrollRect != undefined){
						var scrollBox = _root.inquiryPlace_ref.scrollRect;
						var scrollPlace_mc = _root.inquiryPlace_ref._parent;
						if(delta > 0){
							if((scrollPlace_mc.my_vsb.scrollPosition - delta) < 0){
								scrollPlace_mc.my_vsb.scrollPosition = 0;
							}
							else{
								scrollPlace_mc.my_vsb.scrollPosition -= delta;
							}
						}
						if(delta < 0){
							if((scrollPlace_mc.my_vsb.scrollPosition - delta) > scrollPlace_mc._maxVScrollVal){
								scrollPlace_mc.my_vsb.scrollPosition = scrollPlace_mc._maxVScrollVal;
							}
							else{
								scrollPlace_mc.my_vsb.scrollPosition -= delta;
							}
						}
						scrollBox.y = scrollPlace_mc.my_vsb.scrollPosition;
						_root.inquiryPlace_ref.scrollRect = scrollBox;
					}
				}
				Mouse.addListener(mouseListener);
			}

		}

//		currentItem_index = 0;
//		inquiryPlace["inquiry" + vTmpIndex]._visible = true;

// tao_HAWAI : set focus on the right frame in the stimulus
		var vStartingFrame_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][0].attributes["startingFrame"];
		if((vStartingFrame_str != undefined) && (vStartingFrame_str != "undefined") && (vStartingFrame_str != "")){
			_root.era_rte_snapshotRef_str = "";
			_root.era_rte_starting_frame = vStartingFrame_str;
		}

		var vItemDuration = Number(xmlItemDescription_obj["tao:ITEM"][0]["tao:DURATION"][0].data);
		if(isNaN(vItemDuration) == false){
			_root.itemCountDown_num = vItemDuration;
			var tmpXULNode_str:String = new String(xulNode_str);
			if(tmpXULNode_str.indexOf("listen.swf") != -1){
				trace("tao_item.as: listen.swf will call _root.startItemCountDownTimer");
				// it's the end of listen.swf that will start the timer
			}
			else{
				trace("tao_item.as: call to _root.startItemCountDownTimer");
//				trace("parent"+_parent._name);
//				trace("_parent._parent"+_parent._parent.name);
//				trace("base_mc"+base_mc);
				//_root.startItemCountDownTimer();
				var timerPlace_mc:MovieClip;
				base_mc.createEmptyMovieClip("timerPlace",54321);
				timerPlace_mc = base_mc.timerPlace;
				timerPlace_mc._childNextDepth = 1;
				timerPlace_mc._x = 600;
				timerPlace_mc._y = 0;
				var xulTimerNode:XML = new XML("<xul><image disabled=\"false\" src=\"countdown.swf?countdownStart=" + String(_root.itemCountDown_num) + "&onEndAction=_level0.nextItem\" left=\"0\" top=\"0\" width=\"40\" height=\"40\"/></xul>");
				var vTmpTimerXULObj:Object = new XUL2Item(timerPlace_mc,base_mc).parseXML(xulTimerNode);
			}
		}
		else{
			trace("NO ITEM DURATION detected");
		}

		inquiryPlace["inquiry" + currentItem_index]._visible = true;
		trace("tao_item#: Inquiry" + currentItem_index + " is now visible");

		if(currentItem_index == 0){
			trace("prevButt disabled on frame 5");
			_root._prevInquiry_button_ref.gotoAndStop("disabled");
		}
		else{
			trace("prevButt enabled on frame 1");
			_root._prevInquiry_button_ref.gotoAndStop("up");
		}

		base_mc.baseTimer_num = _level0.getTestGlobalTimer();
		var currentTimer_str = String(base_mc.baseTimer_num);
		base_mc.feedTrace("START","TEST_TIME=" + currentTimer_str,"taoHAWAI");
	}

	public function goUp(Void):Void{
		trace("tao_item: goUp button triggered");
    	var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		var scrollBox = inquiryPlace.scrollRect;
		scrollBox.y -= 10;
		inquiryPlace.scrollRect = scrollBox;
	}
	public function goDown(Void):Void{
		trace("tao_item: goDown button triggered");
    	var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		var scrollBox = inquiryPlace.scrollRect;
		scrollBox.y += 10;
		inquiryPlace.scrollRect = scrollBox;
	}

	private function getInquiries():Number{
		return xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"].length;
	}

	public function getCurrentInquiryIndex():Number{
		return xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][currentItem_index].attributes["order"];
	}

	public function gotoInquiry(inquiryNumber:Number):Void{
		trace("tao_item: Inquiry direct access button triggered");
    	var currentItem_obj = this;
//		var vTmpIndex:Number = 0;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
// is target inquiry allowed
// yes:
//		get current inquiry info and save them
//		load previous inquiry
//		reload subject's response if possible
		inquiryPlace["inquiry" + currentItem_index]._visible = false;

// ## START ######################################################################################################################
		trace("tao_item: STIMULUS gotoInquiry EndTask with currentItem_index='" + currentItem_index + "'");
		_root.era_stimulus_ref.EndTask(currentItem_index);
// ### END #######################################################################################################################

		trace("tao_item: Inquiry" + currentItem_index + " is now hidden");
		currentItem_index = inquiryNumber - 1;
		for(var vCpt=0;vCpt<getInquiries();vCpt++){
			if(xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vCpt].attributes["order"] == inquiryNumber){
				vTmpIndex = vCpt;
			}
		}
		inquiryPlace["inquiry" + vTmpIndex]._visible = true;
// tao_HAWAI : set focus on the right frame in the stimulus
		var vStartingFrame_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vTmpIndex].attributes["startingFrame"];
		if((vStartingFrame_str != undefined) && (vStartingFrame_str != "undefined") && (vStartingFrame_str != "")){
			_root.era_rte_starting_frame = vStartingFrame_str;
			_root.era_rte_snapshotRef_str = (inquiryPlace["inquiry" + vTmpIndex]._snapshotRef != undefined) ? inquiryPlace["inquiry" + vTmpIndex]._snapshotRef : "" ;
			trace("tao_item: STIMULUS gotoInquiry stimulusGotoFrame with vStartingFrame_str='" + vStartingFrame_str + "'");
			_root.doAction(_root,"stimulusGotoFrame",vStartingFrame_str);
		}
		trace("tao_item: Inquiry" + vTmpIndex + " is now visible");
// no:
//		warning and nothing else
	}

	public function prevInquiry(Void):Void{
		trace("tao_item: Previous inquiry button triggered");
    	var currentItem_obj = this;
//		var vTmpIndex:Number = currentItem_index - 1;
		vTmpIndex = currentItem_index - 1;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
// is previous inquiry allowed
// yes:
//		does previous inquiry exist?
		if(inquiryPlace["inquiry" + vTmpIndex] != undefined){
//		yes:
//			get current inquiry info and save them
//			load previous inquiry
//			reload subject's response if possible

// ## START ######################################################################################################################
			trace("tao_item: STIMULUS prevInquiry EndTask with currentItem_index='" + currentItem_index + "'");
			_root.era_stimulus_ref.EndTask(currentItem_index);
// ### END #######################################################################################################################
		}
//		no:
//			do nothing
		else {
// for HAWAI, next line is not useful
//			_root.communicationChannel_I2T_item_lc.send("lc_item2test", "prevItem");
			//_level0.prevItem();
		}
// no:
//		warning and nothing else
	}

	public function inquirySynch(){
		trace("tao_item: inquirySynch entered with vTmpIndex=" + vTmpIndex + " and currentItem_index=" + currentItem_index);
    	var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		inquiryPlace["inquiry" + currentItem_index]._visible = false;
		inquiryPlace["inquiry" + vTmpIndex]._visible = true;
		currentItem_index = vTmpIndex;
		if(currentItem_index == 0){
			trace("prevButt disabled on frame 5");
			_root._prevInquiry_button_ref.gotoAndStop("disabled");
		}
		else{
			trace("prevButt enabled on frame 1");
			_root._prevInquiry_button_ref.gotoAndStop("up");
		}
		var vNoBackButton_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][currentItem_index].attributes["noback"];
		if((vNoBackButton_str == "on") || (vNoBackButton_str == "true") || (vStartingFrame_str == "yes")){
			trace("prevButt disabled by noback");
			_root._prevInquiry_button_ref.gotoAndStop("disabled");
		}
// tao_HAWAI : set focus on the right frame in the stimulus
		var vStartingFrame_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][currentItem_index].attributes["startingFrame"];
		if((vStartingFrame_str != undefined) && (vStartingFrame_str != "undefined") && (vStartingFrame_str != "")){
			_root.era_rte_starting_frame = vStartingFrame_str;
			_root.era_rte_snapshotRef_str = (inquiryPlace["inquiry" + currentItem_index]._snapshotRef != undefined) ? inquiryPlace["inquiry" + currentItem_index]._snapshotRef : "" ;
			trace("tao_item: STIMULUS prevInquiry stimulusGotoFrame with vStartingFrame_str='" + vStartingFrame_str + "'");
			_root.doAction(_root,"stimulusGotoFrame",vStartingFrame_str);
		}
		_level0.hideTransition();
	}

	public function nextInquiry_confirmed(){
		trace("tao_item: nextInquiry_confirmed invoqued");
//		_root._nextInquiry_button_ref._visible = true;
//		_root.communicationChannel_I2T_item_lc.send("lc_item2test", "startTestTimer");
		nextInquiry_overlay();
	}

	public function forceNextItemAllowed(Void):Void{
    	var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		inquiryPlace.inquiry0._answered = "yes";
		trace("tao_item: forceNextItemAllowed invoqued");
	}

	public function preventNextItemAllowed(Void):Void{
    	var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		inquiryPlace.inquiry0._answered = "no";
		trace("tao_item: preventNextItemAllowed invoqued");
	}

	public function isNextItemAllowed(Void):Boolean{
		var vTotalInquiries_num:Number = getInquiries();
		var vAnswered_num:Number = 0;
		var returnVal_bool:Boolean = false;
    	var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		for(var vCpt=0;vCpt<vTotalInquiries_num;vCpt++){
			if(inquiryPlace["inquiry" + vCpt]._answered == "yes"){
				vAnswered_num++;
			}
		}
		if(vAnswered_num == vTotalInquiries_num){
			returnVal_bool = true;
		}
		trace("tao_item: isNextItemAllowed returned: " + returnVal_bool + "(" + String(vAnswered_num) + "/" + String(vTotalInquiries_num) + ")");
		return(returnVal_bool);
	}

	public function saveScoreAndSnapshotRef(targetInquiry_num:Number,scoringValue_str:String,snapshotReferenceValue_str:String):Void{
		trace("tao_item: saveScoreAndSnapshotRef invoqued with vTmpIndex=" + vTmpIndex);
		trace("tao_item: saveScoreAndSnapshotRef invoqued with currentItem_index=" + currentItem_index);
		trace("tao_item: saveScoreAndSnapshotRef invoqued with targetInquiry=" + targetInquiry_num);
		trace("tao_item: saveScoreAndSnapshotRef invoqued with scoringValue=" + scoringValue_str);
		trace("tao_item: saveScoreAndSnapshotRef invoqued with snapshotRef=" + snapshotReferenceValue_str);
    	var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		if(inquiryPlace["inquiry" + targetInquiry_num] != undefined){
			inquiryPlace["inquiry" + targetInquiry_num]._scoringValue = scoringValue_str;
			inquiryPlace["inquiry" + targetInquiry_num]._snapshotRef = snapshotReferenceValue_str;
		}
		if(inquiryPlace["inquiry" + vTmpIndex] != undefined){
			inquirySynch();
		}
		else{
			_root.feedTrace("END","END","stimulus");
//					_root.finishMePlease();
			_root.communicationChannel_I2T_item_lc.send("lc_item2test", "nextItem");
		}
	}

	public function nextInquiry_overlay(Void):Void{
		trace("tao_item: nextInquiry_overlay invoqued");
    	var currentItem_obj = this;
//		var vTmpIndex:Number = currentItem_index + 1;
		vTmpIndex = currentItem_index + 1;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;

		_level0.currentInquiry_num = vTmpIndex;

// is next inquiry allowed
// yes:
//		does next inquiry exist?
		if(inquiryPlace["inquiry" + vTmpIndex] != undefined){
//		yes:
//			get current inquiry info and save them
//			load next inquiry
//			reload subject's response if possible

// ## START ######################################################################################################################
			trace("tao_item.fla: NOT the last inquiry STIMULUS call to EndTask with inquiry:" + currentItem_index);
			_root.era_stimulus_ref.EndTask(currentItem_index);
//			_root.era_stimulus_ref.StopTask(currentItem_index);
// ### END #######################################################################################################################
/*
			inquiryPlace["inquiry" + currentItem_index]._visible = false;
			inquiryPlace["inquiry" + vTmpIndex]._visible = true;
			currentItem_index = vTmpIndex;
// tao_HAWAI : set focus on the right frame in the stimulus

			var vStartingFrame_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][currentItem_index].attributes["startingFrame"];
			if((vStartingFrame_str != undefined) && (vStartingFrame_str != "undefined") && (vStartingFrame_str != "")){
				_root.era_rte_starting_frame = vStartingFrame_str;
				_root.era_rte_snapshotRef_str = (inquiryPlace["inquiry" + currentItem_index]._snapshotRef != undefined) ? inquiryPlace["inquiry" + currentItem_index]._snapshotRef : "" ;
				trace("tao_item: STIMULUS nextInquiry_overlay stimulusGotoFrame with vStartingFrame_str='" + vStartingFrame_str + "'");
				_root.doAction(_root,"stimulusGotoFrame",vStartingFrame_str);
			}
			if(currentItem_index == 0){
				trace("prevButt disabled on frame 5");
				_root._prevInquiry_button_ref.gotoAndStop("disabled");
			}
			else{
				trace("prevButt enabled on frame 1");
				_root._prevInquiry_button_ref.gotoAndStop("up");
			}

			var vNoBackButton_str:String = xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][currentItem_index].attributes["noback"];
			if((vNoBackButton_str == "on") || (vNoBackButton_str == "true") || (vStartingFrame_str == "yes")){
				trace("prevButt disabled by noback");
				_root._prevInquiry_button_ref.gotoAndStop("disabled");
			}
//			ExternalInterface.call("hideTransition", "");
_level0.hideTransition();
*/
		}
//		no:
//			do nothing here and take your chance on the next item
		else {
// ## START ######################################################################################################################
			if(_root.era_stimulus_ref.EndTask != undefined){
				trace("tao_item.fla: last inquiry STIMULUS call to EndTask with inquiry:" + currentItem_index);
				_root.era_stimulus_ref.EndTask(currentItem_index);
			}
			else{
				_level0.nextItem();				
			}
// ### END #######################################################################################################################
//			_root.communicationChannel_I2T_item_lc.send("lc_item2test", "nextItem");
//_level0.hideTransition();
		}
// no:
//		warning and nothing else
	}

	public function nextInquiry(Void):Void{
		trace("tao_item: Next inquiry button triggered");

		_level0.showTransition();

		var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;

		var vLocalIndex_num:Number = currentItem_index + 1;

		trace("_root.era_stimulus_ref.openConfirmationPopup = " + _root.era_stimulus_ref.openConfirmationPopup);
		if((_level0.confirmationNeeded_bool) && (_root.era_stimulus_ref.openConfirmationPopup != undefined) && (inquiryPlace["inquiry" + vLocalIndex_num] == undefined)){
			trace("openConfirmationPopup entered");
			trace("calling openConfirmationPopup()");
			_root.era_stimulus_ref.openConfirmationPopup();
		}
		else{
			if((_level0.confirmationNeeded_bool) && (_root.era_stimulus_ref.openPSConfirmationPopup != undefined) && (inquiryPlace["inquiry" + vLocalIndex_num] == undefined)){
				trace("calling openPSConfirmationPopup()");
/*
				var confirmationType_str:String;
				var vTmpIndex_num:Number = vLocalIndex_num + 1;
				var nextInquiryLabel_str:String = "inquiry" + String(vTmpIndex_num);
				confirmationType_str = (inquiryPlace[nextInquiryLabel_str] != undefined) ? "TASK" : "UNIT";
				_root.era_stimulus_ref.openPSConfirmationPopup(confirmationType_str);
*/
				_root.era_stimulus_ref.openPSConfirmationPopup();
			}
			else{
				trace("openConfirmationPopup is NOT entered");
				_root.item4tao.nextInquiry_confirmed();
			}
		}
	}

	function item_hawai(target_mc:MovieClip, depth_num:Number, x_num:Number, y_num:Number, pItemDescFile_str:String, itemSeq_str:String){
		// load the parameters
		base_mc = target_mc;
		this.itemDescFile_str = new String(pItemDescFile_str);
		this.itemSequence_str = itemSeq_str;
		this.currentItem_index = base_mc.getCurrentItemIndex();//0;
		this.currentLanguage_index = 0;
		this.languagesLookup_array = new Array();
		// create the foundation movie clip
		this.item = target_mc.createEmptyMovieClip("item",target_mc.getNextHighestDepth()); //depth_num);
		this.item._x = x_num;
		this.item._y = y_num;
	}

	public function getItemContext():XML{
//		return xmlItemDescription_obj["tao:ITEM"][0]["tao:ITEMcontent"].length;
		var itemContext_xml:XML = new XML();
		var itemContext_str:String;

//	<itemContext>
		var nodeItemContext_xml:XMLNode = itemContext_xml.createElement("itemContext");
		itemContext_xml.appendChild(nodeItemContext_xml);
		nodeItemContext_xml.attributes.Sequence = itemSequence_str;

//		<itemXmlFile>
		var nodeItemXmlFile_xml:XMLNode = itemContext_xml.createElement("itemXmlFile");
		var nodeItemXmlFile_txt:XMLNode = itemContext_xml.createTextNode(this.itemDescFile_str);
		nodeItemContext_xml.appendChild(nodeItemXmlFile_xml);
		nodeItemXmlFile_xml.appendChild(nodeItemXmlFile_txt);

//		<itemRDFid>
		var nodeItemRDFid_xml:XMLNode = itemContext_xml.createElement("itemRDFid");
		var nodeItemRDFid_txt:XMLNode = itemContext_xml.createTextNode(xmlItemDescription_obj["tao:ITEM"][0].attributes["rdf:ID"]);
		nodeItemContext_xml.appendChild(nodeItemRDFid_xml);
		nodeItemRDFid_xml.appendChild(nodeItemRDFid_txt);

//		<itemItemLabel>
		var nodeItemLabel_xml:XMLNode = itemContext_xml.createElement("itemLabel");
		var nodeItemLabel_txt:XMLNode = itemContext_xml.createTextNode(xmlItemDescription_obj["tao:ITEM"][0]["rdfs:LABEL"][0].data);
		nodeItemContext_xml.appendChild(nodeItemLabel_xml);
		nodeItemLabel_xml.appendChild(nodeItemLabel_txt);

//		<itemItemComment>
		var nodeItemComment_xml:XMLNode = itemContext_xml.createElement("itemComment");
		var nodeItemComment_txt:XMLNode = itemContext_xml.createTextNode(xmlItemDescription_obj["tao:ITEM"][0]["rdfs:COMMENT"][0].data);
		nodeItemContext_xml.appendChild(nodeItemComment_xml);
		nodeItemComment_xml.appendChild(nodeItemComment_txt);

//		<itemTrace>
		var nodeItemTrace_xml:XMLNode = itemContext_xml.createElement("itemTrace");
		
		// was before Dipf change
		var nodeItemTrace_txt:XMLNode = itemContext_xml.createTextNode("*S*ITEMTRACE*" + base_mc.currentSubjectNameTracer_str + "*E*ITEMTRACE*");
//		var nodeItemTrace_txt:XMLNode = itemContext_xml.createTextNode(_root.tracedEvents_array.toString());
		
		nodeItemContext_xml.appendChild(nodeItemTrace_xml);
		nodeItemTrace_xml.appendChild(nodeItemTrace_txt);
/*
//		<itemListeners>
		var nodeItemListeners_xml:XMLNode = itemContext_xml.createElement("itemListeners");
		nodeItemContext_xml.appendChild(nodeItemListeners_xml);

//			<currentImage>
		var nodeCurrentImage_xml:XMLNode = itemContext_xml.createElement("currentImage");
		var imageTagTmp_xml:XML = new XML(xmlItemDescription_obj["tao:ITEM"][0]["tao:ITEMPRESENTATION"][0]["xul"][0].data);
		var currentImage_txt = imageTagTmp_xml.childNodes[0].childNodes[1].childNodes[1].attributes.src;
		var currentImage_str:String = new String(currentImage_txt);
		var nodeCurrentImage_txt:XMLNode = itemContext_xml.createTextNode(currentImage_str);
		nodeItemListeners_xml.appendChild(nodeCurrentImage_xml);
		nodeCurrentImage_xml.appendChild(nodeCurrentImage_txt);
*/
//		<inquiries>
		var nodeInquiries_xml:XMLNode = itemContext_xml.createElement("inquiries");
		nodeItemContext_xml.appendChild(nodeInquiries_xml);

//			<currentInquiry>
		var nodeCurrentInquiry_xml:XMLNode = itemContext_xml.createElement("currentInquiry");
		var currentItem_index_str:String = new String(String(this.currentItem_index));
		var nodeCurrentInquiry_txt:XMLNode = itemContext_xml.createTextNode(currentItem_index_str);
		nodeInquiries_xml.appendChild(nodeCurrentInquiry_xml);
		nodeCurrentInquiry_xml.appendChild(nodeCurrentInquiry_txt);

		var currentItem_obj = this;
		var inquiryPlace = currentItem_obj.item.xul.itemContainer_box.inquiryContainer_box;
		var itemResult_bool:Boolean = true;

		for(var vCpt=0;vCpt<getInquiries();vCpt++){

//			<inquiryX>
			var vInquiryX_str:String = new String("inquiry" + vCpt);
			var nodeInquiryX_xml:XMLNode = itemContext_xml.createElement(vInquiryX_str);
			nodeInquiries_xml.appendChild(nodeInquiryX_xml);

//				<inquiryEndorsment>
			var local_mc:MovieClip;
			local_mc = inquiryPlace[vInquiryX_str];
			var nodeInquiryEndorsment_xml:XMLNode = itemContext_xml.createElement("inquiryEndorsment");
			nodeInquiryX_xml.appendChild(nodeInquiryEndorsment_xml);

			var vEndorsment_str:String = new String("");
/*
			if(inquiryPlace["inquiry" + vCpt]._result_matrix != undefined){
				for(var vRowIndex=0;vRowIndex<inquiryPlace["inquiry" + vCpt]._result_matrix.length;vRowIndex++){
					var vRow_obj:Object = inquiryPlace["inquiry" + vCpt]._result_matrix[vRowIndex];
					if((vRow_obj.groupName == undefined) && (vRow_obj.propValue != "#READONLY#")){
						vEndorsment_str = vEndorsment_str.concat(escape(vRow_obj.selected),"[:]");
					}
					else{
						vEndorsment_str = vEndorsment_str.concat(escape(vRow_obj.selected));
					}
				}
				if(vEndorsment_str.substr(-3,3) == "[:]"){
					vEndorsment_str = vEndorsment_str.substr(0,-3);
				}
			}
			var nodeInquiryEndorsment_txt:XMLNode = itemContext_xml.createTextNode(vEndorsment_str);
			nodeInquiryEndorsment_xml.appendChild(nodeInquiryEndorsment_txt);
*/

/*
			var local_mc:MovieClip;
			local_mc = inquiryPlace[vInquiryX_str];
			var nodeInquiryEndorsment_xml:XMLNode = itemContext_xml.createElement("inquiryEndorsment");
			nodeInquiryX_xml.appendChild(nodeInquiryEndorsment_xml);

			var vEndorsment_str:String = new String("");
			if(inquiryPlace["inquiry" + vCpt]._result_matrix != undefined){
				for(var vRowIndex=0;vRowIndex<inquiryPlace["inquiry" + vCpt]._result_matrix.length;vRowIndex++){
					var vRow_obj:Object = inquiryPlace["inquiry" + vCpt]._result_matrix[vRowIndex];
					var vTmpFormator_str:String = vRow_obj.selected;
					if(vTmpFormator_str.indexOf("TEXTFORMAT ") != -1){
						var my_toolbox:tao_toolbox = new tao_toolbox();
						vTmpFormator_str = my_toolbox.stripTag(vTmpFormator_str);
					}
					vEndorsment_str = vEndorsment_str.concat(vTmpFormator_str,"[:]");
//					if(vRow_obj.groupName == "propositions_radiogroup"){
//						vEndorsment_str = vEndorsment_str.concat(vRow_obj.selected);
//					}
				}
				if(vEndorsment_str.substr(0,-3) == "[:]"){
					vEndorsment_str = vEndorsment_str.substr(0,-3);
				}
			}

			if((vEndorsment_str == "") && (inquiryPlace["inquiry" + vCpt]._result_array != undefined)){
				vEndorsment_str = "";
				for(var vRowIndex=0;vRowIndex<inquiryPlace["inquiry" + vCpt]._result_array.length;vRowIndex++){
					var vRow_obj:Object = inquiryPlace["inquiry" + vCpt]._result_array[vRowIndex];
					var vTmpFormator_str:String = vRow_obj.propValue;
					if(vTmpFormator_str.indexOf("TEXTFORMAT ") != -1){
						var my_toolbox:tao_toolbox = new tao_toolbox();
						vTmpFormator_str = my_toolbox.stripTag(vTmpFormator_str);
					}
					vEndorsment_str = vEndorsment_str.concat(vTmpFormator_str,"[:]");
				}
				if(vEndorsment_str.substr(0,-3) == "[:]"){
					vEndorsment_str = vEndorsment_str.substr(0,-3);
				}
			}

			var nodeInquiryEndorsment_txt:XMLNode = itemContext_xml.createTextNode(vEndorsment_str);
			nodeInquiryEndorsment_xml.appendChild(nodeInquiryEndorsment_txt);
*/
//				<inquiryHasAnswer>
			var nodeInquiryHasAnswer_xml:XMLNode = itemContext_xml.createElement("inquiryHasAnswer");
			nodeInquiryX_xml.appendChild(nodeInquiryHasAnswer_xml);
			var nodeInquiryHasAnswer_txt:XMLNode = itemContext_xml.createTextNode(xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vCpt]["tao:INQUIRYDESCRIPTION"][0]["tao:HASANSWER"][0].data);
			nodeInquiryHasAnswer_xml.appendChild(nodeInquiryHasAnswer_txt);

//					_root.era_rte_snapshotRef_str = arguments[3];

//				<inquirySnapshotRef>
			var nodeInquirySnapshotRef_xml:XMLNode = itemContext_xml.createElement("inquirySnapshotRef");
			nodeInquiryX_xml.appendChild(nodeInquirySnapshotRef_xml);
			var nodeSnapshotRefVal_str:String;
			if(inquiryPlace["inquiry" + vCpt]._snapshotRef != undefined){
				nodeSnapshotRefVal_str = inquiryPlace["inquiry" + vCpt]._snapshotRef;
			}
			else{
				nodeSnapshotRefVal_str = "";
			}
			var nodeInquirySnapshot_txt:XMLNode = itemContext_xml.createTextNode(nodeSnapshotRefVal_str);
			nodeInquirySnapshotRef_xml.appendChild(nodeInquirySnapshot_txt);

//				<inquiryHasAnswerLabel>
			var nodeInquiryHasAnswerLabel_xml:XMLNode = itemContext_xml.createElement("inquiryHasAnswerLabel");
			nodeInquiryX_xml.appendChild(nodeInquiryHasAnswerLabel_xml);
			var hasAnswerIndex_str:String = new String(xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vCpt]["tao:INQUIRYDESCRIPTION"][0]["tao:HASANSWER"][0].data);
			var hasAnswerIndex_num = hasAnswerIndex_str.indexOf("1");
			var hasAnswerIndex_plus_un_num = hasAnswerIndex_num + 1;
			var toMemorize;
			toMemorize = inquiryPlace["inquiry" + vCpt].xul.inquiryContainer_box.propositions_box.propositions_radiogroup["proposition_" + hasAnswerIndex_plus_un_num + "_radio"].resultLabel;

			var nodeInquiryHasAnswerLabel_txt:XMLNode = itemContext_xml.createTextNode(toMemorize);
			nodeInquiryHasAnswerLabel_xml.appendChild(nodeInquiryHasAnswerLabel_txt);

// here are the different Evaluation rules algorithms
			var typeEvaluation_txt = xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vCpt]["tao:INQUIRYDESCRIPTION"][0]["tao:EVALUATIONRULE"][0].data;
			var typeEvaluation_str:String = new String(typeEvaluation_txt);
			typeEvaluation_str = typeEvaluation_str.toUpperCase();
			if(typeEvaluation_str.indexOf(".") != -1){
				typeEvaluation_str = typeEvaluation_str.substr(0,typeEvaluation_str.indexOf("."));
			}
//			trace("typeEvaluation_str = '" + typeEvaluation_str + "'");
			if(typeEvaluation_str == undefined){
				typeEvaluation_str = "AND";
			}
			var vInquiryAnswer_str:String = new String(xmlItemDescription_obj["tao:ITEM"][0]["tao:INQUIRY"][vCpt]["tao:INQUIRYDESCRIPTION"][0]["tao:HASANSWER"][0].data);
			var vTagToEval = "";
			if(typeEvaluation_str.indexOf("_") != -1){
				vTagToEval = typeEvaluation_str.substr(0,typeEvaluation_str.indexOf("_"));
			}
			else{
				vTagToEval = typeEvaluation_str;
			}
			switch(vTagToEval){
				case "AND":{
					var vInquiryAnswerLen_num:Number;
					var vEndorsmentTrimmedToAnswerLen_str:String;
					vInquiryAnswerLen_num = vInquiryAnswer_str.length;
					trace("tao_item: vInquiryAnswerLen_num = " + vInquiryAnswerLen_num);
					vEndorsmentTrimmedToAnswerLen_str = vEndorsment_str.substr(0,vInquiryAnswerLen_num);
					if(vEndorsmentTrimmedToAnswerLen_str != vInquiryAnswer_str){
						itemResult_bool = false;
					}
					else{
						itemResult_bool = true;
					}
					break;
				}
				case "INTERVAL":
				case "MATCH":
				case "COMPLEX":{
					// TODO - The COMPLEX matching algorithm is still to be implemented
//var endorsement_str:String = '{ {{ MATCH(TRIMSTRING(TRIMSTRING(inquiry_1_interaction_0, "0 ", "LEFT"), " ", "RIGHT"),"~1~") } &&{ MATCH(TRIMSTRING(TRIMSTRING(inquiry_1_interaction_2, "0 ", "LEFT"), " ", "RIGHT"),"~35~") }}||  {{ MATCH(TRIMSTRING(TRIMSTRING(inquiry_1_interaction_0, "0 ", "LEFT"), " ", "RIGHT"),"") } &&   { MATCH(TRIMSTRING(TRIMSTRING(inquiry_1_interaction_2, "0 ", "LEFT"), " ", "RIGHT"),"~95~") }} }';
//var endorsement_str:String = '{{ MATCH(TRIMSTRING(TRIMSTRING(inquiry_1_interaction_0, "0 ", "LEFT"), " ", "RIGHT"),"~1~") } &&{ MATCH(TRIMSTRING(TRIMSTRING(inquiry_1_interaction_2, "0 ", "LEFT"), " ", "RIGHT"),"~35~") }}';
					itemResult_bool = new tao_COMPLEX(base_mc._widgetsRepository_array,vTagToEval).scoreThis(vInquiryAnswer_str);
					break;
				}
				case "M":{
					var workArray = new Array();
					var minimumGoodAnswers = 1;
					var vGoodAnswers = 0;
					workArray = typeEvaluation_str.split("_");
					if(workArray.length >= 2){
						minimumGoodAnswers = Number(workArray[1]);
					}
					itemResult_bool = false;
					var userAnswersArray = new Array();
					var goodAnswersArray = new Array();
					userAnswersArray = vEndorsment_str.split("");
					goodAnswersArray = vInquiryAnswer_str.split("");
					var maxCompareLength = 0;
					maxCompareLength = (userAnswersArray.length < goodAnswersArray.length) ? userAnswersArray.length : goodAnswersArray.length;
					for(var cpt=0;cpt<maxCompareLength;cpt++){
						if((userAnswersArray[cpt] == goodAnswersArray[cpt]) && (userAnswersArray[cpt] == "1")){
							vGoodAnswers++;
						}
					}
					if(vGoodAnswers >= minimumGoodAnswers){
						itemResult_bool = true;
					}
					break;
				}
				case "MNF":{
					var workArray = new Array();
					var minimumGoodAnswers = 1;
					var vGoodAnswers = 0;
					var aPrioriResult_bool = false;
					workArray = typeEvaluation_str.split("_");
					if(workArray.length >= 2){
						minimumGoodAnswers = Number(workArray[1]);
					}
					itemResult_bool = false;
					var userAnswersArray = new Array();
					var goodAnswersArray = new Array();
					userAnswersArray = vEndorsment_str.split("");
					goodAnswersArray = vInquiryAnswer_str.split("");
					var maxCompareLength = 0;
					maxCompareLength = (userAnswersArray.length < goodAnswersArray.length) ? userAnswersArray.length : goodAnswersArray.length;
					for(var cpt=0;cpt<maxCompareLength;cpt++){
						if((userAnswersArray[cpt] != goodAnswersArray[cpt]) && (userAnswersArray[cpt] == "1")){
							aPrioriResult_bool = false;
							break;
						}
						if((userAnswersArray[cpt] == goodAnswersArray[cpt]) && (userAnswersArray[cpt] == "1")){
							aPrioriResult_bool = true;
							vGoodAnswers++;
						}
					}
					if((aPrioriResult_bool) && (vGoodAnswers >= minimumGoodAnswers)){
						itemResult_bool = true;
					}
					break;
				}
				case "NA":{
					itemResult_bool = true;
					break;
				}
				case "OR":{
					var aPrioriResult_bool = false;
					itemResult_bool = false;
					var userAnswersArray = new Array();
					var goodAnswersArray = new Array();
					userAnswersArray = vEndorsment_str.split("");
					goodAnswersArray = vInquiryAnswer_str.split("");
					var maxCompareLength = 0;
					maxCompareLength = (userAnswersArray.length < goodAnswersArray.length) ? userAnswersArray.length : goodAnswersArray.length;
					for(var cpt=0;cpt<maxCompareLength;cpt++){
						if((userAnswersArray[cpt] == "1") || (goodAnswersArray[cpt] == "1")){
							aPrioriResult_bool = true;
							break;
						}
					}
					if(aPrioriResult_bool){
						itemResult_bool = true;
					}
					break;
				}
				case "ORX":{
					var aPrioriResult_bool = false;
					itemResult_bool = false;
					var userAnswersArray = new Array();
					var goodAnswersArray = new Array();
					userAnswersArray = vEndorsment_str.split("");
					goodAnswersArray = vInquiryAnswer_str.split("");
					var maxCompareLength = 0;
					maxCompareLength = (userAnswersArray.length < goodAnswersArray.length) ? userAnswersArray.length : goodAnswersArray.length;
					for(var cpt=0;cpt<maxCompareLength;cpt++){
						if(userAnswersArray[cpt] == "1"){
							aPrioriResult_bool = true;
							break;
						}
					}
					if(aPrioriResult_bool == false){
						aPrioriResult_bool = true;
						for(var cpt=0;cpt<maxCompareLength;cpt++){
							if(goodAnswersArray[cpt] == "1"){
								aPrioriResult_bool = false;
								break;
							}
						}
					}
					if(aPrioriResult_bool){
						itemResult_bool = true;
					}
					break;
				}
				case "STIMULUS":{
					trace("tao_item: STIMULUS matching on inquiry " + vCpt);
					trace("tao_item: STIMULUS matching based on " + _root.era_rte_itemScore_array[vCpt]);
//					var my_toolbox:tao_toolbox = new tao_toolbox();
					var vResultFromStimulus_str:String = _root.era_rte_itemScore_array[vCpt];
// Normal Way - START

					if(vResultFromStimulus_str == "true"){
						itemResult_bool = true;
					}
					else{
						itemResult_bool = false;
					}

// Normal Way - END

// Simplified Way - START - only the last inquiry result is considered - works if only STIMILUS inquiries
/*
					if(vResultFromStimulus_str == "false"){
						itemResult_bool = false;
					}
					else{
						itemResult_bool = true;
					}
*/
// Simplified Way - END
					break;
				}
				case "XOR":{
					var aPrioriResult_bool = true;
					itemResult_bool = false;
					var userAnswersArray = new Array();
					var goodAnswersArray = new Array();
					userAnswersArray = vEndorsment_str.split("");
					goodAnswersArray = vInquiryAnswer_str.split("");
					var maxCompareLength = 0;
					maxCompareLength = (userAnswersArray.length < goodAnswersArray.length) ? userAnswersArray.length : goodAnswersArray.length;
					for(var cpt=0;cpt<maxCompareLength;cpt++){
						if(userAnswersArray[cpt] == goodAnswersArray[cpt]){
							aPrioriResult_bool = false;
							break;
						}
					}
					if(aPrioriResult_bool){
						itemResult_bool = true;
					}
					break;
				}
				default:
				{
					itemResult_bool = false;
					// type of evaluation not handled yet
				}
			}

			vEndorsment_str = (itemResult_bool == true) ? "1" : "0";
			var nodeInquiryEndorsment_txt:XMLNode = itemContext_xml.createTextNode(vEndorsment_str);
			nodeInquiryEndorsment_xml.appendChild(nodeInquiryEndorsment_txt);

//				<inquiryValues>
			var nodeInquiryValues_xml:XMLNode = itemContext_xml.createElement("inquiryValues");
			nodeInquiryX_xml.appendChild(nodeInquiryValues_xml);

			if(inquiryPlace["inquiry" + vCpt]._result_array != undefined){
				for(var vRowIndex=0;vRowIndex<inquiryPlace["inquiry" + vCpt]._result_array.length;vRowIndex++){

//					<inquiryValue>
					var nodeInquiryValue_xml:XMLNode = itemContext_xml.createElement("inquiryValue");
					nodeInquiryValues_xml.appendChild(nodeInquiryValue_xml);
					var vRow_obj:Object = inquiryPlace["inquiry" + vCpt]._result_array[vRowIndex];

//						<name>
					var nodeName_xml:XMLNode = itemContext_xml.createElement("name");
					var nodeName_txt:XMLNode = itemContext_xml.createTextNode(vRow_obj.name);
					nodeInquiryValue_xml.appendChild(nodeName_xml);
					nodeName_xml.appendChild(nodeName_txt);

//						<selected>
					var nodeSelected_xml:XMLNode = itemContext_xml.createElement("selected");
					var nodeSelected_txt:XMLNode = itemContext_xml.createTextNode(vRow_obj.selected);
					nodeInquiryValue_xml.appendChild(nodeSelected_xml);
					nodeSelected_xml.appendChild(nodeSelected_txt);

//						<groupName>
					var nodeGroupName_xml:XMLNode = itemContext_xml.createElement("groupName");
					var nodeGroupName_txt:XMLNode = itemContext_xml.createTextNode(vRow_obj.groupName);
					nodeInquiryValue_xml.appendChild(nodeGroupName_xml);
					nodeGroupName_xml.appendChild(nodeGroupName_txt);

//						<inquiryName>
					var nodeInquiryName_xml:XMLNode = itemContext_xml.createElement("inquiryName");
					var nodeInquiryName_txt:XMLNode = itemContext_xml.createTextNode("inquiry" + vCpt);
					nodeInquiryValue_xml.appendChild(nodeInquiryName_xml);
					nodeInquiryName_xml.appendChild(nodeInquiryName_txt);

//						<propValue>
					var nodePropValue_xml:XMLNode = itemContext_xml.createElement("propValue");
					var nodePropValue_txt:XMLNode = itemContext_xml.createTextNode(vRow_obj.propValue);
					nodeInquiryValue_xml.appendChild(nodePropValue_xml);
					nodePropValue_xml.appendChild(nodePropValue_txt);
				}

			}
			else {
				var nodeInquiryValues_txt:XMLNode = itemContext_xml.createTextNode("");
				nodeInquiryValues_xml.appendChild(nodeInquiryValues_txt);
			}

//				<inquiryListeners>
			var nodeInquiryListeners_xml:XMLNode = itemContext_xml.createElement("inquiryListeners");
			nodeInquiryX_xml.appendChild(nodeInquiryListeners_xml);

			if(inquiryPlace["inquiry" + vCpt]._result_array != undefined){
				for(var vRowIndex=0;vRowIndex<inquiryPlace["inquiry" + vCpt]._result_array.length;vRowIndex++){

//					<inquiryListener>
					var nodeInquiryListener_xml:XMLNode = itemContext_xml.createElement("inquiryListener");
					nodeInquiryListeners_xml.appendChild(nodeInquiryListener_xml);
					var vRow_obj:Object = inquiryPlace["inquiry" + vCpt]._result_array[vRowIndex];
//						<listenerName>
					var nodeListenerName_xml:XMLNode = itemContext_xml.createElement("listenerName");
//					var nodeListenerName_txt:XMLNode = itemContext_xml.createTextNode("inquiry" + vCpt);
					var nodeListenerName_txt:XMLNode = itemContext_xml.createTextNode(vRow_obj.name);
					nodeInquiryListener_xml.appendChild(nodeListenerName_xml);
					nodeListenerName_xml.appendChild(nodeListenerName_txt);
//						<listenerValue>
					var nodeListenerValue_xml:XMLNode = itemContext_xml.createElement("listenerValue");
					var nodeListenerValue_txt:XMLNode = itemContext_xml.createTextNode(vRow_obj.propValue);
					nodeInquiryListener_xml.appendChild(nodeListenerValue_xml);
					nodeListenerValue_xml.appendChild(nodeListenerValue_txt);
				}
			}
		}

//		<itemEndorsmentsResult>
		var itemResult_str:String = new String("");
		if (itemResult_bool){
			itemResult_str = "1";
		}
		else {
			itemResult_str = "0";
		}

		var nodeItemEndorsmentsResult_xml:XMLNode = itemContext_xml.createElement("itemEndorsmentsResult");
		var nodeItemEndorsmentsResult_txt:XMLNode = itemContext_xml.createTextNode(itemResult_str);
		nodeItemContext_xml.appendChild(nodeItemEndorsmentsResult_xml);
		nodeItemEndorsmentsResult_xml.appendChild(nodeItemEndorsmentsResult_txt);

		itemContext_str = itemContext_xml.toString();
	
		base_mc.feedTrace("END","END","taoHAWAI");

		var returnedItemContext_xml:XML = new XML(itemContext_str);
		return returnedItemContext_xml;
	}

	public function setItemContext(itemContext_xml:XML):Void{
//		return xmlItemDescription_obj["tao:ITEM"][0]["tao:ITEMcontent"][index_num].attributes["lang"];
	}

	public function garbageCollectAll():Boolean{
		trace("garbageCollectAll entered");
		var currentItem_obj = this;
		var vLimit:Number = currentItem_obj.base_mc._widgetsRepository_array.length;
		for(var vCpt=0; vCpt<vLimit; vCpt++){
			var thisObj_obj:Object = currentItem_obj.base_mc._widgetsRepository_array.pop();
			var vObjRef = thisObj_obj.objRef;
			var vObjType = thisObj_obj.objType;
			var vXulType = thisObj_obj.xulType;
			trace("-----> " + vObjRef._name + " of type " + vObjType + " with signature " + vXulType + " should be released");
			switch(vObjType)
			{
				case "movieClip" :
				{
					vObjRef.onUnload = function () {
						trace("   *-> " + this + " unloaded");
						var vRef = this;
						if(vRef._name == "xul"){
							if(vRef._parent._name == "item"){
								trace("OK garbage collection found xul root layer");

		delete currentItem_obj.base_mc._widgetsRepository_array;
		delete itemDescFile_str;
		delete itemSequence_str;
		delete xmlItemDescription_xml;
		delete xmlItemDescription_obj;
		delete currentItemLanguage_str;
		delete currentLanguage_index;
		delete languagesLookup_array;
		delete currentItem_index;

//		removeMovieClip(base_mc);
//		delete base_mc;

		removeMovieClip(item);
		delete item;

		_root.finishMePlease();
							}
						}
						removeMovieClip(vRef);
						delete vRef;
					};
					vObjRef.swapDepths(800000);
					vObjRef.unloadMovie();
					trace("   --> " + vObjRef._name + " of type " + vObjType + " with signature " + vXulType + " should unload");
					break;
				}
				case "imageMovieClip" :
				{
					vObjRef.onUnload = function () {
						trace("   *-> " + this + " unloaded");
						var vRef = this;
						vRef.destroyObject();
						removeMovieClip(vRef);
						delete vRef;
					};
					vObjRef.swapDepths(800000);
					vObjRef.stopson();
					vObjRef.clearTime();
					vObjRef.unloadMovie();
					trace("   --> " + vObjRef._name + " of type " + vObjType + " with signature " + vXulType + " should unload");
					break;
				}
				case "component" :
				{
					vObjRef.onUnload = function () {
						trace("   *-> " + this + " unloaded");
						var vRef = this;
						vRef.destroyObject();
						removeMovieClip(vRef);
						delete vRef;
					};
					vObjRef.swapDepths(800000);
					vObjRef.unloadMovie();
					vObjRef._parent.unloadClip(vObjRef);
					trace("   --> " + vObjRef._name + " of type " + vObjType + " with signature " + vXulType + " should unload");
					break;
				}
				default :
				{
					trace("***--> " + vObjRef._name + " of type " + vObjType + " with signature " + vXulType + " encountered and maybe deleted");
					delete vObjRef;
					break;
				}
			}
		}
		return(true);
	}

	public function main():Void{
		// load the XML root file
		var xmlRootItem_xml:XML = new XML(); // XML content of the root file that is the table of content of the XML item files
		xmlRootItem_xml.ignoreWhite = true;
    	var currentItem_obj = this;
				currentItem_obj.xmlItemDescription_xml = new XML();
				// fill in the item description object
				currentItem_obj.xmlItemDescription_obj = new XML2Object(currentItem_obj.item).parseXML(xmlRootItem_xml);
//				currentItem_obj.buildItemDescription(currentItem_obj.xmlItemDescription_xml,xmlRootItem_xml);
				var hawaiPlace_mc:MovieClip;
				base_mc.createEmptyMovieClip("hawaiPlace",54333);
				hawaiPlace_mc = base_mc.hawaiPlace;
				hawaiPlace_mc._childNextDepth = 1;
				hawaiPlace_mc._x = 0;
				hawaiPlace_mc._y = 0;
				var xulHawaiNode:XML = new XML("<xul><image disabled=\"false\" src=\"eXULiS.swf?file=" + _level0.currentItemRootLevel.itemXmlFile_str + "\" left=\"0\" top=\"0\"/></xul>");
				var vTmpHawaiXULObj:Object = new XUL2Item(hawaiPlace_mc,base_mc).parseXML(xulHawaiNode);
	}
}
