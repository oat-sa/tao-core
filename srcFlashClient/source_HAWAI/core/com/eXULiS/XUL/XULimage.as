import com.eXULiS.XUL.XULelement;

class com.eXULiS.XUL.XULimage extends XULelement {
	private var _obj:MovieClip;
	private var image_mc:MovieClip;
	function XULimage(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		_defaultWidth = 100;
		_defaultHeight = 100;
	}
	function create(){
		trace("XULimage (create): image (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		trace("XULimage (create): _objDef " + _objDef);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		//_objParent.createClassObject(mx.controls.Button,_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		
		//_obj = super.applyStyle(_obj);
		
		_obj._visible =  (_objDef.attributes["visible"].toLowerCase()=="false") ? false : true;
		
		setLayout();
		drawImage();
		
		return _obj;
	}
	function setLayout(){
		trace("XULimage (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left)
		_obj._y = this.top;
		_obj._x = this.left;
	}
	
	function drawImage()
	{
		trace("XULimage DRAW, _objDef: " + _objDef);
		for(var nam in this){
			trace("XULimage DRAW (" + this.getDepth() + "): ." + nam + " = " + this[nam]);
		}
		if((this._objDef.attributes["src"] != undefined) && (_objDef.attributes["src"] != "")){
			if(_obj["image_mc"] == undefined){
				_obj.createEmptyMovieClip("image_mc",1);
				this.image_mc = _obj["image_mc"];
				this.image_mc.createEmptyMovieClip("content_mc",1);
				var image = new MovieClipLoader();
				image.onLoadInit = function(target_mc:MovieClip) {
					
					// assume that the image top-left is the same as its container
					trace("XULimage OVER target_mc._parent._width:" + target_mc._parent._width + "  target_mc._width:" + target_mc._width);
					target_mc._x = target_mc._parent._x; //(target_mc._parent._width - target_mc._width)/2;
					target_mc._y = target_mc._parent._y; //((target_mc._parent._parent.height - target_mc._height)/2) + 1;
					
					// width and height don't change
					//target_mc._width = target_mc._parent._width;
					//target_mc._height = target_mc._parent._height;
				};
				trace("XULimage wrapRun returns: " + this.toolbox.wrapRun(this._objDef.attributes["src"], _guiSource,"SingleNode","String"));
				image.loadClip(this.toolbox.wrapRun(this._objDef.attributes["src"], _guiSource,"SingleNode","String"),this.image_mc["content_mc"]);
			}
		}
		this._obj.redraw(true);
	}
	
	function destroy(){
		trace("XULwindow (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
