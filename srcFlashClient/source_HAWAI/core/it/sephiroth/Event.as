/**
 *
 * @author
 * @version
 **/

class it.sephiroth.Event extends Object  {
	
	public var addListener:Function
	public var removeListener:Function
	public var broadcastMessage:Function
	
	public var type
	public var target
	public var source_component
	public var target_component
	public var source_item
	public var source_index
	public var target_index
	public var target_item
	
	private var isSkipped:Boolean
	private var isVetoed :Boolean
	
	function Event(){
		AsBroadcaster.initialize(this)
	}
	
	public function Veto():Void{
		if(!isSkipped){
			this.broadcastMessage("onVeto", this)
		}
		isVetoed = true
	}
	
	public function Skip():Void{
		if(!isVetoed){
			this.broadcastMessage("onSkip", this)
		}
		isSkipped = true
	}

}