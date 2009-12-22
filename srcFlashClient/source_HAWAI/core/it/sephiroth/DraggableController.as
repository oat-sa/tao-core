import it.sephiroth.Event;
import mx.core.UIComponent;
import mx.events.EventDispatcher;
import flash.display.BitmapData;
import mx.transitions.Tween;
import mx.transitions.easing.*


[InspectableList("dataProvider")]

/**
 *
 * @author   Alessandro Crugnola
 * @version  1.0
 **/
class it.sephiroth.DraggableController extends UIComponent  {
	
	public var addEventListener:Function
	public var removeEventListener:Function
	
	private var _dataProvider:Object
	private var _controller  :MovieClip
	private var _foo         :MovieClip
	
	private var _icon       :MovieClip
	private var _mouse_icon :MovieClip
	private var _bmp        :BitmapData
	private var dispatchEvent:Function
	
	function DraggableController(){
		EventDispatcher.initialize(this)
		
		_dataProvider = new Object()
		_controller   = this.createEmptyMovieClip("_controller", 1)
		_controller.target = this
		_foo = this.createEmptyObject("_foo", 2)
		init_controller();
		
		
		trace("myDRAG ok");
		
	}
	
	/**
	 * Add a reference between one component to another
	 * Both must be valid components
	 * @usage   
	 * @param   from_mc 
	 * @param   dest_mc 
	 * @return  
	 */
	public function AddReference(from_mc:MovieClip, dest_mc:MovieClip):Void{
		if(!(from_mc instanceof mx.core.UIComponent) or !(dest_mc instanceof mx.core.UIComponent)){
			return;
		}
		if( this.dataProvider[from_mc] != undefined ){
			for(var a:Number = 0; a < this.dataProvider[from_mc].length; a++){
				if(this.dataProvider[from_mc][a] == dest_mc){
					return
				}
			}
			this.dataProvider[from_mc].push(dest_mc)
		} else {
			this.dataProvider[from_mc] = [dest_mc]
			from_mc.addEventListener("itemRollOver", _controller)
			from_mc.addEventListener("itemRollOut",  _controller)
		}
	}
	
	/**
	 * Remove a reference from a component to another
	 * @usage   
	 * @param   from_mc 
	 * @param   dest_mc 
	 * @return  
	 */
	public function RemoveReference(from_mc:MovieClip, dest_mc:MovieClip):Void{
		if( this.dataProvider[from_mc] != undefined){
			for(var a:Number = 0; a < this.dataProvider[from_mc].length; a++){
				if(this.dataProvider[from_mc][a] == dest_mc){
					this.dataProvider[from_mc].splice(a, 1)
					return
				}
			}
		}
	}
	
	/**
	 * Return the dataProvider index of controlled components of a 
	 * given component
	 * @usage   
	 * @param   source 
	 * @return  
	 */
	public function GetSourceControl(source:MovieClip):Number{
		for(var a:Number = 0; a < dataProvider.length; a++){
			if(dataProvider[a].from == source){
				return a
			}
		}
		return -1
	}
	
	/**
	 * Set the references between components
	 * @usage   controller.dataProvider = [{from:myList, to:[ADataGrid, AnotherList]}, {from...}]
	 * @param   dp 
	 * @return  
	 */
	public function set dataProvider(dp:Object):Void{
		_dataProvider = dp
	}

	public function get dataProvider():Object{
		return _dataProvider
	}

	private function _GetSourceItem(index:Number):MovieClip{
		var a:Number
		var lastPosition:Number = 0
		var item:MovieClip
		if( _controller.source_mc ){
			lastPosition = _controller.source_mc.lastPosition || 0
			return _controller.source_mc.rows[index-lastPosition]
		}
	}

	private function _CreateDraggingIcon(item:MovieClip, dest:MovieClip):Void{
		_icon         = this.createEmptyMovieClip("_icon", 10)
		_icon.target  = this
		_icon._alpha  = 60
		_bmp = new BitmapData(item._width,item._height, true, 0xFFFFFF)
		_icon.attachBitmap(_bmp, 1)
		_bmp.draw(item)
		_icon.startDrag(true)
	}
	
	
	private function _AllowMouseIcon(value:Boolean):Void{
		if(value){
			if(_mouse_icon){
				_mouse_icon.removeMovieClip()
				_mouse_icon = undefined
				Mouse.show()
			}
		} else {
			if(_mouse_icon == undefined){
				_mouse_icon = this.createEmptyMovieClip("_mouse_icon", 20)
				_mouse_icon.attachMovie("icon_deny_drag", "_mouse_icon", 1)
				_mouse_icon.onMouseMove = function(){
					this._x = this._parent._xmouse
					this._y = this._parent._ymouse
					updateAfterEvent()
				}
				Mouse.hide()
			}			
		}
	}
	

