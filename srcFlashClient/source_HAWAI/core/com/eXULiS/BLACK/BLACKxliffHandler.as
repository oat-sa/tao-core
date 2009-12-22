import com.eXULiS.BLACK.BLACKelement;
import com.eXULiS.lib.*;
import mx.utils.Delegate;

class com.eXULiS.BLACK.BLACKxliffHandler extends BLACKelement {
	private var _obj:MovieClip;
	public var xliffSource_xml:XML;
	function BLACKxliffHandler(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("BLACKxliffHandler (create): xliffHandler (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		loadXliff();
		return _obj;
	}
	public function loadXliff(){
/*
		var xlfDefinitionFile_str:String;
		xlfDefinitionFile_str = toolbox.wrapRun(_objDef.firstChild.nodeValue, _guiSource,"SingleNode","String");
		trace("BLACKxliffHandler (loadXliff): xlfDefinitionFile_str: " + xlfDefinitionFile_str);
		xliffSource_xml = new XML();
		xliffSource_xml.onLoad = Delegate.create(this,loadXliffHandler);
		xliffSource_xml.ignoreWhite = true;
		xliffSource_xml.load(xlfDefinitionFile_str);
		_root.xlfLoading = true;
*/
	}
	function destroy(){
		trace("BLACKxliffHandler (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}