import com.eXULiS.SVG.SVGelement;
import com.eXULiS.SVG.SVGdefs2Flash;

class com.eXULiS.SVG.SVGuse extends SVGelement {
	private var _objThis:MovieClip;
	private var x_num:Number;
	private var y_num:Number;
	private var width_num:Number;
	private var height_num:Number;
	private var xlink_href_mc:MovieClip;

	function SVGuse(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGuse (create): use (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		var xlink_href_str:String;

		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);

		x_num = parseFloat(_objDef.attributes.x); // x coord of the anchor point (top-left corner)
		y_num = parseFloat(_objDef.attributes.y); // y coord of the anchor point (top-left corner)
		width_num = parseFloat(_objDef.attributes.width); // width of rectangle
		height_num = parseFloat(_objDef.attributes.height); // height of rectangle
		xlink_href_str = _objDef.attributes["xlink:href"];

		x_num = (isNaN(x_num)) ? 0 : x_num; // #IMPLIED value
		y_num = (isNaN(y_num)) ? 0 : y_num; // #IMPLIED value

		_objThis._x = x_num;
		_objThis._y = y_num;

		if((xlink_href_str != undefined) && (xlink_href_str != "")){
			trace("SVGuse (create): " + id + "->name = " + xlink_href_str);
			var xlink_href_ref:MovieClip = _objThis._exulis._objDefsRepository.retrieve(xlink_href_str);
			trace("SVGuse (create): " + id + "->Ref = " + xlink_href_ref);
			if(xlink_href_ref != undefined){
				xlink_href_mc = _objThis.createEmptyMovieClip(id + "_xlink_href", _objThis._childNextDepth);
				var _exulis:Object = new Object();
				_exulis.id = id + "_xlink_href";
				_exulis._xlinkRef = xlink_href_str;
				_exulis._objParent = _objThis;
				xlink_href_mc._exulis = _exulis;
				xlink_href_mc._childNextDepth = 1;
				if(xlink_href_ref._type == "symbol"){
					xlink_href_mc._type = "svg"; // to be slightly compliant with SVG 1.1 standard
				}
				else{
					xlink_href_mc._type = "g";
				}

				var vTmpObj:Object;
				vTmpObj = new SVGdefs2Flash(xlink_href_mc, _objThis._targetExecutionLayer);
				var vTmpCanvasDefObj:Object;
				vTmpCanvasDefObj = vTmpObj.parseXML(xlink_href_ref._exulis._objDef);
				xlink_href_mc._x = 0;
				xlink_href_mc._y = 0;

				xlink_href_mc._childNextDepth++;
			}
		}
		return _objThis;
	}
	function destroy(){
		trace("SVGuse (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}