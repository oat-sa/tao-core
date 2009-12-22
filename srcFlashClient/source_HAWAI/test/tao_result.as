
var NbCookieToUpload;
var utilisateur;

function initRes ()
{
	// We create an instance of result by ginig : rdfid, rdfs_Label, rdfs_Comment
	res = new result.Cresult ("http://www.tao.lu/middleware/moduledetest.rdf#TEST", "Test de geographie", "Ce test a pour but d'evaluer les connaissances de l'etudiant sur les supports et les outils lies a la geographie");

	// for the demonstration we implemant this object with semples values
	// You can find this methodes in the file : /Result/CResult.as
	res.JeuDeTest ();
	return res;
}

function recordCookies(res){
	trace("entering recordCookies");
	var export_str:String;
	var workArray_array:Array = new Array();
	var startItemTraceMarker_str:String = escape("*S*ITEMTRACE*");
	var endItemTraceMarker_str:String = escape("*E*ITEMTRACE*");

	export_str = res.toSting();
	workArray_array = export_str.split(endItemTraceMarker_str);
	export_str = "";

	var vCpt_so_num:Number = 0;
	var preBlock_str:String = "";
	var preBlockEnd_num:Number = 0;
	var targetTraceFileName_str:String = "";
	var trace_str:String = "";
	var maxCpt_num:Number = workArray_array.length - 1;
	var user_t_recovery;
	var vCpt_num:Number;

	for(vCpt_num=0;vCpt_num<maxCpt_num;vCpt_num++){
		trace_str = "";
		vCpt_so_num = 0;
		preBlock_str = String(workArray_array[vCpt_num]);
		preBlockEnd_num = preBlock_str.lastIndexOf(startItemTraceMarker_str);
		targetTraceFileName_str = preBlock_str.substr(-1 * (preBlock_str.length - (preBlockEnd_num + startItemTraceMarker_str.length)));
		targetTraceFileName_str = unescape(targetTraceFileName_str);
		preBlock_str = preBlock_str.substr(0,preBlockEnd_num);
		user_t_recovery = SharedObject.getLocal("/" + targetTraceFileName_str + "_p" + String(vCpt_so_num), "/");
		trace("explored file : " + targetTraceFileName_str + "_p" + String(vCpt_so_num));
		while(user_t_recovery.data.tracedEvents_array != undefined) {
			var tmpEvents_str:String = "";
			tmpEvents_str = String(user_t_recovery.data.tracedEvents_array);
			trace_str += tmpEvents_str;
			delete user_t_recovery.data.tracedEvents_array;
			user_t_recovery.flush();
			user_t_recovery.clear();

			vCpt_so_num++;
			user_t_recovery = SharedObject.getLocal("/" + targetTraceFileName_str + "_p" + String(vCpt_so_num), "/");
		}
		delete user_t_recovery;
		export_str += preBlock_str + escape(trace_str);
	}
	export_str += String(workArray_array[vCpt_num]);
	trace("export_str = " + export_str);
	//delete maxCpt_num;

	subjectName = res.subject.rdfs_Label;

	// We need to know how many files we have to upload
	// so in toupload.sol there is a value (NbCookieToUpload)
	// NbCookieToUpload grow when we added a file and back to 0 after an upload
	NbCookieToUpload = SharedObject.getLocal("/" + "toupload_" + _level0.subjectNameWithoutSpace,"/");

	if (NbCookieToUpload.data.nb == undefined)
		NbCookieToUpload.data.nb = 0;
	else
		NbCookieToUpload.data.nb ++;

	NbCookieToUpload.flush();
	var i = NbCookieToUpload.data.nb;

	// now the data : first we need to know the date it will be record like YYYY_MM_DD_HH_MM
	var ExportDate = new Date();
	var dat = ExportDate.getFullYear().toString() + "_" + ExportDate.getMonth().toString() + "_" + ExportDate.getDate().toString() + "_" + ExportDate.getHours().toString() + "_" + ExportDate.getMinutes().toString();
	// we record in a new file tese+i in order to don't erase informations wich
	// are not yet uploaded on the server
	utilisateur = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_" + i,"/");

	// We save the name of the subject
	utilisateur.data.nom = subjectName;
trace("recordCookies subjectName:" + subjectName);
	// the rdf data
	utilisateur.data.rdf = export_str;

	// and the date of the test
	utilisateur.data.dat = dat;
	utilisateur.flush();
}

