import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULstack extends XULelement {
	private var _obj:MovieClip;
	function XULstack(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 100;
	}
	function create(){
		trace("XULstack (create): stack (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
//		_objDef.attributes["flex"] = 1;
		_obj = super.create(_obj,this,1);
		return _obj;
	}
	function setLayout(){
		trace("XULstack (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left)
		_obj._y = 0;
		_obj._x = 0;
	}
	function destroy(){
		trace("XULstack (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
