import com.eXULiS.SVG.SVGelement;
import com.eXULiS.SVG.SVGdefs2Flash;

class com.eXULiS.SVG.SVGpolyline extends SVGelement {
	private var _objThis:MovieClip;
	private var points_array:Array;
	private var marker_start_mc:MovieClip;
	private var marker_end_mc:MovieClip;

	function SVGpolyline(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGpolyline (create): polyline (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);

		var points_str:String;
		var evaluatedString_str:String;
		var actionArgs_array:Array;
		var expectedNumOfArgs:Number;
		var moduloResult:Number;
		var divResult:Number;
		var deltaCpt:Number;
		var x_num:Number;
		var y_num:Number;
		var secondAnchor_x_num:Number;
		var secondAnchor_y_num:Number;
		var beforeLastAnchor_x_num:Number;
		var beforeLastAnchor_y_num:Number;
		var firstAnchor_x_num:Number;
		var firstAnchor_y_num:Number;
		var lastAnchor_x_num:Number;
		var lastAnchor_y_num:Number;
		var startMarkerAngle_num:Number;
		var endMarkerAngle_num:Number;
		var objDefsRepository_obj:Object;
		var urlPos_num:Number;
		var urlValue_str:String;
		var markerStartValue_str:String;
		var markerEndValue_str:String;
		var marker_ref:Object;
		var startMarker_ref:Object;
		var endMarker_ref:Object;

		points_str = _objDef.attributes.points; // collection of pairs of coords (x,y)

		if(points_str == undefined) {
			// no need to go further
		}
		else{
			evaluatedString_str = toolbox.trimString(points_str,true,true);
			evaluatedString_str = toolbox.replaceString(evaluatedString_str," ",",");
			evaluatedString_str = toolbox.replaceString(evaluatedString_str,"-",",-");
			evaluatedString_str = toolbox.cleanString(evaluatedString_str,true,true,true,true);
			while(evaluatedString_str != toolbox.replaceString(evaluatedString_str,",,",",")){
				evaluatedString_str = toolbox.replaceString(evaluatedString_str,",,",",");
			}
			actionArgs_array = new Array();
			actionArgs_array = evaluatedString_str.split(",");
			expectedNumOfArgs = 2;
			moduloResult = actionArgs_array.length % expectedNumOfArgs;
			if(moduloResult == 0){
				divResult = actionArgs_array.length / expectedNumOfArgs;
				_objThis.lineStyle(stroke.width, stroke.color, stroke.alpha);
				_objThis.beginFill(fill.color, fill.alpha);
				for(var vCpt=0; vCpt < divResult; vCpt++){
					deltaCpt = vCpt * expectedNumOfArgs;
					x_num = parseFloat(actionArgs_array[0 + deltaCpt]);
					y_num = parseFloat(actionArgs_array[1 + deltaCpt]);
					if(vCpt == 0){ // case of a Move action
						_objThis.moveTo(x_num, y_num);
						firstAnchor_x_num = x_num;
						firstAnchor_y_num = y_num;
					}
					else{ // subsequent coords are Line actions
						_objThis.lineTo(x_num, y_num);
					}
					if(vCpt == 1){ // for start marker - angular angle
						secondAnchor_x_num = x_num;
						secondAnchor_y_num = y_num;
					}
					if(vCpt == (divResult - 2)){ // for end marker - angular angle
						beforeLastAnchor_x_num = x_num;
						beforeLastAnchor_y_num = y_num;
					}
					if(vCpt == (divResult - 1)){ // for end marker - angular angle
						lastAnchor_x_num = x_num;
						lastAnchor_y_num = y_num;
					}
				}
				_objThis.lineStyle(stroke.width, stroke.color, 0);
				_objThis.lineTo(firstAnchor_x_num, firstAnchor_y_num);
				_objThis.lineStyle(stroke.width, stroke.color, stroke.alpha);
				_objThis.endFill();
				if(divResult > 1){
/*
					markerEndValue_str = _objDef.attributes["marker-end"]
					markerStartValue_str = _objDef.attributes["marker-start"]
					objDefsRepository_obj = _objThis._exulis._objDefsRepository;
					if(markerEndValue_str != undefined){
						if((beforeLastAnchor_x_num != lastAnchor_x_num) || (beforeLastAnchor_y_num != lastAnchor_y_num)){
							endMarkerAngle_num = Math.acos((lastAnchor_x_num - beforeLastAnchor_x_num) / Math.sqrt(Math.pow((lastAnchor_x_num - beforeLastAnchor_x_num),2) + Math.pow((lastAnchor_y_num - beforeLastAnchor_y_num),2)));
							trace("SVGpolyline: angle for end-marker = " + endMarkerAngle_num);
							urlPos_num = markerEndValue_str.indexOf("url(#");
							if(urlPos_num != -1){
								markerEndValue_str = toolbox.extractString(markerEndValue_str,"url(#",")",0,false);
								marker_ref = objDefsRepository_obj.retrieve(markerEndValue_str);
								if(marker_ref != undefined){
									trace("SVGpolyline: a marker ref is found " + marker_ref);
									endMarker_ref = _objThis.createEmptyMovieClip(_objThis._exulis.id + "_endMarker",_objThis._childNextDepth);
									_objThis[_objThis._exulis.id + "_endMarker"] = marker_ref.duplicateMovieClip(_objThis._exulis.id + "_endMarker",marker_ref._exulis._objParent._childNextDepth);
									_objThis._childNextDepth++ ;
								}
							}
						}
						else{
							trace("SVGpolyline: end-marker discarded because no discrimination in points");
						}
					}
					if(markerStartValue_str != undefined){
						if((firstAnchor_x_num != secondAnchor_x_num) || (firstAnchor_y_num != secondAnchor_y_num)){
							startMarkerAngle_num = Math.acos((firstAnchor_x_num - secondAnchor_x_num) / Math.sqrt(Math.pow((firstAnchor_x_num - secondAnchor_x_num),2) + Math.pow((firstAnchor_y_num - secondAnchor_y_num),2)));
							trace("SVGpolyline: angle for start-marker = " + startMarkerAngle_num);
						}
						else{
							trace("SVGpolyline: start-marker discarded because no discrimination in points");
						}
					}
					_objThis._exulis._firstAnchorX = firstAnchor_x_num;
					_objThis._exulis._firstAnchorY = firstAnchor_y_num;
					_objThis._exulis._lastAnchorX = lastAnchor_x_num;
					_objThis._exulis._lastAnchorY = lastAnchor_y_num;
					_objThis._exulis._startMarkerAngle = startMarkerAngle_num;
					_objThis._exulis._endMarkerAngle = endMarkerAngle_num;
*/
					if(_objDef.attributes["marker-end"] != undefined){
						markerEndValue_str = toolbox.extractString(_objDef.attributes["marker-end"],"url(#",")",0,false);
						if((markerEndValue_str != undefined) && (markerEndValue_str != "")){
							trace("SVGpolyline (marker): " + id + "_marker_end name = " + markerEndValue_str);
							var marker_end_mc_ref:MovieClip = _objThis._exulis._objDefsRepository.retrieve(markerEndValue_str);
							trace("SVGpolyline (marker): " + id + "_marker_end Ref = " + marker_end_mc_ref);
							if(marker_end_mc_ref != undefined){
								if((beforeLastAnchor_x_num != lastAnchor_x_num) || (beforeLastAnchor_y_num != lastAnchor_y_num)){
									endMarkerAngle_num = Math.acos((lastAnchor_x_num - beforeLastAnchor_x_num) / Math.sqrt(Math.pow((lastAnchor_x_num - beforeLastAnchor_x_num),2) + Math.pow((lastAnchor_y_num - beforeLastAnchor_y_num),2)));
									marker_end_mc = _objThis.createEmptyMovieClip(id + "_marker_end", _objThis._childNextDepth);
									var _exulis:Object = new Object();
									_exulis.id = id + "_marker_end";
									_exulis._markerRef = markerEndValue_str;
									_exulis._objParent = _objThis;
									_exulis._markerAngle = endMarkerAngle_num;
									marker_end_mc._exulis = _exulis;
									marker_end_mc._childNextDepth = 1;
									marker_end_mc._type = "g";

									var vTmpObj:Object;
									vTmpObj = new SVGdefs2Flash(marker_end_mc, _objThis._targetExecutionLayer);
									var vTmpCanvasDefObj:Object;
									vTmpCanvasDefObj = vTmpObj.parseXML(marker_end_mc_ref._exulis._objDef);
									marker_end_mc._x = lastAnchor_x_num;
									marker_end_mc._y = lastAnchor_y_num;

									marker_end_mc._childNextDepth++;
								}
							}
						}
					}

				}
			}
			else{
				trace("SVGpolyline: number of args should not be odd number");
			}
		}
		return _objThis;
	}
	function destroy(){
		trace("SVGpolyline (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}