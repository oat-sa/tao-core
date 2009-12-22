import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULlabel extends XULelement {
	private var _obj:TextField;
	function XULlabel(xulParent,xulDef:XMLNode){
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 100;
	}
	function create(){
		trace("XULlabel (create): " + _type + " (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		
		
		
		
		_obj = _objParent.createTextField(_objDef.attributes["id"], _objParent._childNextDepth, 0, 0, 0, 0);
		
			
		
		_obj = super.create(_obj,this,1);
		
		
		setLayout();

		return _obj;
	}
	function setLayout(){
		trace("XULlabel (setLayout): " + _type + " for " + id + ": special properties setting, top:" + this.top + " left:" + this.left);
		_obj._y = this.top;
		_obj._x = this.left;
		
		_obj.autoSize = "Left";
		_obj.wordWrap = true;
		_obj.html = true;
		_obj.border = false;
		_obj.type = "dynamic";
		
		_obj._x = (this.left != undefined) ? Number(this.left):null;
		_obj._y = (this.top != undefined) ? Number(this.top):null;
		_obj._width = (this.width != undefined) ? Number(this.width):null;
		
		if (this.height != undefined)
		{
			_obj.autoSize = false;
		}
		
		_obj._height = (this.height != undefined) ? Number(this.height):null;
		
		_obj.selectable = (_objDef.attributes["disabled"] != undefined) ? Boolean(_objDef.attributes["disabled"]):null;
		_obj.maxChars = (_objDef.attributes["maxlength"] != undefined) ? Number(_objDef.attributes["maxlength"]):null;
		_obj.maxChars = (_objDef.attributes["size"] != undefined) ? Number(_objDef.attributes["maxlength"]):null;
		_obj.multiline = (_objDef.attributes["multiline"] != undefined) ? Boolean(_objDef.attributes["multiline"]):null;
		_obj.wordWrap = (_objDef.attributes["wrap"] != undefined) ? Boolean(_objDef.attributes["wrap"]):null;
		_obj.selectable = (_objDef.attributes["readonly"] != undefined) ? Boolean(_objDef.attributes["readonly"]):null;
		_obj.background = (_objDef.attributes["background"] != undefined) ? Boolean(_objDef.attributes["background"]):null;
		_obj.password = (_objDef.attributes["type"].lowerCase() == "password") ? true : null;
		_obj.htmlText = (_objDef.attributes["value"] != undefined) ? toolbox.wrapRun(_objDef.attributes["value"], _guiSource,"SingleNode","String") : "" ;
		_obj.tabIndex = (_objDef.attributes["tabIndex"] != undefined) ? Number(_objDef.attributes["tabIndex"]) : null ;	
		
		_obj = super.applyStyle(_obj);
		
		
	}
	function destroy(){
		trace("XULbox (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
