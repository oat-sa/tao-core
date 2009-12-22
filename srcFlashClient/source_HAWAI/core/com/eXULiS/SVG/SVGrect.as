import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGrect extends SVGelement {
	private var _objThis:MovieClip;
	private var x_num:Number;
	private var y_num:Number;
	private var width_num:Number;
	private var height_num:Number;
	private var rx_num:Number;
	private var ry_num:Number;

	function SVGrect(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGrect (create): rect (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);
		draw();
	}
	function redraw(objDef:XMLNode){
		trace("SVGrect (redraw): rect (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objThis.clear();
		_objThis = super.setShape(_objThis,objDef)
		draw();
	}
	function draw(){
		x_num = parseFloat(_objDef.attributes.x); // x coord of the anchor point (top-left corner)
		y_num = parseFloat(_objDef.attributes.y); // y coord of the anchor point (top-left corner)
		width_num = parseFloat(_objDef.attributes.width); // width of rectangle
		height_num = parseFloat(_objDef.attributes.height); // height of rectangle
		rx_num = parseFloat(_objDef.attributes.rx); // radius on x axis for rounded corners
		ry_num = parseFloat(_objDef.attributes.ry); // radius on y axis for rounded corners

		x_num = (isNaN(x_num)) ? 0 : x_num; // #IMPLIED value
		y_num = (isNaN(y_num)) ? 0 : y_num; // #IMPLIED value
		rx_num = (isNaN(rx_num)) ? 0 : rx_num; // #IMPLIED value
		ry_num = (isNaN(ry_num)) ? 0 : ry_num; // #IMPLIED value

		if((isNaN(width_num)) || (isNaN(height_num))) {
			// no need to go further
		}
		else{ // ok, args seem to be conform to W3C SVG specs - TODO: for full compliance, should consider %age and so on
			_objThis.lineStyle(stroke.width, stroke.color, stroke.alpha);
			_objThis.beginFill(fill.color, fill.alpha);

			if((rx_num == 0) || (ry_num == 0)){
				_objThis.moveTo(x_num, y_num);
				_objThis.lineTo(x_num + width_num, y_num);
				_objThis.lineTo(x_num + width_num, y_num + height_num);
				_objThis.lineTo(x_num, y_num + height_num);
				_objThis.lineTo(x_num, y_num);
			}
			else{
				var cx_num:Number;
				var cy_num:Number;
				var tan_Pi_num:Number;
				var sin_Pi_num:Number;
				var cos_Pi_num:Number;
				var radius_Pi_1_num:Number;
				var radius_Pi_2_num:Number;
				tan_Pi_num = Math.tan(Math.PI/8);
				sin_Pi_num = Math.sin(Math.PI/4);
				cos_Pi_num = Math.cos(Math.PI/4);
				radius_Pi_1_num = Math.sqrt(Math.pow(Math.cos(Math.PI/8) * ry_num,2) + Math.pow(Math.sin(Math.PI/8) * ry_num,2))
				radius_Pi_2_num = Math.sqrt(Math.pow(Math.cos(3 * Math.PI/8) * rx_num,2) + Math.pow(Math.sin(3 * Math.PI/8) * rx_num,2))

				cx_num = x_num + width_num - rx_num;
				cy_num = y_num + height_num - ry_num;

				_objThis.moveTo(cx_num + rx_num, cy_num);
				_objThis.curveTo(rx_num + cx_num, tan_Pi_num * radius_Pi_1_num + cy_num, cos_Pi_num * rx_num + cx_num, sin_Pi_num * ry_num + cy_num);
				_objThis.curveTo(tan_Pi_num * radius_Pi_2_num + cx_num, ry_num + cy_num, cx_num, ry_num + cy_num);
				_objThis.lineTo(x_num + rx_num, y_num + height_num);

				cx_num = x_num + rx_num;
				cy_num = y_num + height_num - ry_num;

				_objThis.curveTo(-1 * tan_Pi_num * radius_Pi_2_num + cx_num, ry_num + cy_num, -1 * cos_Pi_num * rx_num + cx_num, sin_Pi_num * ry_num + cy_num);
				_objThis.curveTo(-1 * rx_num + cx_num, tan_Pi_num * radius_Pi_1_num + cy_num, -1 * rx_num + cx_num, cy_num);
				_objThis.lineTo(x_num, y_num + ry_num);

				cx_num = x_num + rx_num;
				cy_num = y_num + ry_num;

				_objThis.curveTo(-1 * rx_num + cx_num, -1 * tan_Pi_num * radius_Pi_1_num + cy_num, -1 * cos_Pi_num * rx_num + cx_num, -1 * sin_Pi_num * ry_num + cy_num);
				_objThis.curveTo(-1 * tan_Pi_num * radius_Pi_2_num + cx_num, -1 * ry_num + cy_num, cx_num, -1 * ry_num + cy_num);
				_objThis.lineTo(x_num + width_num - rx_num, y_num);

				cx_num = x_num + width_num - rx_num;
				cy_num = y_num + ry_num;

				_objThis.curveTo(tan_Pi_num * radius_Pi_2_num + cx_num, -ry_num + cy_num, cos_Pi_num * rx_num + cx_num, -1 * sin_Pi_num * ry_num + cy_num);
				_objThis.curveTo(rx_num + cx_num, -1 * tan_Pi_num * radius_Pi_1_num + cy_num, rx_num + cx_num, cy_num);
				_objThis.lineTo(x_num + width_num, y_num + height_num - ry_num);

			}

			_objThis.endFill();
		}
		return _objThis;
	}
	function destroy(){
		trace("SVGrect (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}