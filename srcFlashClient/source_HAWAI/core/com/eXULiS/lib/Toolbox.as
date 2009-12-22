// collection of useful functions
import com.xfactorstudio.xml.xpath.*;
import com.eXULiS.lib.calculator;

class com.eXULiS.lib.Toolbox {
	public function Toolbox(){
		// empty constructor
	}

// this function enables postponed calls
	public function postpone(){
		

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

	public function cleanString(vInitial,vSpace,vTAB,vLF,vCR,vXtraSpace){ //CHR: #x20, #x09, #x0A, #x0D
		var workString = new String(vInitial);
		var workArray = new Array();
		workArray = workString.split("");
		workString = "";
        for(var vCpt=0;vCpt<workArray.length;vCpt++){
			switch(workArray[vCpt]){
				case " ":{
					if(vSpace){
						workString += "";
					}
					else{
						if(vXtraSpace){
							if(workString.substr(-1,1) != " "){
								workString += workArray[vCpt];
							}
						}
						else{
							workString += workArray[vCpt];
						}
					}
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

	public function htmlThis(vInitial){
		var workString = new String(vInitial);
		workString = replaceString(workString,"&","&amp;");
		workString = replaceString(workString,"\"","&quot;");
		workString = replaceString(workString,"'","&apos;");
		workString = replaceString(workString,"<","&lt;");
		workString = replaceString(workString,">","&gt;");
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

	public function cleanEntities(vInitial){ //Entities like &apos; should be replaced by '
		_level0.createTextField("entitiesCleanerTextField_txt", 999998, 0, 0, 0, 0);
		_level0.entitiesCleanerTextField_txt.selectable = false;
		_level0.entitiesCleanerTextField_txt.autoSize = true;
		_level0.entitiesCleanerTextField_txt.backgroundColor = 0xFFFFEE;
		_level0.entitiesCleanerTextField_txt._visible = false;
		_level0.entitiesCleanerTextField_txt.html = true;
		_level0.entitiesCleanerTextField_txt.multiline = true;
		_level0.entitiesCleanerTextField_txt.htmlText = vInitial;
		_level0.entitiesCleanerTextField_txt.html = false;
		var workString = "";
		workString += _level0.entitiesCleanerTextField_txt.text;
		_level0.entitiesCleanerTextField_txt.removeTextField();
		return(workString);
	}

	public function extractString(vInitial,vTarget1,vTarget2,vOffset,vWith){
// TODO problem with simple quote and indexOf in extractString -> urlencode maybe the solution
// TODO add two arguments to process lastindex on vTarget1 and vTarget2
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
		trace("Toolbox::run entered with aFunction_str = " + aFunction_str);
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

	public function wrapRun(){
		trace("Toolbox::wrapRun entered with " + arguments[0]);
		var action_str;
		var finalResult;
		var fullCmd_str:String = new String(arguments[0]);
		var returnVal_array;
		var returnVal_xml:XMLNode;

		fullCmd_str = trimString(fullCmd_str,true,true);

		switch(true){
			case (fullCmd_str.substr(0,8) == "xpath://"):{
				var xmlSource_xml:XML;
				xmlSource_xml = arguments[1];
				var xpathPart:String = fullCmd_str.substr(8);

				switch(arguments[2]){
					case "Nodes":{
						returnVal_array = XPath.selectNodes(xmlSource_xml,xpathPart);
						break;
					}
					case "SingleNode":{
						returnVal_xml = XPath.selectSingleNode(xmlSource_xml,xpathPart);
						break;
					}
					case "NodesAsNumber":{
						returnVal_array = XPath.selectNodesAsNumber(xmlSource_xml,xpathPart);
						break;
					}
					case "NodesAsBoolean":{
						returnVal_array = XPath.selectNodesAsBoolean(xmlSource_xml,xpathPart);
						break;
					}
					case "NodesAsString":
					default:{
						returnVal_array = XPath.selectNodesAsString(xmlSource_xml,xpathPart);
					}
				}

				if(arguments[3] != undefined){
					
					if(arguments[3] == "String"){
						returnVal_xml = (arguments[2] == "SingleNode") ? returnVal_xml : returnVal_array[0];
						trace("Toolbox::wrapRun returnVal_xml:" + returnVal_xml);
						trace("Toolbox::wrapRun returnVal_xml nodeType:" + returnVal_xml.firstChild.nodeType);
						if(returnVal_xml.firstChild.nodeType == 3){
							finalResult = returnVal_xml.firstChild.nodeValue;
						}
						else {
							for(var vNodesCpt = 0;vNodesCpt<returnVal_xml.childNodes.length;vNodesCpt++){
								var finalResult_str = new String();
								finalResult_str = finalResult_str.concat(returnVal_xml.childNodes[vNodesCpt].toString());
							}
							trace("Toolbox::wrapRun finalResult_str:" + finalResult_str);
							if(finalResult_str == undefined){
								finalResult = String(returnVal_xml);
							}
							else{
								finalResult = finalResult_str;
							}
							trace("Toolbox::wrapRun finalResult:" + finalResult);
						}
					}
					else{
						finalResult = (arguments[2] == "SingleNode") ? returnVal_xml : returnVal_array;
					}
				}
				else{
					finalResult = (arguments[2] == "SingleNode") ? returnVal_xml : returnVal_array;
				}

				if(finalResult.substr(0,6) == "xlf://"){
					var sticker_str:String = finalResult.substr(6);
					finalResult = _root._objXLIFFholder_obj[sticker_str];
					trace("Toolbox::wrapRun xliffResult (" + sticker_str + "): " + finalResult);
				}

				break;
			}
			case (fullCmd_str.substr(0,13) == "javascript://"):
			case (fullCmd_str.substr(0,5) == "js://"):
			{
				// TODO js eval implementation
				break;
			}
			case (fullCmd_str.substr(0,6) == "xlf://"):
			{
				
				var xlf_str:String = fullCmd_str.substr(6);
				finalResult = (_root._objXLIFFholder_obj[xlf_str]==undefined) ? fullCmd_str : _root._objXLIFFholder_obj[xlf_str];
		trace("Toolbox (wrapRun) xliffResult (" + xlf_str + "): " + finalResult);
			
				break;
			}
			case (fullCmd_str.substr(0,15) == "actionscript://"):
			case (fullCmd_str.substr(0,5) == "as://"):
			{
				var action_str:String = (fullCmd_str.substr(0,5) == "as://") ? fullCmd_str.substr(5):fullCmd_str.substr(15);
				var cmdPart:String = extractString(action_str,"","(",0,false);
				var argPart:String = extractString(action_str,"(",")",0,false);
//				var argTarget:String = new String();
				var referer_obj = arguments[1];

				var objPart:Object = referer_obj._targetExecutionLayer;
			
				trace("Toolbox::wrapRun: real call is: " + objPart + "." + cmdPart + "(" + argPart + ")");
				var arguments_array:Array = new Array();
				arguments_array = argPart.split(",");
				arguments_array.push(referer_obj);
				finalResult = objPart[cmdPart](arguments_array);
				break;
			}
			case (fullCmd_str.substr(0,13) == "webservice://"):
			case (fullCmd_str.substr(0,5) == "ws://"):
			{
				// TODO ws eval implementation
				break;
			}
			case (fullCmd_str.substr(0,18) == "localconnection://"):
			case (fullCmd_str.substr(0,5) == "lc://"):
			{
				// TODO lc eval implementation
				break;
			}
			default:
			{
				// do nothing 'cause attrib's value should remain as is
				finalResult = arguments[0];
			}
		}
		trace("Toolbox::wrapRun: with " + arguments[0] + " returned " + finalResult);
		return(finalResult);
	}

	public function calculate(expr:String){
		trace ("calculator entered");
		trace ("   to process: " + expr);
		var calc:calculator = new calculator();

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
		trace("Toolbox: label width " + labelMinWidth + " - label height " + labelMinHeight);
		return({minWidth:labelMinWidth,minHeight:labelMinHeight});
	}
}
