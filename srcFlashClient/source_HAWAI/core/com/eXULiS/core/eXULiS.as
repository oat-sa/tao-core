import com.xfactorstudio.xml.xpath.*;
import com.eXULiS.core.XML2Flash;
//import mx.utils.Delegate;
import net.tekool.utils.Relegate;

/**
* eXULiS = extended XUL including SVG
* @author Raynald Jadoul
* @description Translates XUL/SVG syntax in Flash native components
* @usage import com.eXULiS.core.eXULiS;
* @usage myGUIref = new eXULiS(a_canvas_ref); //a_canvas_ref is the base of the GUI contruct
* @usage myGUIref.setGuiDefinitionFile(a_layout_XML); //or setGuiDefinition(a_layout_file)
* @usage myGUIref.setContext(an_execution_context); //where the integrated scripts should run
* @usage myGUIref.buildGUI();
*/

class com.eXULiS.core.eXULiS {
	private var guiDefinition_str:String;
	private var guiDefinitionFile_str:String;
	private var canvas_mc:MovieClip; // holds the reference of container of the generated UI
	private var execContext_str:String; // the qualifier of execution context e.g. "_root", "_level0", "this", "inherit", "myRect"
	private var _xml_ref:com.eXULiS.core.XML2Flash;
	private var guiFile_xul:XML;
	private var xliffFiles_obj:Object;
	private var xliffTotal_num:Number;
	private var xliffCpt_num:Number;

// constructor of eXULiS
	function eXULiS(target_mc:MovieClip){
		canvas_mc = (target_mc == undefined) ? _root : target_mc;
		trace("[eXULiS] constructor - canvas initialized to " + canvas_mc + " (base was " + target_mc + "[" + target_mc._name + "])");
		guiDefinition_str = "";
		guiDefinitionFile_str = "";
		xliffFiles_obj = new Object();
		xliffTotal_num = 0;
		xliffCpt_num = 0;
		trace("[eXULiS] xliff Total_num init: " + xliffTotal_num);
		trace("[eXULiS] xliff Cpt_num init: " + xliffCpt_num);
	}

	function setContext(context_str:String){
		trace("[eXULiS] setContext - execution context initialized to " + context_str);
		execContext_str = (context_str == undefined) ? "_root" : context_str;
	}

	function getContext():String {
		trace("[eXULiS] getContext invoqued - execution context is " + execContext_str);
		return(execContext_str);
	}

	function setGuiDefinition(guiDef_str:String){
		// receive the GUI definition the eXULiS must parse and render
		guiDefinition_str = "";
		guiDefinitionFile_str = "";
		guiDefinition_str = guiDef_str;
		trace("[eXULiS] setGuiDefinition - definition to process: " + guiDefinition_str);
	}

	function setGuiDefinitionFile(guiDefFile_str:String){
		// receive the location of the file containing the GUI def. the eXULiS must render
		guiDefinition_str = "";
		guiDefinitionFile_str = "";
		guiDefinitionFile_str = unescape(guiDefFile_str);
		trace("[eXULiS] setGuiDefinitionFile - definition file name: " + guiDefinitionFile_str);
	}

	function getXMLsource(){
		return(guiFile_xul);
	}

	function getParserRef(){
//		return(_xml_ref.getParserRef(paramNS_str));
		return(_xml_ref);
	}

