import com.eXULiS.XUL.XULelement; 
import mx.utils.Delegate;

class com.eXULiS.XUL.XULtab extends XULelement {
	public var _obj:MovieClip;
	public var _objLabel:TextField;
	public var icon_mc:MovieClip;
	public var toggleable_bool:Boolean;
	public var toggled_bool:Boolean;
//	public var toggleState_num:Number; // modulo 2 or 3 (0, 1 or 2 for tri-state); default is 0
//	public var toggleModulo_num:Number;
	public var group_ref;
	public var linkedPanel_str:String;
	public var linkedPanel_ref;
	public var activeTab_bool:Boolean;
	public var _history_array:Array;
	public var _historyCurrentIndex_num:Number;
	public var closeContainer_mc:MovieClip;
	public var hitZone_mc:MovieClip;

	function XULtab(xulParent,xulDef:XMLNode) {
		
			super(xulParent,xulDef);
			
			trace("myTab "+xulDef);
			
			_objParent.updateTabManager();
			
		_defaultWidth = 100;
		_defaultHeight = 22;
	}
	
	function create(){
		trace("XULtab: create tab (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		trace("XULtab: special properties setting");
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
			
			// ajoute occurence au tableau
			var this_ref = this;
			var str_str:String = "";
			for(var elem in this_ref){
				str_str += "{" + elem + ":" + this_ref[elem] + "}"; 
			}
			trace("myTAB create this_ref = " + str_str);

			group_ref.registerGroupMember(this);
			// vérifie si leader, si oui donne le focus
			if(toggled_bool){
				group_ref.setGroupFocus(this);
			}
		}
		_history_array = new Array();
		_historyCurrentIndex_num = -1;
		if(_objDef.attributes["linkedpanel"] == undefined){
			linkedPanel_str = "";
		}
		else{
			linkedPanel_str = _objDef.attributes["linkedpanel"];
			linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
			historyAdd(linkedPanel_str,true);
		}
		activeTab_bool = ((_objDef.attributes["active"] == undefined) || (_objDef.attributes["active"] == "true")) ? true : false;

		setLayout();
		drawIcon();
		drawHitZone();
		//closeTabButton();
		
		//closeContainer_mc.onPress=Delegate.create(this,closeTab);
		//closeContainer_mc.onRollOver=Delegate.create(this,closeTab);
		
       	hitZone_mc.onPress = Delegate.create(this,pressEvent);
     	hitZone_mc.onRollOver = Delegate.create(this,rollOverEvent);
		hitZone_mc.onRollOut = Delegate.create(this,rollOutEvent);
//		_obj.onMouseMove = this.mouseMoveEvent;

		return _obj;
	}

	private function drawHitZone():Void{
		hitZone_mc=_obj.createEmptyMovieClip("HitZone",_obj._childNextDepth+2500);
		hitZone_mc.beginFill(0x0000ff,0);
		hitZone_mc.moveTo(0,0);
		
		//hitZone_mc.lineTo(121,0);
		//hitZone_mc.lineTo(121,20);
		
		hitZone_mc.lineTo(140,0);
		hitZone_mc.lineTo(140,20);
		hitZone_mc.lineTo(0,20);
		hitZone_mc.lineTo(0,0);
		hitZone_mc.endFill();
	}

	private function closeTabButton(){
		closeContainer_mc=_obj.createEmptyMovieClip("closeBton",_obj._childNextDepth+9000);
		closeContainer_mc.beginFill(0xff00ff,0);
		closeContainer_mc.moveTo(0,0);
		closeContainer_mc.lineTo(10,0);
		closeContainer_mc.lineTo(10,20);
		closeContainer_mc.lineTo(0,20);
		closeContainer_mc.lineTo(0,0);
		closeContainer_mc.endFill();
		closeContainer_mc.lineStyle(1, 0x000000, 100);
		closeContainer_mc.moveTo(0,7);
		closeContainer_mc.lineTo(6,13);
		closeContainer_mc.moveTo(0,13);
		closeContainer_mc.lineTo(6,7);
		closeContainer_mc._x=122;
	}
	
	private function closeTab():Void{
		trace("myClose close tab");
	}
	
	private function setURLandTab(){
		this._targetExecutionLayer._objDefsRepository.retrieve("url").text = (linkedPanel_ref._exulis.holdURL_str==undefined) ? "..." :  linkedPanel_ref._exulis.holdURL_str;
		this.setTabText(linkedPanel_ref._exulis.holdTitle_str);
	}
	
	public function setState(status_str){
		trace("XULtab: setState for " + this._obj + " with status:" + status_str);
		trace("XULtab: current linked panel is " + linkedPanel_str);
		linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
		if(status_str == "checked"){
			// set tabpanel scroller
			linkedPanel_ref._parent._exulis.updateScroll(linkedPanel_ref._height);

			toggled_bool = true;
			for(var vCpt_num:Number = 0;vCpt_num < _actions.length;vCpt_num++){
				var vAction_obj:Object = _actions[vCpt_num];
				if(vAction_obj.type == "command"){
					toolbox.wrapRun(vAction_obj.action,this);
				}
			}
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

			linkedPanel_ref._exulis.nestContent(_root.setStateCallback, linkedPanel_ref);
			linkedPanel_ref._visible = true;
			this._targetExecutionLayer._objDefsRepository.retrieve("url").text = (linkedPanel_ref._exulis.holdURL_str==undefined) ? "..." : linkedPanel_ref._exulis.holdURL_str;
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
			linkedPanel_ref._visible = false;
		}
		this.setTabText(linkedPanel_ref._exulis.holdTitle_str);
	}

