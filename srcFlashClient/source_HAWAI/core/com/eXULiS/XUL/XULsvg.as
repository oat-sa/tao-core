import com.eXULiS.XUL.XULelement;
import com.eXULiS.SVG.SVG2Flash;

class com.eXULiS.XUL.XULsvg extends XULelement {
	private var _obj:MovieClip;
	function XULsvg(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 100;
	}
	function create(){
		trace("XULsvg (create): window (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objDef.attributes["flex"] = 1;
		_obj = super.create(_obj,this,1);
		var vTmpObj:Object;
		var vTmpCanvasXULObj:Object;
		vTmpObj = new SVG2Flash(_obj,_root);
		vTmpCanvasXULObj = vTmpObj.parseXML(_objDef);
		return _obj;
	}
	function setLayout(){
		trace("XULsvg (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left)
		_obj._y = this.top;
		_obj._x = this.left;
	}
	function destroy(){
		trace("XULsvg (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
