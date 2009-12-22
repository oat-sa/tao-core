import lu.tao.utils.tao_toolbox;
import lu.tao.tao_scoring.MyNumberFormatter;
import mx.managers.*;
import mx.services.*;
// implements the COMPLEX matching

class lu.tao.tao_scoring.tao_COMPLEX{

	private var vFinalExpression_str:String;
	private var tokenArray_array:Array;
	private var wgtsRepo_array:Array;
	private var scoreType_str:String;
	private var my_toolbox:tao_toolbox;
	private var my_formatter:MyNumberFormatter;
	private var vIdxChar_num:Number;
	private var keyWords_array:Array = new Array();
	private var targetPointers_array:Array = new Array();

	private var pcSetResult;
	private var isWSDLok:Boolean;
	private var wsdlurl_str:String;
	private var followUpCallFct_str:String;
	private var localizationLog;
	private var localizationService;

	public function tao_COMPLEX(widgetArray_array,scoreType){
		// constructor
		vFinalExpression_str = "";
		tokenArray_array = new Array();
		wgtsRepo_array = widgetArray_array;
		scoreType_str = scoreType;
		my_toolbox = new tao_toolbox();
		my_formatter = new MyNumberFormatter();
		vIdxChar_num = 0;
		keyWords_array = new Array();
		keyWords_array.push("PARSEINT");
		keyWords_array.push("PARSEFLOAT");
		keyWords_array.push("PARSEFRACT");
		keyWords_array.push("TRIMSTRING");
		keyWords_array.push("CLEANSTRINGMASK");
		keyWords_array.push("FILTERSTRING");
	}

	public function scoreThis(endorsement_str):Boolean{
		trace("CPLX IN  scoreThis with " + endorsement_str);
		endorsement_str = tokenizeComplex(endorsement_str);
		trace("CPLX     scoreThis endorsement_str (tokenized) = " + endorsement_str);
		endorsement_str = my_toolbox.cleanString(endorsement_str,true,true,true,true);
		endorsement_str = my_toolbox.replaceString(endorsement_str,"{","(");
		endorsement_str = my_toolbox.replaceString(endorsement_str,"}",")");
		trace("CPLX     scoreThis scoreType_str = " + scoreType_str);
		if(scoreType_str == "MATCH"){
			endorsement_str = my_toolbox.transmuteString(endorsement_str,"==",",","(","MATCH(");
			endorsement_str = my_toolbox.transmuteString(endorsement_str,"!=","$","(","MATCH(");
			endorsement_str = my_toolbox.transmuteString(endorsement_str,"<>","$","(","MATCH(");
		}
		trace("CPLX     scoreThis endorsement_str (ready) = " + endorsement_str);
		var final_str:String = resolveComplex(endorsement_str,wgtsRepo_array);
		var vCptParentheses_num:Number = 0;
		for(var vCpt_num:Number=0;vCpt_num<final_str.length;vCpt_num++){
			if(final_str.charAt(vCpt_num) == "("){
				vCptParentheses_num++ ;
			}
			if(final_str.charAt(vCpt_num) == ")"){
				vCptParentheses_num-- ;
			}
		}
		if(vCptParentheses_num > 0){
			final_str = my_toolbox.format(final_str,")",final_str.length + vCptParentheses_num,"RIGHTPAD");
		}
		if(vCptParentheses_num < 0){
			final_str = my_toolbox.format(final_str,"(",final_str.length - vCptParentheses_num,"LEFTPAD");
		}
		final_str = my_toolbox.replaceString(final_str,"true","t");
		final_str = my_toolbox.replaceString(final_str,"false","f");
		final_str = my_toolbox.replaceString(final_str,"&&","&");
		final_str = my_toolbox.replaceString(final_str,"||","|");
		// final_str = "(f|((t&(f)))|f)"; // for testing purpose
		trace("CPLX     scoreThis mid-term result = " + final_str);
		vFinalExpression_str = final_str;
		//var finalResult_bool:Boolean = evalComplex(final_str);
		var finalResult_bool:Boolean = parseComplex(final_str);
		trace("CPLX OUT scoreThis with result bool = " + finalResult_bool);
		return(finalResult_bool);
	}

