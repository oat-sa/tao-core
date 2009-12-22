// ##### imports of libraries
import mx.managers.*
import mx.services.*;
import mx.controls.*;
import it.sephiroth.XML2Object;

// ##### definition and initialization of some variables and components
//fscommand ("fullscreen", "true");
//fscommand ("showmenu", "false");
//fscommand ("allowscale", "false");

// error window not visible for the moment; may we never need it!
error_mc._visible = false;

// debug status enabled or not, last error message and source frame of the error
//debugMe = "on";
debugMe = "off";

// result encryption
encryptResult = "off";

// to avoid weakness on Flash UI security
UILocker = "on";

//errorMsg = "";
errorSrcFrame = 1;

// connected user
connectedUserName = "";
connectedUserFct = "";
// def of the session_ID and language for the connected user
sessionID = "";
// def of the language for the connected user's GUI
sessionLang = "";
// def of the language for the connected user's data
sessionDataLang = "";

// the type of the current module
//moduleType = "Subject";
if((pMD == undefined) || (pMD == "")){
	pMD = "item";
	dbType = "item";
//	pMD = "mysubjectmodule";
//	dbType = "subject";
};
if(maxSendResult == undefined){
	maxSendResult = 100;
}
// for automated testing of long MCQ
robotTesting = false;
if((automatedTesting != undefined) && (automatedTesting == "on")){
	robotTesting = true;
}
//robotTesting = true;

endTestConfirmation_bool = false;
if((endTestConfirm == undefined) || (endTestConfirm == "on") || (endTestConfirm == "yes") || (endTestConfirm == "1")){
	endTestConfirmation_bool = true;
}

if(_root.packetsize == undefined){
	_root.packetsize = 60000; // defines the size of the returned packets
}

/*
_root.fullscreen = "on";
if(_root.fullscreen != undefined){
	if(_root.fullscreen == "on"){
		Stage.scaleMode = "noScale"; //["showAll", "exactFit", "noBorder", "noScale"]
		fscommand("fullscreen", true);
	}
}
*/

if(_root.noConfirm == "1"){
	_root.confirmationNeeded_bool = false;
}
else{
	_root.confirmationNeeded_bool = true;
}


if(_root.taoIP == undefined){
	_root.taoIP = "127.0.0.1";
}

// specify the WSDL URL
var wsdlURI;
var wsdlURI2;
if(_root.wsdlurl==undefined){
	wsdlURI = "http://localhost/generis/wsdl_contract/tao_res.wsdl.php";
}
else{
	var tmpWsdlUrl_str:String = _root.wsdlurl;
	if(tmpWsdlUrl_str.substr(0,7) == "http://"){
		wsdlURI = _root.wsdlurl;
	}
	else{
		wsdlURI = "http://" + _root.taoIP + _root.wsdlurl;
	}
}
if(_root.wsdlurl2==undefined){
	wsdlURI2 = undefined;
}
else{
	var tmpWsdlUrl2_str:String = _root.wsdlurl2;
	if(tmpWsdlUrl2_str.substr(0,7) == "http://"){
		wsdlURI2 = _root.wsdlurl2;
	}
	else{
		wsdlURI2 = "http://" + _root.taoIP + _root.wsdlurl2;
	}
}

// creates a new log object
TAOwsLog = new Log("DEBUG"); // DEBUG gives full details

// double-click laps
dblClickMax = 500;

// for IRT scoring with threshold
lastTheta_num = 0;
finalScore_str = "";
cllThreshold1_str = "";
cllThreshold2_str = "";
cllThreshold3_str = "";

var followingTest_str:String;
var uploadResultsNow_bool:Boolean;
var callAddStatementNow_bool:Boolean;

if(_root.followup != undefined){
	var tmpFollowUp_str:String = unescape(_root.followup);
	if(tmpFollowUp_str.substr(0,7) == "http://"){
		followingTest_str = tmpFollowUp_str;
	}
	else{
		followingTest_str = "http://" + _root.taoIP + tmpFollowUp_str;
	}
}

if(uploadResults != undefined){
	uploadResultsNow_bool = (uploadResults == "0") ? false : true;
	callAddStatementNow_bool = (uploadResults == "2") ? true : false;
}
else{
	uploadResultsNow_bool = true;
}

// skin path of the application
//currentSkinPath = "./tao_skins/tangerine_dream/";


// TODO Could be good idea to load all that stuff from an XML file
