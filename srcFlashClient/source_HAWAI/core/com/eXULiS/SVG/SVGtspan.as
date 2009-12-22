import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGtspan extends SVGelement {
	private var _objThis:MovieClip;
	private var x_num:Number;
	private var y_num:Number;
	private var dx_num:Number;
	private var dy_num:Number;

	function SVGtspan(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGtspan (create): tspan (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createTextField(_objDef.attributes["id"], _objParent._childNextDepth, 0, 0, 300, 200);
		_objThis = super.create(_objThis,this,1);

		x_num = parseFloat(_objDef.attributes.x); // x coord of the anchor point (bottom-left corner)
		y_num = parseFloat(_objDef.attributes.y); // y coord of the anchor point (bottom-left corner)
		dx_num = parseFloat(_objDef.attributes.dx); // shift on x axis
		dy_num = parseFloat(_objDef.attributes.dy); // shift on y axis

		x_num = (isNaN(x_num)) ? 0 : x_num - _objParent._x; // #IMPLIED value
		y_num = (isNaN(y_num)) ? 0 : y_num - _objParent._y; // #IMPLIED value
		dx_num = (isNaN(dx_num)) ? 0 : dx_num - _objParent._dx; // #IMPLIED value
		dy_num = (isNaN(dy_num)) ? 0 : dy_num - _objParent._dy; // #IMPLIED value

trace("SVGtspan (create): x:" + x_num + " y:" + y_num);
trace("SVGtspan (create): dx:" + x_num + " dy:" + y_num);

		var labelMinWidth:Number = 0;
		var labelMinHeight:Number = 0;
		var aText:String;
		aText = (_objDef.childNodes[0].nodeValue == undefined) ? "" : _objDef.childNodes[0].nodeValue;
		_objThis.wordWrap = false;
		_objThis.multiline = false;
		_objThis.condenseWhite = true;
		_objThis.autoSize = "left";

var my_fmt:TextFormat = _objParent._exulis.text_fmt;
/*
if(_objDef.attributes["font-size"] != undefined){
	my_fmt.size = parseInt(_objDef.attributes["font-size"]);
}
if(_objDef.attributes["font-family"] != undefined){
	my_fmt.font = _objDef.attributes["font-family"];
}
if(_objDef.attributes["font-weight"] != undefined){
	my_fmt.bold = (_objDef.attributes["font-weight"] == "bold") ? true : false;
}
if(_objDef.attributes["text-decoration"] != undefined){
	my_fmt.underline = (_objDef.attributes["text-decoration"] == "underline") ? true : false;
}
my_fmt.color = fill.color;
*/
//_objThis.setNewTextFormat(my_fmt);
_level0._text_fmt.size = my_fmt.size;
_objThis.setNewTextFormat(_level0._text_fmt);
//_objThis.embedFonts = true;

trace("SVGtspan (create) fill.color: " + fill.color);
trace("SVGtspan (create) text-format: " + my_fmt);

		_objThis.text = toolbox.cleanString(aText,false,true,true,true,true);
trace("SVGtspan (create) fmt.color: " + my_fmt.color);
		labelMinWidth = _objThis.getPreferredWidth();
		labelMinHeight = _objThis.getPreferredHeight();
		_objThis._y = y_num + dy_num - (labelMinHeight * 0.73); // TODO: fetch baseline fct of the font-family

// +++++++++
		if(_objParent._exulis._objDef.attributes["text-anchor"] == undefined){
			//_obj_text.autoSize = "left"; // nothing to do
			_objThis._x = x_num + dx_num;// - (labelMinWidth * 0.22);
		}
		else{
			switch (_objParent._exulis._objDef.attributes["text-anchor"]){
				case "middle":
				case "center":
				{
					_objThis._x -= Math.round(labelMinWidth / 2);
					break;
				}
				case "right":
				{
					_objThis._x -= labelMinWidth;
					break;
				}
				case "left":
				default:
				{
					// nothing to do
				}
			}
		}
// +++++++++
		return _objThis;
	}
	function destroy(){
		trace("SVGtspan (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}