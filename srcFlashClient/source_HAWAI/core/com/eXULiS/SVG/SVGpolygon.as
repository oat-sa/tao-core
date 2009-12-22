import com.eXULiS.SVG.SVGelement;

class com.eXULiS.SVG.SVGpolygon extends SVGelement {
	private var _objThis:MovieClip;
	private var points_array:Array;

	function SVGpolygon(objParent,objDef:XMLNode) {
		super(objParent,objDef);
	}
	function create(){
		trace("SVGpolygon (create): polygon (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
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
		var firstAnchor_x_num:Number;
		var firstAnchor_y_num:Number;

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
					}
					else{ // subsequent coords are Line actions
						_objThis.lineTo(x_num, y_num);
					}
				}
				_objThis.endFill();
			}
			else{
				trace("SVGpolygon: number of args should not be odd number");
			}
		}
		if(visibility == "hidden"){
			_objThis._visible = false;
		}

		return _objThis;
	}
	function destroy(){
		trace("SVGpolygon (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}