	private function init_controller():Void{
		_controller.selectedIndex   = undefined
		_controller.selectedItem    = undefined
		
		_controller.source_mc     = undefined
		_controller.drag_start    = false		// can start dragging item
		_controller.__added       = false
		_controller.__index       = undefined
		_controller.__item        = undefined
		_controller.__points      = undefined
		_controller.target_mc     = undefined
		
		_controller.itemRollOver = function(evt:Object):Void{
			var item:Object = evt.target.getItemAt(evt.index)
			if(item != undefined){
				this.source_mc     = evt.target
				this.selectedItem  = evt.target.getItemAt(evt.index)
				this.selectedIndex = evt.index
			} else {
				this.itemRollOut()
			}
		}
		
		_controller.itemRollOut = function(evt:Object):Void{
			this.selectedItem  = undefined
			this.selectedIndex = undefined
		}
		
		_controller.selectDelay = function(scope:MovieClip){
			scope.__item   = scope.selectedItem
			scope.__index  = scope.selectedIndex
		}
		
		_controller.onMouseDown = function(){
			this.drag_start   = false
			this.__added      = false
			this.__item       = undefined
			this.__index      = undefined
			this.target_index = undefined	// destination index
			this.target_item  = undefined
			this.target_mc    = undefined
			
			_global.setTimeout(this.selectDelay, 200, this)
			this.__points = new Array( this._xmouse, this._ymouse);
			if(this.source_mc == undefined){
				return;
			}
			this.onEnterFrame = function(){
				var show_mouse:Boolean = false
				var source_item:MovieClip
				var currentFocus:Object = this._parent._GetFocusManager().getFocus()
				this.target_mc = currentFocus
				var point:Object = new Object()
				var item_mc:MovieClip


				point.x = this._parent._xmouse
				point.y = this._parent._ymouse
				this._parent.localToGlobal( point )
				this._parent._ClearItemUpSideDownBorders();
				
				this.target_index = undefined
				this.target_item  = undefined
				
				if(!this.__added && this.__index != undefined){
					var x = this._xmouse
					var y = this._ymouse
					if(Math.abs( x  - this.__points[0] ) > 2 or Math.abs( y - this.__points[1] ) > 2 ){
						if(!this.__added and this.__item != undefined ){
							source_item = this._parent._GetSourceItem(this.__index)
							this._parent._CreateDraggingIcon(source_item)
							clearInterval(this.source_mc.dragScrolling);
							this.source_mc.clearSelected(false)
							this.drag_start = true
							this.__added    = true
							this._parent.dispatchEvent({type:"drag_start"});
						}
					}
				} else if(this.__added && this.__index != undefined){
					var dest_mc:MovieClip = this._parent.dataProvider[this.source_mc]
					for(var a in dest_mc){
						if(dest_mc[a].hitTest(point.x, point.y, true)){
							show_mouse = true
							if(dest_mc[a] != currentFocus){
								this._parent._ActivateFocus(dest_mc[a], true)
							}
							if(dest_mc[a].className == "TextInput" or dest_mc[a].className == "TextArea"){
								this.target_index = -1
								this.target_item  = -1
							} else {
								this.target_index = dest_mc[a].selectedIndex
								this.target_item  = dest_mc[a].selectedItem
							}
							item_mc = this._parent._GetItemFromIndex(dest_mc[a], this.target_index)
							if(item_mc){
								this.target_index += this._parent._DrawItemUpSideDownBorders(item_mc)
							}
						} else {
							if(dest_mc[a] == currentFocus){
								this._parent._ActivateFocus(dest_mc[a], false)
							}
						}
					}
					this._parent._AllowMouseIcon(show_mouse)
				}
			}
		}
		
		_controller.onMouseUp = function(){
			stopDrag()
			var source_item = this._parent._GetSourceItem(this.__index)
			if(source_item){
				if( this.target_index != undefined){
					
					var evt:Event = new Event()
					evt.addListener(this._parent)
					
					evt.type = "drag_complete"
					evt.target = this._parent
					evt.source_component = this.source_mc
					evt.target_component = this._parent._GetFocusManager().getFocus()
					evt.source_item      = this.__item
					evt.source_index     = this.__index
					evt.target_index     = this.target_index
					evt.target_item      = this.target_item
					
					this.source_mc.clearSelected(false)
					this._parent.dispatchEvent(evt)
				} else {
					var gb:Object = source_item.getBounds(this)
					new Tween(this._parent._icon, "_x", Regular.easeOut, this._parent._icon._x, gb.xMin, .3, true)
					var tw:Tween = new Tween(this._parent._icon, "_y", Regular.easeOut, this._parent._icon._y, gb.yMin, .3, true)
					tw.onMotionFinished = function(evt:Object){
						evt.obj._parent._ReleaseMouse()
					}					
				}
			} else {
				this._parent._ReleaseMouse()
			}
			
			
			this.selectedItem  = undefined
			this.selectedIndex = undefined
			this.target_index  = undefined
			this.target_item   = undefined
			
			this._parent._DeleteFocus(this.source_mc)
			this._parent._ClearItemUpSideDownBorders()
			this._parent._AllowMouseIcon(true)
			
			this.source_mc = undefined
			delete this.onEnterFrame
		}
		
	}
	
	
	private function _DeleteFocus(source:MovieClip):Void{
		if(source){
			var items = this.dataProvider[source]
			for(var a in items){
				items[a].drawFocus(0)
				items[a].clearSelected(false)
			}
			// TODO: Da verificare
			//_GetFocusManager().setFocus(null)
		}
	}
	