	public function LOCAL_PARSEINT(){
		var args:Array = arguments[0];
		var vInitial:String = getWidgetValue(args[0]);
		trace("CPLX IN  localPARSEINT with " + vInitial);
		var vResult_str:String = "";
		vInitial = formatNumber(vInitial);
		vResult_str = String(parseInt(vInitial,10));
		trace("CPLX OUT localPARSEINT with " + vResult_str);	
		return(vResult_str);	
	}

	public function LOCAL_PARSEFLOAT(){
		var args:Array = arguments[0];
		var vInitial:String = getWidgetValue(args[0]);
		trace("CPLX IN  localPARSEFLOAT with " + vInitial);
		var vResult_str:String = "";
		vInitial = formatNumber(vInitial);
		vResult_str = String(parseFloat(vInitial));
		trace("CPLX OUT localPARSEFLOAT with " + vResult_str);	
		return(vResult_str);	
	}

	public function LOCAL_PARSEFRACT(){
		var args:Array = arguments[0];
		var vInitial:String = getWidgetValue(args[0]);
		var vWork1_str:String = vInitial;
		var vWork2_str:String = "";
		var vSlashPos_num:Number;
		var vBlankPos_num:Number;
		trace("CPLX IN  localPARSEFRACT with " + vInitial);
		var vResult_str:String = "";
//		vResult_str = String(parseFloat(vInitial));
		vWork1_str = my_toolbox.trimString(vWork1_str,true,true);

		while(true){
			vWork2_str = my_toolbox.replaceString(vWork1_str," /","/");
			if(vWork1_str == vWork2_str){
				break;
			}
			else{
				vWork1_str = vWork2_str;
			}
		}
		vWork2_str = "";
		while(true){
			vWork2_str = my_toolbox.replaceString(vWork1_str,"/ ","/");
			if(vWork1_str == vWork2_str){
				break;
			}
			else{
				vWork1_str = vWork2_str;
			}
		}
		vSlashPos_num = vWork1_str.indexOf("/");
		if(vSlashPos_num != -1){
			vBlankPos_num = vWork1_str.lastIndexOf(" ",vSlashPos_num);
			if(vBlankPos_num != -1){
				var vSign_str:String = "+";
				var vPrefix_str:String = vWork1_str.substr(0,vBlankPos_num);
				var vSignPosMin_num:Number = vPrefix_str.lastIndexOf("-");
				var vSignPosPlus_num:Number = vPrefix_str.lastIndexOf("+");
				if(vSignPosMin_num != -1){
					if(vSignPosPlus_num != -1){
						vSign_str = (vSignPosMin_num > vSignPosPlus_num) ? "-" : "+";
					}
					else{
						vSign_str = "-";
					}
				}
				vWork1_str = formatNumber(vPrefix_str) + vSign_str + formatNumber(vWork1_str.substring(vBlankPos_num + 1,vSlashPos_num)) + "/" + formatNumber(vWork1_str.substr(vSlashPos_num + 1));
			}
			else{
				vWork1_str = formatNumber(vWork1_str.substring(0,vSlashPos_num)) + "/" + formatNumber(vWork1_str.substr(vSlashPos_num + 1));
			}
		}
		else{
			vWork1_str = formatNumber(vWork1_str);			
		}
		vWork1_str = my_toolbox.cleanString(vWork1_str,true,true,true,true);
		trace("CPLX ->  localPARSEFRACT with work=" + vWork1_str);
		vResult_str = my_toolbox.calculate(vWork1_str);
//		trace("CPLX ->  localPARSEFRACT with result=" + vResult_str);
//		vResult_str = formatNumber(vResult_str);

		trace("CPLX OUT localPARSEFRACT with " + vResult_str);	
		return(vResult_str);	
	}

	public function LOCAL_TRIMSTRING(){
		var args:Array = arguments[0];
		var vInitial:String = getWidgetValue(args[0]);
		var vMask:String = args[1];
		var vLeftRight:String = args[2];
		trace("CPLX IN  localTRIMSTRING with vInitial: " + vInitial + " and vMask: " + vMask + " and vLeftRight: " + vLeftRight);
		var vResult_str:String = "";
		vResult_str = my_toolbox.trimStringMask(vInitial,vMask,vLeftRight);	
		trace("CPLX OUT localTRIMSTRING with " + vResult_str);
		return(vResult_str);
	}

