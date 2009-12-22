import lu.tao.utils.tao_toolbox;
import lu.tao.utils.Event;
import mx.managers.DepthManager;

//import lu.tao.taoWS.taoWS;
/**
* XUL 2 SWF
* @author Raynald Jadoul
* @description Translates XUL syntax in Flash native components
* @usage data = new XUL2Item().parseXML(anXML);

*/
class lu.tao.XUL2SWF.XUL2Item extends XML {
    private var oResult:Object = new Object ();
    private var oXML:XML;
	private var canvas_mc:MovieClip;
	private var targetExecutionLayer_mc:MovieClip;
	private var xulBroadcaster;
	private var xulListeners:Array;
	private var questionBackground_ref;
	
	private var elmtGap_nb:Number = 15;
	
/**
* * Constructor
* * initializes the canvas movie clip
*/
	function XUL2Item(base_mc:MovieClip,target_mc:MovieClip) {
//		trace("XUL2Item: canvas initialized to " + target_mc._name);
trace("new XUL2Item");
		canvas_mc = base_mc;
		//canvas_mc._layoutMode = "flow";
		// for test purpose you could enable the flow mode 
		//to place elements like block elmts in css
		targetExecutionLayer_mc = target_mc;
		xulBroadcaster = new Event();
		canvas_mc._xulBroadcaster = xulBroadcaster;
		xulListeners = new Array();
		canvas_mc._xulListeners = xulListeners;
	}
/**
* @method get xml
* @description return the xml passed in the parseXML method
* @usage theXML = XUL2Item.xml
*/
    public function get xml():XML{
        return oXML    
    }
/**
* @method public parseXML
* @description return the parsed Object
* @usage XUL2Item.parseXML( theXMLtoParse );

* @param sFile XML
* @returns an Object with the contents of the passed XML
*/
    public function parseXML (sXML:XML):Object {
//		trace("XUL2Item: XUL parsing started on node " + sXML.firstChild.nodeName);
		this.oResult = new Object ();
		this.oXML = sXML;
		this.oResult = this.translateXML();
		return this.oResult;
    }
// here we connect the canvas to the XUL construction
    private function xul_root(node:XML,current_mc,local_mc){
		trace("XUL2Item: XUL start tag encountered on " + current_mc._name + " on depth: 1");
		current_mc.createEmptyMovieClip("xul",1);
		local_mc = current_mc.xul;
		local_mc._type = "xul";
		local_mc._repository = canvas_mc;
		if(local_mc._repository._result_matrix == undefined){
			local_mc._repository._result_matrix = new Array();
		}
		if(targetExecutionLayer_mc._widgetsRepository_array == undefined){
			targetExecutionLayer_mc._widgetsRepository_array = new Array();
		}
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++;
		local_mc._x = 0;
		local_mc.left = 0;
		local_mc._y = 0;		
		local_mc.top = 0;	
		
		
		// ***********************************************************************************************
		local_mc._ySize = 0; // initial value;
		
		// autoPosition
		local_mc.setYsize = function(newSize)
		{
			
		if((this._ySize < newSize) || this._ySize==undefined){		
		this._ySize = newSize;
		}
		
		this._parent.setYsize(this._y+newSize);
		}
		
		// ***********************************************************************************************
		
		var thisObj_obj:Object = {objRef:local_mc, objType:"movieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);
		// ***********************************************************************************************
		
		return local_mc;
    }
// here XULbox
    private function xul_box(node:XML, current_mc, local_mc)
    {	
		trace("XUL2Item: XUL box (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		if(node.attributes["id"] == undefined){
			node.attributes["id"] = current_mc._name + "_box" + string(current_mc._childNextDepth + 1);
		}
		var mc:MovieClip=current_mc.createEmptyMovieClip(node.attributes["id"], current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc.layout = node.attributes["layout"];
		local_mc._type = "xul_box";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc._x = xPos;
		local_mc.left = xPos;
		
		// ***********************************************************************************************
		//local_mc._ySize = 0; // initial value;
		
		// layout="normal" to be forced for interaction... need more details from Alexander

		// autoPosition
		if (current_mc.layout == 'normal')
		{			
		current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;	
		local_mc._y  = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;
		} 
		
		local_mc._y = (canvas_mc._layoutMode=="flow" && current_mc.layout != 'normal') ? current_mc._ySize+15 : yPos;	
		// autoPosition
		local_mc.setYsize = function(newSize)
		{
//			trace("TOTO: " + local_mc);
//			trace("TOTO: this._ySize=" + this._ySize);
//			trace("TOTO: newSize=" + newSize);
			if((this._ySize < newSize) || this._ySize==undefined){		
				this._ySize = newSize;
				questionBackground_ref._height = newSize;
			}

			this._parent.setYsize(this._y+newSize);
		}
		// ***********************************************************************************************
		
		
		local_mc.top = yPos;
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
		var thisObj_obj:Object = {objRef:local_mc, objType:"movieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);	
		
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);
		// ***********************************************************************************************
		
		return local_mc;
    }
// here XULradiogroup
    private function xul_radiogroup(node:XML,current_mc,local_mc){
		trace("XUL2Item: XUL radiogroup (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		current_mc.createEmptyMovieClip(node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc.layout = node.attributes["layout"];
		local_mc._type = "xul_radiogroup";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc._x = xPos;
		local_mc.left = xPos;
		
		// ***********************************************************************************************
		// autoPosition
		if (current_mc.layout == 'normal')
		{			
		current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;	
		local_mc._y  = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;
		} 
		
		
		local_mc._y = (canvas_mc._layoutMode=="flow" & current_mc.layout!='normal') ? current_mc._ySize : yPos;	
		
		// autoPosition
		local_mc.setYsize = function(newSize)
		{
			
		if((this._ySize < newSize) || this._ySize==undefined){		
		this._ySize = newSize;
		}
		
		this._parent.setYsize(this._y+newSize);
		}
		// ***********************************************************************************************
		
		
		local_mc.top = yPos;
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
		var thisObj_obj:Object = {objRef:local_mc, objType:"movieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);		
		
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);
		// ***********************************************************************************************
		
		return local_mc;
    }
// here XULradio
    private function xul_radio(node:XML,current_mc,local_mc){
        trace("XUL2Item: XUL radio (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth + " with node: " + node.toString());
        current_mc.createClassObject(mx.controls.RadioButton,node.attributes["id"],current_mc._childNextDepth);
        local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_radio";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
        local_mc._childNextDepth = 1; // local XUL depth (levels) management
        local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		local_mc.groupName = current_mc._name;
		var vRowResult_obj:Object = {name:local_mc._name,selected:"0",propValue:node.attributes["label"],groupName:local_mc.groupName};
        var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		
		// ***********************************************************************************************
		// autoPosition
		if (current_mc.layout == 'normal')
		{
					
				
			current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;		
	
		
		var yPos:Number = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;
		
		
		local_mc.move(xPos, yPos);
		
		}
		
		
		if (canvas_mc._layoutMode!="flow" && current_mc.layout != 'normal'){
        var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc.move(xPos, yPos);
		} else if (canvas_mc._layoutMode=="flow" && current_mc.layout != 'normal')
		{			
			local_mc.move(xPos, current_mc._ySize+15);
		}
		// ***********************************************************************************************
	
        var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 150;
        var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
        local_mc.setSize(wVal, hVal);
		
		// ***********************************************************************************************
		// autoPosition
		local_mc.setYsize = function(newSize)
		{
			
		if((this._ySize < newSize) || this._ySize==undefined){		
		this._ySize = newSize;
		}
		
		this._parent.setYsize(this._y+newSize);
		}
		// ***********************************************************************************************
		
        // for the oncommand event handling
        local_mc.onCommand = node.attributes["oncommand"];
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
        trace("XUL2Item: local_mc.onCommand: " + node.attributes["oncommand"]);
//        local_mc.selected = node.attributes["selected"];
		if(canvas_mc._result_array != undefined){
			for(var vCpt=canvas_mc._result_array.length - 1;vCpt >= 0;vCpt--){
				var vRow_obj:Object = canvas_mc._result_array[vCpt];
				trace("=== vRow_obj.groupName=" + vRow_obj.groupName + " name=" + vRow_obj.name + " selected=" + vRow_obj.selected);
				if ((vRow_obj.groupName == local_mc.groupName) && (vRow_obj.name == local_mc._name)){
					if(vRow_obj.selected == "true"){
						local_mc.selected = true;
						vRowResult_obj.selected = "1";
						break;
					}
				}
				if ((vRow_obj.groupName == local_mc.groupName) && (vRow_obj.selected == "true")){
					break;
				}
			}
		}
// ++++++++++++++++++++++++++		
		local_mc._repository._result_matrix.push(vRowResult_obj);
		local_mc.label = node.attributes["label"];
		local_mc.resultLabel = node.attributes["label"];		

		var listenerObject = new Object();
		listenerObject.click = function(eventObject){
			eventObject.target._repository._answered = "yes";
			if(eventObject.target._repository._result_array == undefined){
				eventObject.target._repository._result_array = new Array();
			}
			var vRow_obj:Object = {name:eventObject.target._name,selected:"true",propValue:eventObject.target.resultLabel,groupName:eventObject.target.groupName}
			eventObject.target._repository._result_array.push(vRow_obj);
			var vRowResult_obj:Object;
			for(var vCpt=0;vCpt<eventObject.target._repository._result_matrix.length;vCpt++){
				vRowResult_obj = eventObject.target._repository._result_matrix[vCpt];
				if(vRowResult_obj.groupName == eventObject.target.groupName){
					vRowResult_obj.selected = "0";
					eventObject.target._repository._result_matrix[vCpt] = vRowResult_obj;
//					trace("######## RAZ de " + vRowResult_obj.name);
				}
			}
			for(var vCpt=0;vCpt<eventObject.target._repository._result_matrix.length;vCpt++){
				vRowResult_obj = eventObject.target._repository._result_matrix[vCpt];
				if(vRowResult_obj.groupName == eventObject.target.groupName){
					if (vRowResult_obj.name == eventObject.target._name){
						if(eventObject.target.selected == true){
							vRowResult_obj.selected = "1";
							eventObject.target._repository._result_matrix[vCpt] = vRowResult_obj;
						}
					}
				}
			}
			trace("just before goOn in click event of " + eventObject.target.label + "(id:" + eventObject.target.id + ")");
			eventObject.target._targetExecutionLayer.feedTrace("RADIO_BTN","id=" + eventObject.target.id,"taoHAWAI");
			_level0.currentItemRootLevel.setAutomationFlag("goOn");
            var fullCmd:String = eventObject.target.onCommand;
			if(fullCmd != undefined){
				trace("XUL2Item: fullCmd: " + fullCmd);
				var my_toolbox:tao_toolbox = new tao_toolbox();
				var cmdPart:String = my_toolbox.extractString(fullCmd,"","(",0,false);
				var argPart:String = my_toolbox.extractString(fullCmd,"(",")",0,false);
				var argArray:Array = new Array();
				var argTarget:String = new String();
				var objPart:Object;
				argArray.push(argPart);
				if (cmdPart.indexOf(".") != -1){
					argTarget = my_toolbox.extractString(cmdPart,"",".",0,false);
					if (argTarget.toUpperCase() == "TAO_TEST"){
						objPart = _level0;
					}
					else {
						objPart = eval(eventObject.target._targetExecutionLayer);
					}
					cmdPart = my_toolbox.extractString(cmdPart,".","",0,false);
				}
				else {
					objPart = eval(eventObject.target._targetExecutionLayer);
				}
				trace("XUL2Item real call is: " + objPart + "." + cmdPart + "(" + argPart + ")");
				eval(objPart + "." + cmdPart)(argPart);
			}
		}
		local_mc.addEventListener("click", listenerObject);

		var vCmd:String;
		vCmd = local_mc.onCommand;

		var ctrlRegistry_ptr = local_mc._parent;
		while(ctrlRegistry_ptr._type != "xul"){
			ctrlRegistry_ptr = ctrlRegistry_ptr._parent;
		}
		if(ctrlRegistry_ptr._ctrlRegistry_array == undefined){
			ctrlRegistry_ptr._ctrlRegistry_array = new Array();
		}
		ctrlRegistry_ptr._ctrlRegistry_array.push(local_mc);
		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
// image mgt
        var image_str:String = node.attributes["src"];
		if(image_str == undefined){
			image_str = node.attributes["image"];
		}
		if(image_str != undefined){
			local_mc.createEmptyMovieClip(node.attributes["id"] + "_image",local_mc._childNextDepth);
			var subLocal_mc:MovieClip;
			subLocal_mc = local_mc[node.attributes["id"] + "_image"];
			subLocal_mc._type = "xul_image";
			subLocal_mc.id = node.attributes["id"] + "_image";
			subLocal_mc._repository = canvas_mc;
			subLocal_mc._childNextDepth = 1; // local XUL depth (levels) management
			subLocal_mc._parent._childNextDepth ++; // local XUL depth (levels) management
			var xImgPos:Number = (node.attributes["imgLeft"] != undefined) ? node.attributes["imgLeft"] : 0;
			var yImgPos:Number = (node.attributes["imgTop"] != undefined) ? node.attributes["imgTop"] : 0 ;
	//        subLocal_mc.move(xImgPos,yImgPos);
			subLocal_mc._x = xImgPos;
			subLocal_mc.left = xImgPos;
			subLocal_mc._y = yImgPos;
			subLocal_mc.top = yImgPos;
	//        var wImgVal:Number = (node.attributes["imgWidth"] != undefined) ? node.attributes["imgWidth"] : 50 ;
	//        var hImgVal:Number = (node.attributes["imgHeight"] != undefined) ? node.attributes["imgHeight"] : 25;
			var wImgVal:Number = node.attributes["imgWidth"];
			var hImgVal:Number = node.attributes["imgHeight"];
	//        subLocal_mc.setSize(wImgVal,hImgVal);
	//        subLocal_mc._width = wVal;
	//        subLocal_mc._height = hVal;
	//		subLocal_mc.scaleContent = true;
	//        var image_str:String = node.attributes["src"];
			var item_mcl:MovieClipLoader;
			var mclListener:Object;
			mclListener = new Object();
			mclListener.onLoadError = function(target_mc:MovieClip, errorCode:String) {
				trace("image load ERROR on " + image_str);
			};
			mclListener.onLoadInit = function(target_mc:MovieClip) {
				trace("image " + image_str + " loaded on " + target_mc._name + " with W:" + wImgVal + " and H:" + hImgVal);
				if(wImgVal != undefined){
					target_mc._width = wImgVal;
				}
				if(hImgVal != undefined){
					target_mc._height = hImgVal;
				}
				item_mcl.removeListener(mclListener);
				if((target_mc._parent._type == "xul_checkbox") || (target_mc._parent._type == "xul_radio")){
					target_mc._parent.resultLabel = image_str;
					var tImageEvent_listener = new Object();
					tImageEvent_listener.click = function (eventObj){
						trace("XUL2Item image clicked for : " + eventObj.target._name);
						eventObj.target._parent.dispatchEvent({type:"click"});
					}
					target_mc.addEventListener("click", tImageEvent_listener);
//					target_mc._parent.addEventListener("click", tImageEvent_listener);
				};
			};
			item_mcl = new MovieClipLoader();
			item_mcl.addListener(mclListener);
			item_mcl.loadClip(image_str, subLocal_mc);
			var thisImageObj_obj:Object = {objRef:subLocal_mc, objType:"imageMovieClip", xulType:subLocal_mc._type};
			targetExecutionLayer_mc._widgetsRepository_array.push(thisImageObj_obj);
		}
		
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);
		// ***********************************************************************************************
		
		return local_mc;
    }
// here XULcheckbox
    private function xul_checkbox(node:XML,current_mc,local_mc){
		trace("XUL2Item: XUL checkbox (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth + " with node: " + node.toString());
		current_mc.createClassObject(mx.controls.CheckBox,node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_checkbox";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		var vRowResult_obj:Object = {name:local_mc._name,selected:"0",propValue:node.attributes["label"],groupName:"#CHECKBOXESGROUP#"};
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
//		local_mc.groupName = current_mc._name;
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		
		// ***********************************************************************************************
		// autoPosition
		if (current_mc.layout == 'normal')
		{
					
				
			current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;		
	
		
		var yPos:Number = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;
		
		
		local_mc.move(xPos, yPos);
		
		}
		
		
		if (canvas_mc._layoutMode!="flow" && current_mc.layout != 'normal'){
        var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc.move(xPos, yPos);
		} else if (canvas_mc._layoutMode=="flow" && current_mc.layout != 'normal')
		{			
			local_mc.move(xPos, current_mc._ySize);
		}
		// ***********************************************************************************************
		
		
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 150;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
		local_mc.setSize(wVal, hVal);
		
		// ***********************************************************************************************
		// autoPosition
		local_mc.setYsize = function(newSize)
		{
			
		if((this._ySize < newSize) || this._ySize==undefined){		
		this._ySize = newSize;
		}
		
		this._parent.setYsize(this._y+newSize);
		}
		// ***********************************************************************************************
		
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
//        local_mc.selected = node.attributes["selected"];
		if(canvas_mc._result_array != undefined){
			for(var vCpt=canvas_mc._result_array.length - 1;vCpt >= 0;vCpt--){
				var vRow_obj:Object = canvas_mc._result_array[vCpt];
				if (vRow_obj.name == local_mc._name){
					if(vRow_obj.selected == "true"){
						local_mc.selected = true;
						vRowResult_obj.selected = "1";
					}
					break;
				}
			}
		}
		local_mc._repository._result_matrix.push(vRowResult_obj);
		local_mc.label = node.attributes["label"];
		local_mc.resultLabel = node.attributes["label"];
		
		
		
		var listenerObject = new Object();
		listenerObject.click = function(eventObject){
			eventObject.target._repository._answered = "yes";
			if(eventObject.target._repository._result_array == undefined){
				eventObject.target._repository._result_array = new Array();
			}
			var vRow_obj:Object = {name:eventObject.target._name,selected:eventObject.target.selected,propValue:eventObject.target.resultLabel,groupName:"#CHECKBOXESGROUP#"};
			eventObject.target._repository._result_array.push(vRow_obj);
			for(var vCpt=0;vCpt<eventObject.target._repository._result_matrix.length;vCpt++){
				var vRowResult_obj:Object = eventObject.target._repository._result_matrix[vCpt];
				if (vRowResult_obj.name == eventObject.target._name){
					if(eventObject.target.selected == true){
						vRowResult_obj.selected = "1";
					}
					else {
						vRowResult_obj.selected = "0";
					}
					eventObject.target._repository._result_matrix[vCpt] = vRowResult_obj;
					break;
				}
			}
			trace("just before goOn in click event of " + eventObject.target.label + "(id:" + eventObject.target.id + ")");
			_level0.currentItemRootLevel.setAutomationFlag("goOn");
			eventObject.target._targetExecutionLayer.feedTrace("CHECKBOX","id=" + eventObject.target.id,"taoHAWAI");
		}
		local_mc.addEventListener("click", listenerObject);
		var ctrlRegistry_ptr = local_mc._parent;
		while(ctrlRegistry_ptr._type != "xul"){
			ctrlRegistry_ptr = ctrlRegistry_ptr._parent;
		}
		if(ctrlRegistry_ptr._ctrlRegistry_array == undefined){
			ctrlRegistry_ptr._ctrlRegistry_array = new Array();
		}
		ctrlRegistry_ptr._ctrlRegistry_array.push(local_mc);
		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
// image mgt
        var image_str:String = node.attributes["src"];
		if(image_str == undefined){
			image_str = node.attributes["image"];
		}
		if(image_str != undefined){
			local_mc.createEmptyMovieClip(node.attributes["id"] + "_image",local_mc._childNextDepth);
			var subLocal_mc:MovieClip;
			subLocal_mc = local_mc[node.attributes["id"] + "_image"];
			subLocal_mc._type = "xul_image";
			subLocal_mc.id = node.attributes["id"] + "_image";
			subLocal_mc._repository = canvas_mc;
			subLocal_mc._childNextDepth = 1; // local XUL depth (levels) management
			subLocal_mc._parent._childNextDepth ++; // local XUL depth (levels) management
			var xImgPos:Number = (node.attributes["imgLeft"] != undefined) ? node.attributes["imgLeft"] : 0;
			var yImgPos:Number = (node.attributes["imgTop"] != undefined) ? node.attributes["imgTop"] : 0 ;
	//        subLocal_mc.move(xImgPos,yImgPos);
			subLocal_mc._x = xImgPos;
			subLocal_mc.left = xImgPos;
			subLocal_mc._y = yImgPos;
			subLocal_mc.top = yImgPos;
	//        var wImgVal:Number = (node.attributes["imgWidth"] != undefined) ? node.attributes["imgWidth"] : 50 ;
	//        var hImgVal:Number = (node.attributes["imgHeight"] != undefined) ? node.attributes["imgHeight"] : 25;
			var wImgVal:Number = node.attributes["imgWidth"];
			var hImgVal:Number = node.attributes["imgHeight"];
	//        subLocal_mc.setSize(wImgVal,hImgVal);
	//        subLocal_mc._width = wVal;
	//        subLocal_mc._height = hVal;
	//		subLocal_mc.scaleContent = true;
	//        var image_str:String = node.attributes["src"];
			var item_mcl:MovieClipLoader;
			var mclListener:Object;
			mclListener = new Object();
			mclListener.parent = current_mc;
			mclListener.local = local_mc;
			mclListener.onLoadError = function(target_mc:MovieClip, errorCode:String) 
			{
				
				trace("image load ERROR on " + image_str);
			};
			mclListener.onLoadInit = function(target_mc:MovieClip) {
				trace("image " + image_str + " loaded on " + target_mc._name + " with W:" + wImgVal + " and H:" + hImgVal);
				if(wImgVal != undefined){
					target_mc._width = wImgVal;
				}
				if(hImgVal != undefined){
					target_mc._height = hImgVal;
				}
				item_mcl.removeListener(mclListener);
				if((target_mc._parent._type == "xul_checkbox") || (target_mc._parent._type == "xul_radio")){
					target_mc._parent.resultLabel = image_str;
					var tImageEvent_listener = new Object();
					tImageEvent_listener.click = function (eventObj){
						trace("XUL2Item image clicked for : " + eventObj.target._name);
						eventObj.target._parent.dispatchEvent({type:"click"});
					}
					target_mc.addEventListener("click", tImageEvent_listener);
//					target_mc._parent.addEventListener("click", tImageEvent_listener);
				}
				
				/*
				// autoPosition ---------------- newSize
				this.parent.setYsize(this.local._y + this.local._height);*/
				
			};
			item_mcl = new MovieClipLoader();
			item_mcl.addListener(mclListener);
			item_mcl.loadClip(image_str, subLocal_mc);
			var thisImageObj_obj:Object = {objRef:subLocal_mc, objType:"imageMovieClip", xulType:subLocal_mc._type};
			targetExecutionLayer_mc._widgetsRepository_array.push(thisImageObj_obj);
		}
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);
		// ***********************************************************************************************
		
		return local_mc;
	}
// here XULlabel
    private function xul_label(node:XML,current_mc,local_mc){
        trace("XUL2Item: XUL label (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth + " with node: " + node.toString());
        current_mc.createClassObject(mx.controls.Label,node.attributes["id"],current_mc._childNextDepth);
        local_mc = current_mc[node.attributes["id"]];
		local_mc.drawFocus = "";
		local_mc._type = "xul_label";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
        local_mc._childNextDepth = 1; // local XUL depth (levels) management
        local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		
		// ***********************************************************************************************
		// autoPosition
		if (current_mc.layout == 'normal')
		{
					
				
			current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;		
	
		
		var yPos:Number = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;
		
		
		local_mc.move(xPos, yPos);
		
		}
		
		
		if (canvas_mc._layoutMode!="flow" && current_mc.layout != 'normal'){
        var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc.move(xPos, yPos);
		} else if (canvas_mc._layoutMode=="flow" && current_mc.layout != 'normal')
		{			
			local_mc.move(xPos, current_mc._ySize+15);
		}
		// ***********************************************************************************************
		
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 150;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
        local_mc.setSize(wVal,hVal);
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
        local_mc.html = true;
        local_mc.autoSize = true;
        local_mc.text = node.attributes["value"];
		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);
		// ***********************************************************************************************
		
		return local_mc;
    }

	// createTextField for evaluate textAreaSize
	/*
	private function dummyTextField(text_str:String,tFieldWidth_nb:Number,areaStyle:TextFormat):Number
	{	
	var txtFieldHeight_nb:Number;
	var dummy:TextField = _root.createTextField("dummyTxt", 6989, 0, 0, tFieldWidth_nb, 20);
	dummy._x = 100;
	dummy.background = true;
	dummy.backgroundColor = 0xff0000;
	dummy.border = true;
	dummy.wordWrap = true;	
	dummy.autoSize = "Left";
	dummy.html = true;
	dummy.htmlText = text_str ;
	dummy.setNewTextFormat(areaStyle);
	txtFieldHeight_nb = dummy._height;	
	//dummy.removeTextField();
	return txtFieldHeight_nb;
	}
	*/
	
	
// here XULtextbox
    private function xul_textbox(node:XML, current_mc, local_mc)
    {
//		var backClip:MovieClip;
		trace("canvas_mc._layoutMode ---> " + canvas_mc._layoutMode);
// ***********************************************************************************************
				
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 150;		
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
		
		var my_fmt:TextFormat = new TextFormat();
		if(node.attributes["leading"] != undefined){
			my_fmt.leading = parseInt(node.attributes["leading"],10);
		}
		if(node.attributes["style"] != undefined){
			var tmpStr_str:String = String(node.attributes["style"]);
			if(tmpStr_str.indexOf("leading") != -1){
				var my_toolbox:tao_toolbox = new tao_toolbox();
				var attribPart:String = my_toolbox.extractString(tmpStr_str,"leading:",";",0,false);
				my_fmt.leading = parseInt(attribPart,10);
			}
		}
		my_fmt.size = 13;
		my_fmt.font = "Arial";

		if(node.attributes["class"]=="question"){
			node.attributes["style"]="border-style:none;backgroundColor:0x99CCFF;";
			if(_root._u16_BLACK_dt_marker == true){
				trace("u16_BLACK_dt_marker with " + node.attributes["value"]);
				var tmpVal_str:String = node.attributes["value"];
				var my_toolbox:tao_toolbox = new tao_toolbox();
				tmpVal_str = my_toolbox.extractString(tmpVal_str,"<br/><br/>","<br/><br/>",10,false);
				tmpVal_str = my_toolbox.replaceString(tmpVal_str,"<br/>"," ");
				_root._u16_BLACK_dt_marker_str = tmpVal_str;
			}
		}
		// ***********************************************************************************************
		if ((node.attributes["class"] == "text_interaction") && (node.attributes["class"] != "questionBackground")){
			if((hVal < 30) && (node.attributes["multiline"] != "yes") && (node.attributes["multiline"] != "true")){
				current_mc.createClassObject(mx.controls.TextInput,node.attributes["id"],current_mc._childNextDepth);
			}
			else{
				current_mc.createClassObject(mx.controls.TextArea,node.attributes["id"],current_mc._childNextDepth);
			}
			local_mc = current_mc[node.attributes["id"]];
		} 
		else{
			if (node.attributes["class"] != "text_interaction" && node.attributes["class"] != "questionBackground"){
				current_mc.createTextField(node.attributes["id"], current_mc._childNextDepth, 0, 0, 0, 0);
				local_mc = current_mc[node.attributes["id"]];

				local_mc.background = true;
				//local_mc.border = true;
				local_mc.type = "dynamic";
				if(hVal < 30){
					local_mc.multiline = false;
				}
				else{
					local_mc.multiline = true;
				}
			} 
			else{
				if (node.attributes["class"] == "questionBackground"){
					current_mc.createEmptyMovieClip(node.attributes["id"], current_mc._childNextDepth);
					local_mc = current_mc[node.attributes["id"]];
					questionBackground_ref = local_mc;
//					trace("TOTO: " + local_mc + "(" + local_mc.getDepth() + ")");
					local_mc.beginFill(0xFF0000);
					local_mc.moveTo(0, 0);
					local_mc.lineTo(5, 0);
					local_mc.lineTo(5, 5);
					local_mc.lineTo(0, 5);
					local_mc.lineTo(0, 0);
					local_mc.endFill();
				}
			}
		}
		// ***********************************************************************************************

		local_mc.drawFocus = "";
		local_mc.setNewTextFormat(my_fmt);

		local_mc._type = "xul_textbox";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management

        var readOnly_bool:Boolean = (node.attributes["readonly"] == "true") ? true : false ;
		var vRowResult_obj:Object;

		if (readOnly_bool){			
			vRowResult_obj = {name:local_mc._name,selected:"",propValue:"#READONLY#"};
		}
		else{
			vRowResult_obj = {name:local_mc._name,selected:"",propValue:""};
		}

    if (current_mc.layout == 'normal'){
        current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;	
        var yPos:Number = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;
    } 
    else{
        var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
    }

    var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;

// ***********************************************************************************************
    if (node.attributes["class"] == "text_interaction"){

        // autoPosition
        if (current_mc.layout == 'normal'){
            current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;		
            var yPos:Number = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;	
            local_mc.move(xPos, yPos);
        }

        if (canvas_mc._layoutMode!="flow" && current_mc.layout != 'normal'){
            var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
            local_mc.move(xPos, yPos);
        }
        else{
            if (canvas_mc._layoutMode=="flow" && current_mc.layout != 'normal'){
                local_mc.move(xPos, current_mc._ySize+15);
            }
        }
    }
    else{				
        if ((current_mc._type != "xul_radio") || (current_mc._type != "xul_checkbox")){	
            local_mc._x = xPos;

            // autoPosition
            local_mc._y = (canvas_mc._layoutMode == "flow") ? current_mc._ySize + 15 : yPos;

            if (current_mc.layout == 'normal'){
                current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;
                var yPos:Number = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;	
                local_mc._y = yPos;		
            }
         }
     }
			
		// ***********************************************************************************************	
		if (node.attributes["class"] == "text_interaction")
		{
		local_mc.setSize(wVal,hVal);
		} else 
			{
				
			local_mc._width = wVal;
				if (canvas_mc._layoutMode != "flow")
				{
				local_mc._height = hVal;
				}
			}
		
		// ***********************************************************************************************
		
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
		
		
		// ***********************************************************************************************
		if (canvas_mc._layoutMode=="flow")
		{
		local_mc.autoSize = "Left";			
		}
		
		if (node.attributes["class"] == "text_interaction")
		{
        local_mc.editable = !readOnly_bool;
		} else
			{
			local_mc.selectable = !readOnly_bool;
			}
		// ***********************************************************************************************
		
		
		local_mc.html = true;
		var tmpWrap_bool:Boolean = new Boolean(node.attributes["wrap"]);
		
		local_mc.wordWrap = (node.attributes["wrap"] != undefined) ? tmpWrap_bool : false;
		
		local_mc.htmlText = node.attributes["value"];
		
			
		if((current_mc._type == "xul_radio") || (current_mc._type == "xul_checkbox")){
			if((current_mc.resultLabel == "") || (current_mc.resultLabel == undefined)){
				var tmpVal_str:String = local_mc.text;
				var my_toolbox:tao_toolbox = new tao_toolbox();
				current_mc.resultLabel = my_toolbox.stripTag(tmpVal_str);
			}
		}

		if((canvas_mc._result_array != undefined) && (node.attributes["readonly"] != "true")){
			for(var vCpt=canvas_mc._result_array.length - 1;vCpt >= 0;vCpt--){
				var vRow_obj:Object = canvas_mc._result_array[vCpt];
//				trace("=== vRow_obj.groupName=" + vRow_obj.groupName + " name=" + vRow_obj.name + " selected=" + vRow_obj.selected);
				if (vRow_obj.name == local_mc._name){
					local_mc.html = false;
					local_mc.text = vRow_obj.selected;
					local_mc.html = true;
					vRowResult_obj.selected = vRow_obj.selected;
					break;
				}
			}
		}

		local_mc._repository._result_matrix.push(vRowResult_obj);
		// Max Character
		local_mc.maxChars = (node.attributes["maxlength"] != undefined) ? parseInt(node.attributes["maxlength"]) : null;
		local_mc.maxChars = (node.attributes["maxChars"] != undefined) ? parseInt(node.attributes["maxChars"]) : local_mc.maxChars;
		local_mc.restrict = (node.attributes["restrict"] != undefined) ? node.attributes["restrict"] : null;
		if(node.attributes["style"] != undefined){
			var localStyle_str = node.attributes["style"];
			var styleWorkArray = new Array();
			styleWorkArray = localStyle_str.split(";");
			for(var firstCpt=0;firstCpt < styleWorkArray.length; firstCpt++){
				var aPropertyCouple_str:String;
				var elementsArray_array:Array;
				elementsArray_array = new Array();
				aPropertyCouple_str = new String(styleWorkArray[firstCpt]);
				elementsArray_array = aPropertyCouple_str.split(":");
				var propName_str:String;
				var propVal_str:String;
				propName_str = elementsArray_array[0];
				propName_str = (propName_str == "border-style")?"borderStyle":propName_str;
				propName_str = (propName_str == "background-color")?"backgroundColor":propName_str;
				propVal_str = elementsArray_array[1];
				//background transparent
				if((propName_str == "backgroundColor") && (propVal_str == "transparent")){
					local_mc.depthChild0._alpha = 0;
					local_mc.background = false;
				}
				else{
					if(isNaN(parseInt(propVal_str))){
						local_mc.setStyle(propName_str,propVal_str);
					}
					else{
						local_mc.setStyle(propName_str,parseInt(propVal_str));
					}
				}
			}
		}
		if(node.attributes["mandatory"] == "false"){ // that's not really a XUL attribute
			local_mc._repository._answered = "yes";
		}
		var listenerObject = new Object();
		listenerObject.change = function(eventObject){
			eventObject.target._repository._answered = "yes";
			if(eventObject.target._repository._result_array == undefined){
				eventObject.target._repository._result_array = new Array();
			}
			var vRow_obj:Object = {name:eventObject.target._name,selected:eventObject.target.text,propValue:""};
			var objAlreadyRegistered_bool:Boolean = false;
			for(var vCpt=0;vCpt<eventObject.target._repository._result_array.length;vCpt++){
				var vRowResult_obj:Object = eventObject.target._repository._result_array[vCpt];
				if (vRowResult_obj.name == eventObject.target._name){
				    objAlreadyRegistered_bool = true;
				    break;
				}
			}
			if(objAlreadyRegistered_bool){
                eventObject.target._repository._result_array[vCpt] = vRow_obj;
            }
            else{
                eventObject.target._repository._result_array.push(vRow_obj);
            }
			for(var vCpt=0;vCpt<eventObject.target._repository._result_matrix.length;vCpt++){
				var vRowResult_obj:Object = eventObject.target._repository._result_matrix[vCpt];
				if (vRowResult_obj.name == eventObject.target._name){
					eventObject.target.html = false;
					vRowResult_obj.selected = eventObject.target.text;
					eventObject.target.html = true;
    				eventObject.target._repository._result_matrix[vCpt] = vRowResult_obj;
	       			break;
				}
			}
		}
		local_mc.addEventListener("change", listenerObject);
		var listenerFocusInObject = new Object();
		listenerFocusInObject.focusIn = function(eventObject){
			eventObject.target.html = false;
			if(eventObject.target.id == undefined){
				eventObject.target._targetExecutionLayer.feedTrace("TEXTBOX_ONFOCUS","value="+escape(eventObject.target.text),"taoHAWAI");
			}
			else{
				eventObject.target._targetExecutionLayer.feedTrace("TEXTBOX_ONFOCUS","id="+eventObject.target.id+"|*$value="+escape(eventObject.target.text),"taoHAWAI");
			}
			eventObject.target.html = true;
		}
		local_mc.addEventListener("focusIn", listenerFocusInObject);
		var listenerFocusOutObject = new Object();
		listenerFocusOutObject.focusOut = function(eventObject){
			eventObject.target.html = false;
			_root.lastWidgetWithFocus_str = eventObject.target;
			trace("lastWidgetWithFocus_str 0 = " + _root.lastWidgetWithFocus_str);
			if(eventObject.target.id == undefined){
				eventObject.target._targetExecutionLayer.feedTrace("TEXTBOX_KILLFOCUS","value="+escape(eventObject.target.text),"taoHAWAI");
			}
			else{
				eventObject.target._targetExecutionLayer.feedTrace("TEXTBOX_KILLFOCUS","id="+eventObject.target.id+"|*$value="+escape(eventObject.target.text),"taoHAWAI");
			}
			eventObject.target.html = true;
		}
		local_mc.addEventListener("focusOut", listenerFocusOutObject);
		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		
		if (node.attributes["class"] != "questionBackground")
		{
/*
			backClip._x = local_mc._x-5;
			backClip._y = local_mc._y-5;
			backClip._width = local_mc._width+10;
			backClip._height = local_mc._height+10;
			backClip = null;
*/
		// autoPosition ---------------- newSize
			current_mc.setYsize(local_mc._y + local_mc._height);
		} 
		else{
			// found color value of background in any possibler position
			
			var colorStr:String = node.attributes["style"];
			var index0:Number;
			if(colorStr.indexOf("backgroundColor") != -1){
				index0 = colorStr.indexOf("backgroundColor") + 16;
			}
			else{
				if(colorStr.indexOf("background-color") != -1){
					index0 = colorStr.indexOf("background-color") + 17;
				}
			}

			trace("index0 " + index0);
			var index1:Number = index0 + 8; 
			trace("index1 " + index1);
			var backGroundColor = colorStr.substring(index0, index1);
			var my_color:Color = new Color(local_mc);
			my_color.setRGB(backGroundColor); // my_mc turns red
		}
		return local_mc;
    }
// here XULbutton
    private function xul_button(node:XML,current_mc,local_mc){
        trace("XUL2Item: XUL button (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		if(node.attributes["id"] == "nextInquiry_button"){
			current_mc.attachMovie("next_but",node.attributes["id"],current_mc._childNextDepth);
			_root._nextInquiry_button_ref = current_mc[node.attributes["id"]];
/*
			if(_root.isERAstimulusInitialized_bool == false){
				trace("feed from XUL2Item : _root._nextInquiry_button_ref._visible = false");
//				_root._nextInquiry_button_ref._visible = false;
			}
			else{
				trace("feed from XUL2Item : _root._nextInquiry_button_ref._visible = true");
			}
*/
		}
		else{
			if(node.attributes["id"] == "prevInquiry_button"){
/*
				var button_link_id_str:String;
				if(_root.itemSequence_str == "1"){
					button_link_id_str = "prev_disabled_but";
				}
				else{
					button_link_id_str = "prev_but";
				}
				current_mc.attachMovie(button_link_id_str,node.attributes["id"],current_mc._childNextDepth);
				_root._prevInquiry_button_ref = current_mc[node.attributes["id"]];
*/
				current_mc.attachMovie("prev_but",node.attributes["id"],current_mc._childNextDepth);
				_root._prevInquiry_button_ref = current_mc[node.attributes["id"]];
			}
			else{
				current_mc.createClassObject(mx.controls.Button,node.attributes["id"],current_mc._childNextDepth);
			}
		}

        local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_button";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
        local_mc._childNextDepth = 1; // local XUL depth (levels) management
        local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		
		// autoPosition
		if (current_mc.layout == 'normal')
		{					
				
		current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;		
		
		var yPos:Number = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;		
		
		local_mc.move(xPos, yPos);
		
		}
		
		
		
		
		if (canvas_mc._layoutMode!="flow" && current_mc.layout != 'normal'){
        var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc.move(xPos, yPos);
		} else if (canvas_mc._layoutMode=="flow" && current_mc.layout != 'normal')
		{			
			local_mc.move(xPos, current_mc._ySize+15);
		}		
		
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 50;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
		if ((local_mc.id == "nextInquiry_button") || (local_mc.id == "prevInquiry_button"))
		{			
			local_mc._x = xPos;
			local_mc._y = yPos;
			local_mc._width = wVal;
			local_mc._height = hVal;
		}
		else{
			local_mc.move(xPos,yPos);
			local_mc.setSize(wVal,hVal);
		}

		var xulStyle_str:String = new String(node.attributes["style"]);
		var style_array:Array = xulStyle_str.split(";");
		for (var i = 0; i<style_array.length; i++) {
			var currentStyle_str:String = new String(style_array[i]);
			var styleArgs_array:Array = currentStyle_str.split(":");
			switch(styleArgs_array[0]){
				case "color":
/*
					local_mc.setStyle("buttonColor",styleArgs_array[1]);
					var toto = local_mc.getStyle("highlightColor");
					local_mc.setStyle("rollOverColor",parseInt(styleArgs_array[1]));
					local_mc.skinName._color.highlightColor = styleArgs_array[1];
					mx.skins.ColoredSkinElement.setColorStyle(this, "highlightColor");
*/
					break;
				default:
			}
		}

        var disabledState:Boolean = false;
		if(node.attributes["disabled"] != undefined){
			var tDisabled_str:String = new String(node.attributes["disabled"]);
			if(tDisabled_str.toUpperCase() == "TRUE"){
				disabledState = true;
			}
		}

		if((local_mc.id != "nextInquiry_button") || (local_mc.id == "prevInquiry_button")){
			local_mc.label = node.attributes["label"];
		}
        // for the oncommand event handling
        local_mc.onCommand = node.attributes["oncommand"];
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
        trace("XUL2Item: local_mc.onCommand: " + node.attributes["oncommand"]);

        var tButtonEvent_listener = new Object();

		function buttonEventClick_fct(eventObj){
			var fullCmd:String;
			var eventObj_target;
			if(eventObj == undefined){
				eventObj_target = this;
			}
			else{
				eventObj_target = eventObj.target;
			}
			fullCmd = eventObj_target.onCommand;
			trace("XUL2Item: fullCmd: " + fullCmd);
			if(eventObj_target.id == "nextInquiry_button"){
			// special ERA
				eventObj_target._targetExecutionLayer.nextInquiry();
			}
			else{
				if(eventObj_target.id == "prevInquiry_button"){
				// special ERA
					eventObj_target._targetExecutionLayer.prevInquiry();
				}
				else{
/*
			if(fullCmd.indexOf("!{WS(") != -1){
				var aTaoWS;
				aTaoWS = eventObj_target.taoWS;
				trace("taoWS before activate");
				aTaoWS.activateWS();
			}
			else{
*/
//            var cmdPart:String = fullCmd.substring(0,fullCmd.indexOf("("));
					var my_toolbox:tao_toolbox = new tao_toolbox();
					var cmdPart:String = my_toolbox.extractString(fullCmd,"","(",0,false);
					var argPart:String = my_toolbox.extractString(fullCmd,"(",")",0,false);
					var argArray:Array = new Array();
					var argTarget:String = new String();
					var objPart:Object;
					argArray.push(argPart);
					if (cmdPart.indexOf(".") != -1){
						argTarget = my_toolbox.extractString(cmdPart,"",".",0,false);
						if (argTarget.toUpperCase() == "TAO_TEST"){
							objPart = _level0;
						}
						else {
							objPart = eval(eventObj_target._targetExecutionLayer);
						}
						cmdPart = my_toolbox.extractString(cmdPart,".","",0,false);
					}
					else {
							objPart = eval(eventObj_target._targetExecutionLayer);
					}
					trace("XUL2Item real call is: " + objPart + "." + cmdPart + "(" + argPart + ")");
					eval(objPart + "." + cmdPart)(argPart);
					//var fctPart:Function = eval(cmdPart);
					//fctPart.apply(objPart,argArray);
	//			}
				}
			}
			eventObj_target._targetExecutionLayer.feedTrace("BUTTON","id="+eventObj_target.id,"taoHAWAI");
			eventObj_target._targetExecutionLayer.feedTrace("DOACTION","action="+fullCmd,"taoHAWAI");
		}
		tButtonEvent_listener.click = buttonEventClick_fct;

		if((local_mc.id != "nextInquiry_button") && (local_mc.id != "prevInquiry_button")){
			trace("XUL2Item: standard button: " + local_mc.id);
			local_mc.addEventListener("click", tButtonEvent_listener);
		}
		else{
			trace("XUL2Item: special button: " + local_mc.id);
			local_mc.onPress = buttonEventClick_fct;
		}

////////
var vCmd:String;
vCmd = local_mc.onCommand;
/*
if(vCmd.indexOf("!{WS(") != -1){
	var vTaoWS:taoWS;
	vTaoWS = new taoWS(local_mc);
	var vResultWS:String;
	vResultWS = vTaoWS.buildWS();
	trace(vResultWS);
	local_mc.taoWS = vTaoWS;
}
*/
////////

		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);		
		// ***********************************************************************************************
		
		
		return local_mc;
    }

// here XULimage
    private function xul_image(node:XML,current_mc,local_mc){
		var tlocal_mc:MovieClip;
		var plocal_mc:MovieClip;
		if(node.attributes["id"] == undefined){
			node.attributes["id"] = current_mc._name + "_image" + string(current_mc._childNextDepth + 1);
		}
		trace("XUL2Item: XUL image (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		current_mc.createEmptyMovieClip(node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc._repository = canvas_mc;
		var vCanvas = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
		if(node.attributes["type"] == "WS"){
		
		}
		else{
    		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
    		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
    		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : undefined;
    		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : undefined;
//			local_mc._lockroot=true;
			local_mc._x = xPos;
			
			
			// ***********************************************************************************************
			// autoPosition
			
			if (current_mc.layout == 'normal')
			{			
			current_mc.baseY = (current_mc.baseY == undefined) ? node.attributes["top"] : current_mc.baseY;	
			local_mc._y  = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc.baseY) : 0;
			} 
			
			
			local_mc._y = (canvas_mc._layoutMode == "flow" & current_mc.layout!='normal') ? current_mc._ySize + 15 : yPos;
			// ***********************************************************************************************
			
			
			var vOnCommand = node.attributes["oncommand"];
			var activeState:Boolean = (node.attributes["active"] != undefined) ? node.attributes["active"] : true ;
			var disabledState:Boolean = (node.attributes["disabled"] != undefined) ? node.attributes["disabled"] : false ;

			var my_toolbx:tao_toolbox = new tao_toolbox();
			var image_str:String = "";
			var image_arg_str:String = "";

			_level0.stopTestTimer();

			if(node.attributes["src"] != undefined){
				var tmp_str:String = new String(node.attributes["src"]);
				if(tmp_str.indexOf("?") != -1){
					image_str = my_toolbx.extractString(node.attributes["src"],"","?",0,false);
					image_arg_str = my_toolbx.extractString(node.attributes["src"],"?","",0,false);
					if((image_str.indexOf("era_rte.swf") != -1) || (image_str.indexOf("cba_rte.swf") != -1) || (image_str.indexOf("eXULiS.swf") != -1)){
							trace("u16_BLACK_dt_marker: before");
						if((image_arg_str.indexOf("u16_BLACK_dt.xml") != -1) && (image_str.indexOf("eXULiS.swf") != -1)){
							_root._u16_BLACK_dt_marker = true;
							trace("u16_BLACK_dt_marker: inside");
						}
						if((image_arg_str.substr(0,5) == "path=") || (image_arg_str.substr(0,5) == "file=")){
							var v_era_rte_path_str = image_arg_str.substring(5);
							trace("XUL2Item: era_rte path is " + v_era_rte_path_str);
							_root.era_rte_path_str = v_era_rte_path_str;
						}
						else{
							trace("XUL2Item: era_rte plugin found without path argument!");
						}
					}
				}
				else{
					image_str =  node.attributes["src"];
				}
			}
			var vTargetExecutionLayer = targetExecutionLayer_mc;
			current_mc._image_defRepository = {_image_arg:image_arg_str};
			var item_mcl:MovieClipLoader;
			var mclListener:Object;
			mclListener = new Object();
			mclListener.onLoadError = function(target_mc:MovieClip, errorCode:String) {
				trace("image load ERROR on " + image_str);
			};
			mclListener.onLoadInit = function(target_mc:MovieClip) {

//				_level0.stopTestDelayTimer();
//				vCanvas._parent.aBroadcaster.addEventListener( "xulEvent", target_mc.pluginListener ); // register our listener to xulEvent
				trace("Now the PluginListener is armed");

				if(wVal != undefined){
//					target_mc._width = wVal;
//					target_mc.width = wVal; // for mtasc
				}
				if(hVal != undefined){
//					target_mc._height = hVal;
//					target_mc.height = hVal; // for mtasc
				}
//				target_mc.setSize(wVal,hVal);

				target_mc._type = "xul_image";
				target_mc._repository = vCanvas;
//				target_mc._repository._xulListeners.push(pluginListener);
				target_mc._childNextDepth = 1; // local XUL depth (levels) management
				target_mc._parent._childNextDepth ++; // local XUL depth (levels) management
//				target_mc._x = xPos;
//				target_mc._y = yPos;
//				target_mc.move(xPos,yPos);
				target_mc._onCommand = vOnCommand;
				target_mc._parent.resultLabel = image_str;
				item_mcl.removeListener(mclListener);

				trace("vCanvas " + vCanvas);
				trace("image " + image_str + " exists with W:" + target_mc._width + " and H:" + target_mc._height);
				trace("image args " + image_arg_str);

				target_mc._image_arg = image_arg_str;

				if(!disabledState){
					var imgUpperCase_str:String = new String(image_str);
					imgUpperCase_str = imgUpperCase_str.toUpperCase();
					if((imgUpperCase_str.substr(-4,4) == ".SWF") && (activeState==false)){
						trace("GOTO xul_placeholder with plocal " + plocal_mc);
						target_mc._parent.createEmptyMovieClip(target_mc._name + "_placeHolder",target_mc._parent._childNextDepth);
						tlocal_mc = target_mc._parent[target_mc._name + "_placeHolder"];
						tlocal_mc._childNextDepth = 1; // local XUL depth (levels) management
						tlocal_mc._parent._childNextDepth ++; // local XUL depth (levels) management
						tlocal_mc._x = target_mc._x;
						tlocal_mc.left = target_mc._x;
						tlocal_mc._y = target_mc._y;
						tlocal_mc.top = target_mc._y;
						var twVal:Number = target_mc._width;
						var thVal:Number = target_mc._height;
						var titem_mcl:MovieClipLoader;
						var tmclListener:Object;
						tmclListener = new Object();
						tmclListener.onLoadInit = function(placeHolder_mc:MovieClip) {
							placeHolder_mc._width = twVal;
							placeHolder_mc._height = thVal;
							trace("image placeHolder exists on " + placeHolder_mc + " and command " + vOnCommand);		
							placeHolder_mc._type = "xul_placeholder";
							placeHolder_mc._childNextDepth = 1; // local XUL depth (levels) management
							placeHolder_mc._parent._childNextDepth ++; // local XUL depth (levels) management
					//			target_mc._x = xPos;
					//			target_mc._y = yPos;
							placeHolder_mc._onCommand = vOnCommand;
							placeHolder_mc.removeListener(tmclListener);
							if(vOnCommand != undefined){
								var tImageEvent_listener = new Object();
								tImageEvent_listener.click = function (){
									trace("XUL2Item placeHolder clicked for : " + this);
									trace("XUL2Item placeHolder clicked with " + this._onCommand);
									var fullCmd:String = this._onCommand;
									trace("XUL2Item: fullCmd: " + fullCmd);
									var my_toolbox:tao_toolbox = new tao_toolbox();
									var cmdPart:String = my_toolbox.extractString(fullCmd,"","(",0,false);
									var argPart:String = my_toolbox.extractString(fullCmd,"(",")",0,false);
									var argArray:Array = new Array();
									var argTarget:String = new String();
									var objPart:Object;
									argArray.push(argPart);
									if (cmdPart.indexOf(".") != -1){
										argTarget = my_toolbox.extractString(cmdPart,"",".",0,false);
										if (argTarget.toUpperCase() == "TAO_TEST"){
											objPart = _level0;
										}
										else {
											objPart = eval(vTargetExecutionLayer);
										}
										cmdPart = my_toolbox.extractString(cmdPart,".","",0,false);
									}
									else {
											objPart = eval(vTargetExecutionLayer);
									}
									trace("XUL2Item real call is: " + objPart + "." + cmdPart + "(" + argPart + ")");
									eval(objPart + "." + cmdPart)(argPart);
								}
								trace("now installing click event for " + imgUpperCase_str);
								placeHolder_mc.addEventListener("click", tImageEvent_listener);
								placeHolder_mc.onRelease = tImageEvent_listener.click;
							}
						};
						titem_mcl = new MovieClipLoader();
						titem_mcl.addListener(tmclListener);
						titem_mcl.loadClip("placeHolder.swf", tlocal_mc);
					}
					else {
						trace("NO_GOTO");
						var tImageEvent_listener = new Object();
						tImageEvent_listener.click = function (){
							trace("XUL2Item image clicked for : " + this);
							trace("XUL2Item image clicked with " + this._onCommand);
							var fullCmd:String = this._onCommand;
							trace("XUL2Item: fullCmd: " + fullCmd);
							var my_toolbox:tao_toolbox = new tao_toolbox();
							var cmdPart:String = my_toolbox.extractString(fullCmd,"","(",0,false);
							var argPart:String = my_toolbox.extractString(fullCmd,"(",")",0,false);
							var argArray:Array = new Array();
							var argTarget:String = new String();
							var objPart:Object;
							argArray.push(argPart);
							if (cmdPart.indexOf(".") != -1){
								argTarget = my_toolbox.extractString(cmdPart,"",".",0,false);
								if (argTarget.toUpperCase() == "TAO_TEST"){
									objPart = _level0;
								}
								else {
									objPart = eval(vTargetExecutionLayer);
								}
								cmdPart = my_toolbox.extractString(cmdPart,".","",0,false);
							}
							else {
									objPart = eval(vTargetExecutionLayer);
							}
							trace("XUL2Item real call is: " + objPart + "." + cmdPart + "(" + argPart + ")");
							eval(objPart + "." + cmdPart)(argPart);
						}
						target_mc.addEventListener("click", tImageEvent_listener);
						target_mc.onRelease = tImageEvent_listener.click;
					}
					trace("NO_WHERE");
				} 
			};
			item_mcl = new MovieClipLoader();
			item_mcl.addListener(mclListener);
/*
			current_mc.loadEraPlugin = function(){
				trace("loadEraPlugin entered");
				item_mcl.loadClip(image_str, local_mc);
			}

			current_mc.startCaching = function(filesDescStructure_array,current_ref,totalFilesSizes_num){
				trace("startCaching invoqued");
				var lv:LoadVars = new LoadVars();
				var local_pb:mx.controls.ProgressBar;
				current_ref.createClassObject(mx.controls.ProgressBar,"plugin_loader",current_ref,1000);
				local_pb = current_ref.plugin_loader;
				lv._owner = current_ref;
				local_pb.move(350,200);
				local_pb.labelPlacement = "bottom";
				local_pb.label = "Loading...";
				local_pb.mode = "manual";
				current_ref.createEmptyMovieClip("timer_mc", 999);
				var sumDowloadedFilesSizes_num = 0;
				current_ref.timer_mc.onEnterFrame = function() {
					var lvBytesLoaded:Number = lv.getBytesLoaded();
					var lvBytesTotal:Number = lv.getBytesTotal();
					if (lvBytesTotal != undefined) {
						trace("Loaded "+lvBytesLoaded+" of "+lvBytesTotal+" bytes (" + sumDowloadedFilesSizes_num + "/" + totalFilesSizes_num + ")");
						local_pb.setProgress(sumDowloadedFilesSizes_num + lvBytesTotal, totalFilesSizes_num);
					}
				};
				lv.onData = function(inString){
					sumDowloadedFilesSizes_num += this.getBytesLoaded();
					if (this.getBytesLoaded() == this.getBytesTotal()) {
						trace("file pre-loaded successfully.");
					}
					else {
						trace("An error occurred while pre-loading plugin.");
					}
					this._owner.loadNextFile_fct();
				}
				lv.onHTTPStatus = function(httpStatus : Number){
					this._owner.httpStatus = httpStatus;
					trace("cacheFile httpStatus " + httpStatus); //u4dipf			
				}
				current_ref.overlay_fct = function(){
					trace("Plugin (era_rte.swf) real load begins... but from browser's cache");
					delete current_ref.timer_mc.onEnterFrame;
					current_ref.destroyObject("plugin_loader");
					current_ref.loadEraPlugin();
				}
				current_ref.loadNextFile_fct = function(){
					var aFileToCache_str:String;
					local_pb.setProgress(sumDowloadedFilesSizes_num, totalFilesSizes_num);
					aFileToCache_str = filesDescStructure_array.shift();
					trace("loadNextFile_fct found " + aFileToCache_str);
					if(aFileToCache_str == undefined){
						current_ref.overlay_fct();
					}
					else{
						lv.load(current_ref._era_path + "resources/" + aFileToCache_str);
						trace("with reference " + current_ref._era_path + "resources/" + aFileToCache_str);
					}
				}
				current_ref.loadNextFile_fct();
//				lv.load("era_rte.swf");	
			}

			if(image_str.indexOf("era_rte.swf") != -1){
				trace("cacheFile called with " + image_str); //u4dipf
				var fileDesc_lv:LoadVars = new LoadVars();
				current_mc._era_path = image_arg_str.substr(5);
				fileDesc_lv._owner = current_mc;
				fileDesc_lv.onLoad = function(success:Boolean) {
					if(success){
						var filesDescStructure_array:Array = new Array();
						for (var prop in this) {
							var propName_str:String;
							propName_str = prop;
							if(propName_str.substr(0,1) == "#"){
								filesDescStructure_array.push(this[prop])
								trace(prop+" -> "+this[prop]);
							}
						}
						this._owner.startCaching(filesDescStructure_array,this._owner,parseInt(this["TOTALSIZE"],10) + 310000);
					}
					else{
						trace("Problem with resDirInfo.php return!");
						this._owner.loadEraPlugin();
					}
				};
				trace("call to ./resDirInfo.php?" + image_arg_str);
				fileDesc_lv.load("./resDirInfo.php?" + image_arg_str);
			}
			else{
				item_mcl.loadClip(image_str, local_mc);
			}
*/
			item_mcl.loadClip(image_str, local_mc);
		};
		var thisObj_obj:Object = {objRef:local_mc, objType:"imageMovieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		
		// ***********************************************************************************************
		// autoPosition ---------------- newSize
		current_mc.setYsize(local_mc._y + local_mc._height);
		// ***********************************************************************************************
		
		
		return local_mc;
    }
/*
// here XULprogressmeter
    private function xul_progressmeter(node:XML,current_mc,local_mc){
		trace("XUL2Item: XUL progressmeter (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		current_mc.createClassObject(mx.controls.ProgressBar,node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_progressmeter";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc.move(xPos,yPos);
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 150;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
		local_mc.setSize(wVal,hVal);
		var modeState:String;
		switch(node.attributes["mode"])
		{
			case "determined":
			{
				modeState = "manual";
				break;
			}
			default:
			{
				modeState = "manual";
				break;
			}
		}
		local_mc.mode = modeState;
		local_mc.label = " ";
		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		return local_mc;
    }
*/
// here XULmenulist
    private function xul_menulist(node:XML,current_mc,local_mc){
		trace("XUL2Item: XUL menulist triggered on (" + node.attributes["id"] + ") " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		current_mc.createEmptyMovieClip(node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_menulist";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		var local_dp:Array = new Array();
		local_mc._dataProvider = local_dp;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc._x = xPos;
		local_mc.left = xPos;
		local_mc._y = yPos;
		local_mc.top = yPos;
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : current_mc.width ;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 100;
		current_mc.dropdownWidth = wVal
		//current_mc.rowCount = hVal/22;
		var thisObj_obj:Object = {objRef:local_mc, objType:"movieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		return local_mc;
 // No Use For ERA		
    }
// here XULmenupopup
    private function xul_menupopup(node:XML,current_mc,local_mc){
		trace("XUL2Item: XUL menupopup triggered on (" + node.attributes["id"] + ") " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		current_mc.createClassObject(mx.controls.ComboBox,node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
//		trace("TOTO: " + local_mc + "(" + local_mc.getDepth() + ")");
//		local_mc.setDepthTo(DepthManager.kTopmost);
/*
		if (current_mc._parent.question_background != undefined){
			trace("TOTO:" + current_mc._parent.question_background.getDepth() + "(before)");
			local_mc.dropdown.depthChild0.swapDepths(current_mc._parent.question_background);
			trace("TOTO:" + current_mc._parent.question_background.getDepth() + "(after)");
		}
*/
		local_mc._type = "xul_menupopup";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;

		if (node.attributes["storeresult"]=="true")
		{
			trace("mySite store >>> "+node.attributes["id"]);
			
			var cbListener:Object = new Object();
			cbListener.nodeId=node.attributes["id"];
			cbListener.change = function(event_obj:Object) {
				event_obj.target._targetExecutionLayer.feedTrace("GLOBAL_VAR","website="+event_obj.target.selectedIndex,"service");
				event_obj.target._targetExecutionLayer.feedTrace("TRANSLATION","address="+event_obj.target.selectedItem.label,"service");
				var globalVars:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
				
				trace("mySite this.nodeId : "+this.nodeId);
				
				globalVars.data[this.nodeId] = event_obj.target.selectedIndex;
				globalVars.flush();
			};
			local_mc.addEventListener("change", cbListener);
		}
		
		
		
		if (node.attributes["rowCount"]!=undefined)
		{
			local_mc.rowCount=Number(node.attributes["rowCount"]);
		}

		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
//		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
//		local_mc.dropdown.swapDepths(20005);
/*
			for(var toto in local_mc.dropdown){
				trace("TOTO:" + toto + "(" + local_mc.dropdown[toto].getDepth() + ") = " + local_mc.dropdown[toto]);
			}
*/
		var yPos:Number;
		if (current_mc._parent.layout == 'normal'){
			current_mc._parent.baseY = (current_mc._parent.baseY == undefined) ? node.attributes["top"] : current_mc._parent.baseY;	
			yPos = (node.attributes["top"] != undefined) ? (node.attributes["top"] - current_mc._parent.baseY) : 0;
		} 
		else{
			yPos = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		}
		local_mc.move(xPos,yPos);
		var _inspector_obj = current_mc;
		var totLeftBorder_num:Number = xPos;
		while(_inspector_obj != _level0){
//			trace("KKK id: " + _inspector_obj);
			totLeftBorder_num += _inspector_obj._x;
			_inspector_obj = _inspector_obj._parent;
		}
//		trace("KKK totLeftBorder_num:" + totLeftBorder_num);
//		trace("KKK ------------------------");
		current_mc.totLeftBorder = totLeftBorder_num;
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 100;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 22;
		local_mc.setSize(wVal,hVal);
		local_mc.dataProvider = current_mc._dataProvider;
		var cboBox_listener:Object = new Object();
/*
cboBox_listener.modelChanged = function(evt){
    trace("XUL2Item: XUL menupopup: " + evt.eventName);
	evt.target.redraw();
}
local_mc.addEventListener("modelChanged", cboBox_listener);
*/
		var disabledState:Boolean = (node.attributes["disabled"] != undefined) ? node.attributes["disabled"] : false ;
		local_mc.enabled = !(disabledState);
		local_mc.label = node.attributes["label"];
		if(node.attributes["mandatory"] == "false"){ // that's not really a XUL attribute
			local_mc._repository._answered = "yes";
		}
		var listenerObject = new Object();
		listenerObject.change = function(eventObject:Object) {
			trace("COMBO change: " + eventObject.target.selectedIndex);
			eventObject.target._targetExecutionLayer.feedTrace("COMBOBOX","id="+eventObject.target.id+"|$*index="+eventObject.target.selectedIndex,"taoHAWAI");
			eventObject.target._targetExecutionLayer.feedTrace("INFORMATION","index="+eventObject.target.selectedIndex,"service");
//			if(_level0.robotTesting == true){
//				eventObject.target.text = "test";				
//			}
			eventObject.target._repository._answered = "yes";
			if(eventObject.target._repository._result_array == undefined){
				eventObject.target._repository._result_array = new Array();
			}
			var vRow_obj:Object = {name:eventObject.target._name,selected:String(eventObject.target.selectedIndex),propValue:eventObject.target.selectedItem.label};
			var objAlreadyRegistered_bool:Boolean = false;
			for(var vCpt=0;vCpt<eventObject.target._repository._result_array.length;vCpt++){
				var vRowResult_obj:Object = eventObject.target._repository._result_array[vCpt];
				if (vRowResult_obj.name == eventObject.target._name){
				    objAlreadyRegistered_bool = true;
				    break;
				}
			}
			if(objAlreadyRegistered_bool){
                eventObject.target._repository._result_array[vCpt] = vRow_obj;
            }
            else{
                eventObject.target._repository._result_array.push(vRow_obj);
            }
			for(var vCpt=0;vCpt<eventObject.target._repository._result_matrix.length;vCpt++){
				var vRowResult_obj:Object = eventObject.target._repository._result_matrix[vCpt];
				if (vRowResult_obj.name == eventObject.target._name){
					vRowResult_obj.selected = String(eventObject.target.selectedIndex);
    				eventObject.target._repository._result_matrix[vCpt] = vRowResult_obj;
	       			break;
				}
			}
		}

		listenerObject.open = function(eventObject:Object) {
			trace("COMBO open: " + eventObject.target + "(" + eventObject.target.getDepth() + ")");
			trace("COMBO DD-O: " + eventObject.target.dropdown + "(" + eventObject.target.dropdown.getDepth() + ")");
			trace("COMBO DD-O: " + _root.item + "(" + _root.item.getDepth() + ")");
			eventObject.target._targetExecutionLayer.feedTrace("COMBOBOX","id="+eventObject.target.id+"|$*action=OPEN","taoHAWAI");
			if(eventObject.target.dropdown.getDepth() < _root.item.getDepth()){
				eventObject.target.dropdown.swapDepths(_root.item);
			}
			trace("COMBO DD-A: " + eventObject.target.dropdown + "(" + eventObject.target.dropdown.getDepth() + ")");
			trace("COMBO DD-A: " + _root.item + "(" + _root.item.getDepth() + ")");
		}
		listenerObject.close = function(eventObject:Object) {
			eventObject.target._targetExecutionLayer.feedTrace("COMBOBOX","id="+eventObject.target.id+"|$*action=CLOSE","taoHAWAI");
			if(eventObject.target.dropdown.getDepth() < _root.item.getDepth()){
				eventObject.target.dropdown.swapDepths(_root.item);
			}
		}

// Add Listener.
		local_mc.addEventListener("change", listenerObject);
		local_mc.addEventListener("open", listenerObject);
		local_mc.addEventListener("close", listenerObject);


		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		current_mc._parent.setYsize(local_mc._y + local_mc._height + 15);
		return local_mc;
    }
// here XULmenuitem
    private function xul_menuitem(node:XML,current_mc,local_mc){
//		trace("XUL2Item: XUL menuitem triggered on (" + node.attributes["id"] + ") " + current_mc._name + " on depth: " + current_mc._childNextDepth);
/*
		current_mc.createEmptyMovieClip(node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_menuitem";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0 ;
		local_mc._x = xPos;
		local_mc.left = xPos;
		local_mc._y = yPos;
		local_mc.top = yPos;
*/
		var labelVal:Number = (node.attributes["label"] != undefined) ? node.attributes["label"] : "" ;
		var dataVal:Number = (node.attributes["value"] != undefined) ? node.attributes["value"] : "";
		var vIndex:Number = current_mc._parent._dataProvider.length;
		current_mc._parent._dataProvider.addItem({label:labelVal,data:dataVal}); //.push({label: labelVal, data: dataVal});
		var my_toolbox:tao_toolbox = new tao_toolbox();
		var minWidthHeight = my_toolbox.evaluateLabelSize(labelVal);
		var tmpMinWidth = minWidthHeight.minWidth;
//		trace("KKK tmpMinWidth:" + tmpMinWidth);
		if(current_mc.dropdownWidth < tmpMinWidth){
			if(tmpMinWidth < (295 - current_mc._parent.totLeftBorder)){
				current_mc.dropdownWidth = tmpMinWidth + 4;
			}
			else{
				current_mc.dropdownWidth = (295 - current_mc._parent.totLeftBorder);
			}
		}
//		trace("KKK dropdownWidth:" + current_mc.dropdownWidth);
//		trace("KKK ------------------------");
//		trace("KKK totLeftBorder:" + current_mc._parent.totLeftBorder);
//		trace("KKK ------------------------");

//		trace("XUL2Item: XUL menuitem vIndex: " + vIndex);
		if(node.attributes["selected"] == "true"){
			current_mc.selectedIndex = vIndex;
		}
		var vRowResult_obj:Object = {name:local_mc._name,selected:"",propValue:"",groupName:"#MENUITEMSSET#"};
		if(canvas_mc._result_array != undefined){
			for(var vCpt=canvas_mc._result_array.length - 1;vCpt >= 0;vCpt--){
				var vRow_obj:Object = canvas_mc._result_array[vCpt];
//				trace("=== vRow_obj.groupName=" + vRow_obj.groupName + " name=" + vRow_obj.name + " selected=" + vRow_obj.selected);
				if (vRow_obj.name == current_mc._name){
					current_mc._repository._answered = "yes";
					current_mc.selectedIndex = parseInt(vRow_obj.selected);
					vRowResult_obj.selected = vRow_obj.selected;
					break;
				}
			}
		}
		var objAlreadyRegistered_bool:Boolean = false;
		for(var vCpt=0;vCpt<current_mc._repository._result_matrix.length;vCpt++){
			var vRowResult_obj:Object = current_mc._repository._result_matrix[vCpt];
			if (vRowResult_obj.name == current_mc._name){
			    objAlreadyRegistered_bool = true;
				break;
			}
		}
		if(!objAlreadyRegistered_bool){
			current_mc._repository._result_matrix.push(vRowResult_obj);
		}

//		var thisObj_obj:Object = {objRef:local_mc, objType:"movieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
//		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
//		return local_mc;
    }

/**
* @method private translateXML
* @description core of the XUL2Item class
*/    
    private function translateXML (from, path, name, position, current_mc) 
    {
		
		
		
		var local_mc:MovieClip;
		
		
		var nodes, node, old_path;
		
		if (path == undefined) {
			trace("XUL2Item: XUL translation started on " + canvas_mc._name);
			current_mc = canvas_mc;
			path = this;
			name = "oResult";
		}
		
		path = path[name];
		if (from == undefined) {
			from = new XML (String(this.xml));
			from.ignoreWhite = true;
		}
		if (from.hasChildNodes ()) {
			nodes = from.childNodes;
			if (position != undefined) {
				var old_path = path;
				path = path[position];
			}
			while (nodes.length > 0) {
				node = nodes.shift ();
				if (node.nodeName != undefined) {
					var __obj__ = new Object ();
					__obj__.attributes = node.attributes;
					__obj__.data = node.firstChild.nodeValue;
					if (position != undefined) {
						var old_path = path;
					}
					if (path[node.nodeName] == undefined) {
						path[node.nodeName] = new Array ();
					}
					path[node.nodeName].push (__obj__);
					name = node.nodeName;
					position = path[node.nodeName].length - 1;
//trace("flow");
					if(node.attributes["class"] == "inquiryContainer_box"){
//						trace("flow : in");
						canvas_mc._layoutMode = "flow";
					}

// GUI factory begins here
					switch (node.nodeName){
						case "xul":
							local_mc = xul_root(node,current_mc,local_mc);
							break;
						case "box":
							local_mc = xul_box(node,current_mc,local_mc);
							break;
						case "button":
							local_mc = xul_button(node,current_mc,local_mc);
							break;
						case "checkbox":
							local_mc = xul_checkbox(node,current_mc,local_mc);
							break;
						case "image":
							local_mc = xul_image(node,current_mc,local_mc);
							break;
						case "label":
							local_mc = xul_label(node,current_mc,local_mc);
							break;
/*
						case "progressmeter":
							local_mc = xul_progressmeter(node,current_mc,local_mc);
							break;
*/
						case "radio":
							local_mc = xul_radio(node,current_mc,local_mc);
							break;
						case "radiogroup":
							local_mc = xul_radiogroup(node,current_mc,local_mc);
							break;
						case "textbox":
							local_mc = xul_textbox(node,current_mc,local_mc);
							break;
						case "menulist":
							local_mc = xul_menulist(node,current_mc,local_mc);
							break;
						case "menupopup":
							local_mc = xul_menupopup(node,current_mc,local_mc);
							break;
						case "menuitem":
							local_mc = xul_menuitem(node,current_mc,local_mc);
							break;
// here unhandled tags
						default:
							trace("XUL2Item: XUL tag undefined: " + node.nodeName);
					}
				}

				if (node.hasChildNodes ()) {					
					this.translateXML (node, path, name, position, local_mc);					
				} else
				{
					if(node.attributes["class"] == "inquiryContainer_box"){
//						trace("flow : out");
						canvas_mc._layoutMode = "normal";
					}
				}
			}
		}
		return this.oResult;
	}
}
