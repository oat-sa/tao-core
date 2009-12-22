//import mx.controls.*;
import lu.tao.utils.Event;
import com.eXULiS.lib.defsRepository;
import com.eXULiS.lib.Toolbox;
//import xpath.*;
import com.eXULiS.XBL2.*;
//import XML2Object;

/**
* XBL 2 SWF
* @author Raynald Jadoul
* @description Translates XBL syntax in Flash native components
* @usage data = new XBL2Flash().parseXML(anXML);

*/
class com.eXULiS.XBL2.XBL2Flash extends XML {
    private var oResult:Object = new Object ();
    private var oXML:XML;
	private var canvas_mc:MovieClip;
	private var xblDefsRepository_obj:defsRepository;
/**
* * Constructor
* * initializes the canvas movie clip
*/
	function XBL2Flash(target_mc:MovieClip) {
		canvas_mc = target_mc;
//		trace("XBL2Flash: canvas initialized to " + canvas_mc + " (base was " + target_mc + "[" + target_mc._name + "])");
		xblDefsRepository_obj = new defsRepository();
		canvas_mc._exulis._xbl2DefsRepository = xblDefsRepository_obj;
	}
	public function destroy(){
		for(var vCpt=0;vCpt < canvas_mc._xbl._xblDescendants.length;vCpt++){
			canvas_mc._xbl._xblDescendants[vCpt].destroy();
		}
		canvas_mc.removeMovieClip();
	}
/**
* @method get xml
* @description return the xml passed in the parseXML method
* @usage theXML = XBL2Flash.xml
*/
    public function get xml():XML{
        return oXML
    }
// here we connect the canvas to the XBL construction
// here XBLcircle
    private function xbl_something(node:XML,current_mc){
//		trace("XBL2Flash: XBL something (" + node.attributes["id"] + ") triggered on " + current_mc._xbl.id + " on depth: " + current_mc._childNextDepth);
//		var object_obj = new XBL2something(current_mc,node);
		var local_mc;
//		local_mc = object_obj.create();
		return local_mc;
    }

/**
* @method translateXML
* @description core of the XBL2Flash class
*/
    public function translateXML(node:XML, current_mc) {
		var local_mc:MovieClip;
// GUI factory begins here
		switch (node.nodeName){
			case "something":
				local_mc = xbl_something(node,current_mc);
				break;
// elements not yet implemented
/*
			case "use":
				local_mc = xbl_use(node,current_mc);
				break;
*/
// here unhandled tags
			default:
				trace("XBL2Flash: XBL tag undefined: " + node.nodeName);
				local_mc = current_mc;
		}
		return local_mc;
	}
}
