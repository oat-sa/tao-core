/**
 * EventListener Object for DragController Events
 * @author   Alessandro Crugnola, Nicolas Zeh
 * @version  1.0.0 [revised version of Alessandro Crugnola's DraggableController component version 1.0 - Event class]
 **/

class it.sephiroth.DragEvent extends Object  {
	
	public var type;
	public var target;
	public var sourceComponent;
	public var targetComponent;
	public var sourceItem;
	public var sourceIndex;
	public var sourceItems;
	public var sourceIndices;
	public var targetItem;
	public var targetIndex;
	
	private var isSkipped:Boolean;
	private var isVetoed:Boolean;
	private var isAdded:Boolean;
	private var isRemoved:Boolean;
	
	function DragEvent( base ){
		target 		= base;
	}

	/**
	 * will veto dropping the sourceItem. i.e. simply moves dragging clip back to its origin position
	 * @usage   
	 * @return Void 
	 */
	public function veto():Void{
		if(!isVetoed && !isSkipped){
			target.onDropVeto( this );
		}
		isVetoed = true;
	}

	/**
	 * will skip dropping the sourceItem. i.e. simply removes dragging clip
	 * @usage   
	 * @return Void 
	 */
	public function skip():Void{
		if(!isVetoed && !isSkipped){
			target.onDropSkip( this );
		}
		isSkipped = true;
	}

	/**
	 * will check the component type of a component
	 * e.g. evt.checkType( evt.sourceComponent, 'Tree' ); 
	 * will return:
	 * true if evt.sourceComponent is a Tree component
	 * false if evt.sourceComponent is NOT a Tree component
	 * @usage   
	 * @param Object obj the component
	 * @param String type
	 * @return Number 
	 */
	public function checkType( obj, type ):Boolean{
		var o = obj.className ? obj.className : typeof( obj );
		return ( o.toLowerCase() == type.toLowerCase() );
	}
	
	/**
	 * will add the sourceItem to the targetComponent at targetIndex
	 * with the mappings object you can map properties of the sourceItem to properties of the targetItem
	 * e.g.: if sourceComponent is a DataGrid you can set with mappings which column of the DataGrid will be used as label for the targetComponent
	 * @usage   
	 * @param Object mappings
	 * @return Void 
	 */
	public function addItem( mappings ):Boolean{
		return target.onAddItems( this, mappings, false );
	}
	
	public function addItems( mappings ):Boolean{
		return target.onAddItems( this, mappings, true );
	}
	
	/**
	 * will remove the sourceItem from the sourceComponent
	 * @usage   
	 * @return Void 
	 */
	public function removeItem():Boolean{
		return target.onRemoveItem( this, false );
	}
	
	public function removeItems():Boolean{
		return target.onRemoveItem( this, true );
	}

	/**
	 * will add the sourceItem to the targetComponent at targetIndex and remove the sourceItem from the sourceComponent
	 * mappings: see addItem
	 * @usage   
	 * @param Object mappings
	 * @return Void 
	 */
	public function transferItem( mappings ):Boolean{
		return target.onTransferItems( this, mappings, false );
	}

	public function transferItems( mappings ):Boolean{
		return target.onTransferItems( this, mappings, true );
	}

}