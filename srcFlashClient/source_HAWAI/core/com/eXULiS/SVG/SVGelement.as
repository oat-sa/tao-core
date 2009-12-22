import com.eXULiS.lib.*;
import flash.geom.Matrix;
//import com.eXULiS.lib.Toolbox;

class com.eXULiS.SVG.SVGelement extends MovieClip {

// internals ( TODO: should be private/protected scope)
	public var _exulis;
	public var _objParent;
	public var _objDescendants:Array;
	public var _objDef:XMLNode;
	public var _childNextDepth:Number = 1;
	public var _nodeName:String = "";
	public var _type:String = "";
	public var _targetExecutionLayer;
	public var _guiSource;
// dedicated to flex management - see flex_v7.xls for details
	public var _containerWidth:Number;				// CW
	public var _containerHeight:Number;							// CH
	public var _labelWidth:Number; 				// LW
	public var _labelHeight:Number;								// LH
	public var _realWidth:Number;					// RW
	public var _realHeight:Number;									// RH
	public var _childsMinNeedWidth:Number = 0; 	// CMiNW
	public var _childsMinNeedHeight:Number = 0;					// CMiNH

// public SVG base element fundamental properties
	public var id:String = "";
	public var left:Number = 0;
	public var top:Number = 0;
	public var width:Number = 0;					// W
	public var height:Number = 0;									// H
	public var minwidth:Number;					// MiW
	public var minheight:Number;									// MiH

	public var fill:Object;
	public var stroke:Object;
	public var opacity:Number;
	public var visibility:String;

	private var toolbox:Toolbox;
	private var colors:w3colors;

