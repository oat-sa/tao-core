import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULtoolbarseparator extends XULelement {
	var _obj:MovieClip;
//	var addMenuItem:Function;
	function XULtoolbarseparator(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		trace("XULtoolbarseparator: relay given to XULelement with xulParent = " + xulParent);
	}
	function create(){
		trace("XULtoolbarseparator: create menu popup (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
// complete the object definition with the ancestor class definition
		_obj = super.create(_obj,this,1);
// not relevant
/*
        _obj.move(left,top);
        _obj.setSize(width,height);
*/
// attach local properties
		trace("XULtoolbarseparator: special properties setting")
//		_obj.addMenuItem = this.addMenuItem;;

		return _obj;
	}
	public function addMenuItem(menuInitObj_obj:Object){
		trace("XULtoolbarseparator: add a menu item (" + menuInitObj_obj.toString() + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.addMenuItem(menuInitObj_obj);
	}
}