	public function LOCAL_FILTERSTRING(){
		var args:Array = arguments[0];
		var vInitial:String = getWidgetValue(args[0]);
		var vMask:String = args[1];
		trace("CPLX IN  localFILTERSTRING with vInitial: " + vInitial + " and vMask: " + vMask);
		var vResult_str:String = "";
		vResult_str = my_toolbox.cleanStringMask(vInitial,vMask);	
		trace("CPLX OUT localFILTERSTRING with " + vResult_str);
		return(vResult_str);		
	}

	public function LOCAL_CLEANSTRINGMASK(){
		var args:Array = arguments[0];
		var vInitial:String = getWidgetValue(args[0]);
		var vMask:String = args[1];
		trace("CPLX IN  localCLEANSTRINGMASK with vInitial: " + vInitial + " and vMask: " + vMask);
		var vResult_str:String = "";
		vResult_str = my_toolbox.cleanStringMask(vInitial,vMask);	
		trace("CPLX OUT localCLEANSTRINGMASK with " + vResult_str);
		return(vResult_str);
	}

	public function LOCAL_CLEANSTRING(){
		var args:Array = arguments[0];
		var vInitial:String = getWidgetValue(args[0]);
		var vSpace:Boolean = Boolean(args[1]);
		var vTAB:Boolean = Boolean(args[2]);
		var vLF:Boolean = Boolean(args[3]);
		var vCR:Boolean = Boolean(args[4]);
		trace("CPLX IN  localCLEANSTRING with vInitial: " + vInitial + " and vSpace: " + vSpace + " and vTAB " + vTAB + " and vLF: " + vLF + " and vCR: " + vCR);
		var vResult_str:String = "";
		vResult_str = my_toolbox.cleanString(vInitial,vSpace,vTAB,vLF,vCR);	
		trace("CPLX OUT localCLEANSTRING with " + vResult_str);
		return(vResult_str);
	}

	public function formatNumber(expression_str):String{
		trace("CPLX IN  formatNumber with expression_str with " + expression_str);
		var vResult_str:String;
		var langCountryCode_str = ((_level0.locale == undefined) || (_level0.locale == "")) ? "en-US" : _level0.locale; //fallback

		vResult_str = my_formatter.normalize(expression_str, langCountryCode_str);

		trace("CPLX OUT formatNumber with vResult_str with " + vResult_str);
		return(vResult_str);
	}

	public function getWidgetValue(vWdgName_str):String{
		trace("CPLX IN  getWidgetValue with " + vWdgName_str);
		var vResult_str:String = vWdgName_str;
		var vHtmlState_bool:Boolean;
		for(var vCpt_num:Number=0;vCpt_num<wgtsRepo_array.length;vCpt_num++){
//			trace("CPLX     getWidgetValue: wgtsRepo_array[" + vCpt_num + "][xulID]: " + wgtsRepo_array[vCpt_num]["xulID"] + " with type:" + wgtsRepo_array[vCpt_num]["xulType"]);
			if(wgtsRepo_array[vCpt_num]["xulID"] == vWdgName_str){
				switch(wgtsRepo_array[vCpt_num]["xulType"]){
					case "xul_textbox":{
						vHtmlState_bool = wgtsRepo_array[vCpt_num]["objRef"].html;
						wgtsRepo_array[vCpt_num]["objRef"].html = false;
						vResult_str = wgtsRepo_array[vCpt_num]["objRef"].text;
						wgtsRepo_array[vCpt_num]["objRef"].html = vHtmlState_bool;
						break;
					}
					case "xul_menupopup":{
						vResult_str = String(wgtsRepo_array[vCpt_num]["objRef"].selectedIndex);
						break;
					}
					case "xul_radio":
					case "xul_checkbox":{
						vResult_str = (wgtsRepo_array[vCpt_num]["objRef"].selected == true) ? "1" : "0";
						break;
					}
					default:{
						trace("CPLX     getWidgetValue: case " + wgtsRepo_array[vCpt_num]["xulType"] + " not handled for " + wgtsRepo_array[vCpt_num]["xulID"]);
					}
				}
				break;
			}			
		}
		trace("CPLX OUT getWidgetValue with " + vResult_str);				
		return(vResult_str);
	}

