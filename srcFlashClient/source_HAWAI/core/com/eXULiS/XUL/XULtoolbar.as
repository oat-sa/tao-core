import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULtoolbar extends XULelement {
	var _obj:MovieClip;
	public var _group_array:Array;
//	var addMenuItem:Function;
	function XULtoolbar(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		trace("XULtoolbar: relay given to XULelement with xulParent = " + xulParent);
	}
	function create(){
		trace("XULtoolbar: create menu popup (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
// complete the object definition with the ancestor class definition
		_obj = super.create(_obj,this,1);
		_obj.setStyle = this.setStyle;
		_obj = super.applyStyle(_obj);

		setLayout();
// attach local properties
		trace("XULtoolbar: special properties setting")
//		_obj.addMenuItem = this.addMenuItem;;

		return _obj;
	}
	function registerGroupMember(aMember){
		trace("XULtoolbar: registerGroupMember triggered");
		if(_group_array == undefined){
			_group_array = new Array();
		}
		_group_array.push(aMember);
	}
	function setGroupFocus(newLeader){
		trace("XULtoolbar: setGroupFocus triggered");
		for(var vCpt_num:Number = 0;vCpt_num<_group_array.length;vCpt_num++){
			if(_group_array[vCpt_num] == newLeader){
				_group_array[vCpt_num].setState("checked");
			}
			else{
				_group_array[vCpt_num].setState("not-checked");
			}
		}
	}
	function setStyle(propName,propVal){
		trace("XULtoolbar: propName=" + propName + " propVal=" + propVal);
		switch(propName){
			case("background"):
			case("background-color"):
			case("backgroundColor"):{
				this["_exulis"]["backgroundColor"] = this["_exulis"].colors.getColor(String(propVal));
				if(this["_exulis"]["backgroundColor"] != undefined){
					this["_exulis"]["background"] = true;
				}
				break;
			}
			default:{
				this["_exulis"][propName] = propVal;
			}
		}
	}
	function setLayout(){
		trace("XULtoolbar (setLayout): " + _type + " for " + id + ": special properties setting, top:" + this.top + " left:" + this.left);
		if(this["backgroundColor"] == undefined){
			//
		}
		else{
//			_obj.backgroundColor = this["backgroundColor"];
			_obj.beginFill(this["backgroundColor"]);//0xFFCC99
			_obj.moveTo(0, 0);
			_obj.lineTo(this.width, 0);
			_obj.lineTo(this.width, this.height);
			_obj.lineTo(0, this.height);
			_obj.lineTo(0, 0);
			_obj.endFill();
		}
		_obj._y = this.top;
		_obj._x = this.left;
	}
	public function addMenuItem(menuInitObj_obj:Object){
		trace("XULtoolbar: add a menu item (" + menuInitObj_obj.toString() + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.addMenuItem(menuInitObj_obj);
	}
}
