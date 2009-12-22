import net.tekool.utils.Relegate;
import com.xfactorstudio.xml.xpath.*;
import com.eXULiS.lib.*;


class com.eXULiS.lib.ItemSpreadSheetModel {	
	
	// current XML
	private var currentXml_xml:XML;
	
	// path to shared Object
	private var sharedObjPath_str:String;	
	
	// ref to controller
	private var controllers_ar:Array;
	
	private var view_mc:MovieClip;
	
	// ERR MSG	
	private var error_str:String;
	
	// state of xml loading
	private var xmlState_bo:Boolean;
	
	// @Path2XmlFile_str : path to LoadXmlFile
	// @soName_str : 
	public function ItemSpreadSheetModel()
	{		
		trace("[ItemSpreadSheetModel]");		
		init();		
	}
	
	private function init()
	{
		trace("[controllerNotify]");
		controllers_ar=new Array();
		error_str="";
	}
	
	public function test()
	{		
		trace("ZETEST MODEL OK");
	}
	
	public function addController(controller):Boolean
	{
		trace("[ItemSpreadSheetModel] addController: controllers_ar.length = " + controllers_ar.length);
		// test if controller is not already in
		for (var i in controllers_ar)
		{
			if (controllers_ar[i]==controller)
			{
				trace("[ItemSpreadSheetModel] > [addController] this controller already in");
				return false;
			}
		}
		controllers_ar.push(controller);
	}	
	
	private function controllerNotify():Void
	{
		trace("[controllerNotify");
		for (var a in controllers_ar)
		{
			controllers_ar[a].updatedInfos();
		}
	}	
		
	private function deleteController(controller):Void
	{
		
		for (var a=0;a<controllers_ar.length;a++)
		{
			if (controllers_ar[a]==controller)
			{
				controllers_ar.splice(a,1);
			}
		}
			
	}	
	
	public function getXML(Path2XmlFile_str:String,sharedObjPath_str:String):Void
	{
		trace("MODEL --- [getXML]");
		this.sharedObjPath_str=sharedObjPath_str;
		// load XML File
		loadXML(Path2XmlFile_str);		
	}
	
	
	// @sharedObjectPath_str : folder's name
	
	public function recover(sharedObjPath_str:String):Void
	{
		trace("[recover]");
		var _sharedObject_so:SharedObject=SharedObject.getLocal("/" + sharedObjPath_str+"/fileSystem");
		currentXml_xml=_sharedObject_so.data.xmlTree;
		_sharedObject_so.flush();
	}
	
	// ------------------------ XML -----------------------------------
	
	// 	loadXML(file_str);
	// @file_str : path of xml file to load on class instentiation
	private function loadXML(file_str:String):Void
	{
		trace("[loadXML]");
		currentXml_xml=new XML();
		currentXml_xml.ignoreWhite=true;
		currentXml_xml.load(file_str);
		_root.view_mc=view_mc;
		currentXml_xml.onLoad=Relegate.create(this,doAfterXmlLoading);
	}

	private function loadXMLstring(content_str:String):Void
	{
		trace("[BLACKspreadSheet ItemSpreadSheetModel] > [loadXMLstring]: " + content_str);
		currentXml_xml=new XML(content_str);
		currentXml_xml.ignoreWhite=true;
		setXMLState(true);
		
		trace("[BLACKspreadSheet ItemSpreadSheetModel] > [loadXMLstring]: " + currentXml_xml);
			
		initControllers();
	}

	private function setXMLState(value_bo:Boolean):Void
	{
		xmlState_bo = value_bo;
	}
	
	public function getXMLState():Boolean
	{
		return xmlState_bo;
	}
	
	public function getListAttributes():String
	{
		return currentXml_xml.childNodes[0].attributes.listheader;
	}

	// when xml is loading do this
	private function doAfterXmlLoading(ref)
	{
		if (currentXml_xml.status==0){

			//broadcast init to attached controller

			setXMLState(true);
			initControllers();
		} 
		else
		{
			translateXmlError(currentXml_xml.status);
		}
	}	
	
	
	private function initControllers():Void
	{
		trace("initControllers");
		for (var a in controllers_ar)
		{
			controllers_ar[a].init();
		}
	}
	
	
	
