// collection of useful functions
import lu.tao.utils.tao_calculator;

class lu.tao.utils.tao_toolbox {
	public function tao_toolbox(){
		// empty constructor
	}

// this function enables postponed calls
	public function postpone(function2CallBack){
		_root.postponer_mc.postpone(function2CallBack);
		_root.postponer_mc.gotoAndPlay(1);
	}
	
	public function dummy(){
		return("");
	}
	
// USAGE:
// var str:String = "<h1>Example of stripTag function</h1>";
// str = str.stripTag();
// will return "Example of stripTag function"
	public function stripTag(vInitial:String):String{
		var workString = new String(vInitial);
		var i = workString.indexOf("<");
		var j = workString.indexOf(">", i);
		while ((i > -1) && (j > -1)){
			workString = workString.substring(0, i) + workString.substring(j+1);
			i = workString.indexOf("<", i);
			j = workString.indexOf(">", i);
		}
		return workString;
	}

	public function cleanString(vInitial,vSpace,vTAB,vLF,vCR){ //CHR: #x20, #x09, #x0A, #x0D
		var workString = new String(vInitial);
		var workArray = new Array();
		workArray = workString.split("");
		workString = "";
        for(var vCpt=0;vCpt<workArray.length;vCpt++){
			switch(workArray[vCpt]){
				case " ":{
					workString += (vSpace) ? "" : workArray[vCpt];
					break;
				}
				case "\t":{
					workString += (vTAB) ? "" : workArray[vCpt];
					break;
				}
				case "\n":{
					workString += (vLF) ? "" : workArray[vCpt];
					break;
				}
				case "\r":{
					workString += (vCR) ? "" : workArray[vCpt];
					break;
				}
				default:{
					workString += workArray[vCpt];
				}
			}
        }
		return(workString);
	}

	public function trimString(vInitial,vLeft,vRight){
		var workString = new String(vInitial);
		var workArray = new Array();
		workArray = workString.split("");
		var a = 0;
		if(vLeft == true){
			while((a < workArray.length) && (workArray[a] == " ")){
				a++ ;
			}
			workString = workString.substring(a);
		}
		workArray = workString.split("");
		if(vRight == true){
			a = workArray.length - 1;
			while((a >= 0) && (workArray[a] == " ")){
				a-- ;
			}
			a = a + 1;
			workString = workString.substring(0,a);
		}
		return(workString);
	}

	public function isIn(vElement,vHaystack):Boolean{
		var vResult_bool:Boolean = false;
		for(var vCpt_num:Number = 0;vCpt_num<vHaystack.length;vCpt_num++){
			if(vHaystack[vCpt_num] == vElement){
				vResult_bool = true;
				break;
			}
		}
		return(vResult_bool);
	}

	public function trimStringMask(vInitial,vMask,vLeftRight){
		var workString:String = new String(vInitial);
		var leftRight_str:String = new String(vLeftRight);
		var theMask_str:String = new String(vMask);
		var theMask_array:Array = new Array();
		var workArray = new Array();
		var vLeft:Boolean;
		var vRight:Boolean;
		var a:Number = 0;

		theMask_array = theMask_str.split("");
		leftRight_str = leftRight_str.toUpperCase();
		vLeft = (leftRight_str == "LEFT") ? true : (leftRight_str == "BOTH") ? true : false;
		vRight = (leftRight_str == "RIGHT") ? true : (leftRight_str == "BOTH") ? true : false;
		workArray = workString.split("");
		if(vLeft == true){
			while((a < workArray.length) && isIn(workArray[a],theMask_array)){
				a++ ;
			}
			workString = workString.substring(a);
		}
		workArray = workString.split("");
		if(vRight == true){
			a = workArray.length - 1;
			while((a >= 0) && isIn(workArray[a],theMask_array)){
				a-- ;
			}
			a = a + 1;
			workString = workString.substring(0,a);
		}
		return(workString);
	}

	public function cleanStringMask(vInitial,vMask){ //for CHR: #x20, #x09, #x0A, #x0D -> cleanString should be used
		var workString = new String(vInitial);
		var theMask_str:String = new String(vMask);
		var theMask_array:Array = new Array();
		theMask_array = theMask_str.split("");
		var workArray = new Array();
		workArray = workString.split("");
		workString = "";
        for(var vCpt=0;vCpt<workArray.length;vCpt++){
			if(!(isIn(workArray[vCpt],theMask_array))){
				workString += workArray[vCpt];
			}
        }
		return(workString);
	}

	public function replaceString(vInitial,vTarget,vReplacer){
//		vInitial.split(vTarget).join(vReplacer); // shortcut
		var workString = new String(vInitial);
		var workArray = new Array();
		workArray = workString.split(vTarget);
		workString = "";
		for(var a = 0; a < workArray.length; a++){
			if(a < (workArray.length - 1)){
				workString = workString + workArray[a] + vReplacer;
			}
			else {
				workString = workString + workArray[a];
			}
		}
		return(workString);
	}

