import com.eXULiS.SVG.SVGelement;
import com.eXULiS.SVG.SVGdefs2Flash;

class com.eXULiS.SVG.SVGline extends SVGelement {
	private var _objThis:MovieClip;
	private var x1_num:Number;
	private var y1_num:Number;
	private var x2_num:Number;
	private var y2_num:Number;
	private var marker_start_mc:MovieClip;
	private var marker_end_mc:MovieClip;

	function SVGline(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGline (create): line (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);

		x1_num = parseFloat(_objDef.attributes.x1); // x coord of the first anchor point (start of the line)
		y1_num = parseFloat(_objDef.attributes.y1); // y coord of the first anchor point (start of the line)
		x2_num = parseFloat(_objDef.attributes.x2); // x coord of the second anchor point (end of the line)
		y2_num = parseFloat(_objDef.attributes.y2); // y coord of the second anchor point (end of the line)

		x1_num = (isNaN(x1_num)) ? 0 : x1_num; // #IMPLIED value
		y1_num = (isNaN(y1_num)) ? 0 : y1_num; // #IMPLIED value

		if((isNaN(x2_num)) || (isNaN(y2_num))) {
			// no need to go further
		}
		else{ // ok, args seem to be conform to W3C SVG specs - TODO: for full compliance, should consider %age and so on
			_objThis.lineStyle(stroke.width, stroke.color, stroke.alpha);
// Because 'line' elements are single lines and thus are geometrically one-dimensional, they have no interior; thus, 'line' elements are never filled
//			_objThis.beginFill(fill.color, fill.alpha);

			_objThis.moveTo(x1_num, y1_num);
			_objThis.lineTo(x2_num, y2_num);

//			_objThis.endFill();

			if(_objDef.attributes["marker-end"] != undefined){
				attachMarker("marker-end",marker_end_mc);
			}
			if(_objDef.attributes["marker-start"] != undefined){
				attachMarker("marker-start",marker_start_mc);
			}
		}

		return _objThis;
	}
	private function attachMarker(markerType_str,marker_ref):Void{
		var markerAngle_num:Number;
		var markerValue_str:String;
		var markerId_str:String;

		markerValue_str = toolbox.extractString(_objDef.attributes[markerType_str],"url(#",")",0,false);
		if((markerValue_str != undefined) && (markerValue_str != "")){
			trace("SVGline (attachMarker): " + id + "_marker_x name = " + markerValue_str);
			var markerRepository_ref:MovieClip = _objThis._exulis._objDefsRepository.retrieve(markerValue_str);
			trace("SVGline (attachMarker): " + id + "_marker_x Ref = " + markerRepository_ref);
			if(markerRepository_ref != undefined){
				if(markerType_str == "marker-end"){
					markerId_str = id + "_marker_end";
					markerAngle_num = Math.acos((x2_num - x1_num) / Math.sqrt(Math.pow((x2_num - x1_num),2) + Math.pow((y2_num - y1_num),2)));
				}
				else{
					markerId_str = id + "_marker_start";
					markerAngle_num = Math.acos((x1_num - x2_num) / Math.sqrt(Math.pow((x1_num - x2_num),2) + Math.pow((y1_num - y2_num),2)));
				}
				marker_ref = _objThis.createEmptyMovieClip(markerId_str, _objThis._childNextDepth);
				var _exulis:Object = new Object();
				_exulis.id = markerId_str;
				_exulis._markerRef = markerValue_str;
				_exulis._objParent = _objThis;
				_exulis._markerAngle = markerAngle_num;
				marker_ref._exulis = _exulis;
				marker_ref._childNextDepth = 1;
				marker_ref._type = "g";

				var vTmpObj:Object;
				vTmpObj = new SVGdefs2Flash(marker_ref, _objThis._targetExecutionLayer);
				var vTmpCanvasDefObj:Object;
				vTmpCanvasDefObj = vTmpObj.parseXML(markerRepository_ref._exulis._objDef);
				marker_ref._x = x2_num;
				marker_ref._y = y2_num;

				marker_ref._childNextDepth++;
			}
		}
	}
	function destroy(){
		trace("SVGline (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}