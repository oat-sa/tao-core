import com.eXULiS.XUL.XULelement;
import mx.utils.Delegate;

class com.eXULiS.XUL.XULkeyset extends XULelement {
	
	private var _obj:MovieClip;
	var oKey;
	var mykeys;
	var keyCount;
	var specialsKeys;
	// modifiers
	var	zemodifiers_ar=new Array();
	
	function XULkeyset(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
	}
	
	function create(){
		trace("XULkeyset (create): image (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createEmptyMovieClip(_objDef.attributes["id"],_objParent._childNextDepth);
		//_objParent.createClassObject(mx.controls.Button,_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		initListener();
		return _obj;
	}
	
	function addCombinationKey(node)
	{		
		//ascii
		var _key=(node.attributes["key"]!=undefined) ? node.attributes["key"] : "" ;
		_key=(isNaN(_key.charCodeAt(0))) ? "" :  _key.charCodeAt(0) ;
		// code
		var _keycode=(node.attributes["keycode"]!=undefined) ? node.attributes["keycode"] : "" ;
		var _modifiers=(node.attributes["modifiers"]!=undefined) ? node.attributes["modifiers"] : "" ;
		var _action = (node.attributes["oncommand"]!=undefined) ? node.attributes["oncommand"] : "" ;
		var _zemodifiers_ar=new Array();
		if (_modifiers!="")
			{
				_zemodifiers_ar=_modifiers.split(" ");
			}

			if (_zemodifiers_ar.length>0)
			{
				for (var i in _zemodifiers_ar)
				{

					_zemodifiers_ar[i]=getCodeFromVirtualCode(_zemodifiers_ar[i]);
				}
			}
			// add New Object();
			var _combo_str="combo"+keyCount;
			mykeys[_combo_str]={modifiers:_zemodifiers_ar,key:_key,keycode:_keycode,action:_action};
			keyCount++;
		}
	
	function initListener(){
		oKey=new Object();
		mykeys=new Object();
		// modifiers
		zemodifiers_ar=new Array();
		keyCount=0;
		specialsKeys=new Object();
		KeyMatches();
		oKey.onKeyDown=Delegate.create(this, onKeyPress);
		Key.addListener(oKey);
	}
	
	function KeyMatches()
	{
		specialsKeys.accel=specialsKeys.accell=specialsKeys.ctrl=specialsKeys.meta=Key.CONTROL;
		specialsKeys.shift=Key.SHIFT;
		specialsKeys.alt=Key.ALT;
		specialsKeys.VK_CANCEL=Key.ESCAPE;
		specialsKeys.VK_BACK=Key.BACKSPACE;
		specialsKeys.VK_TAB=Key.TAB;
		specialsKeys.VK_CLEAR="";
		specialsKeys.VK_RETURN=Key.ENTER;
		specialsKeys.VK_ENTER=Key.ENTER;
		specialsKeys.VK_SHIFT=Key.SHIFT;
		specialsKeys.VK_CONTROL=Key.CONTROL;
		specialsKeys.VK_ALT=Key.ALT;
		specialsKeys.VK_PAUSE="";
		specialsKeys.VK_CAPS_LOCK=Key.CAPSLOCK;
		specialsKeys.VK_ESCAPE=Key.ESCAPE;
		specialsKeys.VK_SPACE=Key.SPACE;
		specialsKeys.VK_PAGE_UP=Key.PGUP;
		specialsKeys.VK_PAGE_DOWN=Key.PGDN;
		specialsKeys.VK_END=Key.END;
		specialsKeys.VK_HOME=Key.HOME;
		specialsKeys.VK_LEFT=Key.LEFT;
		specialsKeys.VK_UP=Key.UP;
		specialsKeys.VK_RIGHT=Key.RIGHT
		specialsKeys.VK_DOWN=Key.DOWN;
		specialsKeys.VK_PRINTSCREEN="";
		specialsKeys.VK_INSERT=Key.INSERT;
		specialsKeys.VK_DELETE=Key.DELETEKEY;
		specialsKeys.VK_NUM_LOCK="";
		specialsKeys.VK_SCROLL_LOCK="";
		specialsKeys.VK_COMMA="";
		specialsKeys.VK_PERIOD="";
		specialsKeys.VK_SLASH="";
		specialsKeys.VK_BACK_QUOTE="";
		specialsKeys.VK_OPEN_BRACKET="";
		specialsKeys.VK_BACK_SLASH="";
		specialsKeys.VK_CLOSE_BRACKET="";
		specialsKeys.VK_QUOTE="";
	}
	
	function onKeyPress()
	{
		
		trace("doACTION PRESSED "+Key.getAscii()+" | "+Key.getCode());
		
		trace("feedTrace for KEYPRESS, Stimulus: " + "ASCII" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+Key.getAscii()+ _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "code" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+Key.getCode());
		_level0.currentItemRootLevel.feedTrace("KEYPRESS","ASCII"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+Key.getAscii() + _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "code"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+Key.getCode(),"stimulus");
	
		for (var a in mykeys)
			{
				// ascii and no modifier
				if (mykeys[a].key!="" && mykeys[a].keycode=="" && mykeys[a].modifiers.length==0)
				{
					trace("doACTION cas 1");
					if (Key.getAscii()==mykeys[a].key)
					{
						trace("doACTION "+mykeys[a].action);
						toolbox.wrapRun(mykeys[a].action,this);
						
						trace("feedTrace for DOACTION, Stimulus: " + "action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action);
						_level0.currentItemRootLevel.feedTrace("DOACTION","action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action,"stimulus");
						
						return;
					}
				}
				
				// ascii and modifiers
				if (mykeys[a].key!="" && mykeys[a].keycode=="" && mykeys[a].modifiers.length>0)
				{
					trace("doACTION cas 2");
					if (Key.getAscii()==mykeys[a].key)
					{
						var task1=true;
					}

					for (var z in mykeys[a].modifiers)
					{
						var pressedModifiers_ar=new Array();

						for (var e in specialsKeys)
						{
							if(Key.isDown(specialsKeys[e])==true)
							{
								pressedModifiers_ar.push(specialsKeys[e]);
							}
						}
					}
					var _toTest=dedloub(pressedModifiers_ar);
					_toTest.sort();
					mykeys[a].modifiers.sort();
					var task2=isArrayEqual(_toTest,mykeys[a].modifiers);

					if (task1 & task2)
					{
						//do acion
						trace("doACTION "+mykeys[a].action);
						toolbox.wrapRun(mykeys[a].action,this);
						
						trace("feedTrace for DOACTION, Stimulus: " + "action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action);
						_level0.currentItemRootLevel.feedTrace("DOACTION","action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action,"stimulus");
						return;
					}

				}

				// code and modifiers

				if (mykeys[a].keycode!="" && mykeys[a].key=="" && mykeys[a].modifiers.length==0)
				{
					trace("doACTION cas 3");
					if (Key.getCode()==Number(mykeys[a].keycode))
					{
						trace("doACTION "+mykeys[a].action);
						toolbox.wrapRun(mykeys[a].action,this);
						
						trace("feedTrace for DOACTION, Stimulus: " + "action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action);
						_level0.currentItemRootLevel.feedTrace("DOACTION","action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action,"stimulus");
						return;
					}
				}
				
				// code and modifiers

				if (mykeys[a].keycode!="" && mykeys[a].key=="" && mykeys[a].modifiers.length>0)
				{
					trace("doACTION cas 3");
					if (Key.getCode()==mykeys[a].keycode)
					{
						var taskCode1=true;
					}



					for (var z in mykeys[a].modifiers)
					{
						var pressedModifiers_ar=new Array();

						for (var e in specialsKeys)
						{
							if(Key.isDown(specialsKeys[e])==true)
							{
								pressedModifiers_ar.push(specialsKeys[e]);
							}
						}
					}

					// no duplicate values on pressed keys array
					var _toTest=dedloub(pressedModifiers_ar);

					_toTest.sort();
					mykeys[a].modifiers.sort();

					trace("|: >>"+_toTest+" |:"+mykeys[a].modifiers);

					var taskCode2=isArrayEqual(_toTest,mykeys[a].modifiers);

					if (taskCode1 & taskCode2)
					{
						//do acion
						trace("doACTION "+mykeys[a].action);
						toolbox.wrapRun(mykeys[a].action,this);
						
						trace("feedTrace for DOACTION, Stimulus: " + "action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action);
						_level0.currentItemRootLevel.feedTrace("DOACTION","action" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + mykeys[a].action,"stimulus");
						return;
					}
				}
			}
	}
	
	
	private function isArrayEqual(tab1,tab2){

		var i = tab1.length;
		if (i != tab2.length) return false;
		while(i--) 
		{
			if (tab1[i] != tab2[i]) 
			{	
			return false;
			}
		}
		return true;
	}

	private function getCodeFromVirtualCode(virtual_str)
	{
		for (var z in specialsKeys)
		{
			if (z==virtual_str)
			{
				return specialsKeys[z];
			}
		}
	}


	function dedloub (array_ar) {
	var obj={},i=array_ar.length,arr=[],t;while(i--)t=array_ar[i],obj[t]=t;for(i in obj)arr.push(obj[i]);return arr;
	};
	
	
	function destroy(){
		trace("XULkeyset (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