	public function transmuteString(vInitial,vTarget,vReplacer,vOriginal,vTransmuted){
		var workString = new String(vInitial);
		var workArray = new Array();
		var originalStart:Number;
		var original:String = vOriginal;
		workArray = workString.split(vTarget);
		workString = "";
		for(var a = 0; a < workArray.length; a++){
			if(a < (workArray.length - 1)){
				workString = workString + workArray[a] + vReplacer;
			}
			else {
				workString = workString + workArray[a];
			}
			originalStart = workString.lastIndexOf(original);
			if(originalStart != -1){
				workString = workString.substr(0,originalStart) + vTransmuted + workString.substr(originalStart + original.length);
			}
		}
		return(workString);
	}

	public function htmlThis(vInitial){
		var workString = new String(vInitial);
		workString = replaceString(workString,"&","&amp;");
		workString = replaceString(workString,"\"","&quot;");
		workString = replaceString(workString,"'","&apos;");
		workString = replaceString(workString,"<","&lt;");
		workString = replaceString(workString,">","&gt;");
		return(workString);
	}

	public function unhtmlThis(vInitial){
		var workString = new String(vInitial);
		workString = replaceString(workString,"&amp;","&");
		workString = replaceString(workString,"&quot;","\"");
		workString = replaceString(workString,"&apos;","'");
		workString = replaceString(workString,"&lt;","<");
		workString = replaceString(workString,"&gt;",">");
		return(workString);
	}

    public function format(vInitial,vToken,vTotalLen,vOrientation){ //orientation is "leftPad" or "rightPad"
		var workString:String = new String(vInitial);
        var initialLen:Number = workString.length;
        var appended_str:String = new String();
		var vOrientation_str:String = new String(vOrientation);
		vOrientation_str = vOrientation_str.toUpperCase();

        for(var vCpt=0;vCpt<(vTotalLen - initialLen);vCpt++){
            appended_str = appended_str.concat(vToken);
        }
        switch(vOrientation_str){
            case "LEFTPAD":{
                workString = appended_str + workString;
                break;
            }
            case "RIGHTPAD":{
                // break; // no need 'cause same as default case
            }
            default:{
                workString = workString.concat(appended_str);
            }
        }
        return(workString);
    }

	public function extractString(vInitial,vTarget1,vTarget2,vOffset,vWith){
		var workString = new String(vInitial);
		var finalResult = new String();
		var workIndex1;
		var workIndex2;
		workIndex1 = workString.indexOf(vTarget1);
		if(workIndex1 == -1){
			workIndex1 = 0;
		}
		else {
			workIndex1 = workIndex1+ vTarget1.length;
		}
		if (vTarget2 == ""){
			workIndex2 = workString.length;
		}
		else {
			workIndex2 = workString.indexOf(vTarget2,workIndex1 + vOffset);
			if(workIndex2 == -1){
				workIndex2 = workString.length;
			}
		}
		if(vWith == true){
			finalResult = vTarget1 + workString.slice(workIndex1,workIndex2) + vTarget2;
		}
		else {
			finalResult = workString.slice(workIndex1,workIndex2);
		}
		return(finalResult);
	}
	
// --- The called function must be something like
// function aFunction_str(){
// --- argument contain the parameters used with the fct
// --- for example initialItems_array, passedItem_array, theta...
//   trace("args = " + arguments);
//   (...)
// }
	public function run(aFunction_str:String, arguments_array:Array){
		trace("toolbox::run entered with aFunction_str = " + aFunction_str);
		var fullCmd_str:String = new String(aFunction_str);
		fullCmd_str = trimString(fullCmd_str,true,true);
		fullCmd_str = replaceString(fullCmd_str," ","_");
		fullCmd_str = fullCmd_str.toUpperCase();
		var objPart_obj:Object = new Object();
		var fctPart_fct:Function = eval(fullCmd_str);
		var returnedResult;
		returnedResult = fctPart_fct.apply(objPart_obj,arguments_array);
		return(returnedResult);
	}

	public function calculate(expr:String){
		trace ("tao_calculator entered");
		trace ("   to process: " + expr);
		var calc:tao_calculator = new tao_calculator();

		expr = calc.remove_space(expr);

		expr = calc.prepare(expr);
		if (calc.preparse(expr) != 0)
		{
			return("Error on parenthesis balance");
		}
		calc.parse (expr);
		var returnedResult_str:String = new String(calc.evaluate());

		trace ("   result: " + returnedResult_str);
		return(returnedResult_str);
	}

	public function evaluateLabelSize(aText){
		var labelMinWidth:Number = 0;
		var labelMinHeight:Number = 0;
//		_root.createEmptyMovieClip("aMC_mc"],12344);
	//	aMC_mc
		//create the textField
		var aLabel = _root.createLabel("aLabel", 12345);
		aLabel.setValue(aText);
		labelMinWidth = aLabel.getPreferredWidth();
		labelMinHeight = aLabel.getPreferredHeight();
		aLabel.destroyObject();
		trace("tao_toolbox: label width " + labelMinWidth + " - label height " + labelMinHeight);
		return({minWidth:labelMinWidth,minHeight:labelMinHeight});
	}
}