function encrypt (export)
{
	////ENCRYPT
	if (_root.encryptResult == "on")
	{
		myText = export;
		passWord = "incoul";
		resulta = "";
		code = 0;
		//ord String function; converts characters to ASCII code numbers.
		for (charCount = 1; charCount <= (passWord.length); charCount ++)
		{
			code += ord (substring (passWord, charCount, 1));
		}
		for (charCount = 1; charCount <= (myText.length); charCount ++)
		{
			encChar = (ord (substring (myText, charCount, 1)) + code);
			resulta += encChar + "-";
		}
		Output = substring (resulta, 1, (resulta.length) - 1);
	}
	else
	{
		Output = export;
	}
	return Output;
}
function decrypt (Output)
{
	//// DECRYPT
	if (_root.encryptResult == "on")
	{
		myText = Output;
		resulta = "";
		code = 0;
		for (charCount = 1; charCount <= (passWord.length); charCount ++)
		{
			code += ord (substring (passWord, charCount, 1));
		}
		myArray = myText.split ("-");
		for (count = 0; count <= (myArray.length - 1); count ++)
		{
			resulta += chr (myArray [count] - code);
		}
	}
	else
	{
		resulta = Output;
	}
	return resulta;
}
function isConnected ()
{
	var dmcXMl = new XML ();
	dmcXMl.load ("http://www.google.com/");
	//trace (dmcXMl.loaded);
	dmcXMl.onLoad = function (s, o)
	{
		//trace("onload: "+dmcXMl.loaded);
		myVar = dmcXMl.loaded;
	}
}
// identifies one result session
var idResult = new String ();
var vDate = new Date ();
var vYear = vDate.getFullYear().toString();
var vMonth = String(vDate.getMonth() + 1);
var vDay = vDate.getDate().toString();
var vHours = vDate.getHours().toString();
var vMinutes = vDate.getMinutes().toString();
var vSec = vDate.getSeconds().toString();
var vIP_str:String;
var myToolbox:tao_toolbox = new tao_toolbox();

if(_root.taoIP == undefined){
	vIP_str = "127_0_0_1";
}
else{
	vIP_str = myToolbox.replaceString(_root.taoIP,".","_");
}

idResult = "TAO_" + escape(_root.pLabel_str) + "_" + vIP_str + "_" + vYear + vMonth + vDay + vHours + vMinutes + vSec + "_GT" + String(_level0.getTestGlobalTimer()) + "_Rand" + Math.round(Math.random() * 1000000000);
_root._currentResultFileName = idResult + ".xml";
var partsCpt = 0;
var partsOkCpt = 0;
var partsTot = 0;
var retryCounter_num = 0;
var resultXML_str = new String();
var partsToTransfer_array = new Array();
var backupResult = "";
var pcSetResult;
var pcAddStatement;
var resultUploadIntervalId:Number;

