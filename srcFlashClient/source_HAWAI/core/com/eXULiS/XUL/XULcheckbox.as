import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULcheckbox extends XULelement {
	private var _obj:mx.controls.CheckBox;
	public var selected:Boolean;
	public var group_ref;

	function XULcheckbox(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 22;
	}

	function create(){
		trace("XULcheckbox (create): checkbox (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
        _objParent.createClassObject(mx.controls.CheckBox,_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
        _obj.label = toolbox.wrapRun(_objDef.attributes["label"], _guiSource,"SingleNode","String");

		if(_objDef.attributes["group"] != undefined){
			group_ref = this._targetExecutionLayer._objDefsRepository.retrieve(_objDef.attributes["group"])._exulis;
			if(group_ref == undefined){
				group_ref = _objParent._exulis;
			}
			group_ref.registerGroupMember(this);
		}

		setLayout();

        var tCheckBoxEvent_listener = new Object();

		tCheckBoxEvent_listener.zeId=_objDef.attributes["id"];
        tCheckBoxEvent_listener.click = function (eventObj){
			var target_str = String(eventObj.target);
            trace("XUL2SWF: fullpath: " + target_str);
			trace("feedTrace for CHECKBOX, with state Before: " +eventObj.target._exulis.selected+ " state Now: " +eventObj.target.selected+ " Stimulus: " + "id" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + this.zeId);
			eventObj.target._exulis.selected = eventObj.target.selected;
			_level0.currentItemRootLevel.feedTrace("CHECKBOX","id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+this.zeId,"stimulus");
			if(eventObj.target.selected){
				eventObj.target._exulis.group_ref.setGroupFocus(eventObj.target._exulis);
			}
		}
		_obj.addEventListener("click", tCheckBoxEvent_listener);

		_obj = super.applyStyle(_obj);
		return _obj;
	}

	public function setState(status_str){
		trace("XULcheckbox: setState for " + this._obj + " with status:" + status_str);
		if(status_str == "checked"){
			_obj.selected = true;
		}
		if(status_str == "not-checked"){
			_obj.selected = false;
		}
	}

	function setLayout(){
		trace("XULcheckbox (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left + " width:" + this.width + " height:" + this.height);
		left = (_objDef.attributes["left"] != undefined) ? parseInt(_objDef.attributes["left"]) : 0;
		top = (_objDef.attributes["top"] != undefined) ? parseInt(_objDef.attributes["top"]) : 0;
		width = (_objDef.attributes["width"] != undefined) ? parseInt(_objDef.attributes["width"]) : _defaultWidth;
		height = (_objDef.attributes["height"] != undefined) ? parseInt(_objDef.attributes["height"]) : _defaultHeight;
		selected = (_objDef.attributes["checked"] == "true") ? true : false;
        _obj.move(this.left,this.top);
        _obj.setSize(this.width,this.height);
		_obj.selected = selected;
	}
	function destroy(){
		trace("XULcheckbox (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		_obj.destroyObject(id);
		this.removeMovieClip();
	}
}