	public function logisticOverlay(vFct_str):String{
		trace("CPLX IN  logistic with " + vFct_str);
		var vResult_str:String = "";
		var vFctName_str:String = "";
		vFctName_str = "LOCAL_" + vFct_str.substr(0,vFct_str.indexOf("("));
		var workString_str:String = vFct_str.substring(vFct_str.indexOf("(") + 1,vFct_str.lastIndexOf(")"));
//		trace("CPLX  in logistic with workString_str = *" + workString_str + "*");
		var args_array:Array = new Array();
		args_array = workString_str.split(",");
		for(var vCpt_num:Number = 0;vCpt_num<args_array.length;vCpt_num++){
			args_array[vCpt_num] = getTokenComplex(args_array[vCpt_num]);
		}

//		var objPart_obj:Object = new Object();
		vResult_str = this[vFctName_str](args_array);
		tokenArray_array.push(vResult_str);
		var vNewExpression_str:String = "";
		vNewExpression_str = "TOKEN" + String(tokenArray_array.length - 1);
		trace("CPLX OUT logistic with " + vNewExpression_str + " = " + vResult_str);
		return(vNewExpression_str);
	}

	public function getTokenComplex(vArg):String{
		var vArg_str:String = String(vArg);
		var vArg_num:Number = 0;
		if(vArg_str.substr(0,5) == "TOKEN"){
			vArg_num = parseInt(vArg_str.substr(5),10);
			vArg_str = tokenArray_array[vArg_num];
		}
		return(vArg_str);
	}

	public function tokenizeComplex(workString_str):String{
		var vNewExpression_str:String = "";
	//	trace("CPLX IN  tokenize with " + workString_str);
		var vCharIndex_num:Number = 0;
		var vNextQuote_num:Number = 0;
		var vToken_str:String = "";
		var vTokenFound_bool:Boolean;
		while(vCharIndex_num < workString_str.length){
	//		trace("CPLX info (vCharIndex_num):" + vCharIndex_num);
			vTokenFound_bool = false;
			if(workString_str.substr(vCharIndex_num,1) == '"'){
				vNextQuote_num = workString_str.indexOf('"',vCharIndex_num + 1);
				if(vNextQuote_num != -1){
					vToken_str = workString_str.substring(vCharIndex_num + 1,vNextQuote_num);
	//				trace("CPLX     token" + tokenArray_array.length + " is '" + vToken_str + "'");
					tokenArray_array.push(vToken_str);
					vTokenFound_bool = true;
					vNewExpression_str += "TOKEN" + String(tokenArray_array.length - 1);
				}
				else{
					trace("CPLX  !  double quotes are not matching");
					break;
				}
			}
			if(workString_str.substr(vCharIndex_num,1) == "'"){
				vNextQuote_num = workString_str.indexOf("'",vCharIndex_num + 1);
				if(vNextQuote_num != -1){
					vToken_str = workString_str.substring(vCharIndex_num + 1,vNextQuote_num);
	//				trace("CPLX     token" + tokenArray_array.length + " is '" + vToken_str + "'");
					tokenArray_array.push(vToken_str);
					vTokenFound_bool = true;
					vNewExpression_str += "TOKEN" + String(tokenArray_array.length - 1);
				}
				else{
					trace("CPLX  !  simple quotes are not matching");
					break;
				}
			}
	//		trace("CPLX info (vTokenFound_bool):" + vTokenFound_bool);
			if(vTokenFound_bool){
				vCharIndex_num = vNextQuote_num + 1;
			}
			else{
				vNewExpression_str += workString_str.substr(vCharIndex_num,1);
				vCharIndex_num++;
			}
		}
		workString_str = vNewExpression_str;
		vNewExpression_str = "";
		vCharIndex_num = 0;
		vNextQuote_num = 0;
		vToken_str = "";
		while(vCharIndex_num < workString_str.length){
	//		trace("CPLX info (vCharIndex_num):" + vCharIndex_num);
			vTokenFound_bool = false;
			if((workString_str.charAt(vCharIndex_num) == '[') || (workString_str.charAt(vCharIndex_num) == ']')){
				vNextQuote_num = vCharIndex_num + 1;
				while((vNextQuote_num < workString_str.length) && (workString_str.charAt(vNextQuote_num) != '[') && (workString_str.charAt(vNextQuote_num) != ']')){
					vNextQuote_num++ ;
				}
				if(vNextQuote_num != workString_str.length){
					vToken_str = workString_str.substring(vCharIndex_num,vNextQuote_num + 1);
					trace("CPLX     token" + tokenArray_array.length + " is '" + vToken_str + "'");
					tokenArray_array.push(vToken_str);
					vTokenFound_bool = true;
					vNewExpression_str += "TOKEN" + String(tokenArray_array.length - 1);
				}
				else{
					trace("CPLX  !  brackets are not matching");
					break;
				}
			}
	//		trace("CPLX info (vTokenFound_bool):" + vTokenFound_bool);
			if(vTokenFound_bool){
				vCharIndex_num = vNextQuote_num + 1;
			}
			else{
				vNewExpression_str += workString_str.substr(vCharIndex_num,1);
				vCharIndex_num++;
			}
		}
	//	trace("CPLX OUT tokenize with " + vNewExpression_str);
		return vNewExpression_str;
	}

