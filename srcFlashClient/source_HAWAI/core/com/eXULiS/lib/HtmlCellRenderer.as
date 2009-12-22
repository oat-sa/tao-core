//****************************************************************************
// Html Cell Renderer
// by PhilFlash - http://philflash.inway.fr
// 
// version 1.30 : 15 Sep 2005
//   - version compatible Flash MX 2004 et Flash 8
//   - amélioration 
// version 1.25 : 12 Jan 2004
//   - style_sheet devient une variable statique pour optimisation 
//     sur chargement
// version 1.2 : 22 Dec 2003
//   - la sélection (highlight) apparait sur ce type de cellule
// version 1.1 : 7 Nov 2003
//   - correction d'un bug sur le scroll
// version 1.0 : 20 Oct 2003
//   - version initiale
//
// Dont't forget :
// - to create a new symbol in Flash MX2004
//   Insert > New Symbol
//   with properties :
//    Name : HtmlCellRenderer
//    Behavior : MovieClip : Checked
//   For Linkage:
//    Identifier: HtmlCellRenderer
//    AS 2.0 Class : HtmlCellRenderer
//    Export for Actionscript : Checked
//    Export for in first frame : Checked
//
// Example:
//   Column:
//   var col:mx.controls.gridclasses.DataGridColumn;
//   col = amazon_grd.getColumnAt(0);
//   col.width = 200;
//   col.cellRenderer = "HtmlCellRenderer";
//   col["htmlFunction"] = amazonHtmlFunction;
//
//   Header:
//   var col:mx.controls.gridclasses.DataGridColumn;
//   col = amazon_grd.getColumnAt(0);
//   col.headerText = "<author>Book:</author>";
//   col.headerRenderer = "HtmlCellRenderer";
//****************************************************************************

import mx.core.UIComponent;
import mx.controls.TextArea;
import TextField.StyleSheet;
import com.eXULiS.lib.*;


class com.eXULiS.lib.HtmlCellRenderer extends UIComponent
{

	static public var CssUrl:String;		// Global: URL for CSS stylesheet
	static public var style_sheet:StyleSheet = null;
	
	var htmlComponent:TextField;

 	var owner; // The row that contains this cell	
	var listOwner : MovieClip;   // the reference we receive to the list
	var getCellIndex : Function; // the function we receive from the list
	var	getDataLabel : Function; // the function we receive from the list
	
	var myClip : MovieClip; 
	
	var previousLabel:String = null; // for optimization
	
	var toolbox:Toolbox;

	function HtmlCellRenderer()
	{
		toolbox=new Toolbox();
	}

	function createChildren(Void) : Void
	{
		if (CssUrl != undefined && style_sheet == null) 
		{
			style_sheet = new TextField.StyleSheet();
			style_sheet.load(CssUrl);
  		}
		if (htmlComponent == undefined)
		{
			createLabel("htmlComponent", 1);
		}
		htmlComponent.html = true;
		htmlComponent.border = false;
		htmlComponent.multiline = false;
		htmlComponent.wordWrap = true;
		htmlComponent.selectable = true;
		htmlComponent.background = false;
		htmlComponent.styleSheet = style_sheet;
		size();		
	}

	// note that setSize is implemented by UIComponent and calls size(), after setting
	// __width and __height
	function size(Void) : Void
	{
		htmlComponent.setSize(__width-2, __height-2);
	}

	function setValue(str:String, item:Object, sel:Boolean) : Void
	{
		
		if(str=="[]")
		{
			myClip = this.createClassObject(mx.controls.CheckBox, "chBox", 1, {selected:false});
			
		} else if(str.toUpperCase()=="[X]")
		{
			myClip = this.createClassObject(mx.controls.CheckBox, "chBox", 1, {selected:true});
		}
		
		/*
		myClip.
		*/
		if (myClip)
		{
		var listenerObject:Object = new Object();
		listenerObject._item=item;
		listenerObject._columnName=getDataLabel();
		listenerObject.click = function(eventObject:Object) {
		
			
  		 //trace("myCheck "+this._columnName+" > "+this._item["id"]);
		  
		};
		myClip.addEventListener("click", listenerObject);
		}
		
		
		
		// Ligne vide ou Header
		if (item == undefined) 
		{   
			// Special case for headerRenderer
			htmlComponent.htmlText = toolbox.wrapRun(str);
			previousLabel = null;
			return;
		}
		
		var columnIndex = this["columnIndex"]; // private property (no access function)
		var columnName = getDataLabel();
		var htmlFunction : Function = listOwner.getColumnAt(columnIndex).htmlFunction;
		if (htmlFunction != undefined) 
		{
			var label = htmlFunction(item, columnName);
			
			if (label != undefined) 
			{
				// Important pour optimisation
				// Empêche un flip-flop des images
				if (label != previousLabel) 
				{
					
					trace("baxa"+item[columnName+"_dataToDisplay"]);
										
					if (item[columnName+"_dataToDisplay"]!=undefined)
					{
					
						if (item[columnName+"_dataToDisplay"]=="")
						{
						htmlComponent.htmlText = toolbox.wrapRun(item[columnName]);
						} else
							{
							htmlComponent.htmlText = toolbox.wrapRun(item[columnName+"_dataToDisplay"]);
							}
						
					} else
					{
					htmlComponent.htmlText = previousLabel = toolbox.wrapRun(label);
					}
				} 
			} 
			else 
			{
				htmlComponent.htmlText = "";				
			}
		}
		else 
		{
			htmlComponent.htmlText = toolbox.wrapRun(str);				
		}
	}

	function getPreferredHeight(Void) : Number
	{
		if (owner == undefined) return 18;
		return owner.__height - 2;
	}

 	//function getPreferredWidth :: only really necessary for menu
}

