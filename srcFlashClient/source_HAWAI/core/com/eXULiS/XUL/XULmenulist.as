import com.eXULiS.XUL.XULelement;
import mx.controls.ComboBox;
import mx.utils.Delegate;

class com.eXULiS.XUL.XULmenulist extends XULelement {
	var _obj:mx.controls.ComboBox;
//	var _dataProvider:XML;
	var _dataProvider:Array;

	function XULmenulist(xulParent,xulDef:XMLNode) {
		super(xulParent,xulDef);
		trace("XULmenulist: relay given to XULelement with xulParent = " + xulParent);
	}
	function create(){
		trace("XULmenulist: create menulist (" + _objDef.attributes["id"] + ") on " + _objParent + " on depth: " + _objParent._childNextDepth);
		_objParent.createClassObject(mx.controls.ComboBox,_objDef.attributes["id"],_objParent._childNextDepth);
		_obj = super.create(_obj,this,1);
		trace("XULmenulist: special properties setting for " + id + ": top:" + top + " left:" + left + " width:" + width + " height:" + height);
		_dataProvider = new Array();
//		_dataProvider_xml = new XML();

		_obj = super.applyStyle(_obj);
		_obj.dataProvider = _dataProvider;
		var vRowCount_num:Number = parseInt(_objDef.attributes["rowCount"]);
		if(!isNaN(vRowCount_num)){
			_obj.rowCount = vRowCount_num;
		}
			if (_objDef.attributes["onfocus"])
			{
				_obj.onSetFocus = Delegate.create(this, onComboEvent);
			}
			
		
			
			//if (_objDef.attributes["storeresult"]=="true")
			//{
				var listenerObject:Object = new Object();
				listenerObject.change = Delegate.create(this, onComboChange);
				_obj.addEventListener("change", listenerObject);
			//}
		setLayout();

/*
		var tMenuEvent_listener = new Object();
        tMenuEvent_listener.change = function (eventObj){
            trace("XULmenulist: Menu item chosen: " + eventObj.menuItem.attributes["label"]);
		}
		_obj.addEventListener("change", tMenuEvent_listener);
*/
		return _obj;
	}
/*
	function attachDataProvider(vDP_obj){
		trace("XULmenulist: (attachDataProvider) for " + id + ": " + vDP_obj);
		_dataProvider_xml = vDP_obj;
	}
*/
	function onComboChange()
	{
		var globalVars:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
		globalVars.data[_objDef.attributes["id"]] = _obj.selectedIndex;
		
		trace("myDATA "+globalVars.data[_objDef.attributes["id"]]);
		
		var event_str:String = "COMBOBOX";
		var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_objDef.attributes["id"] +
		_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_obj.selectedIndex;
		trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
		_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // COMBOBOX
		
		globalVars.flush();
	}

	function onComboEvent(){

		toolbox.wrapRun(_objDef.attributes["onfocus"],this);		
		_obj.close();
	}
	
	function addMenuItem(menulistInitObj_obj:Object){
		trace("XULmenulist: add a menu item (" + removeHTML(menulistInitObj_obj["label"]) + ") on " + _obj);
		this._dataProvider.addItem({label:removeHTML(menulistInitObj_obj["label"]), data:menulistInitObj_obj["instanceName"]});
		if(menulistInitObj_obj["selected"] != undefined){
			if(menulistInitObj_obj["selected"]){
				this._obj.selectedIndex = this._dataProvider.length - 1;
			}			
		}
			if (_objDef.attributes["selectable"]=="false")
			{
				_obj.enabled = false;
			}
	}
	
	function removeHTML(zeString) {  
	    var temp = "" 
	    var s; 
	    while((s=zeString.indexOf("<"))!=-1) { 
	        temp += zeString.substr(0,s); 
	        zeString = zeString.substr(zeString.indexOf(">")+1); 
	    }  
	    return temp+zeString;  
	};
	
	
	
	function setLayout(){
		trace("XULmenulist: (setLayout) for " + id + ": special properties setting, top:" + this.top + " left:" + this.left + " width:" + this.width + " height:" + this.height);
        _obj.move(this.left,this.top);
        _obj.setSize(this.width,this.height);
	}
	function destroy(){
		trace("XULmenulist: (destroy) for " + id + ": " + _objDescendants.length + " children to be deleted");
		for(var vCpt=0;vCpt < _objDescendants.length;vCpt++){
			_objDescendants[vCpt]._exulis.destroy();
		}
		this.removeMovieClip();
	}
}
