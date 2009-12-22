import com.eXULiS.XUL.XULelement;
import mx.utils.Delegate;

class com.eXULiS.XUL.XULtextbox extends XULelement {
	public var _obj:TextField;
	private var _obj_tf:TextFormat;
	public var _invocation_str:String;
	public var _handler_str:String;

	function XULtextbox(xulParent,xulDef:XMLNode){
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 100;
	}
	function create(){
		trace("XULtextbox (create): " + _type + " (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);

		_obj = _objParent.createTextField(_objDef.attributes["id"], _objParent._childNextDepth, 0, 0, 0, 0);
		_obj.multiline=true;
		
		_obj.restrict = (_objDef.attributes["restrict"] != undefined) ? _objDef.attributes["restrict"] : null;
		
		/*_obj.autosize = "Left";
		_obj.wordWrap = true;
		_obj.html = true;

		_obj._x = (_objDef.attributes["left"] != undefined) ? Number(_objDef.attributes["left"]):null;
		_obj._y = (_objDef.attributes["top"] != undefined) ? Number(_objDef.attributes["top"]):null;
		_obj._width = (_objDef.attributes["width"] != undefined) ? Number(_objDef.attributes["width"]):null;
		_obj._height = (_objDef.attributes["height"] != undefined) ? Number(_objDef.attributes["height"]):null;
		_obj.selectable = (_objDef.attributes["disabled"] != undefined ? Boolean(_objDef.attributes["disabled"]):null;
		_obj.maxChars = (_objDef.attributes["maxlength"] != undefined ? Number(_objDef.attributes["maxlength"]):null;
		_obj.multiline = (_objDef.attributes["multiline"] != undefined ? Boolean( (_objDef.attributes["multiline"] == "true") ? true: ((_objDef.attributes["multiline"] == "false") ? false: true) ):null;*/
		_obj.wordWrap = (_objDef.attributes["wrap"] != undefined) ? Boolean(_objDef.attributes["wrap"]):null;
		_obj.selectable = (_objDef.attributes["readonly"] != undefined) ? Boolean(_objDef.attributes["readonly"]):null;
		_obj.background = (_objDef.attributes["background"] != undefined) ? Boolean(_objDef.attributes["background"]):null;
		_obj.password = (_objDef.attributes["type"].lowerCase() == "password") ? true : null;
		_obj.htmlText = (_objDef.attributes["value"] != undefined) ? _objDef.attributes["value"] : "";
		_obj.tabIndex = (_objDef.attributes["tabIndex"] != undefined) ? Number(_objDef.attributes["tabIndex"]) : null;

		_obj = super.create(_obj,this,1);
		_obj.setStyle = this.setStyle;
		_obj = super.applyStyle(_obj);

		if (_objDef.attributes["onfocus"]){
			_obj.onSetFocus = Delegate.create(this, onTfieldEvent);
		}
		else{
			_obj.onSetFocus = Delegate.create(this, onTfieldEventFeed);
		}

		_obj.onKillFocus = Delegate.create(this, onKillTfieldEvent);

		if (_objDef.attributes["onchange"]){
			trace("XULtextbox _type: " + _type + " for onChangeTfieldEvent");
			_obj.onChanged = Delegate.create(this,onChangeTfieldEvent);
		}

		setLayout();

		return _obj;
	}

	function onChangeTfieldEvent(){
		trace("XULtextbox _type: " + _type + " for onChangeTfieldEvent");
		for(var vCpt_num:Number = 0;vCpt_num < _actions.length;vCpt_num++){
			var vAction_obj:Object = _actions[vCpt_num];
			if(vAction_obj.type == "change"){
				toolbox.wrapRun(vAction_obj.action,this);
				
//				trace("feedTrace for DOACTION, Stimulus: " + "action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + vAction_obj.action);
//				_level0.currentItemRootLevel.feedTrace("DOACTION","action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + vAction_obj.action,"stimulus");
			}
		}
	}

	function onKillTfieldEvent(){
		trace("XULtextbox _type: " + _type + " for onKillTfieldEvent");
		if (_type == "textbox") {
			
			var htmlState:Boolean = _obj.html;
			_obj.html = false;
			var cdataStr:String = escape(_obj.text);
			_obj.html = htmlState;

			if (_objDef.attributes["id"] != "url") {
				trace("feedTrace for TEXTBOX_KILLFOCUS, Stimulus: " + "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"] + _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "value"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+cdataStr);
				_level0.currentItemRootLevel.feedTrace("TEXTBOX_KILLFOCUS","id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"] + _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "value"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+cdataStr,"stimulus");
			}
		}
	}
	
	function onTfieldEventFeed(){
		trace("XULtextbox _type: " + _type + " for onTfieldEventFeed");
		if (_type == "textbox") {
			
			var htmlState:Boolean = _obj.html;
			_obj.html = false;
			var cdataStr:String = escape(_obj.text);
			_obj.html = htmlState;

			if (_objDef.attributes["id"] != "url") {
				var event_str:String = "TEXTBOX_ONFOCUS";
				var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"] +
				_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "value"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+cdataStr;
				trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
				_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // TEXTBOX_ONFOCUS
			}
		}
		else if (_type == "description") {
			
			// get the target (e.g. _popup) and href (e.g. u07_pg1_popup1) attributes
			if (_objDef.attributes["class"] == "hlink") {
				//trace("@href: " + _objDef.attributes["href"]);
				//trace("@target: " + _objDef.attributes["target"]);
				
				var event_str:String = "TEXTLINK";
				var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"] +
				_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "href"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["href"] + 
				_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "target"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["target"];
				trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
				_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // TEXTLINK
			}
		}
		else if (_type == "label") {
		}
	}

	function onTfieldEvent(){
		
		trace("XULtextbox _type: " + _type + " for onTfieldEvent");
		
		if (_type == "textbox") {
			
			var htmlState:Boolean = _obj.html;
			_obj.html = false;
			var cdataStr:String = escape(_obj.text);
			_obj.html = htmlState;
	
			toolbox.wrapRun(_objDef.attributes["onfocus"],this);
			//~ trace("feedTrace for TEXTBOX_ACTION_ONFOCUS, Stimulus: " + 
			//~ "action"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["onfocus"] +
			//~ _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "value"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+cdataStr);
			//~ _level0.currentItemRootLevel.feedTrace("TEXTBOX_ONFOCUS_ACTION","action"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["onfocus"] +
			//~ _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "value"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+cdataStr,"stimulus");
			
			var event_str:String = "TEXTBOX_ONFOCUS";
			var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"] +
			_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "value"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+cdataStr;
			trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
			_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // TEXTBOX_ONFOCUS
		}
	}

	function setStyle(propName,propVal){
		trace("XULtextbox (" + id + ") propName=" + propName + " propVal=" + propVal);
		this["html"] = true;
/*
		_obj.autoSize = "Left";
		_obj.wordWrap = true;
		_obj.html = true;
		_obj.border = true;
		
		_obj.type = "input";
		//_obj.selectable = (_objDef.attributes["disabled"] != undefined ) ? Boolean(_objDef.attributes["disabled"]):null;
		_obj.maxChars = (_objDef.attributes["maxlength"] != undefined) ? Number(_objDef.attributes["maxlength"]):null;
		_obj.maxChars = (_objDef.attributes["size"] != undefined) ? Number(_objDef.attributes["maxlength"]):null;
		
		if ( (_objDef.attributes["multiline"] != undefined) && (_objDef.attributes["multiline"] == "false") ) {
				_obj.multiline = false;
		}
		else {
				_obj.multiline = true;
		}
		//_obj.multiline = (_objDef.attributes["multiline"] != undefined) ? Boolean(_objDef.attributes["multiline"]):null;
		
		_obj.wordWrap = (_objDef.attributes["wrap"] != undefined) ? Boolean(_objDef.attributes["wrap"]):null;
		//_obj.selectable = (_objDef.attributes["readonly"] != undefined) ? Boolean(_objDef.attributes["readonly"]):null;
		_obj.background = (_objDef.attributes["background"] != undefined) ? Boolean(_objDef.attributes["background"]):null;
		_obj.password = (_objDef.attributes["type"].lowerCase() == "password") ? true : null;
		_obj.htmlText = (_objDef.attributes["value"] != undefined) ? toolbox.wrapRun(_objDef.attributes["value"], _guiSource,"SingleNode","String") : "" ;
		_obj.tabIndex = (_objDef.attributes["tabIndex"] != undefined) ? Number(_objDef.attributes["tabIndex"]) : null ;	
		
		_obj = super.applyStyle(_obj);
*/

		switch(propName){
			// restrict as a style property
			/*case("restrict"): {
				this["_exulis"]["restrict"] = (propVal == "numeric") ? "0-9" : null;
				trace("restrict: " + propVal);
				break;
			}*/
			case("size"):
			case("maxlength"):
			case("max-length"):{
				this["_exulis"]["maxChars"] = propVal;
				break;
			}
			case("disabled"):{
				this["_exulis"]["selectable"] = ((propVal == "true") || (propVal == "yes") || (propVal == "on") || (propVal == "1")) ? false : true;
				break;
			}
			case("color"):
			case("font-color"):
			case("fontColor"):
			case("text-color"):
			case("textColor"):{
				this["_exulis"]["textColor"] = this["_exulis"].colors.getColor(String(propVal));
				break;
			}
			case("background"):{
				if((propVal == "true") || (propVal == "yes") || (propVal == "on") || (propVal == "solid")){
					this["_exulis"]["background"] = true;
				}
				else{
					this["_exulis"]["backgroundColor"] = this["_exulis"].colors.getColor(String(propVal));
					if(this["_exulis"]["backgroundColor"] != undefined){
						this["_exulis"]["background"] = true;
					}
				}
				break;
			}
			case("background-color"):
			case("backgroundColor"):{
				this["_exulis"]["backgroundColor"] = this["_exulis"].colors.getColor(String(propVal));
				if(this["_exulis"]["backgroundColor"] != undefined){
					this["_exulis"]["background"] = true;
				}
				break;
			}
			case("border"):
			case("border-style"):
			case("borderStyle"):{
				this["_exulis"]["border"] = ((propVal == "none") || (propVal == "0")) ? false : true;
				break;
			}
			case("border-color"):
			case("borderColor"):{
				this["_exulis"]["borderColor"] = this["_exulis"].colors.getColor(String(propVal));
				if(this["_exulis"]["borderColor"] != undefined){
					this["_exulis"]["border"] = true;
				}
				break;
			}
			case("border-size"):
			case("borderSize"):{
				this["_exulis"]["borderSize"] = (isNaN(propVal)) ? undefined : propVal;
				break;
			}
			case("multiline"): {
				this["_exulis"]["multiline"] = ((propVal == "true") || (propVal == "yes") || (propVal == "on") || (propVal == "1")) ? true : false;
				break;
			}
			case("password"):{
				this["_exulis"][propName] = ((propVal == "true") || (propVal == "yes") || (propVal == "on") || (propVal == "1")) ? true : false;
				break;
			}
			case("readonly"):
			case("read-only"):
			case("readOnly"):{
				this["_exulis"]["readOnly"] = ((propVal == "true") || (propVal == "yes") || (propVal == "on") || (propVal == "1")) ? true : false;
				break;
			}
			case("plain-text"):{
				this["_exulis"]["html"] = false;
				break;
			}
			case("font-weight"):
			case("fontWeight"):{
				this["_exulis"]["fontWeight"] = ((propVal == "bold") || (propVal == "bolder")) ? true : false;
				break;
			}
			case("word-wrap"):
			case("wordWrap"):{
				this["_exulis"]["wordWrap"] = ((propVal == "true") || (propVal == "yes") || (propVal == "on") || (propVal == "1")) ? true : false;
				break;
			}
			case("font-size"):
			case("fontSize"):{
				this["_exulis"]["fontSize"] = String(propVal);
				break;
			}
			case("font-family"):
			case("fontFamily"):{
				this["_exulis"]["fontFamily"] = (propVal != "") ? propVal : "";
				break;
			}
			case("font-style"):
			case("fontStyle"):{
				this["_exulis"]["fontStyle"] = (propVal != "italic") ? false : true;
				break;
			}
			case("text-decoration"):
			case("textDecoration"):{
				this["_exulis"]["textDecoration"] = (propVal != "underline") ? false : true;
				break;
			}
			case("text-align"):
			case("textAlign"):{
				this["_exulis"]["textAlign"] = (propVal != "") ? propVal : "";
				break;
			}
			case("leading"):{
				this["_exulis"]["leading"] = String(propVal);
				break;
			}
			default:{
				this["_exulis"][propName] = propVal;
			}
		}
	}
	function setLayout(){
		trace("XULtextbox (setLayout): " + _type + " for " + id + ": special properties setting, t:" + this.top + " l:" + this.left + " w:" + this.width + " h:" + this.height);
		_obj._y = this.top;
		_obj._x = this.left;

		_obj._width = (this.width != undefined) ? Number(this.width):100;
		_obj._height = (this.height != undefined) ? Number(this.height):26;
		_obj.multiline = (this["multiline"] != undefined) ? this["multiline"]:true;

		this["leading"] = ( (this["leading"] != "") && (this["leading"] != undefined)) ? this["leading"] : (( (_root["leading"] != "") && (_root["leading"] != undefined)) ? _root["leading"] : 0);

		if(this.height != undefined){
			_obj.autoSize = false;
		}
		// restrict as a style property
		//_obj.restrict = (this["restrict"] == undefined) ? null : this["restrict"];
		_obj.wordWrap = (this["wordWrap"] == undefined) ? ((this._type == "label") ? false : true) : this["wordWrap"];
		_obj.border = (this["border"] == undefined) ? ((this._type == "textbox") ? true : false) : this["border"];
		_obj.borderColor = (this["borderColor"] == undefined) ? 0x000000 : this["borderColor"];
		_obj.background = (this["background"] == undefined) ? false : this["background"];
		_obj.backgroundColor = (this["backgroundColor"] == undefined) ? 0xFFFFFF : this["backgroundColor"];
	//	_obj.html = (this["html"] == undefined || this["html"] != true) ? false : true;

		if(this._type == "textbox"){
			_obj.type = (this["readOnly"]) ? "dynamic" : "input";
			if((_obj.type == "input") && (_obj._height < 36)){
				_obj.multiline = false;
			}
		}
		else{
			_obj.type = "dynamic";
		}

		trace("XULtextbox (" + id + ") value=*" + ((_objDef.attributes["value"] == undefined)? "undefined":_objDef.attributes["value"]) + "* nodeValue=*" + ((_objDef.firstChild.nodeValue == undefined)? "undefined":_objDef.firstChild.nodeValue) + "*");

		var finalText_str:String;
		finalText_str = ((_objDef.attributes["value"] != undefined) && (_objDef.attributes["value"] != "" )) ? toolbox.wrapRun(_objDef.attributes["value"], _guiSource,"SingleNode","String") : ((_objDef.firstChild.nodeValue == undefined) ? "" : toolbox.wrapRun(_objDef.firstChild.nodeValue, _guiSource,"SingleNode","String"));
		trace("XULtextbox (" + id + ") finalText_str=" + finalText_str);

		_obj.textColor = (this["textColor"] == undefined) ? 0x000000 : this["textColor"];

		if(_objDef.attributes["class"] == "hlink"){
			_obj.type = "dynamic";
			_handler_str = "gotoURL_" + id;
			_invocation_str = "asfunction:" + _handler_str + "," + _objDef.attributes["href"] + "," + _objDef.attributes["target"];
			this["href"] = _objDef.attributes["href"];
			this["target"] = _objDef.attributes["target"];
			_objParent[_handler_str] = function(where_str){
				trace("gotoURL entered with " + where_str);
				trace("gotoURL is in " + this);
				var notFound_bool:Boolean = true;
				var _inspector_obj = this._parent;
				var sourceID_str:String;
				while(notFound_bool){
					_inspector_obj = _inspector_obj._parent;
					trace("met id: " + _inspector_obj._exulis.id + "  with content: " + _inspector_obj._exulis._type);
					if(_inspector_obj._exulis._type == "overlay"){
						sourceID_str = _inspector_obj._exulis.id;
						notFound_bool = false;
					}
					if(_inspector_obj == _root){
						notFound_bool = false;
					}
				}
				where_str = (sourceID_str == undefined) ? where_str + ",unknown" : where_str + "," + sourceID_str;
				this._exulis.toolbox.wrapRun("as://gotoURL(" + where_str + ")",this._exulis);
			}
			trace("SETUP OK with handler: " + _handler_str + " and invovation: " + _invocation_str);
		}

		if((_obj.html == true) && (_obj.type != "input")){
			finalText_str = (this["fontWeight"]) ? "<b>" +finalText_str+ "</b>" : finalText_str;
			finalText_str = (this["fontStyle"]) ? "<i>" +finalText_str+ "</i>" : finalText_str;
			finalText_str = (this["textDecoration"]) ? "<u>" +finalText_str+ "</u>" : finalText_str;
			finalText_str = (this["fontSize"] != "") ? "<font size='" + this["fontSize"] + "'>" +finalText_str+ "</font>" : finalText_str;
			finalText_str = (this["fontFamily"] != "") ? "<font face='" + this["fontFamily"] + "'>" +finalText_str+ "</font>" : finalText_str;
			finalText_str = (_invocation_str == undefined) ? finalText_str : "<A HREF='" + _invocation_str + "'>" + finalText_str + "</A>";
			finalText_str = (this["textAlign"] != "") ? "<p align='" + this["textAlign"] + "'>" +finalText_str+ "</p>" : finalText_str;

			_obj.setTextFormat(new TextFormat());
			_obj.htmlText = finalText_str;
			this._obj_tf = _obj.getTextFormat();
			this._obj_tf.leading = parseInt(this["leading"]);
			_obj.setTextFormat(this._obj_tf);
		}
		else{
//			this._obj_tf = TextFormat(font, size, color, bold, italic, underline, url, target, align, leftMargin, rightMargin, indent, leading);
			this._obj_tf = new TextFormat(((this["fontFamily"] != "") ? this["fontFamily"] : "_sans"), (((this["fontSize"] != "") && (this["fontSize"] != undefined)) ? parseInt(this["fontSize"]) : 10), _obj.textColor, ((this["fontWeight"]) ? true : false), ((this["fontStyle"]) ? true : false), ((this["textDecoration"]) ? true : false), ((_invocation_str == undefined) ? "" : _invocation_str), null, (((this["textAlign"] != "") && (this["textAlign"] != undefined)) ? this["textAlign"] : "left"), 0, 0, 0, parseInt(this["leading"]));
			_obj.setNewTextFormat(this._obj_tf);
			_obj.html = true;
			_obj.htmlText = ((_objDef.attributes["value"] != undefined) && (_objDef.attributes["value"] != "")) ? toolbox.wrapRun(_objDef.attributes["value"], _guiSource,"SingleNode","String") : ((_objDef.firstChild.nodeValue == undefined) ? "" : toolbox.wrapRun(_objDef.firstChild.nodeValue, _guiSource,"SingleNode","String"));
		}
		trace("XULtextbox (setLayout) htmlText for " + id + " = " + _obj.htmlText);

		if(_obj.border){
			if(this["borderSize"] != undefined){
				var borderSize_num:Number;
				var borderThickness_num:Number;
				var borderOffset_num:Number;
				borderSize_num = this["borderSize"] - 1;                             // 3 -> 2 ; 4 -> 3; 5 -> 4
				borderThickness_num = this["borderSize"] - (borderSize_num % 2);     // 3 -> 3 ; 4 -> 3; 5 -> 5
				borderOffset_num = Math.ceil(borderSize_num / 2);           // 2 -> 1 ; 3 -> 2; 4 -> 2
				var _objBorder:MovieClip;
				_objBorder = this._objParent.createEmptyMovieClip("__border_" + String(this._objParent._childNextDepth),this._objParent._childNextDepth);//_obj._childNextDepth + 
				this._objParent._childNextDepth++;
				_obj._border = _objBorder;
				_objBorder.lineStyle(borderThickness_num, _obj.borderColor, 100, true, "none", "square", "miter", borderThickness_num);
				_objBorder.moveTo(_obj._x + borderOffset_num,_obj._y + borderOffset_num);
				_objBorder.lineTo(_obj._x + _obj._width - borderOffset_num,_obj._y + borderOffset_num);
				_objBorder.lineTo(_obj._x + _obj._width - borderOffset_num,_obj._y + _obj._height - borderOffset_num);
				_objBorder.lineTo(_obj._x + borderOffset_num,_obj._y + _obj._height - borderOffset_num);
				_objBorder.lineTo(_obj._x + borderOffset_num,_obj._y + borderOffset_num);
			}
		}
		_obj.selectable = (this["selectable"] == undefined) ? true : this["selectable"];
//		_obj.focusEnabled = true;
//		_obj._focusrect = true;
/*
for(var nam in _objParent){
	trace("TOTO : _objParent." + nam + " = " + _objParent[nam]);
}
*/
/*
for(var nam in _obj){
	trace("TOTO : _obj." + nam + "(" + this[nam].getDepth() + ") = " + _obj[nam]);
}
for(var nam in this){
//	trace("TOTO : this." + nam + "(" + this[nam].getDepth() + ") = " + this[nam]);
	trace("TOTO : this." + nam + " = " + this[nam]);
}
*/
/*
for(var nam in this){
//	trace("TOTO : this." + nam + "(" + this[nam].getDepth() + ") = " + this[nam]);
	trace("TOTO : this." + nam + " = " + this[nam]);
}
for(var nam in _obj){
	trace("TOTO : _obj." + nam + " = " + _obj[nam]);
}
*/

	}
	function destroy(){
		trace("XULtextbox (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
