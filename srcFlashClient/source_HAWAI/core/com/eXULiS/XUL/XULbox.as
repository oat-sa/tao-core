

import com.eXULiS.XUL.XULelement;
// * import com.joangarnet.containers.SplitPane;
import com.eXULiS.lib.*;
import com.eXULiS.core.XML2Flash;
import com.xfactorstudio.xml.xpath.*;
import mx.utils.Delegate;

class com.eXULiS.XUL.XULbox extends XULelement {
	
	public var _obj:MovieClip;	
	public var maskClip_mc:MovieClip;
	public var content_ref:com.eXULiS.core.XML2Flash;
	public var guiFile_xul:XML;
	public var loadStatus_str:String;
	public var pathName_str:String;
	public var holdTitle_str:String;
	public var holdURL_str:String;
	public var fileSystm:FileSystem;
	public var ssheet:ItemSpreadSheetController;
	public var xliffSource_xml:XML;
	private var myScrollInterval;
	
	private var currentWidth;
	private var currentHeight;
	
	private var mytest="ok"
	
	private var intervalCount;
	private var callback_fct;
	private var box_ref;
	public var _group_array:Array;

	function XULbox(xulParent,xulDef:XMLNode){
	
		super(xulParent,xulDef);
		
		_defaultWidth = 100;
		_defaultHeight = 100;	
	}

