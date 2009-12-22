//****************************************************************************
// Flash-db Cellrenderer Tutorial
//****************************************************************************

import mx.core.UIComponent

class com.flashdb.IconCellRenderer extends UIComponent
{

	var car : MovieClip;
	var listOwner : MovieClip; // the reference we receive to the list
	var getCellIndex : Function; // the function we receive from the list
	var	getDataLabel : Function; // the function we receive from the list
	
	function IconCellRenderer()
	{
	}

	function createChildren(Void) : Void
	{	//carIcon is the linkage name or our icon in the library
		car = createObject("carIcon", "Car", 1, {styleName:this, owner:this});		
		size();
	}

	// note that setSize is implemented by UIComponent and calls size(), after setting
	// __width and __height
	function size(Void) : Void
	{
		car.setSize(20, 20);
		car._x = (__width)/2;
		car._y = (__height)/2;
	}

	function setValue(str:String, item:Object, sel:Boolean) : Void
	{		
		car._visible = (item!=undefined);
		//We move the head to the label matching 
		//current cell value
		car.gotoAndStop(item[getDataLabel()]);
	}

	function getPreferredHeight(Void) : Number
	{
		return 16;
	}

	function getPreferredWidth(Void) : Number
	{
		return 20;
	}

}