	function pressEvent(){
		trace("XULtab: onPress on " + this._obj);
		
		if(toggleable_bool){
			group_ref.setGroupFocus(this);
			
			trace("feedTrace for TAB, Stimulus: " + "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"]);
			_level0.currentItemRootLevel.feedTrace("TAB","id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"],"stimulus");
		}
		else{
			
			for(var vCpt_num:Number = 0;vCpt_num < _actions.length;vCpt_num++){
				var vAction_obj:Object = _actions[vCpt_num];
				if(vAction_obj.type == "command"){
					toolbox.wrapRun(vAction_obj.action,this);

					trace("feedTrace for DOACTION, Stimulus: " + "action"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+vAction_obj.action);
					_level0.currentItemRootLevel.feedTrace("DOACTION","action"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+vAction_obj.action,"stimulus");
				}
			}
		}
	}
	
	function historyAddCallback(linkedPanel_ref){
		linkedPanel_ref._visible = true;
		this._targetExecutionLayer._objDefsRepository.retrieve("url").text = (linkedPanel_ref._exulis.holdURL_str == undefined) ? "..." : linkedPanel_ref._exulis.holdURL_str;
		this.setTabText(linkedPanel_ref._exulis.holdTitle_str);

		linkedPanel_ref._parent._exulis.updateScroll(linkedPanel_ref._height);
	}

	function historyAdd(id,silent){
				
		trace("XULtab: (historyAdd): request to add " + id);
		linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
		linkedPanel_ref._visible = false;
		_historyCurrentIndex_num++ ;
		_history_array[_historyCurrentIndex_num] = id;
		_history_array.length = _historyCurrentIndex_num + 1;
		linkedPanel_str = id;
		linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
		linkedPanel_ref._exulis.nestContent(this.historyAddCallback, linkedPanel_ref);
		linkedPanel_ref._visible = true;
		this._targetExecutionLayer._objDefsRepository.retrieve("url").text = (linkedPanel_ref._exulis.holdURL_str == undefined) ? "..." : linkedPanel_ref._exulis.holdURL_str;
		this.setTabText(linkedPanel_ref._exulis.holdTitle_str);

		linkedPanel_ref._parent._exulis.updateScroll(linkedPanel_ref._height);
		
		if (silent==undefined) {
			var opt:String = new String();
			if (linkedPanel_ref._exulis.holdTitle_str != undefined)
				opt = _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "title"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+linkedPanel_ref._exulis.holdTitle_str;
			if (linkedPanel_ref._exulis.holdURL_str != undefined)
				opt += _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "url"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+linkedPanel_ref._exulis.holdURL_str;
			
			trace("feedTrace for HISTORY_ADD,pageid"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+id + opt + ",stimulus");
			_level0.currentItemRootLevel.feedTrace("HISTORY_ADD","pageid"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+id + opt,"stimulus");
		}
		
		return(_historyCurrentIndex_num);
	}

	function historyBack(){
		trace("XULtab: (historyBack) request");
		if(_historyCurrentIndex_num > 0){
			_historyCurrentIndex_num-- ;
			linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
			linkedPanel_ref._visible = false;
			linkedPanel_str = _history_array[_historyCurrentIndex_num];
			linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
			linkedPanel_ref._visible = true;
			this._targetExecutionLayer._objDefsRepository.retrieve("url").text = (linkedPanel_ref._exulis.holdURL_str==undefined) ? "..." : linkedPanel_ref._exulis.holdURL_str;
			this.setTabText(linkedPanel_ref._exulis.holdTitle_str);
			
			//trace("VINCEXO"+linkedPanel_ref._height);
			// set tabpanel scroller
			linkedPanel_ref._parent._exulis.updateScroll(linkedPanel_ref._height);	
		}
		
		var opt:String = new String();
		if (linkedPanel_ref._exulis.holdTitle_str != undefined)
			opt = _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "title"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+linkedPanel_ref._exulis.holdTitle_str;
		if (linkedPanel_ref._exulis.holdURL_str != undefined)
			opt += _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "url"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+linkedPanel_ref._exulis.holdURL_str;
		trace("feedTrace for HISTORY_BACK,pageid: " + "pageid"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+linkedPanel_str + opt + ",stimulus");
		_level0.currentItemRootLevel.feedTrace("HISTORY_BACK","pageid"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+linkedPanel_str + opt,"stimulus");
		
		return(_historyCurrentIndex_num);
	}

	function historyForward(){
		trace("XULtab: (historyForward) request");
		if(_historyCurrentIndex_num < _history_array.length-1){
			_historyCurrentIndex_num++ ;
			linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
			linkedPanel_ref._visible = false;
			linkedPanel_str = _history_array[_historyCurrentIndex_num];
			linkedPanel_ref = this._targetExecutionLayer._objDefsRepository.retrieve(linkedPanel_str);
			linkedPanel_ref._visible = true;
			this._targetExecutionLayer._objDefsRepository.retrieve("url").text = (linkedPanel_ref._exulis.holdURL_str==undefined) ? "..." : linkedPanel_ref._exulis.holdURL_str;
			this.setTabText(linkedPanel_ref._exulis.holdTitle_str);
			
			//trace("VINCEXO"+linkedPanel_ref._height);
			// set tabpanel scroller
			linkedPanel_ref._parent._exulis.updateScroll(linkedPanel_ref._height);
		}
		
		var opt:String = new String();
		if (linkedPanel_ref._exulis.holdTitle_str != undefined)
			opt = _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "title" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + linkedPanel_ref._exulis.holdTitle_str;
		if (linkedPanel_ref._exulis.holdURL_str != undefined)
			opt += _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "url" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + linkedPanel_ref._exulis.holdURL_str;
		trace("feedTrace for HISTORY_NEXT,pageid" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + "pageid" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + linkedPanel_str + opt + ",stimulus");
		_level0.currentItemRootLevel.feedTrace("HISTORY_NEXT","pageid" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + linkedPanel_str + opt,"stimulus");
		
		return(_historyCurrentIndex_num);
	}

	function rollOverEvent(){
		trace("XULtab: onRollOver on " + this._obj);
		if(this._obj["imageRollOver_mc"] != undefined){
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

	function rollOutEvent(){
		trace("XULtab: onRollOut on " + this._obj);
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
//		Mouse.show();
//		this.cursor_mc.removeMovieClip();
	};

	function mouseMoveEvent(){
		trace("XULtab (mouseMoveEvent): " + this);
//		this.cursor_mc._x = this._xmouse;
//		this.cursor_mc._y = this._ymouse;
	};

	function setLayout(){
		trace("XULtab: (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left + " width:" + this.width + " height:" + this.height);
		_obj._y = this.top;
		_obj._x = this.left;
//		_obj.focusEnabled = true;
//		_obj._focusrect = true;

		_objLabel = _obj.createTextField(_objDef.attributes["id"] + "_label", _obj._childNextDepth+100, 0, 0, 0, 0);
		_objLabel.type = "dynamic";
		_objLabel.html = true;
		setTabText(" ");
		_obj._visible = this._visibilityState;
	}

	function setTabText(text_str){
		_objLabel.htmlText = (text_str == undefined) ? "..." : text_str;
		_objLabel._width = _objLabel.getPreferredWidth();
		var textLen_num = text_str.length;
		while(_objLabel._width > 160){
			_objLabel.htmlText = text_str.substr(0,textLen_num);
			_objLabel._width = _objLabel.getPreferredWidth();
			if(textLen_num < 20){
				_objLabel.htmlText = text_str.substr(0,textLen_num) + "...";
				_objLabel._width = _objLabel.getPreferredWidth();
				break;
			}
			if(_objLabel._width < 160){
				_objLabel.htmlText = text_str.substr(0,textLen_num) + "...";
				_objLabel._width = _objLabel.getPreferredWidth();
				break;
			}
			textLen_num-- ;
		}
		_objLabel._height = _objLabel.getPreferredHeight();
		_objLabel._x = (_obj._exulis.width - _objLabel._width) / 2;
		_objLabel._y = (_obj._exulis.height - _objLabel._height) / 2;
	}

	function drawIcon(){
		trace("XULtab: DRAW on " + id);
		for(var objAttr in this._objDef.attributes){
			trace("XULtab: DRAW (" + objAttr + ") scanned");
			switch(objAttr){
				case "image":
				case "imageDisabled":
				case "imagePress":
				case "imageRollOver":
				case "imageRollOut":
				case "imageChecked":
				case "imageMouseNormal":
				case "imageMouseDisabled":
				{
					var vImage_str:String = this._objDef.attributes[objAttr];
					if(vImage_str != undefined){
						if(vImage_str != ""){
							_obj.createEmptyMovieClip(objAttr + "_mc",this._childNextDepth++);
							var vImage_mc = _obj[objAttr + "_mc"];
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
								target_mc._visible = (vTmpButtonName_str.substr(0,-3) == "image") ? true : false;
								if(target_mc._parent._exulis.toggled_bool){
									target_mc._parent._exulis.group_ref.setGroupFocus(target_mc._parent._exulis);
								}
							};
							var vImageTarget_str:String = this.toolbox.wrapRun(vImage_str, _guiSource,"SingleNode","String");
							vImage_loader.loadClip(vImageTarget_str,vImage_mc);
							trace("XULtab: DRAW " + vImageTarget_str + "(" + vImage_mc.getDepth() + ") for " + objAttr);
						}
					}
				}
			}
		}
	}

	function destroy(){
		trace("XULtab: (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		_obj.destroyObject(id);
		this.removeMovieClip();
	}
}
