/**
 * The EventMgr class enable you to manage event trought an high level layer.
 * It helps you to attach events and the associated callback to trig them.
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * 
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * 
 */
EventMgr = function() {
	
	//save the instance
	instance = this.constructor;
	
	//this part is loaded only the first call
	if(instance.single == undefined){
		instance.single = this;
		instance.single.eventTarget = $(document);
		
		instance.single.bind = function(eventType, callback){
			instance.single.eventTarget.bind(eventType, callback);
		}
		instance.single.trigger = function(eventType, params){
			instance.single.eventTarget.trigger(eventType, params);
		}
	}
	else{
		return instance.single;
	}
}
/**
 * 
 * @param eventType
 * @param callback
 * @return
 */
EventMgr.bind = function(eventType, callback){
	new EventMgr().bind(eventType, callback);
}

/**
 * 
 * @param eventType
 * @param params
 * @return
 */
EventMgr.trigger = function(eventType, params){
	new EventMgr().trigger(eventType, params);
}