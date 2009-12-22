import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGtext extends SVGelement {
	private var _objThis:MovieClip;
	private var _obj_text:TextField;
	private var x_num:Number;
	private var y_num:Number;
	private var dx_num:Number;
	private var dy_num:Number;
	public var text_fmt:TextFormat;

	function SVGtext(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGtext (create): text (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);

		x_num = parseFloat(_objDef.attributes.x); // x coord of the anchor point (bottom-left corner)
		y_num = parseFloat(_objDef.attributes.y); // y coord of the anchor point (bottom-left corner)
		dx_num = parseFloat(_objDef.attributes.dx); // shift on x axis
		dy_num = parseFloat(_objDef.attributes.dy); // shift on y axis

		x_num = (isNaN(x_num)) ? 0 : x_num; // #IMPLIED value
		y_num = (isNaN(y_num)) ? 0 : y_num; // #IMPLIED value
		dx_num = (isNaN(dx_num)) ? 0 : dx_num; // #IMPLIED value
		dy_num = (isNaN(dy_num)) ? 0 : dy_num; // #IMPLIED value

		var labelMinWidth:Number = 0;
		var labelMinHeight:Number = 0;
		var aText:String;
		var textFieldName_str:String;

		_objThis._x = x_num + dx_num;
		_objThis._y = y_num + dy_num;

		if((_objDef.attributes["fill"] == undefined) or (_objDef.attributes["fill"] == "none")){
			if((_objDef.attributes["stroke"] == undefined) or (_objDef.attributes["stroke"] == "none")){
				fill = {color:colors["black"], alpha:100}; // solid black fill for text if nothing requested
			}
			else{
				fill = {color:colors.getColor(_objDef.attributes.stroke), alpha:100}; // fill for text is aligned to stroke
			}
		}
		text_fmt = new TextFormat();
		text_fmt.size = parseInt(_objDef.attributes["font-size"]);
		text_fmt.font = _objDef.attributes["font-family"];
		text_fmt.bold = (_objDef.attributes["font-weight"] == "bold") ? true : false;
		text_fmt.underline = (_objDef.attributes["text-decoration"] == "underline") ? true : false;
		text_fmt.color = fill.color;
		text_fmt.align = "center";

		if(_objDef.childNodes[0].nodeValue != undefined){
			textFieldName_str = _objDef.attributes["id"] + "_text";
			_objThis.createTextField(textFieldName_str, _objThis._childNextDepth, 0, 0, 1, 1);
			_obj_text = _objThis[textFieldName_str];
			_objThis._childNextDepth++;
			aText = _objDef.childNodes[0].nodeValue;
			_obj_text.autoSize = "left";
			_obj_text.wordWrap = false;
			_obj_text.multiline = false;
			_obj_text.condenseWhite = true;
			_obj_text.text = toolbox.stripTag(toolbox.cleanString(aText,false,true,true,true,true));
			labelMinWidth = _obj_text.getPreferredWidth();
			labelMinHeight = _obj_text.getPreferredHeight();
			_obj_text._x = 0;
			_obj_text._y = -1 * (labelMinHeight * 0.8); // TODO: fetch baseline fct of the font-family
			if(_objDef.attributes["text-anchor"] == undefined){
				//_obj_text.autoSize = "left"; // nothing to do
				_obj_text.setTextFormat(text_fmt);
			}
			else{
				switch (_objDef.attributes["text-anchor"]){
					case "middle":
					{
						_obj_text._x -= Math.round(labelMinWidth / 2);
						_obj_text.setTextFormat(text_fmt);
						break;
					}
					case "center":
					{
						_obj_text.multiline = true;
						_obj_text.html = true;
						text_fmt.align = "center";
						_obj_text.htmlText = toolbox.cleanString(aText,false,true,true,true,true);
						_obj_text.setTextFormat(text_fmt);
						break;
					}
					case "right":
					{
						_obj_text._x -= labelMinWidth;
						_obj_text.setTextFormat(text_fmt);
						break;
					}
					case "left":
					default:
					{
						_obj_text.setTextFormat(text_fmt);
					}
				}
			}
			trace("SVGtext (create) text-anchor: " + _objDef.attributes["text-anchor"] + "  autosize: " + _obj_text.autoSize);
			trace("SVGtext (create) labelMinWidth: " + labelMinWidth + "  labelMinHeight: " + labelMinHeight);
			trace("SVGtext (create) width: " + _obj_text._width + "  height: " + _obj_text._height);
			trace("SVGtext (create) x: " + _obj_text._x + "  y: " + _obj_text._y);
		}
		else{
			trace("SVGtext (create) for " + id + ": _objDef.childNodes[0].nodeValue is null so there is probably a TSpan");
		}

		return _objThis;
	}
	function destroy(){
		trace("SVGtext (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._obj.destroy();
		}
		this.removeMovieClip();
	}
}