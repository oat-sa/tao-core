import flash.geom.Point;
import flash.geom.Matrix;
import com.eXULiS.SVG.SVGelement;
import com.eXULiS.SVG.SVGdefs2Flash;
import com.eXULiS.lib.DashedLine;

class com.eXULiS.SVG.SVGpath extends SVGelement {
	private var _objThis:MovieClip;
	private var drawPoints:Array;
	private var marker_start_mc:MovieClip;
	private var marker_end_mc:MovieClip;
	private var secondAnchor_x_num:Number;
	private var secondAnchor_y_num:Number;
	private var beforeLastAnchor_x_num:Number;
	private var beforeLastAnchor_y_num:Number;
	private var firstAnchor_x_num:Number;
	private var firstAnchor_y_num:Number;
	private var lastAnchor_x_num:Number;
	private var lastAnchor_y_num:Number;

	function SVGpath(objParent,objDef:XMLNode) {
		super(objParent,objDef);
		drawPoints = new Array();
	}
	function create(){
		trace("SVGpath (create): path (" + id + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		_objThis = super.create(_objThis,this,1);

		var evaluatedString_str:String;
		var actionPos_num:Number;
		var currentAction_str:String;
		var aCommand:Object;
		var currentElement_obj:Object;
		var nextElement_obj:Object;
		var actionLength_num:Number;
		var pathActions_array:Array;
		var evaluatedAction_str:String;

		var actionArgs_array:Array;
		var actionArgs_str:String;
		var degrees:Number;
		var radians:Number;
		var expectedNumOfArgs:Number;
		var moduloResult:Number;
		var divResult:Number;
		var deltaCpt:Number;
		var firstAnchor:Point;
		var lastAnchor:Point;
		var lastControl1:Point;
		var lastControl2:Point;
		var p1:Point; // Anchor 1
		var	p2:Point; // Anchor 2
		var c1:Point; // Control 1 - for Bézier Curves
		var c2:Point; // Control 2 - for Bézier Curves
		var rx_num:Number; // x radius of an Arc definition
		var ry_num:Number; // y radius of an Arc definition
		var x_axis_rotation_num:Number; // x-axis-rotation of an Arc definition
		var large_arc_flag_num:Number; // large-arc-flag flag of an Arc definition
		var sweep_flag_num:Number; // sweep flag of an Arc definition
		var currentPointDef_obj:Object;
		var closedPath_bool:Boolean;

		closedPath_bool = false;
		lastAnchor = new Point();
		lastControl1 = new Point();
		lastControl2 = new Point();
		p1 = new Point();
		p2 = new Point();
		c1 = new Point();
		c2 = new Point();

		if(_objDef.attributes.d != undefined) {
			actionPos_num = 0;
			pathActions_array = new Array();
			evaluatedString_str = _objDef.attributes.d;
			evaluatedString_str = toolbox.replaceString(evaluatedString_str," ",",");
			evaluatedString_str = toolbox.replaceString(evaluatedString_str,"-",",-");
			evaluatedString_str = toolbox.cleanString(evaluatedString_str,true,true,true,true);
			while(evaluatedString_str != toolbox.replaceString(evaluatedString_str,",,",",")){
				evaluatedString_str = toolbox.replaceString(evaluatedString_str,",,",",");
			}
			evaluatedString_str = (evaluatedString_str.indexOf(",a,") != -1) ? toolbox.replaceString(evaluatedString_str,",a,","a") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("a,") != -1) ? toolbox.replaceString(evaluatedString_str,"a,","a") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",a") != -1) ? toolbox.replaceString(evaluatedString_str,",a","a") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",A,") != -1) ? toolbox.replaceString(evaluatedString_str,",A,","A") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("A,") != -1) ? toolbox.replaceString(evaluatedString_str,"A,","A") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",A") != -1) ? toolbox.replaceString(evaluatedString_str,",A","A") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",c,") != -1) ? toolbox.replaceString(evaluatedString_str,",c,","c") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("c,") != -1) ? toolbox.replaceString(evaluatedString_str,"c,","c") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",c") != -1) ? toolbox.replaceString(evaluatedString_str,",c","c") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",C,") != -1) ? toolbox.replaceString(evaluatedString_str,",C,","C") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("C,") != -1) ? toolbox.replaceString(evaluatedString_str,"C,","C") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",C") != -1) ? toolbox.replaceString(evaluatedString_str,",C","C") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",h,") != -1) ? toolbox.replaceString(evaluatedString_str,",h,","h") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("h,") != -1) ? toolbox.replaceString(evaluatedString_str,"h,","h") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",h") != -1) ? toolbox.replaceString(evaluatedString_str,",h","h") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",H,") != -1) ? toolbox.replaceString(evaluatedString_str,",H,","H") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("H,") != -1) ? toolbox.replaceString(evaluatedString_str,"H,","H") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",H") != -1) ? toolbox.replaceString(evaluatedString_str,",H","H") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",l,") != -1) ? toolbox.replaceString(evaluatedString_str,",l,","l") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("l,") != -1) ? toolbox.replaceString(evaluatedString_str,"l,","l") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",l") != -1) ? toolbox.replaceString(evaluatedString_str,",l","l") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",L,") != -1) ? toolbox.replaceString(evaluatedString_str,",L,","L") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("L,") != -1) ? toolbox.replaceString(evaluatedString_str,"L,","L") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",L") != -1) ? toolbox.replaceString(evaluatedString_str,",L","L") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",m,") != -1) ? toolbox.replaceString(evaluatedString_str,",m,","m") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("m,") != -1) ? toolbox.replaceString(evaluatedString_str,"m,","m") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",m") != -1) ? toolbox.replaceString(evaluatedString_str,",m","m") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",M,") != -1) ? toolbox.replaceString(evaluatedString_str,",M,","M") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("M,") != -1) ? toolbox.replaceString(evaluatedString_str,"M,","M") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",M") != -1) ? toolbox.replaceString(evaluatedString_str,",M","M") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",q,") != -1) ? toolbox.replaceString(evaluatedString_str,",q,","q") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("q,") != -1) ? toolbox.replaceString(evaluatedString_str,"q,","q") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",q") != -1) ? toolbox.replaceString(evaluatedString_str,",q","q") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",Q,") != -1) ? toolbox.replaceString(evaluatedString_str,",Q,","Q") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("Q,") != -1) ? toolbox.replaceString(evaluatedString_str,"Q,","Q") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",Q") != -1) ? toolbox.replaceString(evaluatedString_str,",Q","Q") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",s,") != -1) ? toolbox.replaceString(evaluatedString_str,",s,","s") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("s,") != -1) ? toolbox.replaceString(evaluatedString_str,"s,","s") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",s") != -1) ? toolbox.replaceString(evaluatedString_str,",s","s") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",S,") != -1) ? toolbox.replaceString(evaluatedString_str,",S,","S") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("S,") != -1) ? toolbox.replaceString(evaluatedString_str,"S,","S") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",S") != -1) ? toolbox.replaceString(evaluatedString_str,",S","S") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",t,") != -1) ? toolbox.replaceString(evaluatedString_str,",t,","t") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("t,") != -1) ? toolbox.replaceString(evaluatedString_str,"t,","t") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",t") != -1) ? toolbox.replaceString(evaluatedString_str,",t","t") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",T,") != -1) ? toolbox.replaceString(evaluatedString_str,",T,","T") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("T,") != -1) ? toolbox.replaceString(evaluatedString_str,"T,","T") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",T") != -1) ? toolbox.replaceString(evaluatedString_str,",T","T") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf(",v,") != -1) ? toolbox.replaceString(evaluatedString_str,",v,","v") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("v,") != -1) ? toolbox.replaceString(evaluatedString_str,"v,","v") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",v") != -1) ? toolbox.replaceString(evaluatedString_str,",v","v") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",V,") != -1) ? toolbox.replaceString(evaluatedString_str,",V,","V") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("V,") != -1) ? toolbox.replaceString(evaluatedString_str,"V,","V") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",V") != -1) ? toolbox.replaceString(evaluatedString_str,",V","V") : evaluatedString_str;

			evaluatedString_str = (evaluatedString_str.indexOf("z") != -1) ? toolbox.replaceString(evaluatedString_str,"z","Z") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",Z,") != -1) ? toolbox.replaceString(evaluatedString_str,",Z,","Z") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf("Z,") != -1) ? toolbox.replaceString(evaluatedString_str,"Z,","Z") : evaluatedString_str;
			evaluatedString_str = (evaluatedString_str.indexOf(",Z") != -1) ? toolbox.replaceString(evaluatedString_str,",Z","Z") : evaluatedString_str;

			currentAction_str = "a";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "A";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "c";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "C";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "h";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "H";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "l";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "L";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "m";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "M";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "q";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "Q";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "s";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "S";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "t";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "T";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "v";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "V";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
			currentAction_str = "Z";
			actionPos_num = evaluatedString_str.indexOf(currentAction_str);
			while(actionPos_num != -1){
				aCommand = {action:currentAction_str, pos:actionPos_num};
				pathActions_array.push(aCommand);
				actionPos_num = evaluatedString_str.indexOf(currentAction_str, actionPos_num + 1);
			}
		}

		if(pathActions_array.length > 0){
			pathActions_array.sortOn(["pos"],Array.NUMERIC);
			while(pathActions_array.length > 0){
				actionArgs_array = new Array();
				currentElement_obj = pathActions_array.shift();
				if(pathActions_array.length > 0){
					var nextElement_obj:Object;
					nextElement_obj = pathActions_array[0];
					actionLength_num = nextElement_obj.pos - currentElement_obj.pos;
					evaluatedAction_str = evaluatedString_str.substr(currentElement_obj.pos, actionLength_num);
				}
				else{
					evaluatedAction_str = evaluatedString_str.substr(currentElement_obj.pos);
				}
				actionArgs_str = evaluatedAction_str.substr(1);
//				trace("Action is " + currentElement_obj.action + " with " + actionArgs_str);
				actionArgs_array = actionArgs_str.split(",");
				switch(currentElement_obj.action){
					case "a":{ // Elliptical Arc - relative coords
						expectedNumOfArgs = 7;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Elliptical Arc drawn from the current point to (x, y).
								// Size and orientation of the ellipse are defined by two radii (rx, ry)
								// and x-axis-rotation indicates ellipse rotation relative to current coord. system.
								// Center (cx, cy) of ellipse is imposed by the other parameters.
								// large-arc-flag and sweep-flag contribute to the automatic calculations
								// and help determine how the arc is drawn.
								// actionArgs_array[0] = rx
								// actionArgs_array[1] = ry
								// actionArgs_array[2] = x-axis-rotation
								// actionArgs_array[3] = large-arc-flag
								// actionArgs_array[4] = sweep-flag
								// actionArgs_array[5] = end point x
								// actionArgs_array[6] = end point y
								p1 = lastAnchor.clone();
								rx_num = parseFloat(actionArgs_array[0 + deltaCpt]);
								ry_num = parseFloat(actionArgs_array[1 + deltaCpt]);
								x_axis_rotation_num = parseFloat(actionArgs_array[2 + deltaCpt]);
								large_arc_flag_num = parseFloat(actionArgs_array[3 + deltaCpt]);
								sweep_flag_num = parseFloat(actionArgs_array[4 + deltaCpt]);
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[5 + deltaCpt]);
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[6 + deltaCpt]);
//								trace("IN ARC, p1: " + p1 + " p2: " + p2);
								drawPoints = convertSVG2Flash(p1.clone(), rx_num, ry_num, x_axis_rotation_num, large_arc_flag_num, sweep_flag_num, p2.clone(), drawPoints)
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl2 = c2.clone(); // last control point
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "A":{ // Elliptical Arc - absolute coords
						expectedNumOfArgs = 7;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "a" case
								p1 = lastAnchor.clone();
								rx_num = parseFloat(actionArgs_array[0 + deltaCpt]);
								ry_num = parseFloat(actionArgs_array[1 + deltaCpt]);
								x_axis_rotation_num = parseFloat(actionArgs_array[2 + deltaCpt]);
								large_arc_flag_num = parseFloat(actionArgs_array[3 + deltaCpt]);
								sweep_flag_num = parseFloat(actionArgs_array[4 + deltaCpt]);
								p2.x = parseFloat(actionArgs_array[5 + deltaCpt]);
								p2.y = parseFloat(actionArgs_array[6 + deltaCpt]);
								drawPoints = convertSVG2Flash(p1.clone(), rx_num, ry_num, x_axis_rotation_num, large_arc_flag_num, sweep_flag_num, p2.clone(), drawPoints)
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl2 = c2.clone(); // last control point
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "c":{ // Cubic Bézier - relative coords
						expectedNumOfArgs = 6;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Cubic Bézier segment is defined by a start point, an end pt, and 2 control pts
								// actionArgs_array[0] = control point 1 x aka c1.x
								// actionArgs_array[1] = control point 1 y aka c1.y
								// actionArgs_array[2] = control point 2 x aka c2.x
								// actionArgs_array[3] = control point 2 y aka c2.y
								// actionArgs_array[4] = end point x aka p2.x
								// actionArgs_array[5] = end point y aka p2.y
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[4 + deltaCpt]);
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[5 + deltaCpt]);
								c1.x = lastAnchor.x + parseFloat(actionArgs_array[0 + deltaCpt]);
								c1.y = lastAnchor.y + parseFloat(actionArgs_array[1 + deltaCpt]);
								c2.x = lastAnchor.x + parseFloat(actionArgs_array[2 + deltaCpt]);
								c2.y = lastAnchor.y + parseFloat(actionArgs_array[3 + deltaCpt]);
								drawPoints = CBez(p1.clone(), p2.clone(), c1.clone(), c2.clone(), drawPoints);
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl2 = c2.clone(); // last control point
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						break;
					}
					case "C":{ // Cubic Bézier Curve - absolute coords
						expectedNumOfArgs = 6;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "c" case
								p1 = lastAnchor.clone();
								p2.x = parseFloat(actionArgs_array[4 + deltaCpt]);
								p2.y = parseFloat(actionArgs_array[5 + deltaCpt]);
								c1.x = parseFloat(actionArgs_array[0 + deltaCpt]);
								c1.y = parseFloat(actionArgs_array[1 + deltaCpt]);
								c2.x = parseFloat(actionArgs_array[2 + deltaCpt]);
								c2.y = parseFloat(actionArgs_array[3 + deltaCpt]);
								drawPoints = CBez(p1.clone(), p2.clone(), c1.clone(), c2.clone(), drawPoints);
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl2 = c2.clone(); // last control point
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						break;
					}
					case "h":{ // Horizontal Line - relative coords
						expectedNumOfArgs = 1;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Horizontal Line runs from current pt (p1) to end pt (p2 where only x changes)
								// actionArgs_array[0] = end point x aka p2.x
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = lastAnchor.y;
								drawPoints.push({p:p2.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor; // not q, Q, t or T -> reset
						lastControl2 = lastAnchor; // not c, C, s or S -> reset
						break;
					}
					case "H":{ // Horizontal Line - absolute coords
						expectedNumOfArgs = 1;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "h" case
								p1 = lastAnchor.clone();
								p2.x = parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = lastAnchor.y;
								drawPoints.push({p:p2.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "l":{ // Line - relative coords
						expectedNumOfArgs = 2;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Line runs from current pt (p1) to end pt (p2)
								// actionArgs_array[0] = end point x aka p2.x
								// actionArgs_array[1] = end point y aka p2.y
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[1 + deltaCpt]);
								drawPoints.push({p:p2.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
//						trace("AFTER LINE, last anchor: " + lastAnchor);
						break;
					}
					case "L":{ // Line - absolute coords
						expectedNumOfArgs = 2;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "l" case
								p1 = lastAnchor.clone();
								p2.x = parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = parseFloat(actionArgs_array[1 + deltaCpt]);
								drawPoints.push({p:p2.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "m":{ // Move - relative coords
						expectedNumOfArgs = 2;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Move goes from current pt (p1) to end pt (p2)
								// actionArgs_array[0] = end point x aka p2.x
								// actionArgs_array[1] = end point y aka p2.y
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[1 + deltaCpt]);
								if(vCpt == 0){ // case of a Move action
									drawPoints.push({s:p2.clone()});
								}
								else{ // subsequent coords are Line actions
									drawPoints.push({p:p2.clone()});
								}
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "M":{ // Move - absolute coords
						expectedNumOfArgs = 2;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "l" case
								p1 = lastAnchor.clone();
								p2.x = parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = parseFloat(actionArgs_array[1 + deltaCpt]);
								if(vCpt == 0){ // case of a Move action
									drawPoints.push({s:p2.clone()});
								}
								else{ // subsequent coords are Line actions
									drawPoints.push({p:p2.clone()});
								}
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "q":{ // Quadratic Bézier Curve - relative coords
						expectedNumOfArgs = 4;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Quadratic Bézier is defined by a start point, an end pt, and 1 control pt
								// actionArgs_array[0] = control point 1 x aka c1.x
								// actionArgs_array[1] = control point 1 y aka c1.y
								// actionArgs_array[2] = end point x aka p2.x
								// actionArgs_array[3] = end point y aka p2.y
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[2 + deltaCpt]);
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[3 + deltaCpt]);
								c1.x = lastAnchor.x + parseFloat(actionArgs_array[0 + deltaCpt]);
								c1.y = lastAnchor.y + parseFloat(actionArgs_array[1 + deltaCpt]);
								drawPoints.push({p:p2.clone(), c:c1.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "Q":{ // Quadratic Bézier Curve - absolute coords
						expectedNumOfArgs = 4;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "q" case
								p1 = lastAnchor.clone();
								p2.x = parseFloat(actionArgs_array[2 + deltaCpt]);
								p2.y = parseFloat(actionArgs_array[3 + deltaCpt]);
								c1.x = parseFloat(actionArgs_array[0 + deltaCpt]);
								c1.y = parseFloat(actionArgs_array[1 + deltaCpt]);
								drawPoints.push({p:p2.clone(), c:c1.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "s":{ // Shorthand/Smooth Cubic Bézier Curve - relative coords
						expectedNumOfArgs = 4;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Cubic Bézier segment is defined by a start point, an end pt, and 2 control pts
								// but here, only the 2nd control pt is given; the 1st is calculated, either
								// - (if last action is c, C, s or S) by reflexion (on current pt) of previous c2, or
								// - (else) by assuming it is equal to the current pt
								// actionArgs_array[0] = control point 2 x aka c2.x
								// actionArgs_array[1] = control point 2 y aka c2.y
								// actionArgs_array[2] = end point x aka p2.x
								// actionArgs_array[3] = end point y aka p2.y
								p1 = lastAnchor;
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[2 + deltaCpt]);
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[3 + deltaCpt]);
								c1.x = lastAnchor.x + (lastAnchor.x - lastControl2.x);
								c1.y = lastAnchor.y + (lastAnchor.y - lastControl2.y);
								c2.x = lastAnchor.x + parseFloat(actionArgs_array[0 + deltaCpt]);
								c2.y = lastAnchor.y + parseFloat(actionArgs_array[1 + deltaCpt]);
								drawPoints = CBez(p1.clone(), p2.clone(), c1.clone(), c2.clone(), drawPoints);
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl2 = c2.clone(); // last control point
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						break;
					}
					case "S":{ // Shorthand/Smooth Cubic Bézier Curve - absolute coords
						expectedNumOfArgs = 4;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "s" case
								p1 = lastAnchor.clone();
								p2.x = parseFloat(actionArgs_array[4 + deltaCpt]);
								p2.y = parseFloat(actionArgs_array[5 + deltaCpt]);
								c1.x = (lastAnchor.x - lastControl2.x);
								c1.y = (lastAnchor.y - lastControl2.y);
								c2.x = parseFloat(actionArgs_array[2 + deltaCpt]);
								c2.y = parseFloat(actionArgs_array[3 + deltaCpt]);
								drawPoints = CBez(p1.clone(), p2.clone(), c1.clone(), c2.clone(), drawPoints);
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl2 = c2.clone(); // last control point
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						break;
					}
					case "t":{ // Shorthand/Smooth Quadratic Bézier Curve - relative coords
						expectedNumOfArgs = 2;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Quadratic Bézier is defined by a start point, an end pt, and 1 control pt
								// but here, the control pt c1 is calculated, either
								// - (if last action is q, Q, t or T) by reflexion (on current pt) of previous c1, or
								// - (else) by assuming it is equal to the current pt
								// actionArgs_array[0] = end point x aka p2.x
								// actionArgs_array[1] = end point y aka p2.y
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x + parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[1 + deltaCpt]);
								c1.x = lastAnchor.x + (lastAnchor.x - lastControl1.x);
								c1.y = lastAnchor.y + (lastAnchor.y - lastControl1.y);
								drawPoints.push({p:p2.clone(), c:c1.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl1 = c1.clone(); // last control point 1
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "T":{ // Shorthand/Smooth Quadratic Bézier Curve - absolute coords
						expectedNumOfArgs = 2;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "t" case
								p1 = lastAnchor.clone();
								p2.x = parseFloat(actionArgs_array[0 + deltaCpt]);
								p2.y = parseFloat(actionArgs_array[1 + deltaCpt]);
								c1.x = (lastAnchor.x - lastControl1.x);
								c1.y = (lastAnchor.y - lastControl1.y);
								drawPoints.push({p:p2.clone(), c:c1.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
								lastControl1 = c1.clone(); // last control point 1
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "v":{ // Vertical Line - relative coords
						expectedNumOfArgs = 1;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// Vertical Line runs from current pt (p1) to end pt (p2 where only y changes)
								// actionArgs_array[0] = end point y aka p2.y
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x;
								p2.y = lastAnchor.y + parseFloat(actionArgs_array[0 + deltaCpt]);
								drawPoints.push({p:p2.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "V":{ // Vertical Line - absolute coords
						expectedNumOfArgs = 1;
						moduloResult = actionArgs_array.length % expectedNumOfArgs;
						if(moduloResult == 0){
							divResult = actionArgs_array.length / expectedNumOfArgs;
							for(var vCpt=0; vCpt < divResult; vCpt++){
								deltaCpt = vCpt * expectedNumOfArgs;
								// more info in "v" case
								p1 = lastAnchor.clone();
								p2.x = lastAnchor.x;
								p2.y = parseFloat(actionArgs_array[0 + deltaCpt]);
								drawPoints.push({p:p2.clone()});
								if(firstAnchor == undefined){
									firstAnchor = new Point();
									firstAnchor = p2.clone();
								}
								lastAnchor = p2.clone(); // last anchor
							}
						}
						else{
							trace("SVGpath: requested draw operation failed - " + evaluatedAction_str);
						}
						lastControl1 = lastAnchor.clone(); // not q, Q, t or T -> reset
						lastControl2 = lastAnchor.clone(); // not c, C, s or S -> reset
						break;
					}
					case "z":{ // Closing path with straight line to initial point
						// nothing to do 'cause the case is solved in Z
					}
					case "Z":{ // Closing path with straight line to initial point
						if(firstAnchor == undefined){
							firstAnchor = new Point();
							firstAnchor = p2.clone();
						}
						drawPoints.push({p:firstAnchor.clone()});
						closedPath_bool = true;
						break;
					}
					default:{
						trace("SVGpath: requested transform operation unknown - " + evaluatedAction_str);
					}
				}
			}
		}
//_objDef.attributes["stroke-dasharray"] = undefined;
		if((drawPoints.length > 1) && (_objDef.attributes["marker-end"] != undefined)){
/*
			trace("SVGpath (marker): id = " + id + " -----------------");
			for(var vcptdump=0;vcptdump<drawPoints.length;vcptdump++){
				if(drawPoints[vcptdump].s != undefined){
					trace("SVGpath (marker): S - x:" + drawPoints[vcptdump].s.x + " y:" + drawPoints[vcptdump].s.y);
				}
				else{
					if(drawPoints[vcptdump].c != undefined){
						trace("SVGpath (marker): C - x:" + drawPoints[vcptdump].p.x + " y:" + drawPoints[vcptdump].p.y + " - cx:" + drawPoints[vcptdump].c.x + " cy:" + drawPoints[vcptdump].c.y);
					}
					else{
						if(drawPoints[vcptdump].p != undefined){
							trace("SVGpath (marker): P - x:" + drawPoints[vcptdump].p.x + " y:" + drawPoints[vcptdump].p.y);
						}
						else{
							trace("SVGpath (marker): problem!");
						}
					}
				}
			}
			trace("SVGpath (marker): ---------------------------------");
*/
			if(drawPoints[0].s != undefined){
				firstAnchor_x_num = drawPoints[0].s.x;
				firstAnchor_y_num = drawPoints[0].s.y;
			}
			secondAnchor_x_num = drawPoints[1].p.x;;
			secondAnchor_y_num = drawPoints[1].p.y;;
			if(drawPoints[0].c != undefined){
				firstAnchor_x_num = drawPoints[0].p.x;
				firstAnchor_y_num = drawPoints[0].p.y;
				secondAnchor_x_num = drawPoints[0].c.x;;
				secondAnchor_y_num = drawPoints[0].c.y;;
			}
			if(drawPoints[drawPoints.length - 2].p != undefined){
				beforeLastAnchor_x_num = drawPoints[drawPoints.length - 2].p.x;
				beforeLastAnchor_y_num = drawPoints[drawPoints.length - 2].p.y;
			}
			else{
				if(drawPoints[drawPoints.length - 2].s != undefined){
					beforeLastAnchor_x_num = drawPoints[drawPoints.length - 2].s.x;
					beforeLastAnchor_y_num = drawPoints[drawPoints.length - 2].s.y;
				}
			}
			if(drawPoints[drawPoints.length - 1].c != undefined){
				beforeLastAnchor_x_num = drawPoints[drawPoints.length - 1].c.x;;
				beforeLastAnchor_y_num = drawPoints[drawPoints.length - 1].c.y;;
				lastAnchor_x_num = drawPoints[drawPoints.length - 1].p.x;;
				lastAnchor_y_num = drawPoints[drawPoints.length - 1].p.y;;
			}
			else{
				if(drawPoints[drawPoints.length - 1].p != undefined){
					lastAnchor_x_num = drawPoints[drawPoints.length - 1].p.x;;
					lastAnchor_y_num = drawPoints[drawPoints.length - 1].p.y;;
				}
			}
		}
		if((_objDef.attributes["stroke-dasharray"] != undefined) && (_objDef.attributes["stroke-dasharray"] != "none")){
// TODO : adapt the DashedLine class to support variants of attributes stroke-dasharray and co
			var myDashedDrawing:DashedLine = new DashedLine(_objThis, 6, 6);
			myDashedDrawing.lineStyle(stroke.width, stroke.color, stroke.alpha);
			myDashedDrawing.beginFill(fill.color, fill.alpha);	
			while(drawPoints.length > 0){
				currentPointDef_obj = drawPoints.shift();
				if(currentPointDef_obj.c == undefined){
					if(currentPointDef_obj.p == undefined){
						if(currentPointDef_obj.s == undefined){
							trace("SVGpath: it seems we have got a problem, Sir!");
						}
						else{
//							trace("shape_mc.moveTo(" + currentPointDef_obj.s.x + ", " + currentPointDef_obj.s.y + ");");
							myDashedDrawing.moveTo(currentPointDef_obj.s.x, currentPointDef_obj.s.y);
						}
					}
					else{
//						trace("shape_mc.lineTo(" + currentPointDef_obj.p.x + ", " + currentPointDef_obj.p.y + ");");
						myDashedDrawing.lineTo(currentPointDef_obj.p.x, currentPointDef_obj.p.y);
					}
				}
				else{
//					trace("shape_mc.curveTo(" + currentPointDef_obj.c.x + ", " + currentPointDef_obj.c.y + ", " + currentPointDef_obj.p.x + ", " + currentPointDef_obj.p.y + ");");
					myDashedDrawing.curveTo(currentPointDef_obj.c.x, currentPointDef_obj.c.y, currentPointDef_obj.p.x, currentPointDef_obj.p.y);
				}
			}
			if((drawPoints.length == 0) && (!closedPath_bool)){
				myDashedDrawing.lineStyle(stroke.width, stroke.color, 0);
			}
			myDashedDrawing.endFill();
		}
		else{
			_objThis.lineStyle(stroke.width, stroke.color, stroke.alpha);
			_objThis.beginFill(fill.color, fill.alpha);
			while(drawPoints.length > 0){
				currentPointDef_obj = drawPoints.shift();
				if(currentPointDef_obj.c == undefined){
					if(currentPointDef_obj.p == undefined){
						if(currentPointDef_obj.s == undefined){
							trace("SVGpath: it seems we have got a problem, Sir!");
						}
						else{
//							trace("shape_mc.moveTo(" + currentPointDef_obj.s.x + ", " + currentPointDef_obj.s.y + ");");
							_objThis.moveTo(currentPointDef_obj.s.x, currentPointDef_obj.s.y);
						}
					}
					else{
//						trace("shape_mc.lineTo(" + currentPointDef_obj.p.x + ", " + currentPointDef_obj.p.y + ");");
						_objThis.lineTo(currentPointDef_obj.p.x, currentPointDef_obj.p.y);
					}
				}
				else{
//					trace("shape_mc.curveTo(" + currentPointDef_obj.c.x + ", " + currentPointDef_obj.c.y + ", " + currentPointDef_obj.p.x + ", " + currentPointDef_obj.p.y + ");");
					_objThis.curveTo(currentPointDef_obj.c.x, currentPointDef_obj.c.y, currentPointDef_obj.p.x, currentPointDef_obj.p.y);
				}
			}
			if((drawPoints.length == 0) && (!closedPath_bool)){
				_objThis.lineStyle(stroke.width, stroke.color, 0);
			}
			_objThis.endFill();
		}
		if(_objDef.attributes["marker-end"] != undefined){
			attachMarker("marker-end",marker_end_mc);
		}
		if(_objDef.attributes["marker-start"] != undefined){
			attachMarker("marker-start",marker_start_mc);
		}
		return _objThis;
	}

// this CBez function transforms a Cubic Bézier Curve in a set of 4 Quadratic Bézier Curves (Flash compliant)
	function CBez (p1:Point, p2:Point, c1:Point, c2:Point, drawPoints) {
//		trace("CBez [p1" + p1 + " - p2" + p2 + " - c1" + c1 + " - c2" + c2 + "]");
		var pA:Point;
		var pA_1:Point;
		var pA_2:Point;
		var pA_3:Point;
		var pB:Point;
		var pC_1:Point;
		var pC_2:Point;
		var pC_3:Point;
		var pC_4:Point;
		pA = new Point();
		pA_1 = new Point();
		pA_2 = new Point();
		pA_3 = new Point();
		pB = new Point();
		pC_1 = new Point();
		pC_2 = new Point();
		pC_3 = new Point();
		pC_4 = new Point();
		var dx:Number;
		var dy:Number;

		// calculates the useful base points
		pA = Point.interpolate(p1, c1, 1/4);
		pB = Point.interpolate(p2, c2, 1/4);

		// computes x and y delta shares of the [p1, p2] segment (1/16 is good approx)
		dx = (p2.x - p1.x)/16;
		dy = (p2.y - p1.y)/16;

		// computes the control point 1
		pC_1 = Point.interpolate(p1, c1, 5/8);

		// computes the control point 2
		pC_2 = Point.interpolate(pA, pB, 5/8);
		pC_2.x -= dx;
		pC_2.y -= dy;

		// computes the control point 3
		pC_3 = Point.interpolate(pB, pA, 5/8);
		pC_3.x += dx;
		pC_3.y += dy;

		// computes the control point 4
		pC_4 = Point.interpolate(p2, c2, 5/8);

		// deduces the 3 anchor points
		pA_1.x = (pC_1.x + pC_2.x)/2;
		pA_1.y = (pC_1.y + pC_2.y)/2;
		pA_2.x = (pA.x + pB.x)/2;
		pA_2.y = (pA.y + pB.y)/2;
		pA_3.x = (pC_3.x + pC_4.x)/2;
		pA_3.y = (pC_3.y + pC_4.y)/2;

		// save the four quadratic subsegments
		drawPoints.push({p:pA_1.clone(), c:pC_1.clone()});
		drawPoints.push({p:pA_2.clone(), c:pC_2.clone()});
		drawPoints.push({p:pA_3.clone(), c:pC_3.clone()});
		drawPoints.push({p:p2.clone(), c:pC_4.clone()});
		return(drawPoints);
	}
/*
// Convert an elliptical arc based around a central point to an elliptical arc defined with SVG format.
// p1 : startpoint of arc
// rx_num : x-radius of ellipse
// ry_num : y-radius of ellipse
// x_axis_rotation_num : x-axis rotation angle in degrees
// large_arc_flag_num : large-arc-flag as defined in SVG specification
// sweep_flag_num : sweep-flag as defined in SVG specification
// p2 : endpoint of arc
// drawPoints contains the pairs of (point, control point) found in STEP B
*/
	function convertSVG2Flash(p1:Point, rx_num:Number, ry_num:Number, x_axis_rotation_num:Number, large_arc_flag_num:Number, sweep_flag_num:Number, p2:Point, drawPoints){
	// STEP A - let's find the center of the ellipse. Output of stap A
	// cx_num : center x coordinate
	// cy_num : center y coordinate
	// rx_num : x-radius of ellipse
	// ry_num : y-radius of ellipse
	// startAngle_num : beginning angle of arc in degrees
	// angle_num : arc extent in degrees
	// x_axis_rotation_num : x-axis rotation angle in degrees
	// returned
		var cx_num:Number;
		var cy_num:Number;
		var startAngle_num:Number;
		var angle_num:Number;

	// Temporary variables
		var v_Distance_x:Number;
		var v_Distance_y:Number;
		var pD_x_num:Number;
		var pD_y_num:Number;
		var rx2_num:Number;
		var ry2_num:Number;
		var pD_x2_num:Number;
		var pD_y2_num:Number;
		var v_sign:Number;
		var v_sq:Number;
		var v_coef:Number;
		var cx_num1:Number;
		var cy_num1:Number;
		var pS:Point;
		var v_p:Number;
		var v_n:Number;
		var v_ux:Number;
		var v_uy:Number;
		var v_vx:Number;
		var v_vy:Number;
		var v_radius_check:Number;

	// Find the point halfway between start and final point
		v_Distance_x = (p1.x - p2.x) / 2;
		v_Distance_y = (p1.y - p2.y) / 2;
	// Convert from degrees to radians
		x_axis_rotation_num %= 360;
		x_axis_rotation_num = x_axis_rotation_num * Math.PI / 180;
	// Compute (x1, y1)
		pD_x_num = Math.cos(x_axis_rotation_num) * v_Distance_x + Math.sin(x_axis_rotation_num) * v_Distance_y;
		pD_y_num = -Math.sin(x_axis_rotation_num) * v_Distance_x + Math.cos(x_axis_rotation_num) * v_Distance_y;
	// Make sure radii are positive
		rx_num = Math.abs(rx_num);
		ry_num = Math.abs(ry_num);
		rx2_num = Math.pow(rx_num, 2);
		ry2_num = Math.pow(ry_num, 2);
		pD_x2_num = Math.pow(pD_x_num, 2);
		pD_y2_num = Math.pow(pD_y_num, 2);
		v_radius_check = (pD_x2_num / rx2_num) + (pD_y2_num / ry2_num);
		if (v_radius_check > 1){
			rx_num *= Math.sqrt(v_radius_check);
			ry_num *= Math.sqrt(v_radius_check);
			rx2_num = Math.pow(rx_num, 2);
			ry2_num = Math.pow(ry_num, 2);
		}
	// Step 2: Compute (cx1, cy1)
		v_sign = (large_arc_flag_num == sweep_flag_num) ? -1 : 1;
		v_sq = ((rx2_num * ry2_num) - (rx2_num * pD_y2_num) - (ry2_num * pD_x2_num)) / ((rx2_num * pD_y2_num) + (ry2_num * pD_x2_num));
		v_sq = (v_sq < 0) ? 0 : v_sq;
		v_coef = (v_sign * Math.sqrt(v_sq));
		cx_num1 = v_coef * ((rx_num * pD_y_num) / ry_num);
		cy_num1 = v_coef * -((ry_num * pD_x_num) / rx_num);
	// Step 3: Compute (cx, cy) from (cx1, cy1)
		pS = new Point();
		pS = Point.interpolate(p1, p2, 1/2);
		cx_num = pS.x + (Math.cos(x_axis_rotation_num) * cx_num1 - Math.sin(x_axis_rotation_num) * cy_num1);
		cy_num = pS.y + (Math.sin(x_axis_rotation_num) * cx_num1 + Math.cos(x_axis_rotation_num) * cy_num1);

	// Step 4: Compute angle start and angle extent
		v_ux = (pD_x_num - cx_num1) / rx_num;
		v_uy = (pD_y_num - cy_num1) / ry_num;
		v_vx = (-pD_x_num - cx_num1) / rx_num;
		v_vy = (-pD_y_num - cy_num1) / ry_num;
		v_n = Math.sqrt((v_ux * v_ux) + (v_uy * v_uy));
		v_p = v_ux; // 1 * ux + 0 * uy
		v_sign = (v_uy < 0) ? -1 : 1;
		startAngle_num = v_sign * Math.acos(v_p / v_n);
		startAngle_num = startAngle_num * 180 / Math.PI;
		v_n = Math.sqrt((v_ux * v_ux + v_uy * v_uy) * (v_vx * v_vx + v_vy * v_vy));
		v_p = v_ux * v_vx + v_uy * v_vy;
		v_sign = ((v_ux * v_vy - v_uy * v_vx) < 0) ? -1 : 1;
		angle_num = v_sign * Math.acos(v_p / v_n);
		angle_num = angle_num * 180 / Math.PI;
		if (sweep_flag_num == 0 && angle_num > 0){
			angle_num -= 360;
		}
		else{
			if (sweep_flag_num == 1 && angle_num < 0){
				angle_num += 360;
			}
		}
		// if the arc is more than 2Pi, we modulo it
		angle_num %= 360;
		startAngle_num %= 360;

	// STEP B - let's find the pairs of (point, control point) to draw the whole stuff
	//  Init vars
		var segAngle_num:Number;
		var angleMid_num:Number;
		var segs_num:Number;
		var pA:Point;
		var pB:Point;
		var pC:Point;
		var bPoint:Point;

		// Approximation of the arc with segments drawn using Quadratic Bézier curves.
		segs_num = Math.ceil(Math.abs(angle_num)/45);
		segAngle_num = angle_num / segs_num;
		// Converts degrees in radians.
		segAngle_num = -1 * (segAngle_num / 180) * Math.PI;
		// convert angle startAngle_num to radians
		angle_num = -1 * (startAngle_num / 180) * Math.PI;
		// find our starting points (ax,ay) relative to the secified x,y
		pA = new Point(cx_num, cy_num);

		var baseMatrix:Matrix = new Matrix(1, 0, 0, 1, -1 * cx_num, -1 * cy_num); // translate back to origin before rotation
		var rotationMatrix:Matrix = new Matrix(); // prepare for rotation?! 3... 2... 1... go
		rotationMatrix.rotate(x_axis_rotation_num); // rotate!
		baseMatrix.concat(rotationMatrix); // rewrite the base matrix so that it translates and rotates
		var finalMatrix:Matrix = new Matrix(1, 0, 0, 1, cx_num, cy_num); // and then go back to original coord reference

//		drawPoints.push({p:p1.clone()});

		if (segs_num>0) {
			// Loop for drawing arc segments
			var i:Number;
			for(i = 0; i<segs_num; i++) {
				// increment our angle
				angle_num += segAngle_num;
				// find the angle halfway between the last angle and the new
				angleMid_num = angle_num - (segAngle_num / 2);

				// calculate the temporary end point
				pB = new Point(pA.x + Math.cos(-angle_num) * rx_num, pA.y + Math.sin(-angle_num) * ry_num);
				// calculate the needed control point
				pC = new Point(pA.x + Math.cos(-angleMid_num) * (rx_num / Math.cos(-segAngle_num / 2)), pA.y + Math.sin(-angleMid_num) * (ry_num / Math.cos(-segAngle_num / 2)));

				pB = baseMatrix.transformPoint(pB);
				pB = finalMatrix.transformPoint(pB);

				pC = baseMatrix.transformPoint(pC);
				pC = finalMatrix.transformPoint(pC);

				// draw the arc segment
				drawPoints.push({p:pB.clone(), c:pC.clone()});
			}
			drawPoints.push({p:p2.clone()});
		}
		return(drawPoints);
	}
	private function attachMarker(markerType_str,marker_ref):Void{
		var markerAngle_num:Number;
		var markerValue_str:String;
		var markerId_str:String;

		markerValue_str = toolbox.extractString(_objDef.attributes[markerType_str],"url(#",")",0,false);
		if((markerValue_str != undefined) && (markerValue_str != "")){
			trace("SVGpath (attachMarker): " + id + "_marker_x name = " + markerValue_str);
			var markerRepository_ref:MovieClip = _objThis._exulis._objDefsRepository.retrieve(markerValue_str);
			trace("SVGpath (attachMarker): " + id + "_marker_x Ref = " + markerRepository_ref);
			if(markerRepository_ref != undefined){
				if(markerType_str == "marker-end"){
					markerId_str = id + "_marker_end";
					markerAngle_num = Math.acos((lastAnchor_x_num - beforeLastAnchor_x_num) / Math.sqrt(Math.pow((lastAnchor_x_num - beforeLastAnchor_x_num),2) + Math.pow((lastAnchor_y_num - beforeLastAnchor_y_num),2)));
					markerAngle_num *= (beforeLastAnchor_y_num > lastAnchor_y_num) ? -1:1;
				}
				else{
					markerId_str = id + "_marker_start";
					markerAngle_num = Math.acos((firstAnchor_x_num - secondAnchor_x_num) / Math.sqrt(Math.pow((firstAnchor_x_num - secondAnchor_x_num),2) + Math.pow((firstAnchor_y_num - secondAnchor_y_num),2)));
					markerAngle_num *= (secondAnchor_y_num > firstAnchor_y_num) ? -1:1;
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
				if(markerType_str == "marker-end"){
					marker_ref._x = lastAnchor_x_num;
					marker_ref._y = lastAnchor_y_num;
				}
				else{
					marker_ref._x = firstAnchor_x_num;
					marker_ref._y = firstAnchor_y_num;
				}
				trace("SVGpath (attachMarker): markerAngle = " + markerAngle_num + " and _x = " + marker_ref._x + " and _y = " + marker_ref._y);
				marker_ref._childNextDepth++;
			}
		}
	}
	function destroy(){
		trace("SVGpath (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}