	function buildGUIhandler(success){
		var vLocalDef_str:String = this.guiFile_xul.toString();
		var vTmp_str:String = String(vLocalDef_str);
		xliffTotal_num = vTmp_str.split("xliffhandler").length;
		xliffTotal_num = (xliffTotal_num > 0)? (xliffTotal_num - 1)/2:0;
		trace("[eXULiS] xliff Total_num buildGUIhandler init: " + xliffTotal_num);
		trace("[eXULiS] buildGUIhandler entered with status: " + success);
		trace("[eXULiS] buildGUIhandler - definition to process: " + vLocalDef_str);
		trace("[eXULiS] _xml_ref: " + this._xml_ref);

		var xliffSource_xml:XML;
		var xliffHandlerIndex_num:Number;
		var xliffHandlerPrevIndex_num:Number;
		var xlfDefinitionFile_str:String;

		xliffHandlerPrevIndex_num = 0;
		xliffHandlerIndex_num = vLocalDef_str.indexOf("xliffhandler",xliffHandlerPrevIndex_num);
		if(xliffHandlerIndex_num != -1){
			while(xliffHandlerIndex_num != -1){
				xliffHandlerPrevIndex_num = vLocalDef_str.indexOf("<",xliffHandlerIndex_num);
				xlfDefinitionFile_str = vLocalDef_str.substring(vLocalDef_str.indexOf(">",xliffHandlerIndex_num)+1,xliffHandlerPrevIndex_num);
				trace("xliffHandler: xlfDefinitionFile_str: " + xlfDefinitionFile_str);
				xliffSource_xml = new XML();

//						xliffSource_xml.onLoad = Delegate.create(this,loadXliffHandler);
				xliffSource_xml.onLoad = Relegate.create(this,buildXliffHandler,xlfDefinitionFile_str);
				xliffSource_xml.ignoreWhite = true;
				xliffSource_xml.load(xlfDefinitionFile_str);
				xliffFiles_obj[xlfDefinitionFile_str] = xliffSource_xml;						
				xliffHandlerIndex_num = vLocalDef_str.indexOf("xliffhandler",xliffHandlerPrevIndex_num + 12);
			}
		}
		else{
			var vTmpCanvasXULObj:Object;
			var xpathQuery_str = "/";
			trace("xpathQuery_str: " + xpathQuery_str);
			var blackManifest_xml = XPath.selectSingleNode(this.guiFile_xul, xpathQuery_str);
			vTmpCanvasXULObj = _xml_ref.parseXML(blackManifest_xml);
		}
	}

	public function buildXliffHandler(success, xlfDefinitionFile_str){
		trace("xliffHandler src for " + xlfDefinitionFile_str + ": " + xliffFiles_obj[xlfDefinitionFile_str].toString());
		var returnVal_array:Array = new Array();
		var tmp_str:String;
		returnVal_array = XPath.selectNodes(xliffFiles_obj[xlfDefinitionFile_str],"/xliff/file/body/trans-unit");
		for(var vCpt_num:Number = 0; vCpt_num < returnVal_array.length; vCpt_num++) {
			var aNode:XMLNode = returnVal_array[vCpt_num];
            trace("xliffHandler nemo: " + aNode.attributes.id+": "+aNode.firstChild.firstChild.nodeValue);
			for(var aSubNode:XMLNode = aNode.firstChild; aSubNode != null; aSubNode=aSubNode.nextSibling) {
				if (aSubNode.nodeName == "target") {
					tmp_str = String(aSubNode.firstChild.nodeValue);
/*
					var vtemp_str:String = "-";
					for(var vtmpcpt_num:Number = 0;vtmpcpt_num<tmp_str.length;vtmpcpt_num++){
						vtemp_str += tmp_str.charCodeAt(vtmpcpt_num) + "-";
					}
*/
					if(tmp_str.substr(-1) == "\n"){
						tmp_str = tmp_str.substr(0,-1);
					}
//					trace("xliffHandler val: " + vtemp_str + aSubNode.firstChild.nodeValue);
					_root._objXLIFFholder_obj[aNode.attributes.id] = tmp_str;
				}
			}
        }
		xliffCpt_num++;
		trace("[eXULiS] xliff Total_num buildXliffHandler: " + xliffTotal_num);
		trace("[eXULiS] xliff Cpt_num buildXliffHandler: " + xliffCpt_num);
		if(xliffCpt_num == xliffTotal_num){
			var vTmpCanvasXULObj:Object;
			var xpathQuery_str = "/";
			trace("xpathQuery_str: " + xpathQuery_str);
			var blackManifest_xml = XPath.selectSingleNode(this.guiFile_xul, xpathQuery_str);
			vTmpCanvasXULObj = _xml_ref.parseXML(blackManifest_xml);
		}
	}

