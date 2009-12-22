// Class
import mx.events.EventDispatcher;
class com.eXULiS.lib.Event extends mx.core.UIObject {

 	private static var eventDispatcherInitialized:Boolean = false;

	// stuff from EventDispatcher
	var createEvent:Function;
	var dispatchEvent:Function;
	var addEventListener:Function;
	var handleEvent:Function;
	var removeEventListener:Function;

	function Event(){
		if(!eventDispatcherInitialized){
			eventDispatcherInitialized = true;
			EventDispatcher.initialize(Event.prototype);
			trace("init");
		}
		trace("constructor");
	}

	function dispatchXulEvent(targetComponent:String,triggeredEvent:String) {
		dispatchEvent({type:"xulEvent", subtype:triggeredEvent, target:targetComponent});
	}

	function toString():String {
		return "[Event]";
	}

}
