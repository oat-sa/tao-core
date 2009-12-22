// WS log
TAOwsLog.onLog = function(txt){
	if (_root.debugMe == "on"){
		trace(txt);
	}
}

// receives the WSDL document after loading succeeded
function WSLoaded(wsdlDocument){
trace("OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO");
	if (_root.debugMe == "on"){
		trace("WSDL file OK!");
	}
	_root.isWSDLok = true;
}

// receives focus after WSDL loading fault
function WSFault(fault){
	trace("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");

//	if (debugMe == "on"){
	    trace(fault.faultstring);
//	}
	errorSrcFrame = 1;
	showError(fault.faultstring);
	_root.isWSDLok = false;
	stop();
}

// creates a new web service object
var TAOService;
var statementService;

if(_root.wsdlURI != undefined){
	_root.TAOService = new WebService(_root.wsdlURI, _root.TAOwsLog);
	_root.TAOService.onLoad = _root.WSLoaded;
	_root.TAOService.onFault = _root.WSFault;
}
if(_root.wsdlURI2 != undefined){
	_root.statementService = new WebService(_root.wsdlURI2, _root.TAOwsLog);
	_root.statementService.onLoad = _root.WSLoaded;
	_root.statementService.onFault = _root.WSFault;
}