	public function loadXliffHandler(success, xlfDefinitionFile_str){
		var canvasNode_xul:XML;
		var vTmpCanvasXULObj:Object;
		trace("xliffHandler prev: " + xliffFiles_obj[xlfDefinitionFile_str].toString());
		var local_xml:XML = _root._objXLIFFholder_xml;
		var newNode:XMLNode = xliffFiles_obj[xlfDefinitionFile_str];
		trace("xliffHandler new: " + newNode.toString());
		local_xml.firstChild.appendChild(newNode);
		trace("xliffHandler end: " + local_xml.toString());
		xliffCpt_num++;
		trace("[eXULiS] xliff Total_num loadXliffHandler: " + xliffTotal_num);
		trace("[eXULiS] xliff Cpt_num loadXliffHandler: " + xliffCpt_num);
		if(xliffCpt_num == xliffTotal_num){
			canvasNode_xul = new XML(guiDefinition_str);
			vTmpCanvasXULObj = _xml_ref.parseXML(canvasNode_xul);
		}
	}

	function buildGUI(execContext_arg){
		trace("[eXULiS] buildGUI entered");
		var canvasNode_xul:XML;
		var vTmpCanvasXULObj:Object;
		var xliffSource_xml:XML;
		var xliffHandlerIndex_num:Number;
		var xliffHandlerPrevIndex_num:Number;
		var xlfDefinitionFile_str:String;
		var vTmp_str:String = String(guiDefinition_str);
		xliffTotal_num = vTmp_str.split("xliffhandler").length;
		xliffTotal_num = (xliffTotal_num > 0)? (xliffTotal_num - 1)/2:0;
		trace("[eXULiS] xliff Total_num buildGUI init: " + xliffTotal_num);

		execContext_str = (execContext_arg == undefined) ? "_root" : execContext_arg;

		if((guiDefinition_str != "") || (guiDefinitionFile_str != "")){
			_xml_ref = new XML2Flash(canvas_mc, execContext_str);
			if(guiDefinition_str != ""){
				xliffHandlerPrevIndex_num = 0;
				xliffHandlerIndex_num = guiDefinition_str.indexOf("xliffhandler",xliffHandlerPrevIndex_num);
				if(xliffHandlerIndex_num != -1){
					while(xliffHandlerIndex_num != -1){
						xliffHandlerPrevIndex_num = guiDefinition_str.indexOf("<",xliffHandlerIndex_num);
						xlfDefinitionFile_str = guiDefinition_str.substring(guiDefinition_str.indexOf(">",xliffHandlerIndex_num)+1,xliffHandlerPrevIndex_num);
						trace("xliffHandler: xlfDefinitionFile_str: " + xlfDefinitionFile_str);
						xliffSource_xml = new XML();
//						xliffSource_xml.onLoad = Delegate.create(this,loadXliffHandler);
						xliffSource_xml.onLoad = Relegate.create(this,loadXliffHandler,xlfDefinitionFile_str);
						xliffSource_xml.ignoreWhite = true;
						xliffSource_xml.load(xlfDefinitionFile_str);
						xliffFiles_obj[xlfDefinitionFile_str] = xliffSource_xml;						
						xliffHandlerIndex_num = guiDefinition_str.indexOf("xliffhandler",xliffHandlerPrevIndex_num + 12);
					}
				}
				else{
					canvasNode_xul = new XML(guiDefinition_str);
					vTmpCanvasXULObj = _xml_ref.parseXML(canvasNode_xul);
				}
			}
			else{
				guiFile_xul = new XML();
//				guiFile_xul.onLoad = Delegate.create(this,buildGUIhandler);
				guiFile_xul.onLoad = Relegate.create(this,buildGUIhandler);
				guiFile_xul.ignoreWhite = true;
				guiFile_xul.load(guiDefinitionFile_str);
			}
		}		
		trace("[eXULiS] buildGUI - End");
	}

	function reloadGUI(execContext_arg){
		trace("[eXULiS] reloadGUI entered");
		var canvas_ref;
		canvas_ref = canvas_mc._exulis._objDescendants[0];
		canvas_ref._exulis.destroy(); // starts the cascading destruction
		buildGUI(execContext_arg); // before rebuilding the whole stuff
	}

	function cleanGUI(){
		trace("[eXULiS] cleanGUI entered");
		var canvas_ref;
		canvas_ref = canvas_mc._exulis._objDescendants[0];
		canvas_ref._exulis.destroy(); // starts the cascading destruction
	}

	function unloadGUI(){
		trace("[eXULiS] unloadGUI entered");
		var canvas_ref;
		canvas_ref = canvas_mc._exulis._objDescendants[0];
		canvas_ref._exulis.destroy();
	}
}