	// in case of xml error translate error code in entire words
	private function translateXmlError(code_nbr:Number):Void
	{
		switch (code_nbr)
		{
			case -2 :
			trace("### A CDATA section was not properly terminated. ###");
			break;
			case -3 :
			trace("### The XML declaration was not properly terminated. ###");
			break;
			case -4 :
			trace("### The DOCTYPE declaration was not properly terminated. ###");
			break;
			case -5 :
			trace("### A comment was not properly terminated. ###");
			break;
			case -6 :
			trace("### An XML element was malformed. ###");
			break;
			case -7 :
			trace("### Out of memory. ###");
			break;
			case -8 :
			trace("### An attribute value was not properly terminated. ###");
			break;
			case -9 :
			trace("### A start-tag was not matched with an end-tag. ###");
			break;
			case -10 :
			trace("### An end-tag was encountered without a matching start-tag. ###");
			break;
			default:
			trace("### XML ERROR - UNKNOW CODE ###");				
		}
	}
	
	// XML Manipulation methode
	
	//public function renameItem(nodePath_str:String, oldName_str:String, newName_str:String) {
	public function renameItem(id_str:String,newName_str:String):String {		
		trace("[renameItem]");
		// search Item with Xpath
		
		//trace(currentXml_xml);
		// test the type of target path or id, if path convert it to id
		
		// test carcater length
		if (newName_str.length<1 or newName_str.length>9)
		{
			trace("[renameItem] >  new name must be between 1 and 9 chcaracters");
			error_str = " new name must be between 1 and 9 characters";
			setErrorMessage(error_str);			
			return;
		}	
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		
		if (testNameSiblingTarget(id_str,newName_str)==true)
		{
			trace("[renameItem] >  Name already in use");
			error_str = " Name already in use";
			setErrorMessage(error_str);
			
			return;
		}	
		
		// test if file is writable;
		
		if (testLock(id_str)==true)
		{
			trace("[renameItem] >  file is locked");
			error_str = " file is locked";	
			return;
		}	
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// verifier si les noeuds frere n'ont pas le meme nom
		
		// test if doc exist		
		if (nodes_ar.length<1)
		{
			trace("[renameItem] >  target doesn't exist");
			error_str = " target doesn't exist";
			setErrorMessage(error_str);
			return;
		}
		
		//rename;
		nodes_ar[0].attributes.label=newName_str;
		
		// update xml Object
		updateSharedXMLTree();
		controllerNotify();
		trace(currentXml_xml);		
		return "ok";
  		
	}
	
	// move possible if folder us locked ???
	// move an item from / to path or id is detected
	public function moveItem(id_str:String,idTarget_str:String):String {
		
		
		trace("[moveItem ] > "+id_str+" > "+idTarget_str);
		
		trace("[moveItem]");
		// search Item with Xpath		
		// test the type of target path or id, if path convert it to id
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		
		// test the type of target path or id, if path convert it to id
		if (idTarget_str.indexOf("/")!=-1)
		{
			idTarget_str=path2Id(idTarget_str);
		}		
		
		var _element_ar:Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");
		var _target_ar:Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+idTarget_str+"']");
		
		if (_element_ar.length<1)
		{
			trace("[moveItem]  File does not exist");
			error_str = "File does not exist";
			setErrorMessage(error_str);
			return;
		}		
		
		if (_target_ar[0].attributes.type=="file")
		{
			trace("[moveItem] >  Target not a Folder or Storage"+_target_ar[0].label+" > "+_target_ar[0].attributes.type);
			error_str = "Target not a Folder or Storage";
			controllerNotify();
			setErrorMessage(error_str);
			return;
		}
		
		// test if moved file name already exist in target folder
		
		var _targetName_bool:Boolean=testNameChildTarget(idTarget_str,_element_ar[0].attributes.label);		
		trace("///// "+_targetName_bool);
		
		if (_targetName_bool)
		{
			trace("[moveItem] >  target already name exist");
			error_str = " target already name exist";
			controllerNotify();
			setErrorMessage(error_str);
			return;
		}		
		
		
		// test if target is children of moved element		
		
		var _isChild_bool:Boolean=isChild(_element_ar[0].attributes.id,_target_ar[0].attributes.id);
		
		if (_isChild_bool)
		{
			trace("[moveItem] >  target can't be a son of moved element");
			error_str = " target can't be a son of moved element";
			controllerNotify();
			setErrorMessage(error_str);
			return;
		}	
		
