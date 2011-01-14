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
			return instance.single.eventTarget.bind(eventType, callback);
		}
		instance.single.trigger = function(eventType, params){
			instance.single.eventTarget.trigger(eventType, params);
		}
		instance.single.unbind = function(eventType, params){
			return instance.single.eventTarget.unbind(eventType, params);
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
	return new EventMgr().bind(eventType, callback);
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

/**
 * 
 * @param eventType
 * @param params
 * @return
 */
EventMgr.unbind = function(eventType, params){
	return new EventMgr().unbind(eventType);
}

EventMgr.unbindAll = function(params){
	return new EventMgr().unbind();
}