import com.eXULiS.BLACK.BLACKelement;

class com.eXULiS.BLACK.BLACKcontent extends BLACKelement {
	private var _obj:MovieClip;
	function BLACKcontent(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("BLACKcontent (create): content (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		return _obj;
	}
	function destroy(){
		trace("BLACKcontent (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this._obj.removeMovieClip();
	}
}