		_target_ar[0].appendChild(_element_ar[0]);
		
		
		//update Shared Object
		updateSharedXMLTree();
		controllerNotify();
		trace(currentXml_xml);		
		return "ok";  		
	}
	
	
	// verify if node or child of node a locked
	
	public function deleteItem(id_str:String):String {
		
		trace("[deleteItem]");
		
		// test the type of target path or id, if path convert it to id
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		// test if file is writable;
		
		if (testLock(id_str)==true)
		{
			trace("[deleteItem] >  file is locked");
			error_str = " file is locked";			
			setErrorMessage(error_str);
			return;
		}		
		// target node
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// test if descendant have locked attributes true or locked
		var _testChildLockNode_ar:Array=XPathAxes.descendantOrSelf(nodes_ar[0]);
		
		for (var f in _testChildLockNode_ar)
		{
			if (_testChildLockNode_ar[f].attributes.lock=="true" or _testChildLockNode_ar[f].attributes.lock=="lock")
			{
				trace("[deleteItem] >  child file is locked");
				error_str = " file is locked";	
				setErrorMessage(error_str);
				return;
			}			
		}
		
		// erase node
		nodes_ar[0].removeNode();		
		// update Shared Object
		updateSharedXMLTree();
		controllerNotify();
		trace(currentXml_xml);
		
		return "ok";
		
	}	
	
	// save content of document
	public function saveDocumentContent(id_str:String,toSave:String):String
	{		
		// test if document alreadyExist
		trace("[saveDocumentContent]");
		// test the type of target path or id, if path convert it to id		
		var _path_str:String=id_str;
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
		
		// if id = undefined error				
		
		// target node
		var _doc_ar:Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");
		
		if (_doc_ar.length<1)
		{
			if (_path_str.indexOf("/")==0)
			{
				trace("[saveDocumentContent] > Path is not valid");
				error_str = " Path is not valid";
				setErrorMessage(error_str);
				return;
			}			
			
			var _explodePath_ar:Array=_path_str.split("/");			
			
			// recreate path
			var _recomposedString_str:String="";
			
			// -1 to shift last value we suppose to be new file name of file and / of path
			for (var q=0;q<_explodePath_ar.length-1;q++)
			{
				_recomposedString_str+=_explodePath_ar[q]+"/";				
			}
			
			_recomposedString_str=_recomposedString_str.substr(0,_recomposedString_str.length-1);
			
			trace("**** "+_recomposedString_str);
			
			_path_str=path2Id(_recomposedString_str);			
			
			var _path_ar:Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+_path_str+"']");			
			
			// test if path exist
			
			if (_path_ar.length<1)
			{
				trace("[saveDocumentContent] >  Path error");
				error_str = " Path error";
				setErrorMessage(error_str);
				return;
			}
			
			// test if folder is locked
			
			if (testLock(_path_str)==true)
			{
			trace("[saveDocumentContent] >  file is locked");
			error_str = " file is locked";		
			setErrorMessage(error_str);
			return;
			}		
					
			var _id_str:String=findNonUsedId();
			
			var _content_str:String='<item id="'+_id_str+'" label="'+_explodePath_ar[_explodePath_ar.length-1]+'" type="file" date=""  application="" >';
			_content_str+="<![CDATA[ " +toSave+ " ]]></item>";
			
			
			trace("[saveDocumentContent] >  newNode "+_content_str);
				
			var _saveNewDoc_xml:XML=new XML(_content_str);
			
			// save new Content
			_path_ar[0].appendChild(_saveNewDoc_xml);
			
			// set the date of this new Node
			setDate(_id_str);
			
			trace("SAVED !!!! ---> "+currentXml_xml);			
			
			updateSharedXMLTree();
			controllerNotify();
			return "ok";						
		}
		
		// target is a file
		if (_doc_ar[0].attributes.type=="file")
		{
			// test if target name already exist
			if (testLock(id_str)==true)
			{
			trace("[saveDocumentContent] >  file is locked");
			error_str = " file is locked";		
			setErrorMessage(error_str);
			return;
			}
			
			// if there is already content empty old content
			_doc_ar[0].firstChild.removeNode();
			
			
			var _content_str:String="<![CDATA[ " +toSave+ " ]]>";
				
			var _saveNewDoc_xml:XML=new XML(_content_str);
			
			// save new Content
			_doc_ar[0].appendChild(_saveNewDoc_xml);
			
			trace("[saveDocumentContent] > SAVED !!!! > "+_doc_ar[0]);
			
			
			trace("---> "+currentXml_xml);
			
			updateSharedXMLTree();
			controllerNotify();
			return "ok";			
		} else
		
		{
			// path is disk or folder
			trace("[saveDocumentContent] >  missing filename on path");
			error_str = " missing filename on path";
			setErrorMessage(error_str);
			return;
		}		
		
	}
	
	// open document
	// toDo : type verification
	public function readDocumentContent(id_str):String
	{
		// test if document alreadyExist
		trace("[readDocumentContent]");
		// test the type of target path or id, if path convert it to id
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		// target node
		var _doc_ar:Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		if (_doc_ar[0].attributes.type!="file")
		{
			trace("[readDocumentContent] >  it's not a file");
			error_str = " it's not a file";
			setErrorMessage(error_str);
			return;
		}		
		// read textNode
		trace("[readDocumentContent] Result > "+_doc_ar[0].firstChild.nodeValue);
		return _doc_ar[0].firstChild.nodeValue;		
		return "ok";		
	}
	
	private function path2Id(path_str):String
	{
		trace("[path2Id] "+path_str);
		// chain from user entry in textField (for examle)
		var _nameFromPath:Array=path_str.split("/");		
		// construct chain for Xpath Resquest
		var _requestChain_str="";
		
		for (var a=0; a<_nameFromPath.length;a++)
		{
		  _requestChain_str+="/item[@label='"+_nameFromPath[a]+"']";
		}
		
		 var _request_ar:Array=XPath.selectNodes(currentXml_xml, _requestChain_str);		
		// return id of specified path
		return _request_ar[0].attributes.id;
				
	}
		
	// test if name already exist in sibling element 
	// usefull for dialog box
	public function testNameSiblingTarget(id_str:String,testedName_str:String):Boolean
	{
		
		trace("[testNameSiblingTarget]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// verifier si les noeuds frere n'ont pas le meme nom		
		
		var _verif_follow_ar:Array=XPathAxes.followingSibling(nodes_ar[0]);
		var _verif_preceding_ar:Array=XPathAxes.precedingSibling(nodes_ar[0]);		
		var _verif_ar:Array=_verif_preceding_ar.concat(_verif_follow_ar);		
		
		// EXCEPTIONS
		// test if a brother node have the same name
		for (var a in _verif_ar)
		{			
			
			if (_verif_ar[a].attributes.label==testedName_str)
			{
				trace("[testNameSiblingTarget] >  Target Name Already Exist");
				error_str = " Target Name Already Exist";
				setErrorMessage(error_str);
				return true;
			}
		}		
		return false;		
	}
	
	public function getXMLFolderTree()
	{
		trace("[getXMLFolderTree]");
		
		
		
		var _newXmlTree_str:String=String(currentXml_xml);
		
		var _newXmlTree:XML=new XML(_newXmlTree_str);
		
		
		var nodes_ar : Array = XPath.selectNodes(_newXmlTree, "//item[@type='file']");
		
		
		trace("///////////////////// "+nodes_ar);
		
		for (var a in nodes_ar)
		{
			nodes_ar[a].removeNode();
		}
		
		
		
		return _newXmlTree;
		
	}
	
	// test if name already exist in childs of this node (folder)
	// usefull for dialog box
	public function testNameChildTarget(id_str:String,testedName_str:String):Boolean
	{
		trace("[testNameChildTarget]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// verifier si les noeuds frere n'ont pas le meme nom
				
		var _verif_child_ar:Array=XPathAxes.child(nodes_ar[0]);
	
		for (var j in _verif_child_ar){
			
			trace("....>> "+_verif_child_ar[j]);
		}		
		// EXCEPTIONS
		// test if a brother node have the same name
		for (var a in _verif_child_ar)
		{			
			trace("------------->>>>> "+_verif_child_ar[a].attributes.label+" testedName_str : "+testedName_str);			
			if (_verif_child_ar[a].attributes.label==testedName_str)
			{
				trace("[testNameChildTarget] >  Target Name Already Exist");
				error_str = " Target Name Already Exist";
				setErrorMessage(error_str);
				return true;
			}
		}		
		return false;		
	}
	
	// test if name already exist in childs of this node (folder)
	// usefull for dialog box
	public function isChild(id_str:String,testedChild_str:String):Boolean
	{
		trace("[isChild]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
		
		if (testedChild_str.indexOf("/")!=-1)
		{
			testedChild_str=path2Id(id_str);
		}
		
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");
		
		var childs_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+testedChild_str+"']");
		// verifier si les noeuds frere n'ont pas le meme nom
				
		var _verif_child_ar:Array=XPathAxes.ancestorOrSelf(childs_ar[0]);
	
		
		// EXCEPTIONS
		// test if a brother node have the same name
		for (var a in _verif_child_ar)
		{			
						
			if (_verif_child_ar[a].attributes.id==nodes_ar[0].attributes.id)
			{
				trace("[isChild] >  element is a child");
				error_str = "element is a child";
				setErrorMessage(error_str);
				return true;
			}
		}		
		return false;		
	}
	
	
	// test if file is locked
	
	private function testLock(id_str:String):Boolean
	{
		trace("[testLock]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// verifier si les noeuds frere n'ont pas le meme nom
		
		switch(nodes_ar[0].attributes.lock)
		{
			case "true":
				trace("[testLock] >  File is Locked");
				error_str = "[testLock] >  File is Locked";
				setErrorMessage(error_str);
				return true;
			break;
			case "false":
				
			break;
			case "lock":
				trace("[testLock] >  File is Locked");
				error_str = "[testLock] >  File is Locked";
				setErrorMessage(error_str);
				return true;
			break;
			case "unlock":
				
			break;
			default:
			
		}				
	}
	
	// test if file is locked
	
	private function setLock(id_str:String,newState_str:String):String
	{
		
		trace("[setLock] : "+newState_str);
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		
		if (newState_str!="true" or newState_str!="false")
		{
			trace("[setLock] >  invalid lock type argument");
			error_str = " invalid lock type argument";
			setErrorMessage(error_str);
			return;
		}
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// verifier si les noeuds frere n'ont pas le meme nom
		
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");
		
		if (nodes_ar[0].attributes.lock=="lock"){
			trace("[setLock] >  file is admin locked");
			error_str = " file is admin locked";
			setErrorMessage(error_str);
			return;
		}
		
		if (nodes_ar[0].attributes.lock=="unlock"){
			trace("[setLock] >  file is admin unlocked");
			error_str = " file is admin unlocked";
			setErrorMessage(error_str);
			return;
		}
		
		nodes_ar[0].attributes.lock=newState_str;
		
		updateSharedXMLTree();
		
		return "ok";
		
	}
	
	
	public function getDisplayType(id_str):String
	{
		trace("[getDisplayType]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// verifier si les noeuds frere n'ont pas le meme nom
		
	
		
		if (nodes_ar[0].attributes.type!="folder" and nodes_ar[0].attributes.type!="disk"){
			
			trace("[getDisplayType] >  Target Type not a folder or disk");
			error_str = " Target Type not a folder or disk";
			setErrorMessage(error_str);
			return;
		}
		
		trace("[getDisplayType] "+nodes_ar[0].attributes.display);
		return nodes_ar[0].attributes.display;
		
		
	}
	

	public function setDisplayType(id_str,newType_str):String
	{
		trace("[setDisplayType]");
		
		
		if (newType_str!="icons" or newType_str!="list")
		{
			trace("[setDisplayType] >  invalid display type argument");
			error_str = " invalid display type argument";
			setErrorMessage(error_str);
			return;
		}
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
			
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		// verifier si les noeuds frere n'ont pas le meme nom
		
	
		
		if (nodes_ar[0].attributes.type!="folder" and nodes_ar[0].attributes.type!="disk"){
			
			trace("[setDisplayType] >  Target Type not a folder or disk");
			error_str = " Target Type not a folder or disk";
			setErrorMessage(error_str);
			return;
		}
		
		trace("[getDisplayType] "+nodes_ar[0].attributes.display);
		
		nodes_ar[0].attributes.display=newType_str;
		
		updateSharedXMLTree();
		controllerNotify();
		return "ok";
		
	}
	
	
	private function findNonUsedId():String
	{
		var _prefix_str:String="item";
		var _suffix_nbr:Number=0;
		
		var _testedName_str:String=_prefix_str+_suffix_nbr;
		
		while(true)
		{
		_testedName_str=_prefix_str+_suffix_nbr;
		
		trace(">>>>>>>>>>"+_testedName_str);
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+_testedName_str+"']");
		
		if (nodes_ar.length==0){
			break;
		}
		
		_suffix_nbr+=5;
		}
		
		trace("Unused item name "+_testedName_str);
		return _testedName_str;
		
	}
	
	// Get File List From pointer to his child
	
	public function getPartialTree(id_str):String
	{
		
		trace("[getPartialTree]");
		
		if (id_str=="root"){
			return String(currentXml_xml);
		}
		
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
		
		
		
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");
		
		
		var _child_tree_ar:Array=XPathAxes.descendantOrSelf(nodes_ar[0]);
	
		
		
		
		// if path or id not exist
		if (_child_tree_ar[0]==undefined)
		{
			
			trace("[getPartialTree] >  Path does not exist");
			error_str = "[getPartialTree] >  Path does not exist";
			setErrorMessage(error_str);
			return;
		}
		
		
		
		return _child_tree_ar[0];		
		
	
		// if path or id not exist
		if (_child_tree_ar[0]==undefined)
		{
			
			trace("[getPartialTree] >  Path does not exist");
			error_str = "[getPartialTree] >  Path does not exist";
			setErrorMessage(error_str);
			return;
		}
		
		
		//trace("[getPartialTree] > "+_child_tree_ar[0]);
		return String(_child_tree_ar[0]);		
		
	}
	
	
	// Get File List From pointer to his child
	
	public function getParent(id_str):String
	{
		
		trace("[getParent]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		var _child_tree_ar:Array=XPathAxes.parent(nodes_ar[0]);		
		// if path or id not exist
		if (_child_tree_ar[0]==undefined)
		{
			
			trace("[getPartialTree] >  Path does not exist");
			error_str = "[getPartialTree] >  Path does not exist";
			setErrorMessage(error_str);
			return;
		}		
		return _child_tree_ar[0];			
		// if path or id not exist
		if (_child_tree_ar[0]==undefined)
		{
			
			trace("[getPartialTree] >  Path does not exist");
			error_str = "[getPartialTree] >  Path does not exist";
			setErrorMessage(error_str);
			return;
		}		
		//trace("[getPartialTree] > "+_child_tree_ar[0]);
		return String(_child_tree_ar[0]);				
	}
	
	// DATE Attributes
	
	public function getDate(id_str:String):String
	{
		
		trace("[getDate]");		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		if (nodes_ar[0].attributes.date==undefined)
		{			
			trace("[getDate] >  Path or file does not exist");
			error_str = " Path or file does not exist";
			setErrorMessage(error_str);
			return;
		}
		
		trace("[getDate] > "+nodes_ar[0].attributes.date);
		return nodes_ar[0].attributes.date;		
	}
	
	private function setDate(id_str:String):String
	{
		trace("[setDate]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		var _currentDate=new Date();
		var _curYear_str:String=String(1900+_currentDate.getYear());
		var _curMonth_str:String=(_currentDate.getMonth()<10) ? "0"+String(_currentDate.getMonth()) : String(_currentDate.getMonth());
		var _curDay_str:String=(_currentDate.getDay()<10) ? "0"+String(_currentDate.getDay()) : String(_currentDate.getDay());
		var _curHrs_str:String=(_currentDate.getHours()<10) ? "0"+String(_currentDate.getHours()) : String(_currentDate.getHours());
		var _curMin_str:String=(_currentDate.getMinutes()<10) ? "0"+String(_currentDate.getMinutes()) : String(_currentDate.getMinutes());
		var _curSec_str:String=(_currentDate.getSeconds()<10) ? "0"+String(_currentDate.getSeconds()) : String(_currentDate.getSeconds());		
		var _date_str:String=_curYear_str+_curMonth_str+_curDay_str+_curHrs_str+_curMin_str+_curSec_str;		
		trace("[setDate] > "+_date_str);
		
		if (nodes_ar[0].attributes.date==undefined)
		{
			trace("[setDate] >  Path or file does not exist");
			error_str = " Path or file does not exist";
			setErrorMessage(error_str);
			return;
		}		
		trace("[getDate] > "+nodes_ar[0].attributes.date);
		nodes_ar[0].attributes.date=_date_str;
		updateSharedXMLTree();
		controllerNotify();
		trace("__>> "+currentXml_xml);		
	}
	
	// Application Type
	
	public function getApplicationType(id_str:String):String
	{		
		trace("[getApplicationType]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}
		
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");
		
		if (nodes_ar[0].length<1)
		{			
			trace("[getApplicationType] >  Path or file does not exist");
			error_str = " Path or file does not exist";
			setErrorMessage(error_str);
			return;
		}
		
		if (nodes_ar[0].attributes.application==undefined)
		{
			trace("[getApplicationType] >  Application attributes not found");
			error_str = "  application attributes not found";
			setErrorMessage(error_str);
			return;
		}		
		trace("[getApplicationType] > "+nodes_ar[0].attributes.application);
		return nodes_ar[0].attributes.application;		
	}
	
	public function setApplicationType(id_str:String,type_str:String):String
	{		
		trace("[setApplicationType]");
		
		if (id_str.indexOf("/")!=-1)
		{
			id_str=path2Id(id_str);
		}		
		var nodes_ar : Array = XPath.selectNodes(currentXml_xml, "//item[@id='"+id_str+"']");		
		if (nodes_ar[0].length<1)
		{			
			trace("[setApplicationType] >  Path or file does not exist");
			error_str = " Path or file does not exist";
			setErrorMessage(error_str);
			return;
		}
		
		// set application type
		nodes_ar[0].attributes.application=type_str;		
		updateSharedXMLTree();
		controllerNotify();
		trace("[setApplicationType] > "+nodes_ar[0].attributes.application);
		return "ok";		
	}	
	
	// SORTING Elements	
	// Shared Object
	// PRIVATE SHARED OBJECT
	// @name_str > current item name
	private function createSharedXMLTree():Void
	{		
		trace("[createSharedXMLTree]");		
		var _sharedObject_so:SharedObject=SharedObject.getLocal("/" + sharedObjPath_str+"/fileSystem");
			// erase this to work on production
			//trace("SHARED OBJECT ERASED FOR TEST DON'T FORGET TO DELETE !!!!");
			
			_sharedObject_so.data.xmlTree=undefined;			
			if (_sharedObject_so.data.xmlTree==undefined)
			{				
				_sharedObject_so.data.xmlTree=currentXml_xml;
				_sharedObject_so.flush();			
			} else
				{
					trace("Shared Objet Already here "+_sharedObject_so.data.xmlTree);
					trace(currentXml_xml.status);
					currentXml_xml=_sharedObject_so.data.xmlTree;
					trace("+++ "+currentXml_xml);					
				}				
				
	}
	
	// display error
	private function setErrorMessage(error_str:String):Void
	{
		_root.error_mc.output_tf.text=error_str
		_global.setTimeout(function(){_root.error_mc.output_tf.text=""},4500);
	}
	
	// update existin shared Object with new changes
	private function updateSharedXMLTree():Void
	{		
		trace("[updateSharedXMLTree]");		
		var _sharedObject_so:SharedObject=SharedObject.getLocal("/" + sharedObjPath_str+"/fileSystem");				
		_sharedObject_so.data.xmlTree=currentXml_xml;
		_sharedObject_so.flush();									
	}
	
	// Clear Shared Object
	// update existin shared Object with new changes
	private function clearSharedXMLTree():Void
	{		
		trace("[clearSharedXMLTree]");		
		var _sharedObject_so:SharedObject=SharedObject.getLocal("/" + sharedObjPath_str+"/fileSystem");				
		_sharedObject_so.data.xmlTree=undefined;
		_sharedObject_so.flush();							
	}
	
	public function viewSharedObj():XML
	{
		trace("[viewSharedObj]");
		var _sharedObject_so:SharedObject=SharedObject.getLocal("/" + sharedObjPath_str+"/fileSystem");				
				return _sharedObject_so.data.xmlTree;		
	}
	
	public function viewXMLCurrentState():XML
	{
		trace("[viewXMLCurrentState]");		
				return currentXml_xml;				
	}
	
}