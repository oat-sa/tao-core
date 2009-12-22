// ##### imports of libraries
import mx.services.*;
//import mx.data.binding.*;
import lu.tao.utils.tao_toolbox;
import com.xfactorstudio.xml.xpath.*;

class lu.tao.taoWS.taoWS{
	public var base_mc:MovieClip;
	private var itemDescFile_str:String;
	private var wsLog:mx.services.Log;
	private var localWSDLok:Boolean;
	private var pcTaoWS;
	private var xulWS;
	private var wsdlURI;
	private var xulServiceName;
	private var xulServiceArgIN_array:Array;
	private var xulServiceArgOUT_array:Array;

	function taoWS(target_mc:MovieClip){
		// load the parameters
		base_mc = target_mc;
		trace("taoWS init with " + base_mc);
	}

// WS log
	private function wsFct(txt){
		trace(txt);
	}

// receives the WSDL document after loading succeeded
	public function wsdlLoaded(wsdlDocument){
		trace("taoWS local WSDL file OK!");
		localWSDLok = true;
	}

// receives focus after WSDL loading fault
	public function wsdlFault(fault){
		trace("taoWS " + fault.faultstring);
		localWSDLok = false;
	}

// receives reults after a successful WS call
/*
oncommand=
'!{
	WS(
		{http://127.0.0.1/generis/wsdl_contract/taoSQL.wsdl.php},
		{submitSQL},
		{GETVALUE(pProposition=proposal_textbox),
		 GETVALUE(pSubmission=submited_textbox)},
		{SETVALUE(result_textbox=pResult),
		 SETVALUE(other_textbox=pReturnCode)}
	  )
}!'
*/
	public function wsCallResult(result){
		trace("taoWS data received! i.e. " + pcTaoWS.response);
		for(var i in result) {
			trace("Result " + i + ": " + result[i]);
		}
		for(var vCpt1=0;vCpt1<xulServiceArgOUT_array.length;vCpt1++){
			var aSetObj:Object = xulServiceArgOUT_array[vCpt1];
			trace("xulServiceArgOUT_array[vCpt1].target: " + aSetObj.target);
			for(var vCpt2=0;vCpt2<base_mc._targetExecutionLayer._widgetsRepository_array.length;vCpt2++){
				var aWidget_obj:Object = base_mc._targetExecutionLayer._widgetsRepository_array[vCpt2];
				if(aWidget_obj.xulID == aSetObj.target){
					var xpathQuery_str:String = "//" + aSetObj.provider + "/text()";
					var returnVal_str = XPath.selectSingleNode(pcTaoWS.response,xpathQuery_str);
					aWidget_obj.objRef.text = "<FONT FACE='_typewriter' SIZE='11'>" + unescape(returnVal_str) + "</FONT>";
				}
			}
		}
	}

// receives focus after a WS fault
	public function wsCallError(fault){
		trace("taoWS no data: " + fault.faultstring + " i.e. " + pcTaoWS.response);
	}

// creates a new web service object
/*
oncommand=
'!{
	WS(
		{http://127.0.0.1/generis/wsdl_contract/taoSQL.wsdl.php},
		{submitSQL},
		{GETVALUE(pProposition=proposal_textbox),
		 GETVALUE(pSubmission=submited_textbox)},
		{SETVALUE(result_textbox=pResult),
		 SETVALUE(other_textbox=pReturnCode)}
	  )
}!'
*/
	public function buildWS(){
		wsLog = new Log("DEBUG");
		wsLog.onLog = wsFct;
		var workString:String = new String();
		workString = base_mc.onCommand;
//		workString = "oncommand='!{WS({http://127.0.0.1/generis/wsdl_contract/taoSQL.wsdl.php},{submitSQL},{GETVALUE(/xul/box[@id=**inquiryContainer_box**]/box[@id=**proposal_box**]/textbox),GETVALUE(/xul/box[@id=**inquiryContainer_box**]/box[@id=**proposal_box**]/textbox)},{SETVALUE(/xul/box[@id=**compileResult_box**]/textbox[@id=**compilResult_textbox**])})}!'";
		var baseResult:String = new String();
		var finalResult:String = new String();
		var my_toolbox:tao_toolbox = new tao_toolbox();
		var workArray = new Array();
		workString = my_toolbox.extractString(workString,"!{WS(",")}!",0,false);
		trace("initial WS command = " + workString);
		workArray = workString.split(",{");
		if(workArray.length == 3){ // values 3 and 4 are OK
			var lastComma:Number;
			var tmpArg1_str:String = new String(workArray[0]);
			lastComma = tmpArg1_str.lastIndexOf(",");
			wsdlURI = tmpArg1_str.substr(0,lastComma);
			wsdlURI = my_toolbox.extractString(wsdlURI,"{","}",0,false);
			xulServiceName = tmpArg1_str.substr(lastComma + 1);
			xulServiceName = my_toolbox.extractString(xulServiceName,"{","}",0,false);
			trace("wsdlURI: " + wsdlURI);
			trace("xulServiceName: " + xulServiceName);
		}
		else{
			if(workArray.length == 4){
				wsdlURI = String(workArray[0]);
				wsdlURI = my_toolbox.extractString(wsdlURI,"{","}",0,false);
				xulServiceName = String(workArray[1]);
				xulServiceName = my_toolbox.extractString(xulServiceName,"{","}",0,false);
				trace("wsdlURI: " + wsdlURI);
				trace("xulServiceName: " + xulServiceName);
			}
			// ELSE others are invalid
		}
		if((workArray.length == 3) || (workArray.length == 4)){ // Arg. format is valid
			var xulServiceArgIN_str:String;
			var xulServiceArgOUT_str:String;
			xulServiceArgIN_array = new Array();
			xulServiceArgOUT_array = new Array();
			xulServiceArgIN_str = workArray[workArray.length - 2];
			xulServiceArgOUT_str = workArray[workArray.length - 1];
			xulServiceArgIN_str = my_toolbox.extractString(xulServiceArgIN_str,"{","}",0,false);
			xulServiceArgOUT_str = my_toolbox.extractString(xulServiceArgOUT_str,"{","}",0,false);
			xulServiceArgIN_array = xulServiceArgIN_str.split(",GETVALUE(");
			xulServiceArgOUT_array = xulServiceArgOUT_str.split(",SETVALUE(");
			for(var vCpt=0;vCpt<xulServiceArgIN_array.length;vCpt++){
				var aGetValArg_str:String;
				aGetValArg_str = xulServiceArgIN_array[vCpt];
				aGetValArg_str = my_toolbox.extractString(aGetValArg_str,"GETVALUE(",")",0,false);
				var targetVal;
				var providerVal;
				targetVal = my_toolbox.extractString(aGetValArg_str,"","=",0,false);
				providerVal = my_toolbox.extractString(aGetValArg_str,"=","",0,false);
				var aGetObj:Object = {target:targetVal, provider:providerVal};
				xulServiceArgIN_array[vCpt] = aGetObj;
				trace("aGetValArg_str: " + aGetValArg_str + "/" + targetVal + "/" + providerVal);
			}
			for(var vCpt=0;vCpt<xulServiceArgOUT_array.length;vCpt++){
				var aSetValArg_str:String;
				aSetValArg_str = xulServiceArgOUT_array[vCpt];
				aSetValArg_str = my_toolbox.extractString(aSetValArg_str,"SETVALUE(",")",0,false);
				var targetVal;
				var providerVal;
				targetVal = my_toolbox.extractString(aSetValArg_str,"","=",0,false);
				providerVal = my_toolbox.extractString(aSetValArg_str,"=","",0,false);
				var aSetObj:Object = {target:targetVal, provider:providerVal};
				xulServiceArgOUT_array[vCpt] = aSetObj;
				trace("aSetValArg_str: " + aSetValArg_str + "/" + targetVal + "/" + providerVal);
			}
		}
		if((wsdlURI != undefined) && (xulServiceName != undefined)){
			xulWS = new WebService(wsdlURI, wsLog);
			xulWS.onLoad = wsdlLoaded;
			xulWS.onFault = wsdlFault;			
		}
	}

/*
oncommand=
'!{
	WS(
		{http://127.0.0.1/generis/wsdl_contract/taoSQL.wsdl.php},
		{submitSQL},
		{GETVALUE(pProposition=proposal_textbox),
		 GETVALUE(pSubmission=submited_textbox)},
		{SETVALUE(result_textbox=pResult),
		 SETVALUE(other_textbox=pReturnCode)}
	  )
}!'
*/
	public function activateWS(){
		trace("taoWS activateWS entered");
		var argToPass_array:Array = new Array();

		// param: the packet to be sent
		trace("xulServiceArgIN_array.length: " + xulServiceArgIN_array.length);
		for(var vCpt1=0;vCpt1<xulServiceArgIN_array.length;vCpt1++){
			var aGetObj:Object = xulServiceArgIN_array[vCpt1];
			trace("xulServiceArgIN_array[vCpt1].provider: " + aGetObj.provider);
			for(var vCpt2=0;vCpt2<base_mc._targetExecutionLayer._widgetsRepository_array.length;vCpt2++){
				var aWidget_obj:Object = base_mc._targetExecutionLayer._widgetsRepository_array[vCpt2];
				if(aWidget_obj.xulID == aGetObj.provider){
					var argForCall_str:String;
					if(aWidget_obj.objRef.html == true){
						aWidget_obj.objRef.html = false;
						argForCall_str = aWidget_obj.objRef.text;
						aWidget_obj.objRef.html = true;
					}
					else{
						argForCall_str = aWidget_obj.objRef.text;
					}
					argToPass_array.push(argForCall_str);
				}
			}
		}
/*
		var objPart_obj:Object = new Object();
		var fullCmd_str:String;
		fullCmd_str = "xulWS" + xulServiceName;
		var fctPart_fct:Function = eval(fullCmd_str);
		pcTaoWS = fctPart_fct.apply(objPart_obj,argToPass_array);
*/

		pcTaoWS = xulWS[xulServiceName](argToPass_array);

		pcTaoWS.onResult = mx.utils.Delegate.create(this,wsCallResult);
		pcTaoWS.onFault = mx.utils.Delegate.create(this,wsCallError);
		pcTaoWS.doDecoding = false;
		pcTaoWS.doLazyDecoding = false;
	}
}