	public function parseComplex(vExpression_str):Boolean{
		var prevOperator_str:String; // ='&';
		var vLocalIdxChar_num:Number=0;
		var vResult_bool:Boolean; // = true;
		var vCptParentheses_num:Number = 0;
		var vCharIndex_num:Number = 0;
		while(vLocalIdxChar_num < vExpression_str.length){
			switch(vExpression_str.charAt(vLocalIdxChar_num)){
				case('('):{
					vCptParentheses_num = 1;
					vCharIndex_num = vLocalIdxChar_num + 1;
					while((vCptParentheses_num != 0) && (vCharIndex_num < vExpression_str.length)){
						if(vExpression_str.charAt(vCharIndex_num) == "("){
							vCptParentheses_num++ ;
						}
						if(vExpression_str.charAt(vCharIndex_num) == ")"){
							vCptParentheses_num-- ;
						}
						vCharIndex_num++;
					}
					var prevResult_bool:Boolean = vResult_bool;
					vResult_bool = (prevOperator_str == '&') ? vResult_bool && parseComplex(vExpression_str.substring(vLocalIdxChar_num + 1,vCharIndex_num - 1)) : vResult_bool || parseComplex(vExpression_str.substring(vLocalIdxChar_num + 1,vCharIndex_num - 1));
	//				trace(vIdxChar_num + ">   " +prevResult_bool+ " " +prevOperator_str+ " eval(" + vExpression_str.substring(vLocalIdxChar_num + 1,vCharIndex_num - 1) + ") returned " + vResult_bool);
					vLocalIdxChar_num = vCharIndex_num;
					break;
				}
				case('&'):
				case('|'):{
					prevOperator_str = vExpression_str.charAt(vLocalIdxChar_num);
	//				trace(vIdxChar_num + ">   eval operator " + prevOperator_str);
					vLocalIdxChar_num++ ;
					break;
				}
				case('t'):{
					vResult_bool = (prevOperator_str == '&') ? vResult_bool : true;
	//				trace(vIdxChar_num + ">   eval (true) returned " + vResult_bool);
					vLocalIdxChar_num++ ;
					break;
				}
				case('f'):{
					vResult_bool = (prevOperator_str == '|') ? vResult_bool : false;
	//				trace(vIdxChar_num + ">   eval (false) returned " + vResult_bool);
					vLocalIdxChar_num++ ;
					break;
				}
				default:{
	//				trace(vIdxChar_num + ">   eval character " + vExpression_str.charAt(vLocalIdxChar_num));
					vIdxChar_num++ ;
				}
			}
		}
	//	trace("CPLX OUT eval (" + vIdxChar_num-- + ") with " + vResult_bool);
		return vResult_bool;
	}

	public function resolvePart(vArgument_str):String{
		trace("CPLX IN  resolvePart Part with " + vArgument_str);
		var vResult_str:String = "";
		var vIntermediaryResult_str:String = "";
		var lastKeyWordPos_num:Number = -1;
		var currentKeyWordPos_num:Number = -1;
		var stillSomethingToProcess_bool:Boolean = true;
		while(stillSomethingToProcess_bool){
			for(var vCpt_num:Number = 0; vCpt_num<keyWords_array.length;vCpt_num++){
				currentKeyWordPos_num = vArgument_str.lastIndexOf(keyWords_array[vCpt_num]);
				if(currentKeyWordPos_num > lastKeyWordPos_num){
					lastKeyWordPos_num = currentKeyWordPos_num;
				}
			}
			if(lastKeyWordPos_num != -1){
				stillSomethingToProcess_bool = true;
				var vCharIndex_num:Number = lastKeyWordPos_num;
				var vNextLeftParenthesis_num:Number = vArgument_str.indexOf("(",vCharIndex_num);
				if(vNextLeftParenthesis_num != -1){
					vCharIndex_num = vNextLeftParenthesis_num + 1;
					var vCptParentheses_num:Number = 1;
					while((vCptParentheses_num != 0) && (vCharIndex_num < vArgument_str.length)){
						if(vArgument_str.substr(vCharIndex_num,1) == "("){
							vCptParentheses_num++ ;
						}
						if(vArgument_str.substr(vCharIndex_num,1) == ")"){
							vCptParentheses_num-- ;
						}
						vCharIndex_num++;
					}
					var vToResolve_str:String = vArgument_str.substring(lastKeyWordPos_num,vCharIndex_num);
				}
				vIntermediaryResult_str = logisticOverlay(vToResolve_str);
				vArgument_str = vArgument_str.substring(0,lastKeyWordPos_num) + vIntermediaryResult_str + vArgument_str.substr(vCharIndex_num);
				lastKeyWordPos_num = -1;
			}
			else{
				stillSomethingToProcess_bool = false;
			}
		}
		vResult_str = getWidgetValue(vArgument_str);
		trace("CPLX OUT resolvePart Part with " + vResult_str);	
		return(vResult_str);
	}

