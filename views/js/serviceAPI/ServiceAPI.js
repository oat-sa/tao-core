function ServiceApi(){
	this.server = null;
}

ServiceApi.prototype.connect = function(frame){
	frame.contentWindow.serviceApi = this;
	if (typeof(frame.contentWindow.onReady) == "function") {
		frame.contentWindow.onReady();
	}
}

//Context
ServiceApi.prototype.getServiceCallId = function(){
	return 1;
}

// Variables 
ServiceApi.prototype.getVariable = function(identifier){
}

// Flow
ServiceApi.prototype.finish(valueArray) {
	if (valueArray != null && typeof(valueArray) == 'object') {
		//store the values
	}
	//return execution to service caller
};