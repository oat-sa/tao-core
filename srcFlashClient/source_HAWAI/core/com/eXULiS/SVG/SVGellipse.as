import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGellipse extends SVGelement {
	private var _objThis:MovieClip;
	private var cx_num:Number;
	private var cy_num:Number;
	private var rx_num:Number;
	private var ry_num:Number;

	function SVGellipse(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGellipse (create): ellipse (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);

		cx_num = parseFloat(_objDef.attributes.cx); // x coord of the anchor point (center of the circle)
		cy_num = parseFloat(_objDef.attributes.cy); // y coord of the anchor point (center of the circle)
		rx_num = parseFloat(_objDef.attributes.rx); // radius on x axis
		ry_num = parseFloat(_objDef.attributes.ry); // radius on y axis

		cx_num = (isNaN(cx_num)) ? 0 : cx_num; // #IMPLIED value
		cy_num = (isNaN(cy_num)) ? 0 : cy_num; // #IMPLIED value

		if((isNaN(rx_num)) || (isNaN(ry_num))) {
			// no need to go further
		}
		else{ // ok, args seem to be conform to W3C SVG specs - TODO: for full compliance, should consider %age and so on
			_objThis.lineStyle(stroke.width, stroke.color, stroke.alpha);
			_objThis.beginFill(fill.color, fill.alpha);

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

			_objThis.moveTo(cx_num + rx_num, cy_num);
			_objThis.curveTo(rx_num + cx_num, tan_Pi_num * radius_Pi_1_num + cy_num, cos_Pi_num * rx_num + cx_num, sin_Pi_num * ry_num + cy_num);
			_objThis.curveTo(tan_Pi_num * radius_Pi_2_num + cx_num, ry_num + cy_num, cx_num, ry_num + cy_num);
			_objThis.curveTo(-1 * tan_Pi_num * radius_Pi_2_num + cx_num, ry_num + cy_num, -1 * cos_Pi_num * rx_num + cx_num, sin_Pi_num * ry_num + cy_num);
			_objThis.curveTo(-1 * rx_num + cx_num, tan_Pi_num * radius_Pi_1_num + cy_num, -1 * rx_num + cx_num, cy_num);

			_objThis.curveTo(-1 * rx_num + cx_num, -1 * tan_Pi_num * radius_Pi_1_num + cy_num, -1 * cos_Pi_num * rx_num + cx_num, -1 * sin_Pi_num * ry_num + cy_num);
			_objThis.curveTo(-1 * tan_Pi_num * radius_Pi_2_num + cx_num, -1 * ry_num + cy_num, cx_num, -1 * ry_num + cy_num);
			_objThis.curveTo(tan_Pi_num * radius_Pi_2_num + cx_num, -ry_num + cy_num, cos_Pi_num * rx_num + cx_num, -1 * sin_Pi_num * ry_num + cy_num);
			_objThis.curveTo(rx_num + cx_num, -1 * tan_Pi_num * radius_Pi_1_num + cy_num, rx_num + cx_num, cy_num);

			_objThis.endFill();
		}

		return _objThis;
	}
	function destroy(){
		trace("SVGellipse (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}