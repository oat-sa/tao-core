import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGg extends SVGelement {
	private var _objThis:MovieClip;
	function SVGg(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGgroup (create): group (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);
		return _objThis;
	}
	function destroy(){
		trace("SVGgroup (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}