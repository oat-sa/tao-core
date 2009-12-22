/**
* @author Alessandro Crugnola
* @author Raynald Jadoul (TAO adaptation)
* @description return an object with the content of the XML translated
* All nodes with 1 or more than 1 child with the same name give an array (created with the children contents)
* @usage data = new XML2Object().parseXML(anXML);

*/
class it.sephiroth.XML2Object extends XML {
	private var oResult:Object = new Object ();
	private var oXML:XML;
	private var canvas_mc:MovieClip;
/**
* * Constructor
* * initializes the canvas movie clip
*/
	function XML2Object(target_mc:MovieClip) {
		trace("XML2Object: XML transformer initialized to " + target_mc._name);
		canvas_mc = target_mc;
	}
/**
* @method get xml
* @description return the xml passed in the parseXML method
* @usage theXML = XML2Object.xml
*/
	public function get xml():XML{
		return oXML	
	}
/**
* @method public parseXML
* @description return the parsed Object
* @usage XML2Object.parseXML( theXMLtoParse );

* @param sFile XML
* @returns an Object with the contents of the passed XML
*/
	public function parseXML (sFile:XML):Object {
		this.oResult = new Object ();
		this.oXML = sFile;
		this.oResult = this.translateXML();
		return this.oResult;
	}
/**
* @method private translateXML
* @description core of the XML2Object class
*/	
	private function translateXML (from, path, name, position) {
		var nodes, node, old_path;
		if (path == undefined) {
			path = this;
			name = "oResult";
		}
		path = path[name];
		if (from == undefined) {
			from = new XML (String(this.xml));
			from.ignoreWhite = true;
		}
		if (from.hasChildNodes ()) {
			nodes = from.childNodes;
			if (position != undefined) {
				var old_path = path;
				path = path[position];
			}
			while (nodes.length > 0) {
				node = nodes.shift ();
				if (node.nodeName != undefined) {
					var __obj__ = new Object ();
					__obj__.attributes = node.attributes;
					if (node.nodeName == "xul") {
						trace("XML2Object: xul encountered");
						// we'll push xul node as it is 'cause XUL2SWF will take care of it later
						__obj__.data = node.toString();
					}
					else {
						__obj__.data = node.firstChild.nodeValue;
					}
					if (position != undefined) {
						var old_path = path;
					}
					if (path[node.nodeName] == undefined) {
						path[node.nodeName] = new Array ();
					}
					path[node.nodeName].push (__obj__);
					name = node.nodeName;
					position = path[node.nodeName].length - 1;
				}
				if ((node.hasChildNodes ()) && (name != "xul")) {
//				if (node.hasChildNodes ()) {
					this.translateXML (node, path, name, position);
				}
			}
		}
		return this.oResult;
	}
}