// this function will catch the result of the sendResult method call
function resultSentResult (result)
{
//	trace("resultSentResult Response: " + pcSetResult.response);
//	traceSOAPcall (this);
	trace("resultSentResult -> result = " + result);
//	var sessionResult =  result[0].childNodes[0].childNodes[0].nodeValue; // not reliable
	var sessionResult = String(result);
	sessionResult = myToolbox.extractString(sessionResult,"<soapVal xsi:type=\"xsd:string\">","</soapVal>",0,false);
	trace("resultSentResult -> sessionResult = " + sessionResult);
	if (sessionResult != "")
	{
		var workString = new String (sessionResult);
		workString = workString.toUpperCase ();
		partsCpt ++;
		if (workString.indexOf ("ERROR") == -1)
		{
			partsOkCpt ++;
//		trace("partsOkCpt is now: " + partsOkCpt);
			retryCounter_num = 0;
		}
		else
		{
//		showError (sessionResult);
		}
	}
	sendPart(partsToTransfer_array [partsCpt]);
//	traceSOAPcall (this);
/*
	var sessionResult = result [0].childNodes [0].childNodes [0].childNodes [0].childNodes [0].nodeValue;
	var workString = new String (sessionResult);
	workString = workString.toUpperCase ();
	partsCpt ++;
	if (workString.indexOf ("ERROR") == -1)
	{
		partsOkCpt ++;
//		trace("partsOkCpt is now: " + partsOkCpt);
		retryCounter_num = 0;
	}
	else
	{
//		showError (sessionResult);
	}
	sendPart (partsToTransfer_array [partsCpt]);
*/
}
// in case of error on the send result method call
function resultSentError (fault)
{
	trace("------------------------------------------");
	trace("resultSentError Response: " + pcSetResult.response);
	trace("resultSentError Fault: ");
	for(var faultNam in fault){
		trace("fault." + faultNam + " = " + fault[faultNam]);
	}
	trace("------------------------------------------");
//	traceSOAPcall (this);
//	traceVal (fault.faultstring, "fault.faultstring");
//	_level0.finalStatus_lbl.text = "Attente lors de l'envoi des résultats!";
//	showError (fault.faultstring);
	partsCpt ++;
	sendPart (partsToTransfer_array [partsCpt]);
}
function finishCleanUp ()
{
	trace("finishCleanUp entered");
	var partsOkCpt_str:String;
	var partsTot_str:String;
	var statusParts_str:String;
	partsTot_str = String(partsTot + 4);
	for (var i = 0; i <= NbCookieToUpload.data.nb; i ++)
	{
//		var so = SharedObject.getLocal("/" + subjectName + i)
		var so = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_" + i,"/")
		for (var j in so.data)
		{
			delete so.data [j];
		}
		so.flush ();
	}
	partsOkCpt_str = String(partsOkCpt++);
	statusParts_str = partsOkCpt_str + "/" + partsTot_str;
	_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadUpdateStatus", statusParts_str);
	for (var j in NbCookieToUpload.data)
	{
		delete NbCookieToUpload.data [j];
	}
	NbCookieToUpload.flush ();
	smallCleanup();
}
function smallCleanup(){
	var partsOkCpt_str:String;
	var partsTot_str:String;
	var statusParts_str:String;
	var user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_r", "/");
	trace("finishCleanUp user_recovery = " + user_recovery + " and _level0.subjectNameWithoutSpace = " + _level0.subjectNameWithoutSpace);
	for (var j in user_recovery.data)
	{
		trace("finishCleanUp delete for " + j);
		delete user_recovery.data[j];
	}
	user_recovery.flush ();
	partsOkCpt_str = String(partsOkCpt++);
	statusParts_str = partsOkCpt_str + "/" + partsTot_str;
	_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadUpdateStatus", statusParts_str);

	var vCpt_so_num:Number;
	vCpt_so_num = 0;
	user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_c" + String(vCpt_so_num), "/");
	while(user_recovery.data.itemContext != undefined) {
		for (var j in user_recovery.data)
		{
			delete user_recovery.data[j];
		}
		user_recovery.flush ();
		vCpt_so_num++;
		user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_c" + String(vCpt_so_num), "/");
	}
	vCpt_so_num = 0;
	user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_c" + String(vCpt_so_num), "/");
	while(user_recovery.data.contextIndex != undefined) {
		for (var j in user_recovery.data)
		{
			delete user_recovery.data[j];
		}
		user_recovery.flush ();
		vCpt_so_num++;
		user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_c" + String(vCpt_so_num), "/");
	}
	partsOkCpt_str = String(partsOkCpt++);
	statusParts_str = partsOkCpt_str + "/" + partsTot_str;
	_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadUpdateStatus", statusParts_str);
	vCpt_so_num = 0;
	user_recovery = SharedObject.getLocal("/" + _level0.subjectNameWithoutSpace + "_l", "/");
	if(user_recovery.data.itemByLangTime != undefined) {
		for (var j in user_recovery.data)
		{
			delete user_recovery.data[j];
		}
		user_recovery.flush();
	}

	var globalVars:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
	for (var j in globalVars.data){
		delete globalVars.data[j];
	}
	globalVars.flush();
	var ICTScore:SharedObject = SharedObject.getLocal("/" + "ICTScore","/");
	for (var j in ICTScore.data){
		delete ICTScore.data[j];
	}
	ICTScore.flush();
	statusParts_str = partsTot_str + "/" + partsTot_str;
	_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadUpdateStatus", statusParts_str);

//	_level0.finalStatus_lbl.text = "Results sent OK!";

	_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadCompleted", "");

	_global.setTimeout(finalOverlay, 2000); // to let a chance all the sharedObjects to be deleted
}

