import com.eXULiS.XUL.XULelement; 
import mx.utils.Delegate;
import com.eXULiS.lib.ToolTip;

class com.eXULiS.XUL.XULtoolbarbutton extends XULelement {
	public var _obj:MovieClip;
	private var _objLabel:TextField;
	public var icon_mc:MovieClip;
	public var toggleable_bool:Boolean;
	public var toggled_bool:Boolean;
//	public var toggleState_num:Number; // modulo 2 or 3 (0, 1 or 2 for tri-state); default is 0
//	public var toggleModulo_num:Number;
	public var group_ref;
	
	private var toolTipOver:ToolTip;
	
	private var zeText;
	
	function XULtoolbarbutton(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 22;
	}
	function create(){
		trace("XULtoolbarbutton: create toolbarbutton (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
//        _objParent.createClassObject(mx.controls.Button,_objDef.attributes["id"],_objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		trace("XULtoolbarbutton: special properties setting");
/*
		if((_objDef.attributes["checkState"] == "0") || (_objDef.attributes["checkState"] == "1") || (_objDef.attributes["checkState"] == "2")){
			toggleModulo_num = (_objDef.attributes["checkState"] == "2") ? 3 : 2;
			toggleState_num = parseInt(_objDef.attributes["checkState"]);
		}
*/
		toggleable_bool = ((_objDef.attributes["toggled"] != undefined) || (_objDef.attributes["checked"] != undefined) || (_objDef.attributes["toggleable"] == "true")) ? true : false;
		if(toggleable_bool){
			toggled_bool = ((_objDef.attributes["toggled"] == "true") || (_objDef.attributes["checked"] == "true")) ? true : false;
			group_ref = this._targetExecutionLayer._objDefsRepository.retrieve(_objDef.attributes["group"])._exulis;
			if(group_ref == undefined){
				group_ref = _objParent._exulis;
			}
			group_ref.registerGroupMember(this);
		}
		
		trace("myEvent "+ _objDef);

		setLayout();

// attach local properties

		drawIcon();
		
		_obj.onPress = Delegate.create(this,pressEvent);
		
        _obj.onRollOver = Delegate.create(this,rollOverEvent);
		_obj.onRollOut = Delegate.create(this,rollOutEvent);
//		_obj.onMouseMove = this.mouseMoveEvent;

		return _obj;
	}
	
	function addNewText(newText)
	{
	
		
		_objLabel.htmlText="<b>"+zeText+newText+"</b>";
		
		_objLabel._x = (_obj._exulis.width - _objLabel._width) / 2;
		_objLabel._y = (_obj._exulis.height - _objLabel._height) / 2;
	}
	
	function resetText()
	{
		_objLabel.htmlText=zeText;
		
		_objLabel._x = (_obj._exulis.width - _objLabel._width) / 2;
		_objLabel._y = (_obj._exulis.height - _objLabel._height) / 2;
	}
	
	
	public function setState(status_str){
		trace("XULtoolbarbutton: setState for " + this._obj + " with status:" + status_str);
		if(status_str == "checked"){
			toggled_bool = true;
/*
			for(var vCpt_num:Number = 0;vCpt_num < _actions.length;vCpt_num++){
				var vAction_obj:Object = _actions[vCpt_num];
				if(vAction_obj.type == "command"){
					toolbox.wrapRun(vAction_obj.action,this);
				}
			}
*/
			if(this._obj["imagePress_mc"] != undefined){
				for(var nam in this._obj){
					var vTmp_str:String = String(nam);
					if(vTmp_str.substr(0,5) == "image"){
						if(vTmp_str == "imagePress_mc"){
							this._obj[nam]._visible = true;
						}
						else{
							this._obj[nam]._visible = false;
						}
					}
				}
			}
		}
		if(status_str == "not-checked"){
			toggled_bool = false;
			if(this._obj["imagePress_mc"] != undefined){
				for(var nam in this._obj){
					var vTmp_str:String = String(nam);
					if(vTmp_str.substr(0,5) == "image"){
						if(vTmp_str == "image_mc"){
							this._obj[nam]._visible = true;
						}
						else{
							this._obj[nam]._visible = false;
						}
					}
				}
			}
		}
	}

	public function setActive(){
		
		trace("XULtoolbarbutton > setActive "+_obj._name);
		
		for(var nam in _obj){
			var vTmp_str:String = String(nam);
			if(vTmp_str.substr(0,5) == "image"){
				if(vTmp_str == "image_mc"){
					_obj[nam]._visible = true;
					trace("XULtoolbarbutton > setActive "+_obj._name+" > "+nam+" :  _visible true");
				}
				else{
					_obj[nam]._visible = false;
					trace("XULtoolbarbutton > setActive "+_obj._name+" > "+nam+" : _visible false");
				}
			}
		}
	}

	public function setInactive(){
		
			trace("XULtoolbarbutton > setInactive "+_obj._name);
		
		for(var nam in _obj){
			var vTmp_str:String = String(nam);
			if(vTmp_str.substr(0,5) == "image"){
				if(vTmp_str == "imageDisabled_mc"){
					_obj[nam]._visible = true;
					trace("XULtoolbarbutton > setInactive "+_obj._name+" > "+nam+" : _visible true");
				}
				else{
					_obj[nam]._visible = false;
					trace("XULtoolbarbutton > setInactive "+_obj._name+" > "+nam+" : _visible false");
				}
			}
		}
	}

	function pressEvent(){
		trace("XULtoolbarbutton: onPress on " + this._obj);
		
		toolTipOver.removeToolTip();
		
		var event_str:String;
		var payload_str:String;
		event_str = "TOOLBAR";
		payload_str = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"];
		trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
		_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // TOOLBAR
		
		if(this._obj["imageDisabled_mc"]._visible != true){
		
			if(toggleable_bool){
			group_ref.setGroupFocus(this);
			}
			for(var vCpt_num:Number = 0;vCpt_num < _actions.length;vCpt_num++){
					var vAction_obj:Object = _actions[vCpt_num];
					if(vAction_obj.type == "command"){
					toolbox.wrapRun(vAction_obj.action,this);
					
					event_str = "DOACTION";
					payload_str = "action"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+vAction_obj.action;
					trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
					_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // DOACTION
				}
			}
		}
	}

	function rollOverEvent(){
		trace("XULtoolbarbutton: onRollOver on " + this._obj);
		
		var hint_str:String=toolbox.wrapRun(_objDef.attributes["tooltiptext"], _guiSource,"SingleNode","String")
		
		if (hint_str!=undefined & hint_str!="")
		toolTipOver=new ToolTip(hint_str,this._obj);

		if(this._obj["imageDisabled_mc"]._visible != true){
			for(var nam in this._obj){
				var vTmp_str:String = String(nam);

				if(vTmp_str.substr(0,5) == "image"){
					if(vTmp_str == "imageRollOver_mc"){
						this._obj[nam]._visible = true;
					}
					else{
						this._obj[nam]._visible = false;
					}
				}
			}
		}
//		Mouse.hide();
//		this.attachMovie("cursor_help_id", "cursor_mc", this.getNextHighestDepth(), {_x:this._xmouse, _y:this._ymouse});
//		this._exulis.drawIcon();
	}
	function rollOutEvent() {
		trace("XULtoolbarbutton: onRollOut on " + this._obj);
		
		toolTipOver.removeToolTip();
		
		if(_obj["imageDisabled_mc"]._visible != true){
			
		trace("XULtoolbarbutton: onRollOut IN");
			
			if(toggled_bool){
				group_ref.setGroupFocus(this);
			}
			else{
				for(var nam in this._obj){
					var vTmp_str:String = String(nam);
					if(vTmp_str.substr(0,5) == "image"){
						if(vTmp_str == "imageRollOut_mc"){
							this[nam]._visible = true;
						}
						else{
							this[nam]._visible = false;
						}
					}
				}
			}
		}
//		Mouse.show();
//		this.cursor_mc.removeMovieClip();
	};
	
	
	
	
	function mouseMoveEvent(){
		trace("XULtoolbarbutton (mouseMoveEvent): " + this);
//		this.cursor_mc._x = this._xmouse;
//		this.cursor_mc._y = this._ymouse;
	};
	function setLayout(){
		trace("XULtoolbarbutton: (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left + " width:" + this.width + " height:" + this.height);
		_obj._y = this.top;
		_obj._x = this.left;
//		_obj.focusEnabled = true;
//		_obj._focusrect = true;

		_objLabel = _obj.createTextField(_objDef.attributes["id"] + "_label", _obj._childNextDepth+100, 0, 0, 0, 0);
		_objLabel.type = "dynamic";
		_objLabel.autoSize="Left";
		_objLabel.html = true;
		_objLabel.htmlText = ((_objDef.attributes["label"] != undefined) && (_objDef.attributes["label"] != "")) ? toolbox.wrapRun(_objDef.attributes["label"], _guiSource,"SingleNode","String") : "";
		
		zeText=_objLabel.htmlText;
		
		_objLabel._width = _objLabel.getPreferredWidth();
		_objLabel._height = _objLabel.getPreferredHeight();
		_objLabel._x = (_obj._exulis.width - _objLabel._width) / 2;
		_objLabel._y = (_obj._exulis.height - _objLabel._height) / 2;

		_obj._visible = this._visibilityState;
	}
//	function setLayout(){
//		trace("XULtoolbarbutton: (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left + " width:" + this.width + " height:" + this.height);
//        _obj.move(this.left,this.top);
//        _obj.setSize(this.width,this.height);
//        _obj.label = toolbox.wrapRun(_objDef.attributes["label"], _guiSource,"SingleNode","String");
//		trace("XULtoolbarbutton: (setLayout) placement: " + this._objDef.attributes["labelPlacement"]);
//		if(this._objDef.attributes["labelPlacement"] != undefined){
//			_obj.icon = "";
//			_obj.labelPlacement = this._objDef.attributes["labelPlacement"];
//		}
//	}
	function drawIcon(){
		trace("XULtoolbarbutton: DRAW on " + id);
/*
		if(this._objDef.attributes["disabled"]=="true"){
			var vImage_str:String = this._objDef.attributes["imageDisabled"];
			if(vImage_str != undefined){
				if(vImage_str != ""){
					_obj.createEmptyMovieClip("imageDisabled_mc",this._childNextDepth++);
					var vImage_mc = _obj["imageDisabled_mc"];
					var vImage_loader = new MovieClipLoader();
					vImage_loader.onLoadInit = function(target_mc:MovieClip) {
						target_mc._x = (target_mc._parent._exulis.width - target_mc._width)/2;
						if(target_mc._parent._obj.labelPlacement == "bottom"){
							target_mc._y =  (target_mc._parent._exulis.height - 22 - target_mc._height)/2;
						}
						else{
							target_mc._y = ((target_mc._parent._exulis.height - target_mc._height)/2);
						}
						var vTmpButtonName_str:String = target_mc._name;
//						target_mc._visible = (vTmpButtonName_str.substr(0,-3) == "image") ? true : false;
//						if(target_mc._parent._exulis.toggled_bool){
//							target_mc._parent._exulis.group_ref.setGroupFocus(target_mc._parent._exulis);
//						}
					};
					var vImageTarget_str:String = this.toolbox.wrapRun(vImage_str, _guiSource,"SingleNode","String");
					vImage_loader.loadClip(vImageTarget_str,vImage_mc);
					trace("XULtoolbarbutton: DRAW " + vImageTarget_str + "(" + vImage_mc.getDepth() + ") for " + "imageDisabled");
				}
			}
			
		}
		else{
*/
		  for(var objAttr in this._objDef.attributes){
			trace("XULtoolbarbutton: DRAW (" + objAttr + ") scanned");
			switch(objAttr){
				case "image":
				case "imageDisabled":
				case "imagePress":
				case "imageRollOver":
				case "imageRollOut":
				case "imageChecked":
//				case "imageMouseNormal":
//				case "imageMouseDisabled":
				{
					var vImage_str:String = this._objDef.attributes[objAttr];
					if(vImage_str != undefined){
						if(vImage_str != ""){
							_obj.createEmptyMovieClip(objAttr + "_mc",this._childNextDepth++);
							var vImage_mc = _obj[objAttr + "_mc"];
							var vImage_loader = new MovieClipLoader();
							vImage_loader.onLoadInit = function(target_mc:MovieClip) {
//		trace("XULtoolbarbutton target_mc._parent.width:" + target_mc._parent.width + "  target_mc._width:" + target_mc._width);
//		for(var nam in target_mc){
//			trace("TOTO (" + target_mc + "): ." + nam + " = " + target_mc[nam]);
//		}
//		for(var nam in target_mc._parent._exulis){
//			trace("TOTO (" + target_mc + "): ._parent._exulis." + nam + " = " + target_mc._parent._exulis[nam]);
//		}
								target_mc._x = (target_mc._parent._exulis.width - target_mc._width)/2;
			//					trace("XULtoolbarbutton: (drawIcon) placement: " + target_mc._parent._parent._exulis.id);
			//					trace("XULtoolbarbutton: (drawIcon) placement: " + target_mc._parent._parent._obj.labelPlacement);
								if(target_mc._parent._obj.labelPlacement == "bottom"){
									target_mc._y =  (target_mc._parent._exulis.height - 22 - target_mc._height)/2;
								}
								else{
									target_mc._y = ((target_mc._parent._exulis.height - target_mc._height)/2);
								}
//trace("TOTO > target_mc:" + target_mc._name);
//for(var nam in target_mc){
//	trace("TOTO >> " + nam + ":" + target_mc[nam]);
//}
//trace("TOTO > objAttr:" + objAttr);
								var vTmpButtonName_str:String = target_mc._name;

	if((vTmpButtonName_str.substr(0,-3) == "image") || (vTmpButtonName_str.substr(0,-3) == "imageDisabled")){
		if(target_mc._parent._exulis._objDef.attributes["disabled"]=="true"){
			trace("testATTR true");
			target_mc._visible = (vTmpButtonName_str.substr(0,-3) == "image") ? false : true;
		}
		else{
			trace("testATTR false");
			target_mc._visible = (vTmpButtonName_str.substr(0,-3) == "image") ? true : false;		}
	}
	else{
		target_mc._visible = false;
	}

//							 	target_mc._visible = (vTmpButtonName_str.substr(0,-3) == "image") ? true : false;
							
								if(target_mc._parent._exulis.toggled_bool){
									target_mc._parent._exulis.group_ref.setGroupFocus(target_mc._parent._exulis);
								}
//								target_mc._visible = ((vTmpButtonName_str.substr(0,-3) == "image") && (!target_mc._parent._exulis.toggled_bool)) ? true : false;
//								target_mc._visible = ((vTmpButtonName_str.substr(0,-3) == "imageChecked") && (target_mc._parent._exulis.toggled_bool)) ? true : false;
							};
							var vImageTarget_str:String = this.toolbox.wrapRun(vImage_str, _guiSource,"SingleNode","String");
							vImage_loader.loadClip(vImageTarget_str,vImage_mc);
							trace("XULtoolbarbutton: DRAW " + vImageTarget_str + "(" + vImage_mc.getDepth() + ") for " + objAttr);
						}
					}
				}
			}
		  }

//		}
/*
		_obj.attachMovie("emptyContent","hitZone_mc",this._childNextDepth++);
		var vImageHZ_mc = _obj["hitZone_mc"];
		vImageHZ_mc._x = 0;
		vImageHZ_mc._y = 0;
		vImageHZ_mc._width = width;
		vImageHZ_mc._height = height;
		_obj.hitArea = vImageHZ_mc;
*/
	}
	function destroy(){
		trace("XULtoolbarbutton: (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		_obj.destroyObject(id);
		this.removeMovieClip();
	}
}