	public function resolveComplex(vEndorsment_str,widgetsRepository_array):String{
		trace("CPLX IN  resolveComplex with " + vEndorsment_str);
		// the trick is "only MATCH or INTERVAL are evaluated"; for the rest, we normalize the expression and simply eval() it as we expect a simple boolean
		var vNewExpression_str:String = "";
		var vNextIdx_num:Number = 0;
		var vNextPointer_num:Number = 0;
		var vCurrentTarget_str:String;
		var workString_str:String = vEndorsment_str;
		var vNextMatch_num:Number = workString_str.indexOf("MATCH",vNextIdx_num);
		var vNextInterval_num:Number = workString_str.indexOf("INTERVAL",vNextIdx_num);
		while((vNextMatch_num != -1) || (vNextInterval_num != -1)){
			if(vNextMatch_num != -1){
				if(vNextInterval_num != -1){
					if(vNextMatch_num < vNextInterval_num){
						vNextPointer_num = vNextMatch_num;
						vCurrentTarget_str = "MATCH";
					}
					else{
						vNextPointer_num = vNextInterval_num;
						vCurrentTarget_str = "INTERVAL";
					}
				}
				else{
					vNextPointer_num = vNextMatch_num;
					vCurrentTarget_str = "MATCH";
				}
			}
			else{
				vNextPointer_num = vNextInterval_num;
				vCurrentTarget_str = "INTERVAL";
			}
			vNewExpression_str += workString_str.substring(vNextIdx_num,vNextPointer_num);
			trace("CPLX .   resolve Complex with " + vNewExpression_str);
			trace("CPLX >   resolve Complex with " + vCurrentTarget_str);
			targetPointers_array.push(vCurrentTarget_str);
			var vCharIndex_num:Number = vNextPointer_num + 5;
			var vNextLeftParenthesis_num:Number = workString_str.indexOf("(",vCharIndex_num);
			if(vNextLeftParenthesis_num != -1){
				vCharIndex_num = vNextLeftParenthesis_num + 1;
				var vCptParentheses_num:Number = 1;
				while((vCptParentheses_num != 0) && (vCharIndex_num < workString_str.length)){
					if(workString_str.substr(vCharIndex_num,1) == "("){
						vCptParentheses_num++ ;
					}
					if(workString_str.substr(vCharIndex_num,1) == ")"){
						vCptParentheses_num-- ;
					}
					vCharIndex_num++;
				}
				var vToResolve_str:String = workString_str.substring(vNextLeftParenthesis_num + 1,vCharIndex_num - 1);
				vNewExpression_str = vNewExpression_str + resolveComplex(vToResolve_str,widgetsRepository_array);
				trace("CPLX ..  resolve Complex with " + vNewExpression_str);
			}
			else{
				trace("CPLX  !  parentheses are not matching");
				break;
			}
			vNextIdx_num = vCharIndex_num;
			vNextMatch_num = workString_str.indexOf("MATCH",vNextIdx_num);
			vNextInterval_num = workString_str.indexOf("INTERVAL",vNextIdx_num);
		}
		trace("CPLX >.  resolve Complex with '" + vNewExpression_str + "'");
		if(vNewExpression_str == ""){
			var vCharPos_num:Number;
			vCharPos_num = 0;
			var vCptParenthesesOpened_num:Number = 0;
			var vCptQuotesOpened_bool:Boolean = false;
			var currentChar_str:String = workString_str.charAt(vCharPos_num);
//var debugCpt_num:Number = 0;
			while(!(((currentChar_str == ',') || (currentChar_str == '$')) && (vCptParenthesesOpened_num == 0) && (!vCptQuotesOpened_bool) && (vCharPos_num < workString_str.length))){
/*
				trace("CPLX .. EVAL (currentChar_str[" + currentChar_str + "] == ','): " + (currentChar_str == ','));
				trace("CPLX .. EVAL (vCptParenthesesOpened_num == 0): " + (vCptParenthesesOpened_num == 0));
				trace("CPLX .. EVAL (!vCptQuotesOpened_bool): " + (!vCptQuotesOpened_bool));
				trace("CPLX .. EVAL (vCharPos_num[" + vCharPos_num + "] < workString_str.length[" + workString_str.length + "]): " + (vCharPos_num < workString_str.length));
				trace("CPLX ..  resolve Complex EVAL (!((" + (currentChar_str == ',') + ") && (" + (vCptParenthesesOpened_num == 0) + ") && (!" + vCptQuotesOpened_bool + ") && (" + (vCharPos_num < workString_str.length) + "))");
*/
				if(currentChar_str == '"'){
					vCptQuotesOpened_bool = !vCptQuotesOpened_bool;
				}
				if(currentChar_str == '('){
					vCptParenthesesOpened_num++ ;
				}
				if(currentChar_str == ')'){
					vCptParenthesesOpened_num-- ;
				}
				vCharPos_num++;
				currentChar_str = workString_str.charAt(vCharPos_num);
/* // for debugging infinite loops
debugCpt_num++ ;
if(debugCpt_num > 100){
	trace("CPLX  !!!  BREAK for debug");
	break;
}
*/
			}
			if(((currentChar_str == ',') || (currentChar_str == '$')) && (vCptParenthesesOpened_num == 0) && (!vCptQuotesOpened_bool) && (vCharPos_num < workString_str.length)){
				var vFirstArg_str:String = workString_str.substring(0,vCharPos_num);
				var vSecondArg_str:String = workString_str.substring(vCharPos_num + 1);
				var vFirstCasting_str:String;
				var vSecondCasting_str:String;
				switch(true){
					case(vFirstArg_str.charAt(0) == '"'):{
						vFirstCasting_str = "string";
						break;
					}
					case(isNaN(vFirstArg_str) == false):{
						vFirstCasting_str = "number";
						break;
					}
					case(vFirstArg_str.substr(0,5) == "PARSE"):{
						vFirstCasting_str = "number";
						break;
					}
					default:{
						vFirstCasting_str = "string";
					}
				}
				switch(true){
					case(vSecondArg_str.charAt(0) == '"'):{
						vSecondCasting_str = "string";
						break;
					}
					case(isNaN(vSecondArg_str) == false):{
						vSecondCasting_str = "number";
						break;
					}
					case(vSecondArg_str.substr(0,5) == "PARSE"):{
						vSecondCasting_str = "number";
						break;
					}
					default:{
						vSecondCasting_str = "string";
					}
				}
	//		vNewExpression_str = vNewExpression_str + resolveComplex(vToResolve_str,widgetsRepository_array);
				trace("CPLX --  resolve Complex BEFORE vFirstArg_str with " + vFirstArg_str);
				vFirstArg_str = getTokenComplex(resolvePart(vFirstArg_str));
				trace("CPLX --  resolve Complex AFTER vFirstArg_str with " + vFirstArg_str);
				trace("CPLX --  resolve Complex BEFORE vSecondArg_str with " + vSecondArg_str);
				vSecondArg_str = getTokenComplex(resolvePart(vSecondArg_str));
				trace("CPLX --  resolve Complex AFTER vSecondArg_str with " + vSecondArg_str);
			}
			vCurrentTarget_str = String(targetPointers_array.pop());
			trace("CPLX >>  resolve Complex with " + vCurrentTarget_str);
			if((vCurrentTarget_str == "MATCH") || (scoreType_str == "MATCH")){
				if(((vFirstCasting_str == "string") ? String(vFirstArg_str):parseFloat(vFirstArg_str)) == ((vSecondCasting_str == "string") ? String(vSecondArg_str):parseFloat(vSecondArg_str))){
					trace("CPLX ... resolve Complex MATCH arguments are the same: (" + vFirstCasting_str + ") " + vFirstArg_str + " == (" + vSecondCasting_str + ") " + vSecondArg_str);
					vNewExpression_str = "true";
				}
				else{
					trace("CPLX ... resolve Complex MATCH arguments are different: (" + vFirstCasting_str + ") " + vFirstArg_str + " != (" + vSecondCasting_str + ") " + vSecondArg_str);
					vNewExpression_str = "false";
				}
				if(currentChar_str == '$'){
					vNewExpression_str = (vNewExpression_str == "false") ? "true" : "false";
				}
			}
			if(vCurrentTarget_str == "INTERVAL"){
				var interval_str:String = my_toolbox.trimString(vSecondArg_str,true,true);
				var vLeftBoundMarker_str:String = interval_str.charAt(0);
				var vRightBoundMarker_str:String = interval_str.substr(-1,1);
				var vLeftBound_str:String = interval_str.substr(1,interval_str.indexOf(","));
				var vRightBound_str:String = interval_str.substr(interval_str.indexOf(",") + 1);
				trace("CPLX ... resolve Complex INTERVAL arguments are: (" + vFirstCasting_str + ") " + vFirstArg_str + " vs (" + vSecondCasting_str + ") " + vSecondArg_str);
				if(vFirstCasting_str == "string"){
					// text interval - not handled yet
					trace("CPLX  !  text intervals are not handled");
				}
				else{
					var vFirstArg_num:Number = parseFloat(vFirstArg_str);
					var vLeftBound_num:Number = parseFloat(vLeftBound_str);
					var vRightBound_num:Number = parseFloat(vRightBound_str);
					if(isNaN(vFirstArg_num) || isNaN(vLeftBound_num) || isNaN(vRightBound_num)){
						vNewExpression_str = "false";
					}
					else{
						switch(true){
							case((vLeftBoundMarker_str == "[") && (vRightBoundMarker_str == "]")):{
								trace("CPLX  !  case 1 with '" + vLeftBoundMarker_str + "' and '" + vRightBoundMarker_str + "'");
								if((vFirstArg_num >= vLeftBound_num) && (vFirstArg_num <= vRightBound_num)){
									vNewExpression_str = "true";
								}
								else{
									vNewExpression_str = "false";
								}
								break;
							}
							case((vLeftBoundMarker_str == "[") && (vRightBoundMarker_str == "[")):{
								trace("CPLX  !  case 2 with '" + vLeftBoundMarker_str + "' and '" + vRightBoundMarker_str + "'");
								if((vFirstArg_num >= vLeftBound_num) && (vFirstArg_num < vRightBound_num)){
									vNewExpression_str = "true";
								}
								else{
									vNewExpression_str = "false";
								}
								break;
							}
							case((vLeftBoundMarker_str == "]") && (vRightBoundMarker_str == "]")):{
								trace("CPLX  !  case 3 with '" + vLeftBoundMarker_str + "' and '" + vRightBoundMarker_str + "'");
								if((vFirstArg_num > vLeftBound_num) && (vFirstArg_num <= vRightBound_num)){
									vNewExpression_str = "true";
								}
								else{
									vNewExpression_str = "false";
								}
								break;
							}
							case((vLeftBoundMarker_str == "]") && (vRightBoundMarker_str == "[")):{
								trace("CPLX  !  case 4 with '" + vLeftBoundMarker_str + "' and '" + vRightBoundMarker_str + "'");
								if((vFirstArg_num > vLeftBound_num) && (vFirstArg_num < vRightBound_num)){
									vNewExpression_str = "true";
								}
								else{
									vNewExpression_str = "false";
								}
								break;
							}
							default:{
								trace("CPLX  !  intervals bounds are not handled: '" + vLeftBoundMarker_str + "' and '" + vRightBoundMarker_str + "'");
							}
						}
					}
					trace("CPLX  !  vNewExpression_str is '" + vNewExpression_str + "'");
				}
			}
		}
		else{
			trace("CPLX ... resolve Complex vNewExpression_str: " + vNewExpression_str + " and vNextMatch_num: " + vNextMatch_num);
			vNewExpression_str += workString_str.substr(vNextIdx_num);
		}
		trace("CPLX OUT resolveComplex with " + vNewExpression_str);
		return vNewExpression_str;
	}
}
