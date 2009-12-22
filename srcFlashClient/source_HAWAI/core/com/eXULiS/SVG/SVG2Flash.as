//import mx.controls.*;
import lu.tao.utils.Event;
import com.eXULiS.lib.Toolbox;
//import xpath.*;
import com.eXULiS.SVG.*;
//import XML2Object;

/**
* SVG 2 SWF
* @author Raynald Jadoul
* @description Translates SVG syntax in Flash native components
* @usage data = new SVG2Flash().parseXML(anXML);

*/
class com.eXULiS.SVG.SVG2Flash extends XML {
    private var oResult:Object = new Object ();
    private var oXML:XML;
	private var canvas_mc:MovieClip;
/**
* * Constructor
* * initializes the canvas movie clip
*/
	function SVG2Flash(target_mc:MovieClip) {
		canvas_mc = target_mc;
//		trace("SVG2Flash: canvas initialized to " + canvas_mc + " (base was " + target_mc + "[" + target_mc._name + "])");
	}
	public function destroy(){
		for(var vCpt=0;vCpt < canvas_mc._exulis._objDescendants.length;vCpt++){
			canvas_mc._exulis._objDescendants[vCpt].destroy();
		}
		canvas_mc.removeMovieClip();
	}
/**
* @method get xml
* @description return the xml passed in the parseXML method
* @usage theXML = SVG2Flash.xml
*/
    public function get xml():XML{
        return oXML
    }

// here we connect the canvas to the SVG construction
// here SVGcircle
    private function svg_circle(node:XML,current_mc){
//		trace("SVG2Flash: SVG circle (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGcircle(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGdefs
    private function svg_defs(node:XML,current_mc){
//        trace("SVG2Flash: SVG defs (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGdefs(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGdesc
    private function svg_desc(node:XML,current_mc){
//        trace("SVG2Flash: SVG desc (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGdesc(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGellipse
    private function svg_ellipse(node:XML,current_mc){
//		trace("SVG2Flash: SVG ellipse (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGellipse(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGg - g element is used for grouping
    private function svg_g(node:XML,current_mc){
//        trace("SVG2Flash: SVG grouping (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGg(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGimage
    private function svg_image(node:XML,current_mc){
//        trace("SVG2Flash: SVG image (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGimage(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGline
    private function svg_line(node:XML,current_mc){
//		trace("SVG2Flash: SVG line (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGline(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGlinearGradient
    private function svg_linearGradient(node:XML,current_mc){
//		trace("SVG2Flash: SVG linearGradient (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGlinearGradient(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGmarker
    private function svg_marker(node:XML,current_mc){
//        trace("SVG2Flash: SVG marker (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGmarker(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGpath
    private function svg_path(node:XML,current_mc){
//		trace("SVG2Flash: SVG path (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGpath(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGpolygon
    private function svg_polygon(node:XML,current_mc){
//        trace("SVG2Flash: SVG polygon (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGpolygon(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGpolyline
    private function svg_polyline(node:XML,current_mc){
//        trace("SVG2Flash: SVG polyline (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGpolyline(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGradialGradient
    private function svg_radialGradient(node:XML,current_mc){
//		trace("SVG2Flash: SVG radialGradient (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGradialGradient(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGrect
    private function svg_rect(node:XML,current_mc){
//        trace("SVG2Flash: SVG rect (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGrect(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGsvg - svg element is used as svg container
    private function svg_svg(node:XML,current_mc){
//        trace("SVG2Flash: SVG root (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGg(current_mc,node); // svg acts like a grouping tag
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGsymbol - symbol element is used to define graphical template
    private function svg_symbol(node:XML,current_mc){
//        trace("SVG2Flash: SVG symbol (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGg(current_mc,node); // symbol acts like a grouping tag
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGtext
    private function svg_text(node:XML,current_mc){
//        trace("SVG2Flash: SVG text (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGtext(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGtspan
    private function svg_tspan(node:XML,current_mc){
//        trace("SVG2Flash: SVG tspan (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGtspan(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVGuse - use element is used as shortcut
    private function svg_use(node:XML,current_mc){
//        trace("SVG2Flash: SVG use (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new SVGuse(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }

/**
* @method translateXML
* @description core of the SVG2Flash class
*/
    public function translateXML(node:XML, current_mc) {
		var local_mc:MovieClip;
		var originalId:String;
		var _type:String = "";
		var _nodeName:String = node.nodeName;
		_nodeName = node.nodeName;
		_type = (_nodeName.indexOf(":") == -1) ? _nodeName : _nodeName.substr(_nodeName.indexOf(":") + 1);
// GUI factory begins here
		originalId = node.attributes.id;
		switch (_type){
			case "circle":
				local_mc = svg_circle(node,current_mc);
				break;
			case "defs":
				local_mc = svg_defs(node,current_mc);
				break;
			case "desc":
				local_mc = svg_desc(node,current_mc);
				break;
			case "ellipse":
				local_mc = svg_ellipse(node,current_mc);
				break;
			case "g":
				local_mc = svg_g(node,current_mc); // container
				break;
			case "image":
				local_mc = svg_image(node,current_mc);
				break;
			case "line":
				local_mc = svg_line(node,current_mc);
				break;
			case "linearGradient":
				local_mc = svg_linearGradient(node,current_mc);
				break;
			case "marker":
				local_mc = svg_marker(node,current_mc);
				break;
			case "path":
				local_mc = svg_path(node,current_mc);
				break;
			case "polygon":
				local_mc = svg_polygon(node,current_mc);
				break;
			case "polyline":
				local_mc = svg_polyline(node,current_mc);
				break;
			case "radialGradient":
				local_mc = svg_radialGradient(node,current_mc);
				break;
			case "rect":
				local_mc = svg_rect(node,current_mc);
				break;
			case "svg":
				local_mc = svg_svg(node,current_mc); // svg container
				break;
			case "symbol":
				local_mc = svg_symbol(node,current_mc);
				break;
			case "text":
				local_mc = svg_text(node,current_mc);
				break;
			case "tspan":
				local_mc = svg_tspan(node,current_mc);
				break;
			case "use":
				local_mc = svg_use(node,current_mc);
				break;
// elements not yet implemented
/*
			case "stop":
				local_mc = svg_stop(node,current_mc);
				break;
			case "textPath":
				local_mc = svg_textpath(node,current_mc);
				break;
			case "title":
				local_mc = svg_title(node,current_mc);
				break;
*/
// here unhandled tags
			default:
				trace("SVG2Flash: SVG tag undefined: " + node.nodeName);
				local_mc = current_mc;
		}
		if(originalId != undefined){
			var vTmpCptDefs_num:Number;
			vTmpCptDefs_num = local_mc._exulis._targetExecutionLayer._objDefsRepository.add(node.attributes.id, local_mc);
//			trace("SVG2Flash: (objDefsRepository) after '" + node.attributes.id + "', "+ vTmpCptDefs_num + " items are now registered");
		}
		return local_mc;
	}
}
