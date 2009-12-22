//import mx.controls.*;
import lu.tao.utils.Event;
import com.eXULiS.lib.*;
import com.eXULiS.BLACK.BLACK2Flash;
import com.eXULiS.CSS.CSS2Flash;
import com.eXULiS.SVG.SVG2Flash;
import com.eXULiS.XBL1.XBL1Flash;
import com.eXULiS.XBL2.XBL2Flash;
import com.eXULiS.XUL.XUL2Flash;

//import xpath.*;

/**
* XML 2 SWF
* @author Raynald Jadoul
* @description Wrapper that analyzes XML syntax (especially xmlns) in Flash native components
* @usage myFlashObj = new XML2Flash(anchorWidget, executionLayer);
* @usage // anchorWidget is the parent of the generated component,
* @usage // executionLayer is the place where the inbound AS is runned
* @usage myFlashObj.parseXML(a_layout_XML);
*/
class com.eXULiS.core.XML2Flash extends XML {
    private var oResult:Object = new Object ();
    private var oXML:XML;
	private var canvas_mc:MovieClip;
	private var targetExecutionLayer_mc:MovieClip;
	private var targetExecutionLayer_str:String;
	private var objBroadcaster;
	private var objListeners:Array;
	private var _black_ref:com.eXULiS.BLACK.BLACK2Flash;
	private var _css_ref:com.eXULiS.CSS.CSS2Flash;
	private var _svg_ref:com.eXULiS.SVG.SVG2Flash;
	private var _xbl1_ref:com.eXULiS.XBL1.XBL1Flash;
	private var _xbl2_ref:com.eXULiS.XBL2.XBL2Flash;
	private var _xul_ref:com.eXULiS.XUL.XUL2Flash;
	public var objDefsRepository_obj:defsRepository;
/**
* * Constructor
* * initializes the canvas movie clip
*/
	function XML2Flash(target_mc:MovieClip,execContext_str:String) {
		canvas_mc = target_mc;
//		trace("XML2Flash: canvas initialized to " + canvas_mc + " (base was " + target_mc + "[" + target_mc._name + "])");
		targetExecutionLayer_str = (execContext_str == undefined) ? "_root" : execContext_str;
		switch (execContext_str){
// root element
			case "_root":
				targetExecutionLayer_mc = _root;
//				trace("XML2Flash: execution context set to _root");
				break;
			case "_level0":
				targetExecutionLayer_mc = _level0;
//				trace("XML2Flash: execution context set to _level0");
				break;
// locals
			case "local":
			case "this":
				targetExecutionLayer_mc = target_mc;
//				trace("XML2Flash: execution context set to Local");
				break;
			case "inherit":
				targetExecutionLayer_mc = undefined;
//				trace("XML2Flash: execution context fetched in parent's");
				break;
// here other targets
			default:
				targetExecutionLayer_mc = undefined;
//				trace("XML2Flash: execution context unresolved");
		}

		_black_ref = new com.eXULiS.BLACK.BLACK2Flash(canvas_mc);
		_css_ref = new com.eXULiS.CSS.CSS2Flash(canvas_mc);
		_svg_ref = new com.eXULiS.SVG.SVG2Flash(canvas_mc);
		_xbl1_ref = new com.eXULiS.XBL1.XBL1Flash(canvas_mc);
		_xbl2_ref = new com.eXULiS.XBL2.XBL2Flash(canvas_mc);
		_xul_ref = new com.eXULiS.XUL.XUL2Flash(canvas_mc);
/*
for(var nam in canvas_mc){
	trace("TOTU >> canvas_mc." + nam + ":" + canvas_mc._objParent[nam]);
}
trace("TUTU >>");
*/
		if(targetExecutionLayer_mc._objDefsRepository == undefined){
//			trace("XML2Flash: _objDefsRepository was " + targetExecutionLayer_mc._objDefsRepository);
			objDefsRepository_obj = new defsRepository();
			targetExecutionLayer_mc._objDefsRepository = objDefsRepository_obj;
//			trace("XML2Flash: _objDefsRepository is " + targetExecutionLayer_mc._objDefsRepository);
		}

		if(targetExecutionLayer_mc._objBroadcaster == undefined){
			objBroadcaster = new Event();
			targetExecutionLayer_mc._objBroadcaster = objBroadcaster;
		}

		if(targetExecutionLayer_mc._objListeners == undefined){
			objListeners = new Array();
			targetExecutionLayer_mc._objListeners = objListeners;
		}

		var _exulis:Object = new Object();
		var posterity:Array = new Array();
		canvas_mc._x = (canvas_mc._x == undefined) ? 0 : canvas_mc._x;
		canvas_mc._y = (canvas_mc._y == undefined) ? 0 : canvas_mc._y;
		if(canvas_mc._childNextDepth == undefined){
			canvas_mc._childNextDepth = 1;
		}
		if(canvas_mc._exulis._childNextDepth != undefined){
			canvas_mc._childNextDepth = canvas_mc._exulis._childNextDepth;
		}
		canvas_mc._targetExecutionLayer = targetExecutionLayer_mc;
		canvas_mc._targetExecutionLayerName = targetExecutionLayer_str;
		if(canvas_mc._exulis.id != undefined){
			_exulis.id = canvas_mc._exulis.id; // + "_canvas";
			_exulis._type = "overlay";
			_exulis._guiSource = canvas_mc._exulis.guiFile_xul;
		}
		else{
			_exulis.id = "canvas";
			_exulis._type = "root";
		}
		_exulis._targetExecutionLayer = targetExecutionLayer_mc;
		_exulis.left = 0;
		_exulis.top = 0;
		_exulis.width = (target_mc._width == undefined) ? Stage.width : target_mc._width;
		_exulis.height = (target_mc._height == undefined) ? Stage.height : target_mc._height;
//		trace("CANVAS: (buildGUI) width:" + _exulis.width + " height:" + _exulis.height);
/*
		_exulis._totalFlex = 0;
		_exulis._toDecreaseFlexWidth = 0;
		_exulis._toDecreaseFlexHeight = 0;
		_exulis._childsMinNeedWidth = 0;
		_exulis._childsMinNeedHeight = 0;
		_exulis._nextElementFlexLeftEdge = 0;
		_exulis._nextElementFlexTopEdge = 0;
*/
		_exulis._objDescendants = posterity;
		canvas_mc._exulis = _exulis;

	}
	public function destroy(){
		for(var vCpt=0;vCpt < canvas_mc._exulis._objDescendants.length;vCpt++){
			canvas_mc._exulis._objDescendants[vCpt].destroy();
		}
		canvas_mc.removeMovieClip();
	}
	public function getParserRef(paramNS_str){
// TODO should be improved (more generic) with a reference keeper like DefRepository
		var returned_ref;
		switch (paramNS_str){
			case "http://www.exulis.lu/black.rdfs#":
				returned_ref = _black_ref;
				break;
			case "http://www.w3.org/1998/CSS":
				returned_ref = _css_ref;
				break;
			case "http://www.w3.org/2000/svg":
				returned_ref = _svg_ref;
				break;
			case "http://www.mozilla.org/xbl":
				returned_ref = _xbl1_ref;
				break;
			case "http://www.w3.org/ns/xbl":
				returned_ref = _xbl2_ref;
				break;
			case "http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul":
				returned_ref = _xul_ref;
				break;
// here unhandled tags and plugged namespaces (for plugins)
			default:
				trace("XML2Flash: unknown namespace " + paramNS_str);
		}
		return(returned_ref);
	}
/**
* @method get xml
* @description return the xml passed in the parseXML method
* @usage theXML = XML2Flash.xml
*/
    public function get xml():XML{
        return oXML
    }
/**
* @method public parseXML
* @description return the parsed Object
* @usage XML2Flash.parseXML( theXMLtoParse );

* @param sFile XML
* @returns an Object with the contents of the passed XML
*/
    public function parseXML (sXML:XML):Object {
//		trace("XML2Flash: XML UI parsing started on node " + sXML.nodeName);

		this.oResult = new Object ();
		this.oXML = sXML;
		this.oResult = this.translateXML();
//		_xul_ref.xul_reflex(canvas_mc);
		return this.oResult;
    }
/**
* @method private translateXML
* @description core of the XML2Flash class
*/
    private function translateXML (from, path, name, position, current_mc, previousNS_str:String) {
		var local_mc:MovieClip;
		var currentNS_str:String;
		var nodes;
		var node;
		var old_path;
		if (path == undefined) {
//			trace("XML2Flash: XML translation started on " + canvas_mc + " [" + canvas_mc._name + "]");
			current_mc = canvas_mc;
			path = this;
			name = "oResult";
		}
		path = path[name];
		if (from == undefined) {
			from = new XML (this.xml.toString());
			from.ignoreWhite = true;
		}
		if (from.hasChildNodes ()) {
			nodes = from.childNodes;
			if (position != undefined) {
				old_path = path;
				path = path[position];
			}
			while (nodes.length > 0) {
				node = nodes.shift ();
				if (node.nodeName != undefined) {
					var __obj__ = new Object ();
					__obj__.attributes = node.attributes;
					__obj__.data = node.firstChild.nodeValue;
					if (position != undefined) {
						old_path = path;
					}
					if (path[node.nodeName] == undefined) {
						path[node.nodeName] = new Array ();
					}
					path[node.nodeName].push (__obj__);
					name = node.nodeName;
					position = path[node.nodeName].length - 1;
					currentNS_str = node.namespaceURI;
					currentNS_str = (isNaN(currentNS_str))? currentNS_str : previousNS_str; // to avoid problem with number in namespaceURI
					currentNS_str = ((currentNS_str == "") || (currentNS_str == undefined))? previousNS_str : currentNS_str; // to avoid problem with number in namespaceURI
// GUI factory begins here
//					trace("XML2Flash: namespace is " + currentNS_str + " for " + name);
					switch (currentNS_str){
						case "http://www.exulis.lu/black.rdfs#":
							local_mc = _black_ref.translateXML(node,current_mc);
							break;
						case "http://www.w3.org/1998/CSS":
							local_mc = _css_ref.translateXML(node,current_mc);
							break;
						case "http://www.w3.org/2000/svg":
							local_mc = _svg_ref.translateXML(node,current_mc);
							break;
						case "http://www.mozilla.org/xbl":
							local_mc = _xbl1_ref.translateXML(node,current_mc);
							break;
						case "http://www.w3.org/ns/xbl":
							local_mc = _xbl2_ref.translateXML(node,current_mc);
							break;
						case "http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul":
							local_mc = _xul_ref.translateXML(node,current_mc);
							break;
// here unhandled tags and plugged namespaces (for plugins)
						default:
							trace("XML2Flash: unknown namespace " + currentNS_str + " for " + name);
							local_mc = current_mc;
					}
					current_mc._exulis._objDescendants.push(local_mc);
				}
//				if(node.hasChildNodes()){
				if((node.hasChildNodes()) && (node.nodeName != "black:action") && (node.nodeName != "black:Action") && (node.nodeName != "black:content") && (node.nodeName != "black:Content") && (node.nodeName != "black:knowledge") && (node.nodeName != "black:Knowledge")){
					this.translateXML (node, path, name, position, local_mc, currentNS_str);
				}
//				else{
//					if((local_mc != undefined) && (currentNS_str == "http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul")){
//						_xul_ref.xul_flex(local_mc);
//					}
//				}
			}
		}
		return this.oResult;
	}
}