function finalOverlay(){
	if((_root.breakoffUrl != undefined) && _root.breakOffNow_bool){
		getURL(unescape(_root.breakoffUrl),"_top"); // in case of breakoff	
	}
	else{ // followupUrl special Younes
		if((followingTest_str != undefined) && (followingTest_str != "")){
			if((_root.unit != undefined) && (_root.unit != "")){
				var unit_str:String = String(_root.unit);
				unit_str = unit_str.substr(1);
				unit_str = "?unit=../PST/Unit" + unit_str + ".php&currentFile=" + _level0._currentResultFileName;
				getURL(followingTest_str + unit_str,"_self");
			}
			else{ // normal followupUrl
				getURL(followingTest_str,"_self");
			}
		}	
	}	
}

function sendPart(vPartsCpt){
//	trace("partsCpt: " + vPartsCpt + " on " + partsTot);
	if (vPartsCpt != -1)
	{
//		_level0.result_pb.setProgress (partsOkCpt, partsTot - 1);
		var partsOkCpt_str:String;
		var partsTot_str:String;
		var statusParts_str:String;
		partsOkCpt_str = String(partsOkCpt);
		partsTot_str = String(partsTot + 4);
		statusParts_str = partsOkCpt_str + "/" + partsTot_str;
		_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadUpdateStatus", statusParts_str);
		var pcktSize = _root.packetsize;
		var part_array = new Array ();
		var idResult_array = new Array ();
		var partsCpt_array = new Array ();
		var partsTot_array = new Array ();
		if (((vPartsCpt + 1) * pcktSize) > resultXML_str.length)
		{
			part_array.push (resultXML_str.substring (vPartsCpt * pcktSize));
		}
		else
		{
			part_array.push (resultXML_str.substring (vPartsCpt * pcktSize, (vPartsCpt + 1) * pcktSize));
		}
//		trace ("part_array[0]: " + part_array.toString ());
		idResult_array [0] = idResult;
		partsCpt_array [0] = vPartsCpt;
		// param: the packet to be sent
		partsTot_array [0] = partsTot;
		pcSetResult = TAOService.setResult (part_array, idResult_array, partsCpt_array, partsTot_array);
		pcSetResult.onFault = _level0.resultSentError;
		pcSetResult.onResult = _level0.resultSentResult;
		pcSetResult.doDecoding = false;
		pcSetResult.doLazyDecoding = false;
	}
	else
	{
		postSynchronization ();
	}
}

