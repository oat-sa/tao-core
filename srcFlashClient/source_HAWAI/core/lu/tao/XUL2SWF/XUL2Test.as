import lu.tao.utils.tao_toolbox;
import lu.tao.utils.Event;
//import lu.tao.taoWS.taoWS;
/**
* XUL 2 SWF
* @author Raynald Jadoul
* @description Translates XUL syntax in Flash native components
* @usage data = new XUL2Test().parseXML(anXML);

*/
class lu.tao.XUL2SWF.XUL2Test extends XML {
    private var oResult:Object = new Object ();
    private var oXML:XML;
	private var canvas_mc:MovieClip;
	private var targetExecutionLayer_mc:MovieClip;
	private var xulBroadcaster;
	private var xulListeners:Array;
/**
* * Constructor
* * initializes the canvas movie clip
*/
	function XUL2Test(base_mc:MovieClip,target_mc:MovieClip) {
//		trace("XUL2Test: canvas initialized to " + target_mc._name);
		canvas_mc = base_mc;
		targetExecutionLayer_mc = target_mc;
		xulBroadcaster = new Event();
		canvas_mc._xulBroadcaster = xulBroadcaster;
		xulListeners = new Array();
		canvas_mc._xulListeners = xulListeners;
	}
/**
* @method get xml
* @description return the xml passed in the parseXML method
* @usage theXML = XUL2Test.xml
*/
    public function get xml():XML{
        return oXML    
    }
/**
* @method public parseXML
* @description return the parsed Object
* @usage XUL2Test.parseXML( theXMLtoParse );

* @param sFile XML
* @returns an Object with the contents of the passed XML
*/
    public function parseXML (sXML:XML):Object {
//		trace("XUL2Test: XUL parsing started on node " + sXML.firstChild.nodeName);
		this.oResult = new Object ();
		this.oXML = sXML;
		this.oResult = this.translateXML();
		return this.oResult;
    }
// here we connect the canvas to the XUL construction
    private function xul_root(node:XML,current_mc,local_mc){
		trace("XUL2Test: XUL start tag encountered on " + current_mc._name + " on depth: 1");
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
		local_mc._x = 0;
		local_mc.left = 0;
		local_mc._y = 0;
		local_mc.top = 0;
		var thisObj_obj:Object = {objRef:local_mc, objType:"movieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		return local_mc;
    }
// here XULbox
    private function xul_box(node:XML,current_mc,local_mc){
		trace("XUL2Test: XUL box (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		current_mc.createEmptyMovieClip(node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_box";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc._x = xPos;
		local_mc.left = xPos;
		local_mc._y = yPos;
		local_mc.top = yPos;
		var thisObj_obj:Object = {objRef:local_mc, objType:"movieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		return local_mc;
    }
// here XULlabel
    private function xul_label(node:XML,current_mc,local_mc){
        trace("XUL2Test: XUL label (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth + " with node: " + node.toString());
        current_mc.createClassObject(mx.controls.Label,node.attributes["id"],current_mc._childNextDepth);
        local_mc = current_mc[node.attributes["id"]];
		local_mc.drawFocus = "";
		local_mc._type = "xul_label";
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
        local_mc.html = true;
        local_mc.autoSize = true;
        local_mc.text = node.attributes["value"];
		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		return local_mc;
    }
/* no need for ERA
// here XULtextbox
    private function xul_textbox(node:XML,current_mc,local_mc){
		trace("XUL2Test: XUL textbox (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth + " with node: " + node.toString());
		current_mc.createClassObject(mx.controls.TextArea,node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		local_mc.drawFocus = "";
		local_mc._type = "xul_textbox";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var vRowResult_obj:Object = {name:local_mc._name,selected:"",propValue:""};
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		local_mc.move(xPos,yPos);
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 150;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
		local_mc.setSize(wVal,hVal);
        var readOnly_bool:Boolean = (node.attributes["readonly"] == "true") ? true : false ;
        local_mc.editable = !readOnly_bool;
		local_mc.html = true;
		var tmpWrap_bool:Boolean = new Boolean(node.attributes["wrap"]);
		local_mc.wordWrap = (node.attributes["wrap"] != undefined) ? tmpWrap_bool : false;
		local_mc.text = node.attributes["value"];
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
		local_mc.maxChars = (node.attributes["maxlength"] != undefined) ? parseInt(node.attributes["maxlength"]) : null;
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
				propVal_str = elementsArray_array[1];
				if(isNaN(parseInt(propVal_str))){
					local_mc.setStyle(propName_str,propVal_str);
				}
				else{
					local_mc.setStyle(propName_str,parseInt(propVal_str));
				}
			}
		}
		var listenerObject = new Object();
		listenerObject.change = function(eventObject){
//			if(_level0.robotTesting == true){
//				eventObject.target.text = "test";				
//			}
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
		var thisObj_obj:Object = {objRef:local_mc, objType:"component", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		return local_mc;
    }
*/
// here XULbutton
    private function xul_button(node:XML,current_mc,local_mc){
        trace("XUL2Test: XUL button (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		if(node.attributes["id"] == "help_button"){
			current_mc.attachMovie("help_but",node.attributes["id"],current_mc._childNextDepth);
		}
		else{
			current_mc.createClassObject(mx.controls.Button,node.attributes["id"],current_mc._childNextDepth);
		}

        local_mc = current_mc[node.attributes["id"]];
		local_mc._type = "xul_button";
		local_mc.id = node.attributes["id"];
		local_mc._repository = canvas_mc;
        local_mc._childNextDepth = 1; // local XUL depth (levels) management
        local_mc._parent._childNextDepth ++; // local XUL depth (levels) management
		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : 50;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : 25;
		if(local_mc.id == "help_button"){
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

		if(local_mc.id != "help_button"){
			local_mc.label = node.attributes["label"];
		}
        // for the oncommand event handling
        local_mc.onCommand = node.attributes["oncommand"];
		local_mc._targetExecutionLayer = targetExecutionLayer_mc;
        trace("XUL2Test: local_mc.onCommand: " + node.attributes["oncommand"]);

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
		trace("XUL2Test: fullCmd: " + fullCmd);
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
				trace("XUL2Test real call is: " + objPart + "." + cmdPart + "(" + argPart + ")");
				eval(objPart + "." + cmdPart)(argPart);
				//var fctPart:Function = eval(cmdPart);
				//fctPart.apply(objPart,argArray);
//			}
			eventObj_target._targetExecutionLayer.feedTrace("BUTTON","id="+eventObj_target.id,"taoHAWAI");
			eventObj_target._targetExecutionLayer.feedTrace("DOACTION","action="+fullCmd,"taoHAWAI");
		}

        tButtonEvent_listener.click = buttonEventClick_fct;
		if(local_mc.id != "help_button"){
			trace("XUL2Test: standard button: " + local_mc.id);
			local_mc.addEventListener("click", tButtonEvent_listener);
		}
		else{
			trace("XUL2Test: special button: " + local_mc.id);
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
		return local_mc;
    }

// here XULimage
    private function xul_image(node:XML,current_mc,local_mc){
		var tlocal_mc:MovieClip;
		var plocal_mc:MovieClip;
		if(node.attributes["id"] == undefined){
			node.attributes["id"] = current_mc._name + "_image" + string(current_mc._childNextDepth + 1);
		}
		trace("XUL2Test: XUL image (" + node.attributes["id"] + ") triggered on " + current_mc._name + " on depth: " + current_mc._childNextDepth);
		current_mc.createEmptyMovieClip(node.attributes["id"],current_mc._childNextDepth);
		local_mc = current_mc[node.attributes["id"]];
		trace("XUL2Test: XUL image is " + local_mc);
		local_mc._repository = canvas_mc;
		var vCanvas = canvas_mc;
		local_mc._childNextDepth = 1; // local XUL depth (levels) management
		local_mc._parent._childNextDepth ++; // local XUL depth (levels) management

		var xPos:Number = (node.attributes["left"] != undefined) ? parseInt(node.attributes["left"]) : 0;
		var yPos:Number = (node.attributes["top"] != undefined) ? parseInt(node.attributes["top"]) : 0;
		var wVal:Number = (node.attributes["width"] != undefined) ? parseInt(node.attributes["width"]) : undefined;
		var hVal:Number = (node.attributes["height"] != undefined) ? parseInt(node.attributes["height"]) : undefined;
//			local_mc._lockroot=true;
		local_mc._x = xPos;
		local_mc._y = yPos;
		var vOnCommand = node.attributes["oncommand"];
		var activeState:Boolean = (node.attributes["active"] != undefined) ? node.attributes["active"] : true ;
		var disabledState:Boolean = (node.attributes["disabled"] != undefined) ? node.attributes["disabled"] : false ;

		var my_toolbx:tao_toolbox = new tao_toolbox();
		var image_str:String = "";
		var image_arg_str:String = "";

		_level0.stopTestTimer();

		var tmp_str:String = new String(node.attributes["src"]);
		if(tmp_str.indexOf("?") != -1){
			image_str = my_toolbx.extractString(node.attributes["src"],"","?",0,false);
			image_arg_str = my_toolbx.extractString(node.attributes["src"],"?","",0,false);
		}
		else{
			image_str =  node.attributes["src"];
		}

//		var image_str:String = (node.attributes["src"] != undefined) ? node.attributes["src"] : "undefined.swf" ;
		var vTargetExecutionLayer = targetExecutionLayer_mc;
		var item_mcl:MovieClipLoader;
		var mclListener:Object;
		mclListener = new Object();
		mclListener.onLoadError = function(target_mc:MovieClip, errorCode:String) {
			trace("image load ERROR on " + image_str);
		};
		mclListener.onLoadInit = function(target_mc:MovieClip) {
			trace("Now the PluginListener is armed");

			target_mc._type = "xul_image";
			target_mc._repository = vCanvas;
			target_mc._childNextDepth = 1; // local XUL depth (levels) management
			target_mc._parent._childNextDepth ++; // local XUL depth (levels) management
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
				trace("NO_GOTO");
				var tImageEvent_listener = new Object();
				tImageEvent_listener.click = function (){
					trace("XUL2Test image clicked for : " + this);
					trace("XUL2Test image clicked with " + this._onCommand);
					var fullCmd:String = this._onCommand;
					trace("XUL2Test: fullCmd: " + fullCmd);
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
					trace("XUL2Test real call is: " + objPart + "." + cmdPart + "(" + argPart + ")");
					eval(objPart + "." + cmdPart)(argPart);
				}
				target_mc.addEventListener("click", tImageEvent_listener);
				target_mc.onRelease = tImageEvent_listener.click;
				trace("NO_WHERE");
			} 
		};
		item_mcl = new MovieClipLoader();
		item_mcl.addListener(mclListener);
		item_mcl.loadClip(image_str, local_mc);

		var thisObj_obj:Object = {objRef:local_mc, objType:"imageMovieClip", xulType:local_mc._type, xulID:node.attributes["id"]};
		targetExecutionLayer_mc._widgetsRepository_array.push(thisObj_obj);
		return local_mc;
    }

/**
* @method private translateXML
* @description core of the XUL2Test class
*/    
    private function translateXML (from, path, name, position,current_mc) {
		var local_mc:MovieClip;
		var nodes, node, old_path;
		if (path == undefined) {
			trace("XUL2Test: XUL translation started on " + canvas_mc._name);
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
						case "image":
							local_mc = xul_image(node,current_mc,local_mc);
							break;
						case "label":
							local_mc = xul_label(node,current_mc,local_mc);
							break;
/* no need for ERA
						case "textbox":
							local_mc = xul_textbox(node,current_mc,local_mc);
							break;
*/
// here unhandled tags
						default:
							trace("XUL2Test: XUL tag undefined: " + node.nodeName);
					}
				}
				if (node.hasChildNodes ()) {
					this.translateXML (node, path, name, position,local_mc);
				}
			}
		}
		return this.oResult;
	}
}
