import com.eXULiS.BLACK.BLACKelement;

class com.eXULiS.BLACK.BLACKmanifest extends BLACKelement {
	private var _obj:MovieClip;
	function BLACKmanifest(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
//		trace("BLACKmanifest (create): manifest (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
//trace("BLACKmanifest >> guiSource 1: " + _guiSource);
		if(_guiSource == undefined){
			_guiSource = new XML(String(_root.eXULiS.getParserRef().xml));
		}
/*
trace("BLACKmanifest >> guiSource 2: " + _guiSource);
for(var nam in _objParent._exulis){
		trace("BLACKmanifest (dump) [" + nam + "]: " + _objParent._exulis[nam]);
}
*/
//		trace("BLACKmanifest (create) [" + id + "]: parent._exulis._visibilityState: " + _objParent._exulis._visibilityState);
		return _obj;
	}
	function destroy(){
		trace("BLACKmanifest (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this._obj.removeMovieClip();
	}
}