// this function will catch the result of the postsynchro transfer function
function isFullyOkResult (result)
{
//	traceSOAPcall (this);
	trace("isFullyOkResult -> result = " + result);
//	var sessionResult =  result[0].childNodes[0].childNodes[0].nodeValue; // not reliable
	var sessionResult = String(result);
	sessionResult = myToolbox.extractString(sessionResult,"<soapVal xsi:type=\"xsd:string\">","</soapVal>",0,false);
	if (sessionResult != "")
	{
		var workString = new String (sessionResult);
		trace ("PostSynchro result: " + workString);
		partsCpt = 0;
		var postSynchroResult_array = new Array ();
		postSynchroResult_array = workString.split (";");
		var errorMsg_str = new String (postSynchroResult_array [0]);
		errorMsg_str = errorMsg_str.toUpperCase ();
		if (errorMsg_str.indexOf ("ERROR") == -1)
		{
			finishCleanUp ();
		}
		else
		{
			retryCounter_num = 0;
			var errorCode = postSynchroResult_array [1];
			var nbPartsToUpload = 0;
			if (postSynchroResult_array [2] != undefined)
			{
				nbPartsToUpload = Number (postSynchroResult_array [2]);
				for (var i = 0; i < nbPartsToUpload; i ++)
				{
					partsToTransfer_array [i] = postSynchroResult_array [i + 2];
				}
				partsToTransfer_array [nbPartsToUpload] = - 1;
				sendPart (partsToTransfer_array [0]);
			}
		}
	}
	else
	{
		retrySynchronization ();
	}
}
// in case of error on the transfer postsynchro call
function isFullyOkError (fault)
{
//	traceSOAPcall (this);
//	traceVal (fault.faultstring, "fault.faultstring");
//	_level0.finalStatus_lbl.text = "Problème temporaire lors de l'envoi des résultats!";
//	showError (fault.faultstring);
	retrySynchronization ();
}
function retrySynchronization ()
{
	clearInterval(resultUploadIntervalId);
	// _root.maxSendResult times consecutive before abend
	retryCounter_num ++;
	trace ("PostSynchro failed " + retryCounter_num + " time(s)");
	if (retryCounter_num <= _root.maxSendResult)
	{
		resultUploadIntervalId = setInterval(_root, "postSynchronization", 5000 + Math.round(Math.random() * 10000));
	}
	else
	{
//		_level0.finalStatus_lbl.text = "ERREUR DEFINITIVE lors de l'envoi des résultats!";
		_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadFailed", "");
	}
}
function postSynchronization(){
	trace ("PostSynchro triggered");
	var idResult_array = new Array ();
	var partsTot_array = new Array ();
	idResult_array [0] = idResult;
	partsTot_array [0] = partsTot;
	pcIsFullyOk = TAOService.isFullyOk (idResult_array, partsTot_array);
	pcIsFullyOk.onFault = _level0.isFullyOkError;
	pcIsFullyOk.onResult = _level0.isFullyOkResult;
	pcIsFullyOk.doDecoding = false;
	pcIsFullyOk.doLazyDecoding = false;
}

// this function will catch the result of the addStatement method call
function addStatementResult(result){
	trace("addStatementResult -> result = " + result);
	trace("addStatementError Response: " + pcAddStatement.response);
}
// in case of error on the addStatement method call
function addStatementError (fault){
	trace("------------------------------------------");
	trace("addStatementError Response: " + pcAddStatement.response);
	trace("addStatementError Fault: ");
	for(var faultNam in fault){
		trace("fault." + faultNam + " = " + fault[faultNam]);
	}
	trace("------------------------------------------");
}

function callAddStatementNow(){
	var subject_str:String;
	var predicate_str:String;
	var object_str:String;
	var subjectPart_array = new Array();
	var predicatePart_array = new Array();
	var objectPart_array = new Array();
	subject_str = unescape(_root.pSubject_str);
	predicate_str = unescape(_root.pPredicate_str);
	var ICTScore:SharedObject = SharedObject.getLocal("/" + "ICTScore","/");
	var ictScore_num:Number = 0;
	ictScore_num += (isNaN(ICTScore.data.uic02)) ? 0 : Number(ICTScore.data.uic02);
	ictScore_num += (isNaN(ICTScore.data.uic03)) ? 0 : Number(ICTScore.data.uic03);
	ictScore_num += (isNaN(ICTScore.data.uic04)) ? 0 : Number(ICTScore.data.uic04);
	ictScore_num += (isNaN(ICTScore.data.uic05)) ? 0 : Number(ICTScore.data.uic05);
	if(isNaN(ICTScore.data.uic06)){
		ictScore_num *= 0.1;
	}
	else{
		ictScore_num += Number(ICTScore.data.uic06);
	}
	object_str = String(ictScore_num);
	subjectPart_array[0] = subject_str;
	predicatePart_array[0] = predicate_str;
	objectPart_array[0] = object_str;
//	pcAddStatement = statementService.addStatement(subjectPart_array, predicatePart_array, objectPart_array); // special NuSOAP
	pcAddStatement = statementService.addStatement(subject_str, predicate_str, objectPart_array);
	pcAddStatement.onFault = _level0.addStatementError;
	pcAddStatement.onResult = _level0.addStatementResult;
	pcAddStatement.doDecoding = false;
	pcAddStatement.doLazyDecoding = false;
}