	function SVGelement(objParent,objDef:XMLNode) {
		_objParent = objParent;
		_objDescendants = new Array();
		_objDef = objDef;
		_nodeName = _objDef.nodeName;
		_type = (_nodeName.indexOf(":") == -1) ? _nodeName : _nodeName.substr(_nodeName.indexOf(":") + 1);
		if(_objDef.attributes["id"] == undefined){
			_objDef.attributes["id"] = _objParent._exulis.id + "_" + _type + _objParent._childNextDepth;
		}
		id = _objDef.attributes["id"];
		_targetExecutionLayer = _objParent._exulis._targetExecutionLayer;
		_guiSource = _objParent._exulis._guiSource;
		colors = (_objParent._colors == undefined) ? new w3colors() : _objParent._colors; // inherit (or not) colors object
		toolbox = (_objParent._toolbox == undefined) ? new Toolbox() : _objParent._toolbox; // inherit (or not) toolbox object
		
		trace("SVGelement (constructor): " + _type + " named " + id + " is based on " + _objParent._exulis.id);
	}
	function create(__obj,__creator,childNextDepth_num){
		trace("SVGelement (create): " + _type + " (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth + " - general properties setting");

		__obj = _objParent[_objDef.attributes["id"]];
		__obj._childNextDepth = childNextDepth_num;
		__obj._colors = colors;
		__obj._toolbox = toolbox;
		_objParent._childNextDepth++;
		__obj._exulis = __creator;
//		__obj._exulis._objDefsRepository = _objParent._exulis._objDefsRepository;
//		for (var nam in _objParent._exulis) {
//			trace("SVGelement (dump) [" + _objDef.attributes["id"] + "] _objParent._exulis."+nam+" = "+_objParent._exulis[nam]);
//		}
		__obj = setShape(__obj);
		return __obj;
	}

	function setShape(__obj,objDef:XMLNode){

		var transformActions_array:Array;
		var evaluatedString_str:String;
		var evaluatedAction_str:String;
		var actionPos_num:Number;
		var currentAction_str:String;
		var aCommand:Object;
		var currentElement_obj:Object;
		var nextElement_obj:Object;
		var actionLength_num:Number;
		var pathActions_array:Array;
		var actionArgs_array:Array;
		var actionArgs_str:String;
		var degrees:Number;
		var radians:Number;

		if(objDef != undefined){
			_objDef = objDef;
			_type = _objDef.nodeName;
			if(_objDef.attributes["id"] == undefined){
				_objDef.attributes["id"] = _objParent._exulis.id + "_" + _type + _objParent._childNextDepth;
			}
			id = _objDef.attributes["id"];
		}

		// pre-processing the "style" attribute
		if(_objDef.attributes["style"] != undefined){
			var workString = new String(_objDef.attributes["style"]);
			var workArray = new Array();
			workArray = workString.split(";");
			workString = "";
			for(var vCpt=0;vCpt<workArray.length;vCpt++){
				var aPart_str:String;
				var attribKey_str:String;
				var attribValue_str:String;
				var posSemiCol_num:Number;
				aPart_str = workArray[vCpt];
				posSemiCol_num = aPart_str.indexOf(":");
				if(posSemiCol_num > -1){
					attribKey_str = aPart_str.substring(0, posSemiCol_num);
					attribValue_str = aPart_str.substring(posSemiCol_num + 1);
					_objDef.attributes[attribKey_str] = attribValue_str;
				}
			}
		}

		// processing the "fill" attribute - color and alpha
		if(_objDef.attributes["fill"] != undefined) {
			fill = {color:colors.getColor(_objDef.attributes.fill), alpha:100};
//			trace("SVGelement (create): FILL (step 1) with color: " + fill.color + " and alpha: " + fill.alpha);
			fill = ((fill == undefined) && (colors.getColor(_objDef.attributes.fill) != undefined)) ? {color:colors.getColor(_objDef.attributes.fill),alpha:100} : fill;
//			trace("SVGelement (create): FILL (step 2) with color: " + fill.color + " and alpha: " + fill.alpha);
			// if 'fill' but nothing is matching, we consider fill='none' i.e. black with alpha=0
			fill = ((fill == undefined) || (_objDef.attributes.fill == "none")) ? {color:colors.getColor("none"), alpha:0} : fill;
//			trace("SVGelement (create): FILL (step 3) with color: " + fill.color + " and alpha: " + fill.alpha);
			// TODO: _defaultColor and _parentColor (e.g. <g> and color inheritance)
		}
		else {
			fill = {color:colors.getColor("black"), alpha:100}; // solid black fill if nothing stated elsewhere
//			trace("SVGelement (create): FILL : no Fill attribute defined; default color assumed: " + fill.color + " and alpha: " + fill.alpha);
		}
		if(_objDef.attributes["fill-opacity"] != undefined){
			fill.alpha = Math.round(parseFloat(_objDef.attributes["fill-opacity"]) * 100);
		}
//		trace("SVGelement (create): FILL set with color: " + fill.color + " and alpha: " + fill.alpha);

		// processing the "stroke" attribute - stroke color, stroke width and alpha
//		trace("STROKE set on: " + _objDef.attributes.stroke);
		if(_objDef.attributes.stroke != undefined) {
			stroke = {color:colors.getColor(_objDef.attributes.stroke), width:1, alpha:100};
			stroke = ((stroke == undefined) && (colors.getColor(_objDef.attributes.stroke) != undefined)) ? {color:colors.getColor(_objDef.attributes.stroke), width:1, alpha:100} : stroke;
			// if 'stroke' but nothing is matching, we consider stroke='none' i.e. black with stroke-width=0 and alpha=0
			stroke = ((stroke == undefined) || (_objDef.attributes.stroke == "none")) ? {color:colors.getColor("none"), width:0, alpha:0} : stroke;
			// TODO: _defaultColor and _parentColor (e.g. <g> and color inheritance) and verify if stroke heritance
		}
		else {
			stroke = {color:0, width:0, alpha:0}; // if stroke is undefined, use invisible stroke
		}
		if((_objDef.attributes.stroke != undefined) && (_objDef.attributes.stroke != "none")){
			if(_objDef.attributes["stroke-width"] != undefined){
				stroke.width = parseInt(_objDef.attributes["stroke-width"]);
			}
			if(_objDef.attributes["stroke-opacity"] != undefined){
				stroke.alpha = Math.round(parseFloat(_objDef.attributes["stroke-opacity"]) * 100);
			}
		}
		trace("STROKE set with color: " + stroke.color + " width: " + stroke.width + " and alpha: " + stroke.alpha);
		if(_objDef.attributes["opacity"] != undefined){
			opacity = parseFloat(_objDef.attributes["opacity"]);
			fill.alpha = Math.round(fill.alpha * opacity);
			stroke.alpha = Math.round(stroke.alpha * opacity);
		}
		else{
			_objDef.attributes["opacity"] = 1;
			opacity = 1;
		}
//		trace("OPACITY set with alpha: " + opacity);
		// TODO: the gradient filling should be developed in conformity with W3C SVG

		if(_objDef.attributes.transform != undefined){
			actionPos_num = 0;
			transformActions_array = new Array();
			// parse the 'transform' content
			// first 'matrix'
			evaluatedString_str = toolbox.replaceString(_objDef.attributes.transform," ",",");
			evaluatedString_str = toolbox.replaceString(evaluatedString_str,"-",",-");
			evaluatedString_str = toolbox.cleanString(evaluatedString_str,true,true,true,true,false);
			while(evaluatedString_str != toolbox.replaceString(evaluatedString_str,",,",",")){
				evaluatedString_str = toolbox.replaceString(evaluatedString_str,",,",",");
			}
			evaluatedString_str = toolbox.replaceString(evaluatedString_str,"(,","(");
			currentAction_str = "matrix";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				transformActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			// 'translate'
			currentAction_str = "translate";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				transformActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			// 'scale'
			currentAction_str = "scale";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				transformActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			// 'rotate'
			currentAction_str = "rotate";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				transformActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			// 'skewX'
			currentAction_str = "skewX";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				transformActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			// 'skewY'
			currentAction_str = "skewY";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				transformActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
		}

		if(transformActions_array.length > 0){
			transformActions_array.sortOn(["pos"],Array.NUMERIC);
			while(transformActions_array.length > 0){
				var a_num:Number;
				var b_num:Number;
				var c_num:Number;
				var d_num:Number;
				var tx_num:Number;
				var ty_num:Number;
				var aTransformationMatrix:Matrix;
				var baseMatrix:Matrix;

				actionArgs_array = new Array();
				currentElement_obj = transformActions_array.shift();
				if(transformActions_array.length > 0){
					nextElement_obj = transformActions_array[0];
					actionLength_num = nextElement_obj.pos - currentElement_obj.pos;
					evaluatedAction_str = evaluatedString_str.substr(currentElement_obj.pos, actionLength_num);
				}
				else{
					evaluatedAction_str = evaluatedString_str.substr(currentElement_obj.pos);
				}
				switch(currentElement_obj.action){
					case "matrix":{
						actionArgs_str = toolbox.extractString(evaluatedAction_str,"(",")",1,false);
						actionArgs_array = actionArgs_str.split(",");
						if(actionArgs_array.length == 6){
							a_num = parseFloat(actionArgs_array[0]);
							b_num = parseFloat(actionArgs_array[1]);
							c_num = parseFloat(actionArgs_array[2]);
							d_num = parseFloat(actionArgs_array[3]);
							tx_num = parseFloat(actionArgs_array[4]);
							ty_num = parseFloat(actionArgs_array[5]);
							aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, tx_num, ty_num);
							baseMatrix = __obj.transform.matrix;
							baseMatrix.concat(aTransformationMatrix);
							__obj.transform.matrix = baseMatrix;
						}
						else{
							trace("SVGelement: requested transform operation failed - " + evaluatedAction_str);
						}
						break;
					}
					case "translate":{
						actionArgs_str = toolbox.extractString(evaluatedAction_str,"(",")",1,false);
						actionArgs_array = actionArgs_str.split(",");
						if((actionArgs_array.length > 0) && (actionArgs_array.length < 3)){
							a_num = 1;
							b_num = 0;
							c_num = 0;
							d_num = 1;
							tx_num = parseFloat(actionArgs_array[0]);
							ty_num = (actionArgs_array[1] == undefined) ? 0 : parseFloat(actionArgs_array[1]);
							aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, tx_num, ty_num);
							baseMatrix = __obj.transform.matrix;
							baseMatrix.concat(aTransformationMatrix);
							__obj.transform.matrix = baseMatrix;
						}
						else{
							trace("SVGelement: requested transform operation failed - " + evaluatedAction_str);
						}
						break;
					}
					case "scale":{
						actionArgs_str = toolbox.extractString(evaluatedAction_str,"(",")",1,false);
						actionArgs_array = actionArgs_str.split(",");
						if((actionArgs_array.length > 0) && (actionArgs_array.length < 3)){
							a_num = parseFloat(actionArgs_array[0]);
							b_num = 0;
							c_num = 0;
							d_num = (actionArgs_array[1] == undefined) ? 1 : parseFloat(actionArgs_array[1]);
							tx_num = 0;
							ty_num = 0;
							aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, tx_num, ty_num);
							baseMatrix = __obj.transform.matrix;
							baseMatrix.concat(aTransformationMatrix);
							__obj.transform.matrix = baseMatrix;
						}
						else{
							trace("SVGelement: requested transform operation failed - " + evaluatedAction_str);
						}
						break;
					}
					case "rotate":{
						actionArgs_str = toolbox.extractString(evaluatedAction_str,"(",")",1,false);
						actionArgs_array = actionArgs_str.split(",");
						if((actionArgs_array.length > 0) && (actionArgs_array.length < 4)){
							a_num = 1;
							b_num = 0;
							c_num = 0;
							d_num = 1;
							if(actionArgs_array.length > 1){
								tx_num = parseFloat(actionArgs_array[1]);
								ty_num = (actionArgs_array[2] == undefined) ? 0 : parseFloat(actionArgs_array[2]);
							}
							else{
								tx_num = 0;
								ty_num = 0;
							}
							aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, -1 * tx_num, -1 * ty_num);
							degrees = parseFloat(actionArgs_array[0]);
							radians = (degrees/180)*Math.PI;
							aTransformationMatrix.rotate(radians);
							aTransformationMatrix.translate(tx_num, ty_num);
							baseMatrix = __obj.transform.matrix;
							baseMatrix.concat(aTransformationMatrix);
							__obj.transform.matrix = baseMatrix;
						}
						else{
							trace("SVGelement: requested transform operation failed - " + evaluatedAction_str);
						}
						break;
					}
					case "skewX":{
						actionArgs_str = toolbox.extractString(evaluatedAction_str,"(",")",1,false);
						actionArgs_array = actionArgs_str.split(",");
						if(actionArgs_array.length == 1){
							degrees = parseFloat(actionArgs_array[0]);
							radians = (degrees/180)*Math.PI;
							a_num = 1;
							b_num = 0;
							c_num = Math.tan(radians);
							d_num = 1;
							tx_num = 0;
							ty_num = 0;
							var aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, tx_num, ty_num);
							var baseMatrix = __obj.transform.matrix;
							baseMatrix.concat(aTransformationMatrix);
							__obj.transform.matrix = baseMatrix;
						}
						else{
							trace("SVGelement: requested transform operation failed - " + evaluatedAction_str);
						}
						break;
					}
					case "skewY":{
						actionArgs_str = toolbox.extractString(evaluatedAction_str,"(",")",1,false);
						actionArgs_array = actionArgs_str.split(",");
						if(actionArgs_array.length == 1){
							degrees = parseFloat(actionArgs_array[0]);
							radians = (degrees/180)*Math.PI;
							a_num = 1;
							b_num = Math.tan(radians);
							c_num = 0;
							d_num = 1;
							tx_num = 0;
							ty_num = 0;
							var aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, tx_num, ty_num);
							var baseMatrix = __obj.transform.matrix;
							baseMatrix.concat(aTransformationMatrix);
							__obj.transform.matrix = baseMatrix;
						}
						else{
							trace("SVGelement: requested transform operation failed - " + evaluatedAction_str);
						}
						break;
					}
					default:{
						trace("SVGelement: requested transform operation unknown - " + evaluatedAction_str);
					}
				}
			}
		}

		// processing the "visibility" attribute
		if(_objDef.attributes["visibility"] != undefined) {
			visibility = _objDef.attributes.visibility;
			visibility = visibility.toLowerCase();
		}
		else {
			visibility = "inherit";
		}
		// processing the event attributes
		if(_objDef.attributes["onclick"] != undefined){
			//
			var tClickEvent_listener = new Object();
			tClickEvent_listener.click = function(){
				trace("eXULiS clicked for : " + __obj);
/*
		for (var nam in __obj) {
			trace("SVGelement (dump1) __obj."+nam+" = "+__obj[nam]);
		}

		for (var nam in __obj._exulis) {
			trace("SVGelement (dump2) __obj._exulis."+nam+" = "+__obj._exulis[nam]);
		}
*/
			}
			__obj.addEventListener("click", tClickEvent_listener);
			__obj.onRelease = tClickEvent_listener.click;
		}
		if(_objDef.attributes["onmouseover"] != undefined){
			//
			var tMouseOverEvent_listener = new Object();
			tMouseOverEvent_listener.mouseOver = function(){
				trace("eXULiS mouseOver for : " + __obj);
			}
			__obj.addEventListener("mouseOver", tMouseOverEvent_listener);
			__obj.onRollOver = tMouseOverEvent_listener.mouseOver;
		}
		if(_objDef.attributes["onmouseout"] != undefined){
			//
			var tMouseOutEvent_listener = new Object();
			tMouseOutEvent_listener.mouseOut = function(){
				trace("eXULiS mouseOut for : " + __obj);
			}
			__obj.addEventListener("mouseOut", tMouseOutEvent_listener);
			__obj.onRollOut = tMouseOutEvent_listener.mouseOut;
		}
		return __obj;
	}
/*
	function rTrace(msg){
		trace("SVGelement (rTrace) : " + msg);
		_level0.aBroadcaster.dispatchXulEvent("orig","RTrace",msg);
		return(msg);
	}
*/
}