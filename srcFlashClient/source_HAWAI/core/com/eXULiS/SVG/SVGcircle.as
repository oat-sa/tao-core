import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGcircle extends SVGelement {
	private var _objThis:MovieClip;
	private var cx_num:Number;
	private var cy_num:Number;
	private var r_num:Number;

	function SVGcircle(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGcircle (create): circle (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);
		draw();
	}
	function redraw(objDef:XMLNode){
		trace("SVGcircle (redraw): circle (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objThis.clear();
		_objThis = super.setShape(_objThis,objDef)
		draw();
	}
	function draw(){
		cx_num = parseFloat(_objDef.attributes.cx); // x coord of the anchor point (center of the circle)
		cy_num = parseFloat(_objDef.attributes.cy); // y coord of the anchor point (center of the circle)
		r_num = parseFloat(_objDef.attributes.r); // radius

		cx_num = (isNaN(cx_num)) ? 0 : cx_num; // #IMPLIED value
		cy_num = (isNaN(cy_num)) ? 0 : cy_num; // #IMPLIED value

		if(isNaN(r_num)) {
			// no need to go further
		}
		else{ // ok, args seem to be conform to W3C SVG specs - TODO: for full compliance, should consider %age and so on
			_objThis.lineStyle(stroke.width, stroke.color, stroke.alpha);
			_objThis.beginFill(fill.color, fill.alpha);
			var tan_Pi_num:Number;
			var sin_Pi_num:Number;
			tan_Pi_num = Math.tan(Math.PI/8);
			sin_Pi_num = Math.sin(Math.PI/4);

			_objThis.moveTo(cx_num + r_num, cy_num);
			_objThis.curveTo(r_num + cx_num, tan_Pi_num * r_num + cy_num, sin_Pi_num * r_num + cx_num, sin_Pi_num * r_num + cy_num);
			_objThis.curveTo(tan_Pi_num * r_num + cx_num, r_num + cy_num, cx_num, r_num + cy_num);
			_objThis.curveTo(-1 * tan_Pi_num * r_num + cx_num, r_num + cy_num, -1 * sin_Pi_num * r_num + cx_num, sin_Pi_num * r_num + cy_num);
			_objThis.curveTo(-1 * r_num + cx_num, tan_Pi_num * r_num + cy_num, -1 * r_num + cx_num, cy_num);
			_objThis.curveTo(-1 * r_num + cx_num, -1 * tan_Pi_num * r_num + cy_num, -1 * sin_Pi_num * r_num + cx_num, -1 * sin_Pi_num * r_num + cy_num);
			_objThis.curveTo(-1 * tan_Pi_num * r_num + cx_num, -1 * r_num + cy_num, cx_num, -1 * r_num + cy_num);
			_objThis.curveTo(tan_Pi_num * r_num + cx_num, -1 * r_num + cy_num, sin_Pi_num * r_num + cx_num, -1 * sin_Pi_num * r_num + cy_num);
			_objThis.curveTo(r_num + cx_num, -1 * tan_Pi_num * r_num + cy_num, r_num + cx_num, cy_num);

			_objThis.endFill();
		}
		return _objThis;
	}
	function destroy(){
		trace("SVGcircle (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}