function sendXML(res){
//	_root.isWSDLok = true;
	if((noresult != undefined) && (noresult == "1")){
		finishCleanUp();
	}
	else{
		if(uploadResultsNow_bool){
			if(_root.callAddStatementNow_bool){
				callAddStatementNow();
			}
			if (_root.isWSDLok){
				var subjectNameWoSpace;
				tete = res.header;
				pied = res.footer;
				subjectName = res.subject.rdfs_Label;
		//		a = subjectName.split (" ");
		//		subjectNameWoSpace = a.join ("");
				subjectNameWoSpace = _level0.subjectNameWithoutSpace;
				var monxml = new String (tete);
				var pcktSize = _root.packetsize;
				var previous_rr = "";
				var fullSize = 0;
				trace ("before SO loop");
				NbCookieToUpload = SharedObject.getLocal("/" + "toupload_" + subjectNameWoSpace,"/");
				if (NbCookieToUpload.data.nb != undefined){
					for (var i = 0; i <= NbCookieToUpload.data.nb; i ++){
						utilisateur = SharedObject.getLocal("/" + subjectNameWoSpace + "_" + i,"/");
		//				var rr = decrypt (utilisateur.data.rdf);
						var rr = utilisateur.data.rdf;
						if(rr != previous_rr){
							monxml += rr + "\n";
						}
						previous_rr = rr;
					}
					monxml += pied;
					// now, we try to handle the problem of Flash frozen with returned xml slicing
					fullSize = monxml.length;
					partsTot = Math.floor (fullSize / pcktSize) + 1;
					for (var i = 0; i < partsTot; i++){
						partsToTransfer_array.push(i);
					}
					partsToTransfer_array.push(-1);
					resultXML_str = monxml;
					trace ("fullsize = " + fullSize);
					trace ("monxml ===> " + monxml);
					sendPart (partsToTransfer_array [0]);
				}
				else{
					trace("NbCookieToUpload.data.nb == undefined !");
				}
			}
			else{
		//		_level0.finalStatus_text = "WSDL inaccessible: tentative de connexion en cours";
				backupResult = res;
				tryToGetAConnection();
			}
		}
		else{
			smallCleanup();
/*
			_level0.aBroadcaster.dispatchXulEvent(this_mc,"uploadCompleted", "");
			if((followingTest_str != undefined) && (followingTest_str != "")){
				if((_level0.unit != undefined) && (_level0.unit != "")){
					var unit_str:String = String(_level0.unit);
					unit_str = unit_str.substr(1);
					unit_str = "?unit=../PST/Unit" + unit_str + ".php&currentFile=" + _level0._currentResultFileName;
//					getURL(followingTest_str + unit_str,"_self");
					getURL(followingTest_str + unit_str,"_top");
				}
				else{
//					getURL(followingTest_str,"_self");					
					getURL(followingTest_str,"_top");					
				}
			}
*/
		}
	}
}

function WSRetryLoaded (wsdlDocument){
	trace ("WSDL finally Ok");
	_root.isWSDLok = true;
	_root.sendXML (_root.backupResult);
}
// receives focus after WSDL loading fault
function WSRetryFault(fault){
	trace ("WSDL not accessible");
	if (debugMe == "on"){
		trace (fault.faultstring);
	}
	_root.isWSDLok = false;
	_root.tryToGetAConnection ();
}
function tryToGetAConnection(){
	// try to create a new web service object
	_root.TAOService = new WebService(_root.wsdlURI, _root.TAOwsLog);
	_root.TAOService.onLoad = _root.WSRetryLoaded;
	_root.TAOService.onFault = _root.WSRetryFault;
}
