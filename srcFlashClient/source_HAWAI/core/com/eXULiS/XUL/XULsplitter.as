import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULsplitter extends XULelement {
	var _obj:MovieClip;
	function XULsplitter(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 6; //in case of vertical split (hbox) and no flex property set
		_defaultHeight = 6; //in case of horizontal split (vbox)
	}
	function create(){
		trace("XULsplitter (create): splitter (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
// complete the object definition with the ancestor class definition
		_obj = super.create(_obj,this,1);
// attach local properties
		_objParent._exulis._splitState = "after";
/*
		_objParent._exulis._obj.dividerSize = 6;
		_objParent._exulis._obj.dividerLocation = 50;
		_objParent._exulis._obj.dividerExpanded = true;
		if(_objParent._exulis._type == "hbox"){
			width = 6;
//			_objParent._exulis._reservedWidth += width;
		}
		if(_objParent._exulis._type == "vbox"){
			height = 6;
//			_objParent._exulis._reservedHeighth += height;
		}
*/

		return _obj;
	}
	function setLayout(){
		trace("XULsplitter (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left)
//		_obj._y = this.top;
//		_obj._x = this.left;
	}
	function destroy(){
		trace("XULsplitter (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}