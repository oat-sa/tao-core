//import mx.controls.*;
import lu.tao.utils.Event;
import com.eXULiS.lib.Toolbox;
//import xpath.*;
import com.eXULiS.XUL.*;
//import XML2Object;

/**
* XUL 2 SWF
* @author Raynald Jadoul
* @description Translates XUL syntax in Flash native components
* @usage myFlashObj = new XUL2Flash(anchorWidget, executionLayer);
* @usage // anchorWidget is the parent of the generated component,
* @usage // executionLayer is the place where the inbound AS is runned
* @usage myFlashObj.parseXML(a_layout_XML);
*/
class com.eXULiS.XUL.XUL2Flash extends XML {
    private var oResult:Object = new Object ();
    private var oXML:XML;
	private var canvas_mc:MovieClip;
/**
* * Constructor
* * initializes the canvas movie clip
*/
	function XUL2Flash(target_mc:MovieClip) {
		canvas_mc = target_mc;
//		trace("XULFlash: canvas initialized to " + canvas_mc + " (base was " + target_mc + "[" + target_mc._name + "])");
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
* @usage theXML = XUL2Flash.xml
*/
    public function get xml():XML{
        return oXML
    }
// here we connect the canvas to the XUL construction via XULwindow tag
    private function xul_window(node:XML,current_mc){
//		trace("XUL2Flash: XUL window (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULwindow(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULbox
    private function xul_box(node:XML,current_mc){
//		trace("XUL2Flash: XUL box (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULhbox
    private function xul_hbox(node:XML,current_mc){
//		trace("XUL2Flash: XUL hbox (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULhbox
    private function xul_groupbox(node:XML,current_mc){
//		trace("XUL2Flash: XUL groupbox (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtabbox
    private function xul_tabbox(node:XML,current_mc){
//		trace("XUL2Flash: XUL tabbox (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULvbox
    private function xul_vbox(node:XML,current_mc){
//		trace("XUL2Flash: XUL vbox (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtoolbox
    private function xul_toolbox(node:XML,current_mc){
//		trace("XUL2Flash: XUL toolbox (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULstack
    private function xul_stack(node:XML,current_mc){
//		trace("XUL2Flash: XUL stack (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULstack(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULbutton
    private function xul_button(node:XML,current_mc){
//        trace("XUL2Flash: XUL button (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbutton(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULcheckbox
    private function xul_checkbox(node:XML,current_mc){
//        trace("XUL2Flash: XUL checkbox (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULcheckbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULradiogroup
    private function xul_radiogroup(node:XML,current_mc){
//        trace("XUL2Flash: XUL radiogroup (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULradiogroup(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULradio
    private function xul_radio(node:XML,current_mc){
//        trace("XUL2Flash: XUL radio (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULradio(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULsplitter
    private function xul_splitter(node:XML,current_mc){
// first retrieve the real parent (it means a SplitPane)
		var pointer_mc = current_mc;
// discover the splitted component containing the splitter
		while(!pointer_mc._exulis._splitted){
// next element (bottom up)
			pointer_mc = pointer_mc._exulis._objParent;
		}
		current_mc = pointer_mc;
//        trace("XUL2Flash: XUL splitter (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULsplitter(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULmenu
    private function xul_menu(node:XML,current_mc){
//        trace("XUL2Flash: XUL menu (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULmenu(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULmenu
    private function xul_menulist(node:XML,current_mc){
//        trace("XUL2Flash: XUL menulist (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULmenulist(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULmenubar
    private function xul_menubar(node:XML,current_mc){
//        trace("XUL2Flash: XUL menubar (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULmenubar(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULmenuitem
    private function xul_menuitem(node:XML,current_mc){
//        trace("XUL2Flash: XUL menuitem (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULmenuitem(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
    
// XULlistbox
	private function xul_listbox(node:XML,current_mc){
//        trace("XUL2Flash: XUL listbox (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULlistbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }

// XULlistitem
	private function xul_listitem(node:XML,current_mc){
//        trace("XUL2Flash: XUL listitem (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULlistitem(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }

// here XULmenupopup
    private function xul_menupopup(node:XML,current_mc){
//        trace("XUL2Flash: XUL menupopup (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULmenupopup(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULmenuseparator
    private function xul_menuseparator(node:XML,current_mc){
//        trace("XUL2Flash: XUL menuseparator (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULmenuitem(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtabs
    private function xul_tabs(node:XML,current_mc){
//        trace("XUL2Flash: XUL tabs (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtabs(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtab
    private function xul_tab(node:XML,current_mc){
//        trace("XUL2Flash: XUL tab (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtab(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtabpanels
    private function xul_tabpanels(node:XML,current_mc){
//        trace("XUL2Flash: XUL tabpanels (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtabpanel
    private function xul_tabpanel(node:XML,current_mc){
//        trace("XUL2Flash: XUL tabpanel (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtoolbar
    private function xul_toolbar(node:XML,current_mc){
//        trace("XUL2Flash: XUL toolbar (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtoolbar(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtoolbarbutton
    private function xul_toolbarbutton(node:XML,current_mc){
//        trace("XUL2Flash: XUL toolbarbutton (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtoolbarbutton(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here XULtoolbar
    private function xul_toolbarseparator(node:XML,current_mc){
//        trace("XUL2Flash: XUL toolbarseparator (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtoolbarseparator(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here SVG
    private function xul_svg(node:XML,current_mc){
//		trace("XUL2Flash: XUL SVG node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULsvg(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
// here image
    private function xul_image(node:XML,current_mc){
//		trace("XUL2Flash: XUL image node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULimage(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
	
	// here XULtextbox
    private function xul_textbox(node:XML,current_mc){
//		trace("XUL2Flash: XUL textbox node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtextbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
	// here XULlabel
    private function xul_label(node:XML,current_mc){
//		trace("XUL2Flash: XUL label node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtextbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
	// here XULdescription
    private function xul_description(node:XML,current_mc){
//		trace("XUL2Flash: XUL description node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULtextbox(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
	// here XULkeyset
    private function xul_keyset(node:XML,current_mc){
//		trace("XUL2Flash: XUL keyset node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULkeyset(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
	// here XULkey
    private function xul_key(node:XML,current_mc){
//		trace("XUL2Flash: XUL key node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULkey(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
/*
	// here XULlabel
    private function xul_label(node:XML,current_mc){
		trace("XUL2Flash: XUL label node (" + node.attributes["id"] + ") triggered on " + current_mc._exulis.id + " on depth: " + current_mc._childNextDepth);
		var object_obj = new XULlabel(current_mc,node);
		var local_mc = object_obj.create();
		return local_mc;
    }
*/

/*
// here XUL flex computing
    public function xul_flex(current_mc){
		trace("-----------------------------------------------------------------------------");
        trace("XUL2Flash: XUL Flex compute triggered by leaf " + current_mc._exulis.id);
		trace(" ");
		var pointer_mc = current_mc;
// sum the flex property of inbond elements for dynamic graphic dispatch
		while(pointer_mc != canvas_mc){
			if(!pointer_mc._exulis._flexAlreadySummed){
// flex analyze
				if(isNaN(parseInt(pointer_mc._exulis._objDef.attributes["flex"]))){
					pointer_mc._exulis._numberFlex = 0;
				}
				else{
					pointer_mc._exulis._numberFlex = parseInt(pointer_mc._exulis._objDef.attributes["flex"]);
				}
// compute minimum width & height thanks to label (if available)
				var my_toolbox:Toolbox = new Toolbox();
				var tmpMinWidth:Number = 0;
				var tmpMinHeight:Number = 0;
				if(pointer_mc._exulis._objDef.attributes["label"] != undefined){
					var minWidthHeight:Object;
					minWidthHeight = my_toolbox.evaluateLabelSize(pointer_mc._exulis._objDef.attributes["label"]);
					tmpMinWidth = minWidthHeight.minWidth + 4;
					tmpMinHeight = minWidthHeight.minHeight + 4;
				}
				trace("XUL2Flash (xul_flex) After label size eval: w=" + tmpMinWidth + " h=" + tmpMinHeight);
// minwidth analyze
				if(isNaN(parseInt(pointer_mc._exulis._objDef.attributes["minwidth"]))){
					if(pointer_mc._exulis._defaultWidth > tmpMinWidth){
						tmpMinWidth = pointer_mc._exulis._defaultWidth;
					}
				}
				else{
					tmpMinWidth = parseInt(pointer_mc._exulis._objDef.attributes["minwidth"]);
				}
// minheight analyze
				if(isNaN(parseInt(pointer_mc._exulis._objDef.attributes["minheight"]))){
					if(pointer_mc._exulis._defaultHeight > tmpMinHeight){
						tmpMinHeight = pointer_mc._exulis._defaultHeight;
					}
				}
				else{
					tmpMinHeight = parseInt(pointer_mc._exulis._objDef.attributes["minheight"]);
				}
// width analyze
				if(isNaN(parseInt(pointer_mc._exulis._objDef.attributes["width"]))){
					// nothing to do
				}
				else{
					if((parseInt(pointer_mc._exulis._objDef.attributes["width"])) > tmpMinWidth){
						tmpMinWidth = parseInt(pointer_mc._exulis._objDef.attributes["width"]);
					}
				}
// height analyze
				if(isNaN(parseInt(pointer_mc._exulis._objDef.attributes["height"]))){
					// nothing to do
				}
				else{
					if((parseInt(pointer_mc._exulis._objDef.attributes["height"])) > tmpMinHeight){
						tmpMinHeight = parseInt(pointer_mc._exulis._objDef.attributes["height"]);
					}
				}
// store results of size eval
				pointer_mc._exulis._minFlexWidth = tmpMinWidth;
				pointer_mc._exulis._minFlexHeight = tmpMinHeight;
			}
// take the descendants in consideration
			if(pointer_mc._exulis._childsMinNeedWidth > pointer_mc._exulis._minFlexWidth){
				if(pointer_mc._exulis._flexAlreadySummed){
					pointer_mc._exulis._objParent._exulis._childsMinNeedWidth -= pointer_mc._exulis._minFlexWidth;
					if(pointer_mc._exulis._numberFlex == 0){
						if((pointer_mc._exulis._objParent._exulis._type != "vbox") && (pointer_mc._exulis._objParent._exulis._type != "window")){
							pointer_mc._exulis._objParent._exulis._toDecreaseFlexWidth += pointer_mc._exulis._minFlexWidth;
						}
					}
				}
				pointer_mc._exulis._minFlexWidth = pointer_mc._exulis._childsMinNeedWidth;
				if(pointer_mc._exulis._numberFlex == 0){
					if((pointer_mc._exulis._objParent._exulis._type != "vbox") && (pointer_mc._exulis._objParent._exulis._type != "window")){
						pointer_mc._exulis._objParent._exulis._toDecreaseFlexWidth -= pointer_mc._exulis._minFlexWidth;
					}
				}
				if((pointer_mc._exulis._objParent._exulis._type == "vbox") || (pointer_mc._exulis._objParent._exulis._type == "window")){
					if(pointer_mc._exulis._minFlexWidth > pointer_mc._exulis._objParent._exulis._childsMinNeedWidth){
						pointer_mc._exulis._objParent._exulis._childsMinNeedWidth = pointer_mc._exulis._minFlexWidth;
					}
				}
				else{
					if(!pointer_mc._exulis._flexAlreadySummed){
						pointer_mc._exulis._objParent._exulis._childsMinNeedWidth += pointer_mc._exulis._minFlexWidth;
					}
				}
			}
			else{
				if(!pointer_mc._exulis._flexAlreadySummed){
					if((pointer_mc._exulis._objParent._exulis._type != "vbox") && (pointer_mc._exulis._objParent._exulis._type != "window")){
						if(pointer_mc._exulis._numberFlex == 0){
							pointer_mc._exulis._objParent._exulis._toDecreaseFlexWidth -= pointer_mc._exulis._minFlexWidth;
						}
						pointer_mc._exulis._objParent._exulis._childsMinNeedWidth += pointer_mc._exulis._minFlexWidth;
					}
					else{
						if(pointer_mc._exulis._minFlexWidth > pointer_mc._exulis._objParent._exulis._childsMinNeedWidth){
							pointer_mc._exulis._objParent._exulis._childsMinNeedWidth = pointer_mc._exulis._minFlexWidth;
						}
					}
				}

			}
			if(pointer_mc._exulis._childsMinNeedHeight > pointer_mc._exulis._minFlexHeight){
				if(pointer_mc._exulis._flexAlreadySummed){
					pointer_mc._exulis._objParent._exulis._childsMinNeedHeight -= pointer_mc._exulis._minFlexHeight;
					if(pointer_mc._exulis._numberFlex == 0){
						if((pointer_mc._exulis._objParent._exulis._type == "vbox") || (pointer_mc._exulis._objParent._exulis._type == "window")){
							pointer_mc._exulis._objParent._exulis._toDecreaseFlexHeight += pointer_mc._exulis._minFlexHeight;
						}
					}
				}
				pointer_mc._exulis._minFlexHeight = pointer_mc._exulis._childsMinNeedHeight;
				if(pointer_mc._exulis._numberFlex == 0){
					if((pointer_mc._exulis._objParent._exulis._type == "vbox") || (pointer_mc._exulis._objParent._exulis._type == "window")){
						pointer_mc._exulis._objParent._exulis._toDecreaseFlexHeight -= pointer_mc._exulis._minFlexHeight;
					}
				}
				if((pointer_mc._exulis._objParent._exulis._type != "vbox") && (pointer_mc._exulis._objParent._exulis._type != "window")){
					if(pointer_mc._exulis._minFlexHeight > pointer_mc._exulis._objParent._exulis._childsMinNeedHeight){
						pointer_mc._exulis._objParent._exulis._childsMinNeedHeight = pointer_mc._exulis._minFlexHeight;
					}
				}
				else{
					if(!pointer_mc._exulis._flexAlreadySummed){
						pointer_mc._exulis._objParent._exulis._childsMinNeedHeight += pointer_mc._exulis._minFlexHeight;
					}
				}
			}
			else{
				if(!pointer_mc._exulis._flexAlreadySummed){
					if((pointer_mc._exulis._objParent._exulis._type == "vbox") || (pointer_mc._exulis._objParent._exulis._type == "window")){
						if(pointer_mc._exulis._numberFlex == 0){
							pointer_mc._exulis._objParent._exulis._toDecreaseFlexHeight -= pointer_mc._exulis._minFlexHeight;
						}
						pointer_mc._exulis._objParent._exulis._childsMinNeedHeight += pointer_mc._exulis._minFlexHeight;
					}
					else{
						if(pointer_mc._exulis._minFlexHeight > pointer_mc._exulis._objParent._exulis._childsMinNeedHeight){
							pointer_mc._exulis._objParent._exulis._childsMinNeedHeight = pointer_mc._exulis._minFlexHeight;
						}
					}
				}
			}
			if(!pointer_mc._exulis._flexAlreadySummed){
				pointer_mc._exulis._objParent._exulis._totalFlex += pointer_mc._exulis._numberFlex;
				pointer_mc._exulis._objParent._exulis._objDescendants.push(pointer_mc);
				pointer_mc._exulis._flexAlreadySummed = true;
			}
// some debug info
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis.id + ".flex = " + pointer_mc._exulis._numberFlex);
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis.id + "._minFlexWidth = " + pointer_mc._exulis._minFlexWidth);
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis.id + "._minFlexHeight = " + pointer_mc._exulis._minFlexHeight);
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis._objParent._exulis.id + "._toDecreaseFlexWidth = " + pointer_mc._exulis._objParent._exulis._toDecreaseFlexWidth);
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis._objParent._exulis.id + "._toDecreaseFlexHeight = " + pointer_mc._exulis._objParent._exulis._toDecreaseFlexHeight);
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis._objParent._exulis.id + "._totalFlex = " + pointer_mc._exulis._objParent._exulis._totalFlex);
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis._objParent._exulis.id + "._childsMinNeedWidth = " + pointer_mc._exulis._objParent._exulis._childsMinNeedWidth);
			trace("XUL2Flash (xul_flex): " + pointer_mc._exulis._objParent._exulis.id + "._childsMinNeedHeight = " + pointer_mc._exulis._objParent._exulis._childsMinNeedHeight);
// next element (bottom up)
			pointer_mc = pointer_mc._exulis._objParent;
			trace(" ");
		}
		return pointer_mc;
    }
*/
/*
    public function xul_reflex(current_mc){
		trace("XUL2Flash: XUL ReFlex compute in " + current_mc._exulis.id);
		var pointer_mc = current_mc;
		for(var vCpt=0;vCpt<current_mc._exulis._objDescendants.length;vCpt++){
			var aChild = current_mc._exulis._objDescendants[vCpt];
			trace("XUL2Flash: XUL ReFlex      child " + aChild._exulis.id);
			if((current_mc._exulis._type == "vbox") || (current_mc._exulis._type == "window")){
				aChild._exulis.left = 0;
				aChild._exulis.top = current_mc._exulis._nextElementFlexTopEdge;
				aChild._exulis.width = current_mc._exulis.width;
				if(aChild._exulis._numberFlex != 0){
//					aChild._exulis.height = (current_mc._exulis.height + current_mc._exulis._toDecreaseFlexHeight) * aChild._exulis._numberFlex / current_mc._exulis._totalFlex;
					if(((current_mc._exulis.height + current_mc._exulis._toDecreaseFlexHeight) * aChild._exulis._numberFlex / current_mc._exulis._totalFlex) < aChild._exulis._minFlexHeight){
						aChild._exulis.height = aChild._exulis._minFlexHeight;
						current_mc._exulis._totalFlex -= aChild._exulis._numberFlex;
						current_mc._exulis._toDecreaseFlexHeight -= aChild._exulis._minFlexHeight;
					}
					else{
						aChild._exulis.height = (current_mc._exulis.height + current_mc._exulis._toDecreaseFlexHeight) * aChild._exulis._numberFlex / current_mc._exulis._totalFlex;
					}
				}
				else{
					aChild._exulis.height = aChild._exulis._minFlexHeight;
				}
				current_mc._exulis._nextElementFlexTopEdge = aChild._exulis.top + aChild._exulis.height;
			}
			else{
				if(current_mc._exulis._type == "stack"){
					// no flex - XULstack
				}
				else{
					aChild._exulis.left = current_mc._exulis._nextElementFlexLeftEdge;
					aChild._exulis.top = 0;
					aChild._exulis.height = current_mc._exulis.height;
					if((aChild._exulis._type == "vbox") || (aChild._exulis._type == "window")){
						if((aChild._exulis._numberFlex != 0) && (current_mc._exulis._numberFlex != 0)){
							aChild._exulis.width = current_mc._exulis.width;
						}
						else{
							aChild._exulis.width = aChild._exulis._minFlexWidth;
						}
					}
					else{
						if((aChild._exulis._numberFlex != 0)){ //&& (current_mc._exulis._numberFlex != 0)){
							aChild._exulis.width = (current_mc._exulis.width + current_mc._exulis._toDecreaseFlexWidth) * aChild._exulis._numberFlex / current_mc._exulis._totalFlex;
						}
						else{
							aChild._exulis.width = aChild._exulis._minFlexWidth; //current_mc._exulis.width; //
						}

					}
					current_mc._exulis._nextElementFlexLeftEdge = aChild._exulis.left + aChild._exulis.width;
				}
			}
			trace("XUL2Flash: XUL ReFlex      child : left=" + aChild._exulis.left + ", top=" + aChild._exulis.top + ", width=" + aChild._exulis.width + ", height=" + aChild._exulis.height);
			aChild._exulis.setLayout();
			this.xul_reflex(aChild);
		}
	}
*/
/**
* @method translateXML
* @description core of the XUL2Flash class
*/
    public function translateXML (node:XML, current_mc) {
		var local_mc:MovieClip;
		var originalId:String;
		var _type:String = "";
		var _nodeName:String = node.nodeName;
		_nodeName = node.nodeName;
		_type = (_nodeName.indexOf(":") == -1) ? _nodeName : _nodeName.substr(_nodeName.indexOf(":") + 1);
// GUI factory begins here
		originalId = node.attributes.id;
//		trace("node: " + node);
		switch (_type){
// root element
			case "window":
				local_mc = xul_window(node,current_mc);
				break;
			case "xul":
				local_mc = xul_window(node,current_mc);
				break;
// containers
			case "box":
				local_mc = xul_box(node,current_mc);
				break;
			case "hbox":
				local_mc = xul_hbox(node,current_mc);
				break;
			case "groupbox":
				local_mc = xul_groupbox(node,current_mc);
				break;
			case "tabbox":
				local_mc = xul_tabbox(node,current_mc);
				break;
			case "vbox":
				local_mc = xul_vbox(node,current_mc);
				break;
			case "radiogroup":
				local_mc = xul_radiogroup(node,current_mc);
				break;
// widgets
			case "button":
				local_mc = xul_button(node,current_mc);
				break;
			case "checkbox":
				local_mc = xul_checkbox(node,current_mc);
				break;
			case "radio":
				local_mc = xul_radio(node,current_mc);
				break;
			case "splitter":
				local_mc = xul_splitter(node,current_mc);
				break;
			case "stack":
				local_mc = xul_stack(node,current_mc);
				break;
			case "svg":
				local_mc = xul_svg(node,current_mc);
				break;
			case "image":
				local_mc = xul_image(node,current_mc);
				break;
			case "textbox":
				local_mc = xul_textbox(node,current_mc);
				break;
			case "description":
				local_mc = xul_description(node,current_mc);
				break;
			case "label":
			local_mc = xul_label(node,current_mc);
				break;
// widgets for menus and toolbars
			case "menu":
				local_mc = xul_menu(node,current_mc);
				break;
			case "menulist":
				local_mc = xul_menulist(node,current_mc);
				break;
			case "listbox":
				local_mc = xul_listbox(node,current_mc);
				break;
			case "listitem":
				local_mc = xul_listitem(node,current_mc);
				break;
			case "menubar":
				local_mc = xul_menubar(node,current_mc);
				break;
			case "menuitem":
				local_mc = xul_menuitem(node,current_mc);
				break;
			case "menupopup":
				local_mc = xul_menupopup(node,current_mc);
				break;
			case "menuseparator":
				local_mc = xul_menuseparator(node,current_mc);
				break;
			case "tabs":
				local_mc = xul_tabs(node,current_mc);
				break;
			case "tab":
				local_mc = xul_tab(node,current_mc);
				break;
			case "tabpanels":
				local_mc = xul_tabpanels(node,current_mc);
				break;
			case "tabpanel":
				local_mc = xul_tabpanel(node,current_mc);
				break;
			case "toolbar":
				local_mc = xul_toolbar(node,current_mc);
				break;
			case "toolbarbutton":
				local_mc = xul_toolbarbutton(node,current_mc);
				break;
			case "toolbarseparator":
				local_mc = xul_toolbarseparator(node,current_mc);
				break;
			case "toolbox":
				local_mc = xul_toolbox(node,current_mc);
				break;
			case "keyset":
				local_mc = xul_keyset(node,current_mc);
				break;
			case "key":
				local_mc = xul_key(node,current_mc);
				break;
// here unhandled tags
			default:
				trace("XUL2Flash: XUL tag undefined: " + node.nodeName);
				local_mc = current_mc;
		}
		if(originalId != undefined){
			var vTmpCptDefs_num:Number;
			vTmpCptDefs_num = current_mc._exulis._targetExecutionLayer._objDefsRepository.add(node.attributes.id, local_mc);
//			trace("XUL2Flash: (objDefsRepository) after '" + node.attributes.id + "', "+ vTmpCptDefs_num + " items are now registered");
			if(current_mc._exulis._targetExecutionLayer.authorMode = "true"){
//				vTmpCptDefs_num = current_mc._exulis._targetExecutionLayer._objDefsRepository.add(node.attributes.id + "__" + current_mc._exulis.toolbox.wrapRun("xpath:///black:Manifest/@id", current_mc._exulis._guiSource,"SingleNode","String"), local_mc);
				vTmpCptDefs_num = current_mc._exulis._targetExecutionLayer._objDefsRepository.add(node.attributes.id + "__" + current_mc._exulis.toolbox.wrapRun("xpath:///black:manifest/@id", current_mc._exulis._guiSource,"SingleNode","String"), local_mc);
			}
		}
		return local_mc;
	}
}
