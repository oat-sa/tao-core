import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULradiogroup extends XULelement {
	private var _obj:MovieClip;
	private var selectedRadio_str:String;
	function XULradiogroup(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 100;
	}
	function create(){
		trace("XULradiogroup (create): radiogroup (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		setLayout();
		
		return _obj;
	}
	function setLayout(){
		trace("XULradiogroup (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left)
		_obj._y = this.top;
		_obj._x = this.left;
	}

	function destroy(){
		trace("XULwindow (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