	function create(){
		trace("XULbox (create): " + _type + " (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		trace("myREDIM BOX");	
		
		_objParent.createEmptyMovieClip(_objDef.attributes["id"], _objParent._childNextDepth);
		
		
		_obj = super.create(_obj,this,1);
		_obj.setStyle = this.setStyle;
	
		_obj = super.applyStyle(_obj);
		loadStatus_str = (_objDef.attributes["load"] == undefined) ? "immediate" : _objDef.attributes["load"];

				
		if (_objDef.attributes["oncommand"])
		{
		_obj.onPress=Delegate.create(this, onBoxEvent);
		}
		

			if (_objDef.attributes["buttons"])
			{
			this._targetExecutionLayer.buttonsMailer_str=_objDef.attributes["buttons"];
			}
		
		
		intervalCount = 0;
		
		if (_objDef.attributes["todisapear"] != undefined) {	
			if (_objDef.attributes["id"] == "r4") _root.r4ref =  _obj ;
			if (_objDef.attributes["id"] == "r5") _root.r5ref =  _obj ;
		}
			
		
		switch (_objDef.attributes["type"])
		{
			case "fstree":
			case "fslist":
			case "fshead":
			case "fsview":
			case "fsicons":
			case "fssort":
			{
				
				insertFileSystem();	
				break;
			}
			case "ssheet":
			{
				insertSpreadSheet();
				break;
			}
			case "content":
			{
				if(loadStatus_str == "onrequest"){
					//
				}
				else{
					
					nestContent();
				}
				break;
			}
			default:
				break;			
		}
		setLayout();
		onLoad();

		return _obj;
	}

	function registerGroupMember(aMember){
		trace("XULbox: registerGroupMember triggered");
		if(_group_array == undefined){
			_group_array = new Array();
		}
		_group_array.push(aMember);
	}

	function setGroupFocus(newLeader){
		trace("XULbox: setGroupFocus triggered");
		for(var vCpt_num:Number = 0;vCpt_num<_group_array.length;vCpt_num++){
			if(_group_array[vCpt_num] == newLeader){
				_group_array[vCpt_num].setState("checked");
				_level0.currentItemRootLevel.feedTrace("GLOBAL_VAR","graphid"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+newLeader.id,"service");
				var globalVars:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
				globalVars.data["graphViews_box"]=newLeader.id;
			}
			else{
				_group_array[vCpt_num].setState("not-checked");
			}
		}
	}

	function onBoxEvent(eventObj){
		trace("XULbox: fullpath: " + eventObj.target);
		for(var vCpt_num:Number = 0;vCpt_num < _actions.length;vCpt_num++){
			var vAction_obj:Object = _actions[vCpt_num];
			if(vAction_obj.type == "command"){
				toolbox.wrapRun(vAction_obj.action,this);
				
				trace("feedTrace for BOX_PRESS, Stimulus: " + "action"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+vAction_obj.action);
				_level0.currentItemRootLevel.feedTrace("BOX_PRESS","action"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+vAction_obj.action,"stimulus");
			}
		}
	}
	
	function onLoad()
	{
		for(var vCpt_num:Number = 0;vCpt_num < _actions.length;vCpt_num++){
			var vAction_obj:Object = _actions[vCpt_num];
			if(vAction_obj.type == "load"){
				trace("XUL2SWF: fullpath onload");
				toolbox.wrapRun(vAction_obj.action,this);
				trace("cellPress onLoad "+vAction_obj.action);
			}
		}
		
	}

	public function redimTabPanels()
	{
		var _tabsNb_nb:Number=this._targetExecutionLayer._objDefsRepository.retrieve("tabs-toolbar")._exulis.getTabsNumber();

		if (_tabsNb_nb<2)
		{				
			
			_obj._y=Number(_objDef.attributes["top"])-20;
			var _objHeight_nbr:Number=Number(_objDef.attributes["height"]);
			maskClip_mc._y=	_obj._y;
			maskClip_mc._height=_objHeight_nbr;
			updateScroll(_obj._height);
		} 
		else{
			var _objHeight_nbr:Number=Number(_objDef.attributes["height"]);
			maskClip_mc._y=	Number(_objDef.attributes["top"]);
			maskClip_mc._height=_objHeight_nbr-20;
			updateScroll(_obj._height);
		}
	}

	public function buildXliffHandler(success){
		trace("xliffHandler: file load entered with status: " + success);
		trace("xliffHandler src: " + this.xliffSource_xml.toString());
		var returnVal_array:Array = new Array();
		var tmp_str:String;
		returnVal_array = XPath.selectNodes(this.xliffSource_xml,"/xliff/file/body/trans-unit");
		for (var vCpt_num:Number = 0; vCpt_num < returnVal_array.length; vCpt_num++) {
			var aNode:XMLNode = returnVal_array[vCpt_num];
            trace("xliffHandler nemo: " + aNode.attributes.id+": "+aNode.firstChild.firstChild.nodeValue);
			for(var aSubNode:XMLNode = aNode.firstChild; aSubNode != null; aSubNode=aSubNode.nextSibling) {
				if (aSubNode.nodeName == "target") {
					tmp_str = String(aSubNode.firstChild.nodeValue);
/*
					var vtemp_str:String = "-";
					for(var vtmpcpt_num:Number = 0;vtmpcpt_num<tmp_str.length;vtmpcpt_num++){
						vtemp_str += tmp_str.charCodeAt(vtmpcpt_num) + "-";
					}
*/
					if(tmp_str.substr(-1) == "\n"){
						tmp_str = tmp_str.substr(0,-1);
					}
//					trace("xliffHandler val: " + vtemp_str + aSubNode.firstChild.nodeValue);
					_root._objXLIFFholder_obj[aNode.attributes.id] = tmp_str;
				}
			}
        }
		nestContentHandlerSeq();
	}

	public function nestContentHandlerSeq(){
		var renderedContent:Object;
		content_ref = new XML2Flash(this._obj, "_root");//String(this._targetExecutionLayer)); // _root.eXULiS.getParserRef()
		trace("XULbox (nestContent): buildGUI - definition to process: " + this.guiFile_xul.toString());
		var xpathQuery_str = "/black:manifest"; //"/black:Manifest";
		trace("XULbox (nestContentHandler): xpathQuery_str: " + xpathQuery_str);
		var blackManifest_xml = XPath.selectSingleNode(this.guiFile_xul, xpathQuery_str);

			if(_level0.currentItemRootLevel._u16_BLACK_dt_marker == true){
				trace("u16_BLACK_dt_marker_str with " + _level0.currentItemRootLevel._u16_BLACK_dt_marker_str);
				_level0.currentItemRootLevel._u16_BLACK_dt_marker = undefined;
				xpathQuery_str = "//item[@id='u16_item101']/@from";
				var tmpVal1_str:String = _root._objXLIFFholder_obj[XPath.selectSingleNode(this.guiFile_xul, xpathQuery_str).toString().substr(6)];
				xpathQuery_str = "//item[@id='u16_item102']/@from";
				var tmpVal2_str:String = _root._objXLIFFholder_obj[XPath.selectSingleNode(this.guiFile_xul, xpathQuery_str).toString().substr(6)];
				xpathQuery_str = "//item[@id='u16_item104']/@from";
				var tmpVal3_str:String = _root._objXLIFFholder_obj[XPath.selectSingleNode(this.guiFile_xul, xpathQuery_str).toString().substr(6)];
				_level0.currentItemRootLevel.feedTrace("TRANSLATION","address"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_level0.currentItemRootLevel._u16_BLACK_dt_marker_str+_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR+"name1"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+tmpVal1_str+_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR+"name2"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+tmpVal2_str+_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR+"name3"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+tmpVal3_str,"service");
			}


		renderedContent = content_ref.parseXML(blackManifest_xml);
		
		this._targetExecutionLayer._objDefsRepository.retrieve("tabs-toolbar")._exulis.setGroupFocus();
		
		updateScroll(_obj._height);
		var objPart:Object = callback_fct;
		objPart(box_ref);
	}
	
	public function nestContentHandler(success){
		trace("XULbox (nestContentHandler): file load entered with status: " + success);
		var vRes_str:String = this.guiFile_xul.toString();
		
		if(vRes_str.indexOf("xliffhandler") != -1){
			var xlfDefinitionFile_str:String = pathName_str + vRes_str.substring(vRes_str.indexOf(">",vRes_str.indexOf("xliffhandler"))+1,vRes_str.indexOf("<",vRes_str.indexOf("xliffhandler")));
			trace("xliffHandler: xlfDefinitionFile_str: " + xlfDefinitionFile_str);
			xliffSource_xml = new XML();
			xliffSource_xml.onLoad = Delegate.create(this,buildXliffHandler);
			xliffSource_xml.ignoreWhite = true;
			
			xliffSource_xml.load(xlfDefinitionFile_str);
		}
		else{
			nestContentHandlerSeq();
		}
	}
	
	public function returnListOfElement(elemt_str)
	{
		trace("myFIND "+typeof(_guiSource));
		var nodes_ar : Array = XPath.selectNodes(_guiSource, "//"+elemt_str);
		return nodes_ar;
	}

	public function nestContent(vCallback_fct, vBox_ref){
		callback_fct = vCallback_fct;
		box_ref = vBox_ref;
		var guiDefinitionFile_str:String;
		
		if(loadStatus_str != "done"){
			guiDefinitionFile_str = toolbox.wrapRun(_objDef.firstChild.nodeValue, _guiSource,"SingleNode","String");
			guiDefinitionFile_str = guiDefinitionFile_str.substr(0,-4) + ((this._targetExecutionLayer.lgGUI != undefined)?this._targetExecutionLayer.lgGUI:"")+ ".xml";
			pathName_str = guiDefinitionFile_str.substr(0,guiDefinitionFile_str.lastIndexOf("/") + 1);
			trace("XULbox (nestContent): guiDefinitionFile_str: " + guiDefinitionFile_str);
			loadStatus_str = "done";
			guiFile_xul = new XML();
			guiFile_xul.onLoad = Delegate.create(this,nestContentHandler);
			guiFile_xul.ignoreWhite = true;
			guiFile_xul.load(guiDefinitionFile_str);
		}
		
	}
	
	public function setVscrollPosition(position_nb)
	{

		if (position_nb==undefined || isNaN(position_nb)) return;
		_objParent.vScroll.scrollPosition=position_nb;
		_obj._y=-position_nb;
//		trace("mySCROLL called coord : "+position_nb);
	}
	
	
	// called from setState(status_str)
	public function updateScroll(boxHeight_nbr):Void
	{
		
		
		if (_type=="tabpanels")
		{
			var gap=0;
		} else
			{
				var gap=20;
			}
		
//		trace("mySCROLL boxHeight_nbr"+_obj._height);
		var boxHeight_nbr=boxHeight_nbr-20;
		
		clearInterval(myScrollInterval);
		intervalCount=0;
		
		// reset Box Position		
		/*if (this._targetExecutionLayer._objDefsRepository.retrieve("tabs-toolbar")._exulis.getTabsNumber()<2)
		{
		//_obj._y=Number(_objDef.attributes["top"])-20;
		} else
			{
				_obj._y=Number(_objDef.attributes["top"]);
			}
		*/
		
		
		
		if (_objParent.vScroll)
		{
			_objParent.vScroll.scrollPosition=0;
		}

		if (_objDef.attributes["scrollable"]=="true")
		{						
			
			if (!_objParent.vScroll)
			{
				var _zeScroll=_objParent.createClassObject(mx.controls.UIScrollBar, "vScroll", _objParent._childNextDepth+5300);
				_objParent._childNextDepth++;
				this._targetExecutionLayer._objDefsRepository.scrollBox=_obj;
				this._targetExecutionLayer._objDefsRepository.scrollBoxPosition=_objDef.attributes["top"];
//				trace("mySCROLL "+_zeScroll);
			}
			
			
			// content height
			var deltaHeight_nbr=boxHeight_nbr-maskClip_mc._height;
			
			_objParent.vScroll._visible= (deltaHeight_nbr<=0) ? false : true;
			
		
					
			_objParent.vScroll.move(Number(_objDef.attributes["width"])+Number(_objDef.attributes["left"])-16,maskClip_mc._y);

			_objParent.vScroll.setSize(15,maskClip_mc._height);
			_objParent.vScroll.setScrollProperties(100, 0, deltaHeight_nbr);

			var _scrollEvent=new Object();
			_scrollEvent.zeTarget=_obj;
			_scrollEvent.zeZone=Number(_objDef.attributes["height"]);
			_scrollEvent.scroll=function(eventObject)
			{								
				this.zeTarget._y = -eventObject.target.scrollPosition+gap;
			}

			_objParent.vScroll.addEventListener("scroll", _scrollEvent);						

		}
	}
	
		
		function setStyle(propName,propVal)
		{
			switch(propName)
			{
				case("backgroundColor"):
				case("background-color"):
				case("background"):
				{
					this["_exulis"]["background"] = (propVal == "none") ? "none" : "0x"+String(propVal).substr(1);
					break;
				}

				case("borderWidth"):
				case("border-width"):
				{
					this["_exulis"]["border-width"] = propVal;
					break;
				}

				case("borderColor"):
				case("border-color"):
				{
					this["_exulis"]["border-color"] = "0x"+String(propVal).substr(1);
					break;
				}

				case("borderStyle"):
				case("border-style"):
				{
					this["_exulis"]["border-style"] = (propVal=="solid") ? "solid" : "none";
					break;
				}
				default:{
					//
				}
			}
		}

/*
				if((_objDef.nodeName == "vbox") || (_objDef.nodeName == "window")){
					_obj.orientation = "HORIZONTAL_SPLIT";
				}
				else{
					_obj.orientation = "VERTICAL_SPLIT";
				}
*/
		
	function setLayout(){
		
		_obj._y = this.top;
		_obj._x = this.left;

		createBackground();
		
		
	
		
		createMaskedZone();
		
		
		
		if (_type=="tabpanels")
		{
			this._targetExecutionLayer._objDefsRepository.retrieve("tabs-toolbar")._exulis.registerLinkedPanels(this);
			redimTabPanels();
		}
		
		//updateScroll();
		trace("XULbox (setLayout) [" + this.id + "]: this._visibilityState: " + this._visibilityState);

		_obj._visible = this._visibilityState;
		_obj.startpath=_objDef.attributes["startpath"];
		
	}
	
	private function createBackground():Void
	{
		_obj.clear();
		
		
		if((this["background"]!=undefined) && (this["background"]!="none")){
			_obj.beginFill(Number(this["background"]),100);
		}
		if((this["border-style"]=="solid") && (Number(this["border-width"])>0)){				
			var _lineWidth_nb:Number=Number(this["border-width"]);			
			var _lineColor_nb:Number=Number(this["border-color"]);
			_obj.lineStyle(_lineWidth_nb, _lineColor_nb, 100);
		}
		_obj.moveTo(0,0);
		_obj.lineTo(this.width,0);
		_obj.lineTo(this.width,this.height);
		_obj.lineTo(0,this.height);
		_obj.lineTo(0,0);	
		if((this["background"]!=undefined) && (this["background"]!="none")){
			_obj.endFill();
		}
		
			currentWidth=this.width;
			currentHeight=this.height;
	}
	
	function updateBackground(myWidth,myHeight):String
	{
		
		
		trace("myREDIM EXULIS SIDE");
	
		
		_obj.clear();
		
		var _zeWidth=(myWidth!=undefined) ? myWidth : currentWidth;
		var _zeHeight=(myHeight!=undefined) ? myHeight : currentHeight;
		
		if((this["background"]!=undefined) && (this["background"]!="none")){
			_obj.beginFill(Number(this["background"]),100);
		}
		if((this["border-style"]=="solid") && (Number(this["border-width"])>0)){				
			var _lineWidth_nb:Number=Number(this["border-width"]);			
			var _lineColor_nb:Number=Number(this["border-color"]);
			_obj.lineStyle(_lineWidth_nb, _lineColor_nb, 100);
		}
		_obj.moveTo(0,0);
		_obj.lineTo(_zeWidth,0);
		_obj.lineTo(_zeWidth,_zeHeight);
		_obj.lineTo(0,_zeHeight);
		_obj.lineTo(0,0);	
		if((this["background"]!=undefined) && (this["background"]!="none")){
			_obj.endFill();
		}
		
		
		currentWidth=_zeWidth;
		currentHeight=_zeHeight;
		
		//_obj._xscale=_obj._yscale=100;
		
		return "ok";
	}

	function test()
	{
		trace("myTEST ok");
	}
	
	
	private function insertFileSystem():Void
	{

		fileSystm=new FileSystem(this._targetExecutionLayer[_objDef.attributes["model"]],_obj._parent["startpath"],_obj,_objDef.attributes["type"].substr(2), _objDef.attributes["style"]);
		
		fileSystm.addlisteners(this);
		
		
		//trace("myStyle "+_objDef.attributes["style"]);
		
		
		_obj.view=fileSystm;
		
		// insérer timeout
		
		// draganddrop
		// it.sephiroth.DragController
		
		if (_objDef.attributes["type"]=="fstree" && _objDef.attributes["dragconnection"]!=undefined)
		{
			this._targetExecutionLayer.dragTreeView=fileSystm.fileSystTreeView.myTree;
			trace("myDRAG XULBox");
		} 
		
		if (_objDef.attributes["type"]=="fslist" && _objDef.attributes["dragconnection"]!=undefined)
		{
			this._targetExecutionLayer.dragListView=fileSystm.fileSystListView.myList;
			_root.dc_mc.addReference(this._targetExecutionLayer.dragListView,this._targetExecutionLayer.dragTreeView);
			trace("myDRAG XULBox "+_root.dc_mc);
			
		}
			
	}
	
	private function insertSpreadSheet():Void
	{

		ssheet=new ItemSpreadSheetController(this._targetExecutionLayer[_objDef.attributes["model"]],_obj,_objDef.attributes["headerheight"],_objDef.attributes["chkboxcol"],_objDef.attributes["numbers"],_objDef.attributes["colorlinebyeven"]);
		
		
	}
	

	public function createMaskedZone():Void
	{
		
		if (_objDef.attributes["scrollable"]=="true" or _objDef.attributes["type"]=="fshead" or _objDef.attributes["id"]=="desktop")
		{
			maskClip_mc.removeMovieClip();
			var _objWidth_nbr:Number=_objDef.attributes["width"];
			var _objHeight_nbr:Number=_objDef.attributes["height"];
			maskClip_mc=_objParent.createEmptyMovieClip("zemask"+_objParent._childNextDepth,_objParent._childNextDepth);
			_objParent._childNextDepth++;
			
			maskClip_mc.beginFill(0x00FF00,100);
			maskClip_mc.moveTo(0,0);
			maskClip_mc.lineTo(50,0);
			maskClip_mc.lineTo(50,50);
			maskClip_mc.lineTo(0,50);
			maskClip_mc.lineTo(0,0);
			maskClip_mc.endFill();
			maskClip_mc._x=Number(_objDef.attributes["left"]);
			maskClip_mc._y=Number(_objDef.attributes["top"]);
			maskClip_mc._width= (_objDef.attributes["id"]!="desktop") ? _objWidth_nbr : _objWidth_nbr+5;
			maskClip_mc._height= (_objDef.attributes["id"]!="desktop") ? _objHeight_nbr-20 : _objHeight_nbr ;
			_obj.setMask(maskClip_mc);
		}
		if (_objDef.attributes["scrollable"]=="true")
		{
		myScrollInterval=setInterval(this,"testObjHeight",200);
		}
	}
	
	function testObjHeight()
	{
		if (_obj._height>maskClip_mc._height)
		{
			updateScroll(_obj._height);
		}
		intervalCount++;
		if (intervalCount>10)
		{
			intervalCount=0;
			clearInterval(myScrollInterval);
		}
	}
	
	function destroy(){
		trace("XULbox (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this._obj.removeMovieClip();
	}
/*
	function computeFlex(){
		var vCpt:Number;
		var my_toolbox:Toolbox = new Toolbox();
// initialize the container working area
		trace("##### w:" + width + " ##### h:" + height);
		if(width>100){
			_containerWidth = width - 16;
		}
		else{
			_containerWidth = 100;
		}
		_toShareRemainingWidth = _containerWidth;
		if(height>100){
			_containerHeight = height - 16;
		}
		else{
			_containerHeight = 100;
		}
		_toShareRemainingHeight = _containerHeight;
// phase 1.b
		for(vCpt=0;vCpt<_objDef.childNodes.length;vCpt++){
			var aChild = _objDef.childNodes[vCpt];
// compute minimum width & height thanks to label (if available)
			var tmpMinWidth:Number = 0;
			var tmpMinHeight:Number = 0;
			if(aChild.attributes["label"] != undefined){
				var minWidthHeight:Object;
				minWidthHeight = my_toolbox.evaluateLabelSize(aChild.attributes["label"]);
				tmpMinWidth = minWidthHeight.minWidth + 4;
				tmpMinHeight = minWidthHeight.minHeight + 4;
			}
// check minwidth compared to label width; keep the widest
			_minFlexWidth = ((aChild.attributes["minwidth"] != undefined) && (parseInt(aChild.attributes["minwidth"]) > tmpMinWidth)) ? parseInt(aChild.attributes["minwidth"]) : tmpMinWidth;
// check minheight compared to label height; keep the tallest
			_minFlexHeight = ((aChild.attributes["minheight"] != undefined) && (parseInt(aChild.attributes["minheight"]) > tmpMinHeight)) ? parseInt(aChild.attributes["minheight"]) : tmpMinHeight;
			delete tmpMinWidth;
			delete tmpMinHeight;
// check width; if bigger, we keep it
			if((aChild.attributes["width"] != undefined) && (parseInt(aChild.attributes["width"]) > _minFlexWidth)){
				_minFlexWidth = parseInt(aChild.attributes["width"]);
			}
// check height; if bigger, we keep it
			if((aChild.attributes["height"] != undefined) && (parseInt(aChild.attributes["height"]) > _minFlexHeight)){
				_minFlexHeight = parseInt(aChild.attributes["height"]);
			}
// if no width defined, we keep the default width of the element
			if(_minFlexWidth == 0){
				switch(aChild.nodeName){
					case "splitter":
					{
						_minFlexWidth = 6; //splitter _defaultWidth
						break;
					}
					case "button":
					{
						_minFlexWidth = 100;
						break;
					}
					default:
					{
						_minFlexWidth = 100;
					}
				}
			}
// if no height defined, we keep the default height of the element
			if(_minFlexHeight == 0){
				switch(aChild.nodeName){
					case "splitter":
					{
						_minFlexHeight = 6; //splitter _defaultHeight
						break;
					}
					case "button":
					{
						_minFlexHeight = 25;
						break;
					}
					default:
					{
						_minFlexHeight = 100;
					}
				}
			}
			var expectedWidth:Number;	// EW
			var expectedHeight:Number;

// retrieve NF aka the number of flex of the element
			_numberFlex = ((aChild.attributes["flex"] != undefined) && !(isNaN(parseInt(aChild.attributes["flex"])))) ? parseInt(aChild.attributes["flex"]) : 0;

// now we manage what really matters for correct display of flexed elements TSTFW, TSRW and ToDECF
			if((_objDef.nodeName == "hbox") || (_objDef.nodeName == "box")){
				expectedWidth = _containerWidth * _numberFlex / _totalFlex;
				_toShareTotalFlexWidth = (expectedWidth < _minFlexWidth) ? _toShareTotalFlexWidth + _minFlexWidth - expectedWidth : _toShareTotalFlexWidth;
		trace("----- HBOX -----------------");
		trace("expectedWidth = _containerWidth * _numberFlex / _totalFlex");
		trace(expectedWidth + " = " + _containerWidth + " * " + _numberFlex + " / " + _totalFlex);
		trace("----------------------------");
		trace("_toShareTotalFlexWidth: " + _toShareTotalFlexWidth);
		trace("_toShareRemainingWidth -= _minFlexWidth");
		trace("_toShareRemainingWidth: " + _toShareRemainingWidth);
		trace("_minFlexWidth: " + _minFlexWidth);
				_toShareRemainingWidth -= _minFlexWidth;
		trace("_toShareRemainingWidth: " + _toShareRemainingWidth);
		trace("----------------------------");
		trace("_toDecreaseFlex += (expectedWidth >= _minFlexWidth) ? _numberFlex : 0");
		trace("_toDecreaseFlex: " + _toDecreaseFlex);
				_toDecreaseFlex += (expectedWidth >= _minFlexWidth) ? _numberFlex : 0;
		trace("_toDecreaseFlex: " + _toDecreaseFlex);
		trace("----------------------------");
			}
			if((_objDef.nodeName == "vbox") || (_objDef.nodeName == "window")){
				expectedHeight = _containerHeight * _numberFlex / _totalFlex;
				_toShareTotalFlexHeight = (expectedHeight < _minFlexHeight) ? _toShareTotalFlexHeight + _minFlexHeight - expectedHeight : _toShareTotalFlexHeight;
		trace("----- VBOX -----------------");
		trace("expectedHeight = _containerHeight * _numberFlex / _totalFlex");
		trace(expectedHeight + " = " + _containerHeight + " * " + _numberFlex + " / " + _totalFlex);
		trace("----------------------------");
		trace("_toShareTotalFlexHeight: " + _toShareTotalFlexHeight);
		trace("_toShareRemainingHeight -= _minFlexHeight");
		trace("_toShareRemainingHeight: " + _toShareRemainingHeight);
		trace("_minFlexHeight: " + _minFlexHeight);
				_toShareRemainingHeight -= _minFlexHeight;
		trace("_toShareRemainingHeight: " + _toShareRemainingHeight);
		trace("----------------------------");
		trace("_toDecreaseFlex += (expectedHeight >= _minFlexHeight) ? _numberFlex : 0");
		trace("_toDecreaseFlex: " + _toDecreaseFlex);
				_toDecreaseFlex += (expectedHeight >= _minFlexHeight) ? _numberFlex : 0;
		trace("_toDecreaseFlex: " + _toDecreaseFlex);
		trace("----------------------------");
			}
			trace(vCpt + ". " + aChild.attributes["id"] + "(" + aChild.nodeName + ") CW:" + _containerWidth + " TF:" + _totalFlex + " NF:" + _numberFlex + " EW:" + expectedWidth + " MiFW:" + _minFlexWidth + " TSTFW:" + _toShareTotalFlexWidth + " TSRW:" + _toShareRemainingWidth + " EH:" + expectedHeight + " TSTFH:" + _toShareTotalFlexHeight + " TSRH:" + _toShareRemainingHeight + " ToDECF:" + _toDecreaseFlex);
		}
		delete my_toolbox;
	}
*/
}
