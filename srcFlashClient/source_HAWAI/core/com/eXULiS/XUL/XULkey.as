import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULkey extends XULelement {
	
	private var _obj:MovieClip;
	private var image_mc:MovieClip;
	
	function XULkey(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
	}
	
	function create(){
		trace("XULkey (create): image (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		trace("XULkey (create): _objDef " + _objDef);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		_objParent._exulis.addCombinationKey(_objDef);
		return _obj;
	}
	
	

	function destroy(){
		trace("XULkey (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