	private function _ActivateFocus(mc:Object, value:Boolean):Void{
		if(value){
			_GetFocusManager().setFocus(mc)
			mc.pressFocus()
			mc.drawFocus(1)
			if(mc.selectedIndex == undefined){
				mc.onRowPress(0)
			}
		} else {
			mc.clearSelected(false)
			clearInterval(mc.dragScrolling);
		}
	}
	
	
	private function _GetItemFromIndex(source:MovieClip, index:Number):MovieClip{
		var lastPosition:Number = source.lastPosition
		if(!lastPosition) lastPosition = 0
		if((source.className == "DataGrid") or (source.className == "List")){
			return source.rows[index-lastPosition]
		} else if(source.className == "Tree"){
			return source.rows[index-lastPosition]
		}
		return null
	}
	
	private function GetFocus(Void):Object{
		var selFocus:String = Selection.getFocus();
		return (selFocus === null ? null : eval(selFocus));
	}

	function _GetFocusManager(Void):Object{
		var o:MovieClip = this;
		while (o != undefined)
		{
			if (o.focusManager != undefined){
				return o.focusManager;
			}
			o = o._parent;
		}
		return undefined;
	}	

	private function _ReleaseMouse(Void):Void{
		_bmp.dispose()
		_icon.removeMovieClip()
	}
	
	
	private function _DrawItemUpSideDownBorders(item:MovieClip):Number{
		var gb:Object   = item.getBounds(this)
		var gb_2:Object = item.getBounds(item.owner)
		
		if(item.owner.className == "Tree"){
			return 0
		}
		
		if(gb_2.yMax < item.owner._height){
			_foo.beginFill(0x000000, 50)
			_foo.drawRect(1, 0, item.owner.width - item.owner.vSB.width-3, 1)
			_foo.endFill();
			_foo._x = gb.xMin
		}

		if( item._ymouse > ((item.bG_mc._height/2) + item.bG_mc._height/8)){
			_foo._y = gb.yMin + item._height
			return +1
		} else if (item._ymouse < ((item.bG_mc._height/2) - item.bG_mc._height/8)){
			_foo._y = gb.yMin
			return 0
		}
		return 0
	}
	
	
	private function _ClearItemUpSideDownBorders(Void):Void{
		_foo.clear();
	}
	
	
	/**
	 * If Drag Event is Vetoed, restore source item position
	 * @usage   
	 * @param   evt 
	 * @return  
	 */
	public function onVeto(evt:Object):Void{
		var source_item = this._GetSourceItem(evt.source_index)
		var gb:Object = source_item.getBounds(this)
		new Tween(this._icon, "_x", Regular.easeOut, this._icon._x, gb.xMin, .3, true)
		var tw:Tween = new Tween(this._icon, "_y", Regular.easeOut, this._icon._y, gb.yMin, .3, true)
		tw.onMotionFinished = function(evt:Object){
			evt.obj._parent._ReleaseMouse()
		}
	}
	
	
	private function onSkip():Void{
		this._ReleaseMouse()
	}
	
}