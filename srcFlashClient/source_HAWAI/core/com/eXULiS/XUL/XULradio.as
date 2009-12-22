import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULradio extends XULelement {
	private var _obj:mx.controls.RadioButton;
	public var selected:Boolean;

	function XULradio(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 22;
	}

	function create(){
		trace("XULradio (create): radio (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
        _objParent.createClassObject(mx.controls.RadioButton,_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
        _obj.label = toolbox.wrapRun(_objDef.attributes["label"], _guiSource,"SingleNode","String");
		_obj.groupName = _objParent._exulis.id;
		setLayout();

        var tRadioEvent_listener = new Object();
		tRadioEvent_listener.zeId=_objDef.attributes["id"];
        tRadioEvent_listener.click = function (eventObj){
			var target_str = String(eventObj.target);
            trace("XUL2SWF: fullpath: " + target_str);

			trace("feedTrace for RADIO_BTN, Stimulus: " + "id" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + this.zeId);
			_level0.currentItemRootLevel.feedTrace("RADIO_BTN","id" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + this.zeId,"stimulus");
		}
		_obj.addEventListener("click", tRadioEvent_listener);

		_obj = super.applyStyle(_obj);
		return _obj;
	}
	function setLayout(){
		trace("XULradio (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left + " width:" + this.width + " height:" + this.height);
		left = (_objDef.attributes["left"] != undefined) ? parseInt(_objDef.attributes["left"]) : 0;
		top = (_objDef.attributes["top"] != undefined) ? parseInt(_objDef.attributes["top"]) : 0;
		width = (_objDef.attributes["width"] != undefined) ? parseInt(_objDef.attributes["width"]) : _defaultWidth;
		height = (_objDef.attributes["height"] != undefined) ? parseInt(_objDef.attributes["height"]) : _defaultHeight;
		selected = (_objDef.attributes["selected"] == "true") ? true : false;
        _obj.move(this.left,this.top);
        _obj.setSize(this.width,this.height);
		_obj.selected = selected;
	}
	function destroy(){
		trace("XULradio (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		_obj.destroyObject(id);
		this.removeMovieClip();
	}
}
