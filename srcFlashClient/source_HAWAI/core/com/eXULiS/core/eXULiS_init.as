System.security.allowDomain("*");
import com.eXULiS.core.eXULiS; // withFontsManager;

Stage.align = "TL";
Stage.scaleMode = "noScale";

var this_mc:MovieClip;
this_mc = this; //_root
this_mc._lockroot = true;

var _objXLIFFholder_obj:Object = new Object();

if (this_mc.file != undefined) {
	// eXULiS standard mode
	this_mc.lgGUI = (this_mc.lgGUI == undefined) ? "" : this_mc.lgGUI;
	this_mc.authorMode = (this_mc.authorMode == undefined) ? false : true;
}
else {
	// eXULiS embedded mode
	/*for(var nam in this_mc._parent._image_defRepository){
		trace("eXULiS init: " + nam + " = " + this_mc._parent._image_defRepository[nam]);
	}*/
	var image_arg_str = this_mc._parent._image_defRepository._image_arg;
	var posArg_num:Number = image_arg_str.indexOf("lgGUI=");
	if(posArg_num != -1){
		posArg_num = posArg_num + 6;
		var posEndArg_num:Number = image_arg_str.indexOf("&", posArg_num);
		if(posEndArg_num != -1){
			this_mc.lgGUI = "_" + image_arg_str.slice(posArg_num,posEndArg_num);
		}
		else{
			this_mc.lgGUI = "_" + image_arg_str.substr(posArg_num);
		}
	}
	else{
		this_mc.lgGUI = "";
	}
	posArg_num = image_arg_str.indexOf("file=");
	if(posArg_num != -1){
		posArg_num = posArg_num + 5;
		posEndArg_num = image_arg_str.indexOf("&", posArg_num);
		if(posEndArg_num != -1){
			this_mc.file = image_arg_str.slice(posArg_num,posEndArg_num);
		}
		else{
			this_mc.file = image_arg_str.substr(posArg_num);
		}
	}
	else{
		this_mc.file = "";
	}
	posArg_num = image_arg_str.indexOf("authorMode=");
	if(posArg_num != -1){
		posArg_num = posArg_num + 11;
		posEndArg_num = image_arg_str.indexOf("&", posArg_num);
		if(posEndArg_num != -1){
			this_mc.authorMode = image_arg_str.slice(posArg_num,posEndArg_num);
		}
		else{
			this_mc.authorMode = image_arg_str.substr(posArg_num);
		}
	}
	else{
		this_mc.authorMode = "";
	}
}
var eXULiS:com.eXULiS.core.eXULiS;
var guiDefFile_str;
var guiDefFileExtension_str;

function start(){
	trace("AAA init start with: " + _root.this_mc);
	_root.eXULiS = new com.eXULiS.core.eXULiS(_root.this_mc);
	_root.guiDefFile_str = _root.this_mc.file;
/*
	_root.guiDefFileExtension_str = _root.guiDefFile_str.substr(_root.guiDefFile_str.lastIndexOf("."));
	if(_root.guiDefFileExtension_str != ".php"){
		_root.guiDefFile_str = _root.guiDefFile_str.substr(0,_root.guiDefFile_str.lastIndexOf(".")) + _root.this_mc.lgGUI + _root.guiDefFileExtension_str;
	}
*/
	trace("AAA init file: "+ _root.guiDefFile_str);
	_root.eXULiS.setGuiDefinitionFile(_root.guiDefFile_str);
	_root.eXULiS.buildGUI("local");
	_level0.starter_mc = _root.this_mc;
}

function restart(guiDef_str,guiDefFile_str){
	trace("AAA init restart with guiDef_str: " + guiDef_str);
	trace("AAA init restart with guiDefFile_str: " + guiDefFile_str);
	trace("AAA init restart with _root.this_mc: " + _root.this_mc);
	trace("AAA init restart with _root.eXULiS: " + _root.eXULiS);
	_root.eXULiS.cleanGUI();
	trace("after cleanGUI");
	delete _root.eXULiS;
	_root.this_mc._objDefsRepository.clean();
	trace("after repository cleanup");
	_root.eXULiS = new com.eXULiS.core.eXULiS(_root.this_mc);
	if(guiDef_str != ""){
		_root.eXULiS.setGuiDefinition(guiDef_str);
	}
	else{
		if(guiDefFile_str != ""){
			_root.eXULiS.setGuiDefinitionFile(guiDefFile_str);
		}
	}
	_root.eXULiS.buildGUI("local");
}
start();