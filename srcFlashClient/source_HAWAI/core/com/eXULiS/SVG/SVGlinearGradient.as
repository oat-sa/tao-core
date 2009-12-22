import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGlinearGradient extends SVGelement {
    public var defsRepository:Array;
	private var _objThis:MovieClip;
	function SVGlinearGradient(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGlinearGradient (create): linear gradient (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);
		_objThis._x = -1;
		_objThis._y = -1;
		_objThis._width = 1;
		_objThis._height = 1;
		_objThis._visible = false;
		defsRepository = new Array();
		return _objThis;
	}
	function destroy(){
		trace("SVGlinearGradient (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
