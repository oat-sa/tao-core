import com.eXULiS.SVG.SVGelement;
import flash.geom.Matrix;

class com.eXULiS.SVG.SVGmarker extends SVGelement {
	private var _objThis:MovieClip;
	private var refX_num:Number;
	private var refY_num:Number;
	private var markerUnits_str:String;
	private var orient_str:String;
	private var markerWidth_num:Number;
	private var markerHeight_num:Number;

	function SVGmarker(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGmarker (create): marker (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		var a_num:Number;
		var b_num:Number;
		var c_num:Number;
		var d_num:Number;
		var tx_num:Number;
		var ty_num:Number;
		var aTransformationMatrix:Matrix;
		var baseMatrix:Matrix;
		var degrees:Number;
		var radians:Number;

		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);
// refX='10' refY='5' markerUnits='strokeWidth' orient='auto' markerWidth='8' markerHeight='6' viewBox='0 0 10 10'
		refX_num = parseFloat(_objDef.attributes.refX); // x coord of the first reference point (pivot of the marker)
		refY_num = parseFloat(_objDef.attributes.refY); // y coord of the first reference point (pivot of the marker)
		markerWidth_num = parseFloat(_objDef.attributes.markerWidth1); // width constraint on the marker
		markerHeight_num = parseFloat(_objDef.attributes.markerHeight); // height constraint on the marker
		markerUnits_str = _objDef.attributes.markerUnits;
		orient_str = _objDef.attributes.orient;
/* this will be moved to SVGelement
		// viewBox mechanic
	private var viewBox_str:String;
		var viewBox_array:Array;
		var viewBoxMinX_num:Number;
		var viewBoxMinY_num:Number;
		var viewBoxWidth_num:Number;
		var viewBoxHeight_num:Number;
		viewBox_str = _objDef.attributes.viewBox;
		if(viewBox_str != undefined){
			viewBox_array = new Array();
			viewBox_array = viewBox_str.split(" ");
			if(viewBox_array.length == 4){
				viewBoxMinX_num = viewBox_array[0];
				viewBoxMinY_num = viewBox_array[1];
				viewBoxWidth_num = viewBox_array[2];
				viewBoxHeight_num = viewBox_array[3];
			}
		}
		if(!(isNaN(markerWidth_num))){
			a_num = parseFloat(markerWidth_num) / viewBoxWidth_num;
			b_num = 0;
			c_num = 0;
			d_num = (actionArgs_array[1] == undefined) ? 1 : parseFloat(actionArgs_array[1]);
			tx_num = 0;
			ty_num = 0;
			aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, tx_num, ty_num);
			baseMatrix = __obj.transform.matrix;
			baseMatrix.concat(aTransformationMatrix);
			__obj.transform.matrix = baseMatrix;
		}
*/
		// "rotate"
		a_num = 1;
		b_num = 0;
		c_num = 0;
		d_num = 1;
		tx_num = (isNaN(refX_num)) ? 0 : refX_num;
		ty_num = (isNaN(refY_num)) ? 0 : refY_num;
		aTransformationMatrix = new Matrix(a_num, b_num, c_num, d_num, -1 * tx_num, -1 * ty_num);
		if(orient_str == "auto"){
			radians = parseFloat(_objParent._exulis._markerAngle);
		}
		else{
			degrees = parseFloat(orient_str);
			radians = (degrees/180)*Math.PI;
		}
		aTransformationMatrix.rotate(radians);
//		aTransformationMatrix.translate(tx_num, ty_num);
		baseMatrix = _objThis.transform.matrix;
		baseMatrix.concat(aTransformationMatrix);
		_objThis.transform.matrix = baseMatrix;

		return _objThis;
	}
	function destroy(){
		trace("SVGmarker (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}