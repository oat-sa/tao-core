import com.eXULiS.BLACK.BLACKelement;
import com.eXULiS.lib.*;
import com.xfactorstudio.xml.xpath.*;

class com.eXULiS.BLACK.BLACKspreadSheet extends BLACKelement {
	private var _obj:MovieClip;
	public var _ss:Object;
	function BLACKspreadSheet(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	
	function create(){
		trace("BLACKspreadSheet (create): spreadSheet (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);

		_ss=new ItemSpreadSheetModel();

		var tmpPath_str:String;
		tmpPath_str = toolbox.wrapRun(_objDef.attributes["path"], _guiSource, "SingleNode", "String");
		
		//tmpPath_str = _objDef.attributes["path"];
		
		trace("BLACKspreadSheet (create): XML source = " + _guiSource);
		trace("BLACKspreadSheet (create): XML source = " + _guiSource);
		trace("BLACKspreadSheet (create): source = " + tmpPath_str);
		
		/*var nextXLFref_num:Number;
		var stickerEnd_num:Number;
		var toInsertText_str:String = "";
		var indicator_str:String = "";
		var stickerText_str:String = "";
		
		nextXLFref_num = tmpPath_str.indexOf("xlf://");
		while(nextXLFref_num != -1){
			indicator_str = tmpPath_str.substr(nextXLFref_num - 1,1);
		trace("BLACKspreadSheet (create): " + indicator_str + " on " + nextXLFref_num);
			if(indicator_str == ">"){
				stickerEnd_num = tmpPath_str.indexOf("<",nextXLFref_num + 1);
				stickerText_str = tmpPath_str.substring(nextXLFref_num,stickerEnd_num);
				toInsertText_str = toolbox.wrapRun(stickerText_str);
		trace("BLACKspreadSheet (create): " + stickerText_str + " -> " + toInsertText_str);
				tmpPath_str = tmpPath_str.substr(0,nextXLFref_num) + "<![CDATA[" + toInsertText_str + "]]>" + tmpPath_str.substr(stickerEnd_num);
		trace("BLACKspreadSheet (create): new = " + tmpPath_str);
			}
			else{
				stickerEnd_num = tmpPath_str.indexOf(indicator_str,nextXLFref_num + 1);
				stickerText_str = tmpPath_str.substring(nextXLFref_num,stickerEnd_num);
				toInsertText_str = toolbox.wrapRun(stickerText_str);
		trace("BLACKspreadSheet (create): " + stickerText_str + " -> " + toInsertText_str);
				tmpPath_str = tmpPath_str.substr(0,nextXLFref_num) + toInsertText_str + tmpPath_str.substr(stickerEnd_num);
		trace("BLACKspreadSheet (create): new = " + tmpPath_str);
			}
			nextXLFref_num = tmpPath_str.indexOf("xlf://",nextXLFref_num);
		}
		trace("BLACKspreadSheet (create): final = " + tmpPath_str);*/
		
		
		_ss.loadXMLstring(tmpPath_str);
		
		
		this._targetExecutionLayer[_objDef.attributes["id"]] = _ss;

		return _obj;
	}
	function destroy(){
		trace("BLACKspreadSheet (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}