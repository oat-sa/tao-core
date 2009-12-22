import com.eXULiS.lib.*;

class com.eXULiS.BLACK.BLACKelement extends MovieClip {

// internals ( TODO: should be private/protected scope)
	public var _exulis;
	public var _objParent;
	public var _objDescendants:Array;
	public var _objDef:XMLNode;
	public var _childNextDepth:Number = 1;
	public var _nodeName:String = "";
	public var _type:String = "";
	public var _targetExecutionLayer;
	public var _guiSource:XML;
// public BLACK base element fundamental properties
	public var id:String = "";
	public var left:Number = 0;
	public var top:Number = 0;
	public var width:Number = 0;					// W
	public var height:Number = 0;									// H

	private var toolbox:Toolbox;

	function BLACKelement(objParent,objDef:XMLNode) {
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
		toolbox = (_objParent._toolbox == undefined) ? new Toolbox() : _objParent._toolbox; // inherit (or not) toolbox object
		
//		trace("BLACKelement (constructor): " + _type + " named " + id + " is based on " + _objParent._exulis.id);
	}
	function create(__obj,__creator,childNextDepth_num){
//		trace("BLACKelement (create): " + _type + " (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth + " - general properties setting");

		__obj = _objParent[_objDef.attributes["id"]];
		__obj._childNextDepth = childNextDepth_num;
		__obj._toolbox = toolbox;
		_objParent._childNextDepth++;
		__obj._exulis = __creator;
//		__obj._exulis._objDefsRepository = _objParent._exulis._objDefsRepository;
//		for (var nam in _objParent._exulis) {
//			trace("BLACKelement (dump) [" + _objDef.attributes["id"] + "] _objParent._exulis."+nam+" = "+_objParent._exulis[nam]);
//		}
		__obj = setupComponent(__obj);
		return __obj;
	}

	function setupComponent(__obj,objDef:XMLNode){

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

		// processing the event attributes
		// nothing so far
		return __obj;
	}
/*
	function rTrace(msg){
		trace("BLACKelement (rTrace) : " + msg);
		_level0.aBroadcaster.dispatchXulEvent("orig","RTrace",msg);
		return(msg);
	}
*/
}