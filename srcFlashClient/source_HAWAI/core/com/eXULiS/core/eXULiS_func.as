stop();
import net.tekool.utils.Relegate;
import com.eXULiS.lib.*;
import com.xfactorstudio.xml.xpath.*;
import com.eXULiS.XUL.XULbox;
import mx.transitions.Tween;
import mx.transitions.easing.*; 

// hide flash contextual menu
Stage.showMenu = false;

var r4ref, r5ref;

_level0.currentItemRootLevel.era_stimulus_ref = this;

var FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR:String = "|*$";
var FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR:String = "=";

var leading;
var renderedBox:com.eXULiS.XUL.XULbox;

function buildHawai(){
	var renderedSVG:Object;
	var renderedNode_xml:XML;
	renderedNode_xml = new XML('<box id="hawai" top="0" left="0" visible="true" type="content"><![CDATA[' + _level0.currentItemRootLevel.itemXmlFile_str + ']]></box>');
	renderedBox = new XULbox(this,renderedNode_xml.firstChild);
	var local_mc = renderedBox.create();
}

function getVal4Sum(target_str){
	var localVal_num:Number = 0;
	target_obj = this._objDefsRepository.retrieve(target_str);	
	target_obj.html = false;
	localVal_num = ((target_obj.text == undefined) || (target_obj.text == "")) ? 0 : parseInt(target_obj.text);
	target_obj.html = true;
	return(localVal_num);
}

function computeSum(){
	trace("computeSum entered");
	var target_fmt:TextFormat;
	var args:Array = arguments[0];
	var referer_obj = args[1];
	var newTotal_num:Number = 0;
	var newTotal_str:String;
	var referer_str = referer_obj.id;
	referer_obj._obj.html = false;
	var newValue_str = referer_obj._obj.text;
	referer_obj._obj.html = true;

	trace("computeSum entered with " + referer_str + " with value: " + newValue_str);
	switch(referer_str){
		case("textbox2"):
		case("textbox7"):
		case("textbox12"):
		case("textbox17"):{
			newTotal_num = getVal4Sum("textbox2");	
			newTotal_num += getVal4Sum("textbox7");	
			newTotal_num += getVal4Sum("textbox12");	
			newTotal_num += getVal4Sum("textbox17");
			newTotal_str = String(newTotal_num);
			target_obj = this._objDefsRepository.retrieve("textbox22");	
			target_fmt = target_obj.getTextFormat();
			target_obj.text = newTotal_str;
			target_obj.setTextFormat(target_fmt);
			break;
		}
		case("textbox3"):
		case("textbox8"):
		case("textbox13"):
		case("textbox18"):{
			newTotal_num = getVal4Sum("textbox3");	
			newTotal_num += getVal4Sum("textbox8");	
			newTotal_num += getVal4Sum("textbox13");	
			newTotal_num += getVal4Sum("textbox18");
			newTotal_str = String(newTotal_num);
			target_obj = this._objDefsRepository.retrieve("textbox23");	
			target_fmt = target_obj.getTextFormat();
			target_obj.text = newTotal_str;
			target_obj.setTextFormat(target_fmt);
			break;
		}
		case("textbox4"):
		case("textbox9"):
		case("textbox14"):
		case("textbox19"):{
			newTotal_num = getVal4Sum("textbox4");	
			newTotal_num += getVal4Sum("textbox9");	
			newTotal_num += getVal4Sum("textbox14");	
			newTotal_num += getVal4Sum("textbox19");
			newTotal_str = String(newTotal_num);
			target_obj = this._objDefsRepository.retrieve("textbox24");	
			target_fmt = target_obj.getTextFormat();
			target_obj.text = newTotal_str;
			target_obj.setTextFormat(target_fmt);
			break;
		}
		case("textbox5"):
		case("textbox10"):
		case("textbox15"):
		case("textbox20"):{
			newTotal_num = getVal4Sum("textbox5");	
			newTotal_num += getVal4Sum("textbox10");	
			newTotal_num += getVal4Sum("textbox15");	
			newTotal_num += getVal4Sum("textbox20");
			newTotal_str = String(newTotal_num);
			target_obj = this._objDefsRepository.retrieve("textbox25");	
			target_fmt = target_obj.getTextFormat();
			target_obj.text = newTotal_str;
			target_obj.setTextFormat(target_fmt);
			break;
		}
		default:{
			trace("computeSum WARNING: unknow cell id!");
		}
	}

	return("");
}

var latestMailMessageFunction:String = "";

/**
	customFeedTrace function
	arguments:
	- arg0: type (event name)
	- arg1 and no arg1+: whole payload
	- arg1+: couples of (name,value)
	example: customFeedTrace(ENVIRONMENT,environment,WB);
*/
function customFeedTrace(){
	var args:Array = arguments[0];
	var payload_str:String = "";
	if((args[0] != undefined) && (args[1] != undefined)){
		var argsLength_num:Number = args.length - 1;
		if((argsLength_num % 2) == 1){
			for (var i:Number = 1; i < argsLength_num; i+=2) {
				payload_str += _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + args[i] + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + args[i+1];
			}
			payload_str = payload_str.substr(_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR.length);
		}
		else{
			payload_str = args[1];
		}
		trace("feedTrace (custom) for " + args[0] + ", Stimulus " + payload_str);
		_level0.currentItemRootLevel.feedTrace(args[0],payload_str,"stimulus"); // CUSTOM FEED TRACE overlay
	}
}

function checkForm(){
	var args:Array = arguments[0];
	var formId_str:String = args[0];
	var referer_obj = args[args.length - 1];
	var params_array:Array;
	var formSuccessOutputId_str:String = args[(args.length - 3)];
	var formFailureOutputId_str:String = args[(args.length - 2)];
	var alternativeformFailureOutputId_str:String = args[(args.length - 4)];

	switch(formId_str){
		case("u03_default_form1"):{
			trace("checkForm u03_default_form1");
			var obj_str:String = "unit03apage1";
			params_array = new Array(obj_str);
			_root.show(params_array);
			obj_str = "unit03default";
			params_array = new Array(obj_str);
			_root.hide(params_array);
			break;
		}
		case("u23_pg12_form1"):{
			// target list view on mail client and thru that modify the model to display mail
			var mcListView_mc=this._objDefsRepository.retrieve("listview")._exulis.fileSystm.fileSystModel.setAttributes("u23_item305","visible","true");
			
			this._objDefsRepository.retrieve("mailApp")._exulis.addNewText(referer_obj.toolbox.wrapRun("xlf://u23_incoming_mail"));
		
			trace("checkForm u23_pg12_form1" +mcListView_mc);
			break;
		}
		
		
		
		
		
		case("u23_pg2_form1"):{
			var result_bool:Boolean=true;
			// CheckBox 1
			var checkBox_u23_pg21_prefix_str:String = args[1];
			// ComboBox 1
			var combo1_u23_pg21Id_str:String = args[2];
			var combo1_u23_pg21_mc:MovieClip = this._objDefsRepository.retrieve(combo1_u23_pg21Id_str);
			// unexpected answer
			var combo1Unexpected_str=String(referer_obj.toolbox.wrapRun(args[7],referer_obj._guiSource,"SingleNode","String"));
			// Authorization field
			var tField1_u23_pg21Id_str:String = args[3];
			var tField1_u23_pg21Value_str:String = this._objDefsRepository.retrieve(tField1_u23_pg21Id_str).text;
			// Popup 1
			var popup1_u23_pg21_str=args[4];
			// Popup 2
			var popup2_u23_pg21_str=args[5];
			// Good Answer
			var answer_str=String(referer_obj.toolbox.wrapRun(args[6],referer_obj._guiSource,"SingleNode","String"));
			
			var count=0;
			
			// evaluate 6 checkboxes to count (at least 2 chkBoxes have to be checked).
			for (var a=5;a<11;a++){
				if (this._objDefsRepository.retrieve(checkBox_u23_pg21_prefix_str+a).selected==true){
					count++;
				}
				
				trace("myCHECK "+this._objDefsRepository.retrieve(checkBox_u23_pg21_prefix_str+a).selected);
				
				if (count>=2){
					break;
				}
			}
			
			// if not ...
			if (count<1){
				result_bool=false;
				trace("RESULT count pas bon");
			}
			
			// verify combo box have a selection
			if (combo1_u23_pg21_mc.text==combo1Unexpected_str){
				result_bool=false;
				trace("RESULT comboResult "+combo1Unexpected_str+" > "+combo1_u23_pg21_mc.text);
			}
			
			// verify tField match answer
			if (tField1_u23_pg21Value_str!=answer_str){
				trace("RESULT tField match answer "+tField1_u23_pg21Value_str+" > "+answer_str);
				result_bool=false;
			}
			
			// display two different popup depending on the results
			if (!result_bool){
					params_array = new Array(popup1_u23_pg21_str);
					show(params_array);
			}
			else{
					params_array = new Array(popup2_u23_pg21_str);
					show(params_array);
			}
			break;
		}
		case("u010b_default_form1"):{
			// Combo Job Type
			var combo1Id_str:String =  args[1];
			var combo1_mc:MovieClip = this._objDefsRepository.retrieve(combo1Id_str);
			var combo1expectedVal_str:String=String(referer_obj.toolbox.wrapRun(args[2],referer_obj._guiSource,"SingleNode","String"));
			// Combo Include jobs posted within last
			var combo2Id_str:String =  args[3];
			var combo2_mc:MovieClip = this._objDefsRepository.retrieve(combo2Id_str);
			var combo2expectedVal_str:String=String(referer_obj.toolbox.wrapRun(args[4],referer_obj._guiSource,"SingleNode","String"));
			// Checkbox Full Time
			var checkBox1Id_str:String = args[5];
			var checkBox1Value_bool:Boolean = this._objDefsRepository.retrieve(checkBox1Id_str).selected;
			// Checkbox Part Time
			var checkBox2Id_str:String = args[6];
			var checkBox2Value_bool:Boolean = this._objDefsRepository.retrieve(checkBox2Id_str).selected;
			// Contractor
			var checkBox3Id_str:String = args[7]; 
			var checkBox3Value_bool:Boolean = this._objDefsRepository.retrieve(checkBox3Id_str).selected;
			// Intern
			var checkBox4Id_str:String = args[8];
			var checkBox4Value_bool:Boolean = this._objDefsRepository.retrieve(checkBox4Id_str).selected;	
			//popup1
			var popup1Id_str:String =args[9];
			//popup2
			var popup2Id_str:String =args[10];
			//page1
			var page1Id_str:String =args[11];
			//page5
			var page5Id_str:String =args[12];
			//page9
			var page9Id_str:String =args[13];
			//page13
			var page13Id_str:String =args[14];
			
			trace("myCHECK > combo1_mc.text : "+combo1_mc.text);
			trace("myCHECK > combo1_mc Expected Value : "+combo1expectedVal_str);
			
			trace("myCHECK > combo2_mc.text : "+combo2_mc.text);
			trace("myCHECK >  check : "+(combo1_mc.text==combo1expectedVal_str));
			
			// first attempt
			if (combo1_mc.text==combo1expectedVal_str && combo2_mc.text==combo2expectedVal_str && checkBox2Value_bool==true)
			{		
  			trace("myCHECK > RESULT CASE 1"+page1Id_str);
  			params_array = new Array(page1Id_str,"_self","unknown");
  			gotoURL(params_array);
			}
      else if (combo1_mc.text==combo1expectedVal_str && combo2_mc.text!=combo2expectedVal_str && checkBox2Value_bool==true)
				{
					trace("myCHECK > RESULT CASE 1.1"+page5Id_str);
					params_array = new Array(page5Id_str,"_self","unknown");
					gotoURL(params_array);
				}
					else if (combo1_mc.text==combo1expectedVal_str && combo2_mc.text==combo2expectedVal_str && (checkBox1Value_bool==true || checkBox3Value_bool==true || checkBox4Value_bool==true))
						{
							trace("myCHECK > RESULT CASE 1.2"+page9Id_str);
							params_array = new Array(page9Id_str,"_self","unknown");
							gotoURL(params_array);
						} 	else if (combo1_mc.text==combo1expectedVal_str && combo2_mc.text!=combo2expectedVal_str && (checkBox1Value_bool==true || checkBox3Value_bool==true || checkBox4Value_bool==true))
								{
									trace("myCHECK > RESULT CASE 1.2"+page13Id_str);
									params_array = new Array(page13Id_str,"_self","unknown");
									gotoURL(params_array);
								}
								else if (!this._objDefsRepository.u010b_default_form1_flag)
								{
									trace("myCHECK > RESULT CASE 2 "+popup1Id_str);
									params_array = new Array(popup1Id_str);
									show(params_array);
									// put flag
									this._objDefsRepository.u010b_default_form1_flag=true;
									
									} else
									{
										trace("myCHECK > myflag : "+this._objDefsRepository.u010b_default_form1_flag);
										params_array = new Array(popup2Id_str);
										show(params_array);
										// force choice
										// retrieve expected value and force response on combo1
										forceComboCorrectAnswer(combo1Id_str,combo1_mc,combo1expectedVal_str);
										// retrieve expected value and force response on combo2
										forceComboCorrectAnswer(combo2Id_str,combo2_mc,combo2expectedVal_str);		
										// retrieve and force combo 2
										this._objDefsRepository.retrieve(checkBox2Id_str).selected=true;
										// disable all component to avoid modification after forced choice
										componentActivationState([combo1_mc],false)
									}
			break;
		}
		case("u021_default_form1"):
		case("u021_pg4_form1"):{
			var menu1Id_str:String = args[1];
			var menu1expectedVal_str:String = String(referer_obj.toolbox.wrapRun(args[2],referer_obj._guiSource,"SingleNode","String"));
			var menu2Id_str:String = args[3];
			var menu2expectedVal_str:String = String(referer_obj.toolbox.wrapRun(args[4],referer_obj._guiSource,"SingleNode","String"));
			var menu1_ref = this._objDefsRepository.retrieve(menu1Id_str); //._exulis
			var menu2_ref = this._objDefsRepository.retrieve(menu2Id_str);
			if((menu1_ref.text == menu1expectedVal_str) && (menu2_ref.text == menu2expectedVal_str)){
				params_array = new Array(formSuccessOutputId_str,"_self","unknown");
				gotoURL(params_array);
			}
			else {
					if (formId_str=="u021_default_form1" && this._objDefsRepository.u021_default_form1!=true)
					{
					params_array = new Array(formFailureOutputId_str);
					show(params_array);
					this._objDefsRepository.u021_default_form1=true;
					} else if (formId_str=="u021_default_form1" && this._objDefsRepository.u021_default_form1==true)
						{
							params_array = new Array(alternativeformFailureOutputId_str);
							show(params_array);
						} else 
						{
							params_array = new Array(formFailureOutputId_str);
							show(params_array);
						}
			}
			break;
		}
		case("u021_pg1_form1"):{
			var cptChkArgs_num:Number = 1;
			var successState_bool:Boolean = true;
			while(cptChkArgs_num < (args.length - 3)){
				var chkId_str:String =  args[cptChkArgs_num++];
				var chkVal_str = String(this._objDefsRepository.retrieve(chkId_str).selected);
				var chkExpectedVal_str:String = args[cptChkArgs_num++];
				
				trace("MYDATAS ------------------------------------------");
				trace("MYDATAS ID : " + chkId_str);
				trace("MYDATAS CURRENT VALUE " + chkVal_str);
				trace("MYDATAS EXPECTED VALUE " + chkExpectedVal_str);
				trace("MYDATAS ------------------------------------------");
				
				if(chkVal_str != chkExpectedVal_str){
					successState_bool = false;
					break;
				}
			}
			if (successState_bool) {
				trace("MYDATAS SUCCESS");
				params_array = new Array(formSuccessOutputId_str,"_self","unknown");
				gotoURL(params_array);
				this._objDefsRepository.u21p2_bool = true;
				r4ref._visible = false;
				r5ref._visible = false;
				
				var comboRoom1_mc = this._objDefsRepository.retrieve("u021_pg2_menu3");
				var comboRoom2_mc=this._objDefsRepository.retrieve("u021_pg2_menu4");
				comboRoom1_mc.selectedIndex=0;
				comboRoom2_mc.selectedIndex=0;
				
				switchLayout();
			}
			else {
				trace("MYDATAS NO SUCCESS");
				params_array = new Array(formFailureOutputId_str,"_self","unknown");
				gotoURL(params_array);
				this._objDefsRepository.u21p2_bool = false;
				r4ref._visible = false;
				r5ref._visible = false;
				switchLayout();
			}
			break;
		}
		case("u021_pg2_form1"):{
			trace("let's enter pg2_form1");
			var cptWgtArgs_num:Number = 1;
			var successState_bool:Boolean = true;
			var wgtVal_str:String;
			while(cptWgtArgs_num < (args.length - 3)){
				var wgtId_str:String =  args[cptWgtArgs_num++];
				var wgt_ref = this._objDefsRepository.retrieve(wgtId_str);
				switch(wgt_ref._exulis._type){
					case("menulist"):{
						wgtVal_str = String(wgt_ref.text);
						break;
					}
					case("checkbox"):{
						wgtVal_str = String(wgt_ref.selected);
						break;
					}
					default:{
						wgtVal_str = "";
					}
				}
				var wgtExpectedVal_str:String = args[cptWgtArgs_num++];
				
				trace("myCOMBOX wgtVal_str "+wgtVal_str);
				trace("myCOMBOX wgtExpectedVal_str : "+wgtExpectedVal_str);
				
				switch(true){
					case(wgtExpectedVal_str.substr(0,1) == "["):{
						wgtExpectedVal_str = wgtExpectedVal_str.substr(1,wgtExpectedVal_str.length - 2);
						var splitterPlace_num:Number = wgtExpectedVal_str.indexOf("..");
						var bound1_str:String = wgtExpectedVal_str.substr(0,splitterPlace_num);
						var bound2_str:String = wgtExpectedVal_str.substr(splitterPlace_num + 2);
//			trace("let it be a range min:" + bound1_str + " max:" + bound2_str);
						var subSuccessState_bool:Boolean = false;
						if(isNaN(parseInt(bound1_str))){
							// letters - this case is not yet handled
							subSuccessState_bool = true;
						}
						else{
							for(var vSubCpt_num:Number = parseInt(bound1_str); vSubCpt_num <= parseInt(bound2_str); vSubCpt_num++){
								if(wgtVal_str == String(vSubCpt_num)){
									subSuccessState_bool = true;
									break;
								}
							}
						}
						successState_bool = successState_bool && subSuccessState_bool;
						break;
					}
					case(wgtExpectedVal_str.substr(0,6) == "xpath:"):{
						wgtExpectedVal_str = String(referer_obj.toolbox.wrapRun(wgtExpectedVal_str,referer_obj._guiSource,"SingleNode","String"));
						// no break
					}
					default:{
						if(wgtVal_str != wgtExpectedVal_str){
								successState_bool = false;
						}
					}
				}
			}
			if(successState_bool){
				params_array = new Array(formSuccessOutputId_str);
				show(params_array);
			}
			else{
				if (!this._objDefsRepository.u021_pg2_form1)
				{
				params_array = new Array(formFailureOutputId_str);
				show(params_array);
				this._objDefsRepository.u021_pg2_form1=true;
				} else
				{
					params_array = new Array(formSuccessOutputId_str);
					show(params_array);
				}
			}
			break;
		}
		default:{
			trace("WARNING: the Form " + formId_str + " is not yet managed!");
		}
	}
	return("");
}

function mailFind(){
	var args:Array = arguments[0];
	var fsList_str=args[0];
	var fsTree_str=args[1];
	var fsHeader_str=args[2];
	var fsView_str=args[3];
	var searchField_str=args[4];
	var previewBton_str=args[5];
	var nextBton_str=args[6];
	
	
	this._objDefsRepository.navCount=0;

	
	var searchTfield_tf=this._objDefsRepository.retrieve(searchField_str);
	
	var previewBton_mc = this._objDefsRepository.retrieve(previewBton_str);
	var nextBton_mc = this._objDefsRepository.retrieve(nextBton_str);
	var fsTree_obj=this._objDefsRepository.retrieve(fsTree_str)._exulis.fileSystm;
	var fsHeader_obj=this._objDefsRepository.retrieve(fsHeader_str)._exulis.fileSystm;
	var fsView_obj=this._objDefsRepository.retrieve(fsView_str)._exulis.fileSystm;
	var findMC_str = searchTfield_tf.text;
	
	if ((findMC_str==undefined) || (findMC_str.length<1))
	{
		return;
	}
	var zemodel_xml=this._objDefsRepository.retrieve(fsList_str)._exulis.fileSystm.fileSystModel.currentXml_xml;
	// only mails
	var mails_ar : Array = XPath.selectNodes(zemodel_xml, "//item[@type='file']");
	
	trace("CRASH ----- "+mails_ar);
	
	var mailsThatMatch_ar=new Array();
	// check each mail to find string

	// create fake dummy tField
	var tFieldDummy_tf=this.createTextField("tFieldDummy_tf",998877,-5122,5,460,340);
	tFieldDummy_tf.autoSize="Left";
	tFieldDummy_tf.multiline=true;
	tFieldDummy_tf.wordWrap=true;
	tFieldDummy_tf.html=true;
	
	for (var a in mails_ar)
	{
			var _zeMail_str=this._objDefsRepository.retrieve(fsList_str)._exulis.toolbox.wrapRun(mails_ar[a].firstChild);			
			
			tFieldDummy_tf.htmlText=_zeMail_str;
		
			var _textTmp=tFieldDummy_tf.text;
			
			_textTmp=_textTmp.toLowerCase();
			findMC_str=findMC_str.toLowerCase();
			// 
			
		trace("CRASH _textTmp "+_textTmp);
		trace("CRASH findMC_str "+findMC_str);
			
			
		if (_textTmp==undefined || findMC_str==undefined)
		{
			return;
		}
			
			
			if (_textTmp.indexOf(findMC_str)!=-1)
			{
			
			var _searchCount=0;		
			
			while(true)
			{	
				if (_textTmp.indexOf(findMC_str,_searchCount)!=-1)
				{
					var mailInfos_obj=new Object();
					mailInfos_obj.id=mails_ar[a].attributes.id;
					mailInfos_obj.wordLength=findMC_str.length;
					mailInfos_obj.indexResult=_textTmp.indexOf(findMC_str,_searchCount);
					_searchCount=_textTmp.indexOf(findMC_str,_searchCount)+1;
					mailsThatMatch_ar.push(mailInfos_obj);
					} 
					else 
				{
					break;
				}

			}
			
		}
	}
	
	tFieldDummy_tf.removeTextField();
	
	this._objDefsRepository.mcResult=mailsThatMatch_ar;
	this._objDefsRepository.mcCount=0;
	
	if(mailsThatMatch_ar.length>1)
	{
	previewBton_mc._visible=true;
	nextBton_mc._visible=true;
	}
	
	previewBton_mc.onPress=Relegate.create(this,onPressPreviewFindMC,mailsThatMatch_ar,fsList_str,fsTree_obj,fsHeader_obj,fsView_obj);
	nextBton_mc.onPress=Relegate.create(this,onPressNextFindMC,mailsThatMatch_ar,fsList_str,fsTree_obj,fsHeader_obj,fsView_obj);
	this._objDefsRepository.retrieve(fsList_str)._exulis.fileSystm.displayMail(mailsThatMatch_ar[this._objDefsRepository.navCount],fsTree_obj,fsHeader_obj,fsView_obj);
}



function onPressNextFindMC(mailsThatMatch_ar,fsList_str,fsTree_obj,fsHeader_obj,fsView_obj){
	this._objDefsRepository.navCount++;
	if (this._objDefsRepository.navCount>mailsThatMatch_ar.length) this._objDefsRepository.navCount=mailsThatMatch_ar.length-1;
	trace("TTT NEXT counter : "+this._objDefsRepository.navCount+" > "+mailsThatMatch_ar.length);
	this._objDefsRepository.retrieve(fsList_str)._exulis.fileSystm.displayMail(mailsThatMatch_ar[this._objDefsRepository.navCount],fsTree_obj,fsHeader_obj,fsView_obj);
}

function onPressPreviewFindMC(mailsThatMatch_ar,fsList_str,fsTree_obj,fsHeader_obj,fsView_obj){	
	this._objDefsRepository.navCount--;
	if (this._objDefsRepository.navCount<0) this._objDefsRepository.navCount=0;
	trace("TTT PREVIEW counter : "+this._objDefsRepository.navCount+" > "+mailsThatMatch_ar.length);
	this._objDefsRepository.retrieve(fsList_str)._exulis.fileSystm.displayMail(mailsThatMatch_ar[this._objDefsRepository.navCount],fsTree_obj,fsHeader_obj,fsView_obj);
}

function switchStateButton(){
	var args:Array = arguments[0];
	var formId_str:String = args[0];
	var destination_str=args[1];
	var alternativePopup_str=args[2];
	var params_array:Array;
	if (this._objDefsRepository[formId_str+"_switch"]!=true){
	params_array = new Array(destination_str,"_self","unknown");
	gotoURL(params_array);
	this._objDefsRepository[formId_str+"_switch"]=true;
	} else
		{
			params_array = new Array(alternativePopup_str);
			show(params_array);
		}
}

// box_reply,reply
function mailAction(){
	var args:Array = arguments[0];
	var replybox_str:String = args[0];
	var actionType_str=	args[1];
	var headerview_str= args[2];
	// textFields id
	var replyToTfield_str=args[3];
	var replyCcTfield_str=args[4];
	var replySubject_str=args[5];
	var replyMessage_str=args[6];
	// textfields
	var replyToTfield_tf=this._objDefsRepository.retrieve(replyToTfield_str);
	var replyCcTfield_tf=this._objDefsRepository.retrieve(replyCcTfield_str);
	var replySubject_tf=this._objDefsRepository.retrieve(replySubject_str);
	var replyMessage_tf=this._objDefsRepository.retrieve(replyMessage_str);
	
	// display reply, replyall, forward box
	var params_array = new Array(replybox_str);
	show(params_array);
	// target list view
	var _referer_obj=this._objDefsRepository.retrieve(headerview_str)._exulis;
	var _myHeader = _referer_obj.fileSystm;
	
	var _myMailId = _myHeader.currentMailId_str;
	//var _currentMailId_str=


	trace("DEBUGMAIL _myMailId > "+this._objDefsRepository.retrieve(headerview_str));

	var node_xml:XML=_myHeader.fileSystModel.getNode(_myMailId);
	
	var _from=String(_referer_obj.toolbox.wrapRun(node_xml.attributes.from,_referer_obj._guiSource,"SingleNode","String"));
	_from=(_from==undefined || _from=="undefined") ? "" : _from;
	
	var _type=node_xml.attributes.type;
	
	var _cc = String(_referer_obj.toolbox.wrapRun(node_xml.attributes.cc,_referer_obj._guiSource,"SingleNode","String"));
	
	_cc=(_cc==undefined || _cc=="undefined") ? "" : _cc;
	
	var _subject = String(_referer_obj.toolbox.wrapRun(node_xml.attributes.label,_referer_obj._guiSource,"SingleNode","String"));
	
	_subject=(_subject==undefined || _subject=="undefined") ? "" : _subject;

	var _message = String(_referer_obj.toolbox.wrapRun(node_xml.firstChild.nodeValue,_referer_obj._guiSource,"SingleNode","String"));
	
	_message=(_message==undefined || _message=="undefined") ? "" : _message;
	
	

	
	var replyPrefix_str=String(_referer_obj.toolbox.wrapRun("xlf://RE_str"));
	var forwardPrefix_str=String(_referer_obj.toolbox.wrapRun("xlf://FW_str"));
	var header_str=String(_referer_obj.toolbox.wrapRun("xlf://HEADER_str"));
	
	
	// test type
	if (_type=="file")
	{
		latestMailMessageFunction = actionType_str;
		
		switch(actionType_str)
		{
			case "new":
			replyToTfield_tf.text = "";
			replyCcTfield_tf.text = "";
			replySubject_tf.text = "";
			replyMessage_tf.htmlText = "";
			break;
			
			case "reply":
			replyToTfield_tf.text = (_from!="undefined" || _from!=undefined) ? _from: "";			
			replyCcTfield_tf.text = "";
			//replyCcTfield_tf.text = (_cc!="undefined" || _cc!=undefined) ? _cc : "";
			trace("ReStr: " + replyPrefix_str);
			replySubject_tf.text = (_subject!="undefined" || _subject!=undefined) ? replyPrefix_str+" "+_subject : "";
			
			replyMessage_tf.htmlText = (_message!="undefined" || _message!=undefined) ? "\n\n"+header_str+"\n\n"+_message : "";
			break;
			
			case "replyall":
			replyToTfield_tf.text = (_from!="undefined" || _from!=undefined) ? _from: "";
			replyCcTfield_tf.text = (_cc!="undefined" || _cc!=undefined) ? _cc : "";
			replySubject_tf.text = (_subject!="undefined" || _subject!=undefined) ? replyPrefix_str+" "+_subject : "";
			replyMessage_tf.htmlText = (_message!="undefined" || _message!=undefined) ? "\n\n"+header_str+"\n\n"+_message : "";
			break;
			
			case "forward":
			//replyToTfield_tf.text = (_from!="undefined") ? _from: "";
			replyToTfield_tf.text = "";
			replyCcTfield_tf.text = "";
			//replyCcTfield_tf.text = (_cc!="undefined" || _cc!=undefined) ? _cc : "";
			replySubject_tf.text = (_subject!="undefined" || _subject!=undefined) ? forwardPrefix_str+" "+_subject : "";
			replyMessage_tf.htmlText = (_message!="undefined" || _message!=undefined) ? "\n\n"+header_str+"\n\n"+_message : "";
			break;
			
			default: trace("mailAction file type with unhandled actionType!");
		}
	}
	
	trace("SENDMAIL replybox_str :"+_myMailId);
	
	this._objDefsRepository.retrieve(replybox_str).refMessage=_myMailId;
}

function sendMail(){
	var args:Array = arguments[0];
	var replybox_str:String = args[0];
	var headerview_str:String = args[1];
	var sentFolder_str=args[2];
	var _referer_obj=this._objDefsRepository.retrieve(headerview_str)._exulis;
	
	replybox_mc=this._objDefsRepository.retrieve(replybox_str);
	
	
	
	
	var _myHeader = _referer_obj.fileSystm;
	// ref message
	var node_xml:XML=_myHeader.fileSystModel.getNode(replybox_mc.refMessage);
	
	trace("SENDMAIL _myHeader :"+_myHeader);
	trace("SENDMAIL replybox_mc.refMessage :"+replybox_mc.refMessage);
	trace("SENDMAIL node_xml :"+node_xml);
	
	// textFields id
	var replyToTfield_str=args[3];
	var replyCcTfield_str=args[4];
	var replySubject_str=args[5];
	var replyMessage_str=args[6];
	// noreplyerpopup
	//var noreplyerpopup_str:String= args[7];
	
	// send popup
	var sendbox_str:String= args[7];
	var sendbox_mc:MovieClip= this._objDefsRepository.retrieve(sendbox_str);
	
	// textfields
	var replyToTfield_tf=this._objDefsRepository.retrieve(replyToTfield_str);	
	var replyCcTfield_tf=this._objDefsRepository.retrieve(replyCcTfield_str);
	var replySubject_tf=this._objDefsRepository.retrieve(replySubject_str);
	var replyMessage_tf=this._objDefsRepository.retrieve(replyMessage_str);

	// textFields content
	var _fromContent = (replyToTfield_tf.text.length>1) ? replyToTfield_tf.text : "";
	var _ccContent = (replyCcTfield_tf.text.length>1) ? replyCcTfield_tf.text : "";
	var _subjectContent = (replySubject_tf.text.length>1) ? replySubject_tf.text : '...';
	var _messageContent = (replySubject_tf.text.length>1) ? replyMessage_tf.text : "";
	
	if (_fromContent.length<1){
		var params_array = new Array("popup_unfilled_mc");
		show(params_array);
		return;
	}
	
	//trace("mySEND "+node_xml);
	var event_str:String = "MAIL_SENT";
	var payload_str:String = "function"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+latestMailMessageFunction +
		_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "to"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_fromContent+
		_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "cc" +_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_ccContent+
		_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "subject"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_subjectContent+
		_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "message"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+escape(_messageContent);
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // MAIL_SENT
	
	var _dateContent = (node_xml.attributes.date) ? node_xml.attributes.date : "";
	var _dateDisplayedContent = (node_xml.attributes.datedisplayed) ? node_xml.attributes.datedisplayed : "";
	var _sizeContent = (node_xml.attributes.size) ? node_xml.attributes.size : "";
	var _sizeDisplayedContent = (node_xml.attributes.sizedisplayed) ? node_xml.attributes.sizedisplayed : "";
	
	trace("SENDMAIL node_xml :"+node_xml);
	trace("SENDMAIL ");
	trace("SENDMAIL ");
	trace("SENDMAIL _dateContent : "+_dateContent);
	trace("SENDMAIL _dateDisplayedContent : "+_dateDisplayedContent);
	trace("SENDMAIL _sizeContent : "+_sizeContent);
	trace("SENDMAIL _sizeDisplayedContent : "+_sizeDisplayedContent);
	trace("SENDMAIL ");
	trace("SENDMAIL ");
	
	// retrieve model
	var MCmodel = this._objDefsRepository.retrieve(headerview_str)._exulis.fileSystm.fileSystModel;
	// retrieve bow that store current refMessage
	var replybox_mc=this._objDefsRepository.retrieve(replybox_str);
	if (replybox_mc.refMessage){
	// create new doc and set attributes
  	var mailAttr_obj=new Object();
  	mailAttr_obj.label=_subjectContent;
  	mailAttr_obj.cc=_ccContent;

  	//mailAttr_obj.from=_fromContent;
	//mailAttr_obj.from=_fromContent;
	mailAttr_obj.from=	replybox_mc._exulis.toolbox.wrapRun("xlf://mc_sender_text");
  	mailAttr_obj.size=_sizeContent;
  	mailAttr_obj.sizedisplayed=_sizeDisplayedContent;
  	mailAttr_obj.date=_dateContent;
  	mailAttr_obj.datedisplayed=_dateDisplayedContent;

	
	
  	var newMail=MCmodel.createNewDocument(sentFolder_str,_attributes_str,_messageContent,false,mailAttr_obj);
  	replybox_mc.refMessage=undefined;
	}

		trace("MYANSWER "+sendbox_mc);

		if (sendbox_mc==undefined)
		{
				var params_array = new Array(replybox_str);
				hide(params_array);
		} else
		{
		// hide reply, replyall, forward box
		//var params_array = new Array(replybox_str);
		//hide(params_array);
		
		// send popup
		var params_array = new Array(sendbox_str);
		show(params_array);
		}
	
}

function getClipBoardFromKb(){
	if (Selection.getBeginIndex()!=-1 && Selection.getFocus()!=undefined){
  	var _mySelection = eval(Selection.getFocus()).text.substring(Selection.getBeginIndex(),Selection.getEndIndex());
  	this._targetExecutionLayer.currentSelection = (this._targetExecutionLayer.currentSelection!=undefined) ? this._targetExecutionLayer.currentSelection : "";
  	this._targetExecutionLayer.currentSelection= (_mySelection!=undefined) ? _mySelection : this._targetExecutionLayer.currentSelection;
  	this._targetExecutionLayer.currentFocusedField=eval(Selection.getFocus());
  	var _mySelectedField=this._targetExecutionLayer.currentFocusedField;
  	this._targetExecutionLayer.pasteFieldBeginIndex=Selection.getBeginIndex();
  	this._targetExecutionLayer.pasteFieldEndIndex=Selection.getEndIndex();
	}
	
	var event_str:String = "SHORTCUT";
	var payload_str:String = "modifiers"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"ctrl" +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "key"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"c";
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // SHORTCUT
	
	getClipBoard();
}

function pasteClipBoardFromKb(){

	var event_str:String = "SHORTCUT";
	var payload_str:String = "modifiers"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"ctrl" +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "key"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"v";
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // SHORTCUT
	
	var _mystr=this._targetExecutionLayer.clipBoardContent;
	
	
	System.setClipboard(_mystr.substr(-1,1));
	
	eval(Selection.getFocus()).replaceSel(_mystr.substr(0,_mystr.length-1));
	
	this._targetExecutionLayer.clipBoardContent = (this._targetExecutionLayer.clipBoardContent==undefined) ? "" : this._targetExecutionLayer.clipBoardContent;
	
	var event_str:String = "PASTE";
	var payload_str:String = "content"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+this._targetExecutionLayer.clipBoardContent;
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // PASTE
}

function getClipBoard(){
	this._targetExecutionLayer.clipBoardContent=this._targetExecutionLayer.currentSelection;
	//System.setClipboard(this._targetExecutionLayer.clipBoardContent);

	var event_str:String = "COPY";
	var payload_str:String = "content"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+this._targetExecutionLayer.clipBoardContent;
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // COPY
}

function pasteClipBoard(){
	//this._targetExecutionLayer.clipBoardContent=this._targetExecutionLayer.currentSelection;
	var focuzed=Selection.setFocus(this._targetExecutionLayer.currentFocusedField);
	trace("myCB index : "+this._targetExecutionLayer.pasteFieldBeginIndex+" > "+this._targetExecutionLayer.pasteFieldEndIndex);
	
	this._targetExecutionLayer.clipBoardContent = (this._targetExecutionLayer.clipBoardContent==undefined) ? "" : this._targetExecutionLayer.clipBoardContent;
	
	Selection.setSelection(this._targetExecutionLayer.pasteFieldBeginIndex,this._targetExecutionLayer.pasteFieldEndIndex);
	eval(Selection.getFocus()).replaceSel(this._targetExecutionLayer.clipBoardContent);
}

function forceComboCorrectAnswer(comboId_str,combo_mc,correctAnswer){
	var count=0
	for (var i=0;i<combo_mc.length;i++){
		if (combo_mc.getItemAt(i).label==correctAnswer){
			combo_mc.selectedIndex=i;
			
			var event_str:String = "COMBOBOX";
			var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+comboId_str +
			_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+combo_mc.selectedIndex;
			trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
			_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // COMBOBOX
			break;	
		}
	}
}

function componentActivationState(compo_ar,state_bool){
	for (var a in compo_ar){
		compo_ar[a].enabled=state_bool;
	}
}

function historyBack(){
	// reset scroll box position
	var _scrollBox=this._objDefsRepository.scrollBox
	_scrollBox._y=Number(this._objDefsRepository.scrollBoxPosition)-20;
	
	var args:Array = arguments[0];
	var tButton_str:String = args[0];
	var tab_ref;
	tab_ref = this._objDefsRepository.retrieve("tabs-toolbar")._exulis.leaderIndicator_ref;
	tab_ref.historyBack();
	var _myTBinst = this._objDefsRepository.retrieve(tButton_str);
	Selection.setFocus(_myTBinst);
}

function historyForward(){
	// reset scroll box position
	var _scrollBox=this._objDefsRepository.scrollBox
	_scrollBox._y=Number(this._objDefsRepository.scrollBoxPosition)-20;
	
	var args:Array = arguments[0];
	var tButton_str:String = args[0];
	var tab_ref;
	tab_ref = this._objDefsRepository.retrieve("tabs-toolbar")._exulis.leaderIndicator_ref;
	tab_ref.historyForward();
	var _myTBinst = this._objDefsRepository.retrieve(tButton_str);
	Selection.setFocus(_myTBinst);
}

// ADDTAB 
function addTab(){
	var args:Array = arguments[0];
	var tabsRef:String = args[0];
	var tab_ref = this._objDefsRepository.retrieve("tabs-toolbar")._exulis.addNewTab();
}

function closeTab(){
	var args:Array = arguments[0];
	var tabsRef:String = args[0];
	var tab_ref = this._objDefsRepository.retrieve("tabs-toolbar")._exulis.closeTab();
}

function showCallback(box_ref){
	trace("showCallback: displays -> " + box_ref);
	box_ref._visible = true;
}

function gotoURL(){
	// reset scroll box position
	

	
	var urlBox=this._objDefsRepository.retrieve("url");
	
	Selection.setFocus(urlBox);
	Selection.setSelection(0,0);
	
	var _scrollBox=this._objDefsRepository.scrollBox;
	_scrollBox._y=Number(this._objDefsRepository.scrollBoxPosition);
	
	var args:Array = arguments[0];
	var url_str:String = args[0];
	var target_str:String = args[1];
	var source_str:String = args[2];
	trace("gotoURL: triggered with '" + url_str + "' on " + target_str);
	var tab_ref;
	var box_ref;
	this._objDefsRepository.retrieve("tabs-toolbar")._exulis;
	tab_ref = this._objDefsRepository.retrieve("tabs-toolbar")._exulis.leaderIndicator_ref;
	
	// reset scroll box position
	var _scrollBox=this._objDefsRepository.scrollBox;
	_scrollBox._y=Number(this._objDefsRepository.scrollBoxPosition)-20;
	
	if((source_str == "unknown") || (source_str == undefined)){
		source_str = tab_ref.linkedPanel_str;
	}
	trace("gotoURL: linked panel was '" + source_str + "' and became '" + url_str + "'");
	if(target_str == "_self"){
		tab_ref.historyAdd(url_str);
	}
	else{
		box_ref = this._objDefsRepository.retrieve(url_str);
		box_ref._exulis.nestContent(_root.showCallback, box_ref);
		box_ref._visible = true;
	}
	//this._objDefsRepository.retrieve("tabs-toolbar")._exulis.updateScroll(this._objDefsRepository.retrieve(source_str)._height);
	return("");
}

function show(){
	var args:Array = arguments[0];
	var obj_str:String = args[0];
	
	var buttonsToShow_str:String = args[1];
	var buttonsToHide_str:String = args[2];
	
	var windowRef_str:String = args[3];
	
	if (args[1])
	{
	var buttonsToShow_ar:Array = buttonsToShow_str.split("|");
	var buttonsToHide_ar:Array = buttonsToHide_str.split("|");
	
	var buttonRefToShow_ar:Array = new Array();
	var buttonRefToHide_ar:Array = new Array();
	
		for (var s in buttonsToShow_ar)
		{
			this._objDefsRepository.retrieve(buttonsToShow_ar[s])._exulis._objDef["disabled"]=false;
			this._objDefsRepository.retrieve(buttonsToShow_ar[s])._exulis.setActive();
			buttonRefToShow_ar.push(this._objDefsRepository.retrieve(buttonsToShow_ar[s]));
		}
		
		for (var z in buttonsToHide_ar)
		{
			this._objDefsRepository.retrieve(buttonsToHide_ar[z])._exulis._objDef["disabled"]=false;
			this._objDefsRepository.retrieve(buttonsToHide_ar[z])._exulis.setInactive();
			buttonRefToHide_ar.push(this._objDefsRepository.retrieve(buttonsToHide_ar[z]));
		}
	
	
	this._objDefsRepository.retrieve(windowRef_str).buttonRefToShow_ar=buttonRefToShow_ar;
	this._objDefsRepository.retrieve(windowRef_str).buttonRefToHide_ar=buttonRefToHide_ar;
	
	}
	
	
	var box_ref;
	box_ref = this._objDefsRepository.retrieve(obj_str);
	trace("show: displays '" + obj_str + "' -> " + box_ref);
		
	box_ref._exulis.nestContent(_root.showCallback, box_ref);
	box_ref._visible = true;
	return("");
}

function hide(){
	var args:Array = arguments[0];
	var obj_str:String = args[0];
	
	var buttonsToShow_str:String = args[1];
	var buttonsToHide_str:String = args[2];
	
	var windowRef_str:String = args[3];
	
	if (args[1])
	{
	var buttonsToShow_ar:Array = buttonsToShow_str.split("|");
	var buttonsToHide_ar:Array = buttonsToHide_str.split("|");
	
	var buttonRefToShow_ar:Array = new Array();
	var buttonRefToHide_ar:Array = new Array();
	
		for (var s in buttonsToShow_ar)
		{
			this._objDefsRepository.retrieve(buttonsToShow_ar[s])._exulis._objDef["disabled"]=false;
			this._objDefsRepository.retrieve(buttonsToShow_ar[s])._exulis.setActive();
			buttonRefToShow_ar.push(this._objDefsRepository.retrieve(buttonsToShow_ar[s]));
		}
		
		for (var z in buttonsToHide_ar)
		{
			this._objDefsRepository.retrieve(buttonsToHide_ar[z])._exulis._objDef["disabled"]=false;
			this._objDefsRepository.retrieve(buttonsToHide_ar[z])._exulis.setInactive();
			buttonRefToHide_ar.push(this._objDefsRepository.retrieve(buttonsToHide_ar[z]));
		}
	
	
	this._objDefsRepository.retrieve(windowRef_str).buttonRefToShow_ar=buttonRefToShow_ar;
	this._objDefsRepository.retrieve(windowRef_str).buttonRefToHide_ar=buttonRefToHide_ar;
	
	}
	
	var box_ref;
	box_ref = this._objDefsRepository.retrieve(obj_str);
	trace("hide: displays '" + obj_str + "' -> " + box_ref);
	box_ref._visible = false;
	return("");
}

function processSort(){
	var args:Array = arguments[0];
	var ssName_str:String = args[0];
	var combo1_str:String = args[1];
	var combo2_str:String = args[2];
	var combo3_str:String = args[3];
	var radio1_str:String = args[4];
	var radio2_str:String = args[5];
	var radio3_str:String = args[6];
	var radio4_str:String = args[7];
	var radio5_str:String = args[8];
	var radio6_str:String = args[9];
	var _mySSinst = this._objDefsRepository.retrieve(ssName_str)._exulis;
	var _combo1Value = this._objDefsRepository.retrieve(combo1_str).selectedItem.data;
	var _combo2Value = this._objDefsRepository.retrieve(combo2_str).selectedItem.data;
	var _combo3Value = this._objDefsRepository.retrieve(combo3_str).selectedItem.data;
	var _radio1Value = this._objDefsRepository.retrieve(radio1_str).selected;
	//var _radio2Value = this._objDefsRepository.retrieve(radio2_str).selected;
	var _radio3Value = this._objDefsRepository.retrieve(radio3_str).selected;
	//var _radio4Value = this._objDefsRepository.retrieve(radio4_str).selected;
	var _radio5Value = this._objDefsRepository.retrieve(radio5_str).selected;
	//var _radio6Value = this._objDefsRepository.retrieve(radio6_str).selected;
	if (_combo1Value!="")
	{
		methode0 = (_radio1Value==true) ? "ASCENDING" : "DESCENDING";
	} else
	{
		methode0=undefined;
	}
	if (_combo2Value!="")
	{
		methode1 = (_radio3Value==true) ? "ASCENDING" : "DESCENDING";
	}else
	{
		methode1=undefined;
	}
	if (_combo3Value!="")
	{
		methode2 = (_radio5Value==true) ? "ASCENDING" : "DESCENDING";
	}else
	{
		methode2=undefined;
	} 
	_mySSinst.ssheet.spreadDisplayer.sortSsheet(_combo1Value,methode0,_combo2Value,methode1,_combo3Value,methode2);
	
	var event_str:String = "SS_SORT";
	var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+ssName_str +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "sortby1"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_combo1Value +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "order1"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+((methode0 != undefined) ? methode0 : "") +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "sortby2"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_combo2Value + 
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "order2"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+((methode1 != undefined) ? methode1 : "") +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "sortby3"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_combo3Value +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "order3"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+((methode2 != undefined) ? methode2 : "");
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // SS_SORT
	
	return("");
}

function searchWB()
{	
	var args:Array = arguments[0];
	var tabsId_str:String = args[0];
		
	var tFieldFind_str:String =args[1];
	var _myBtPrev_str:String =args[2];
	var _myBtNext_str:String =args[3];
	// currentPage Id
	var currentDisplayedPageId_str=this._objDefsRepository.retrieve(tabsId_str)._exulis.leaderIndicator_ref.linkedPanel_str;
	currentDisplayedPageId_str = (currentDisplayedPageId_str==undefined) ? tabsId_str : currentDisplayedPageId_str;
	// currentdisplayedPageContainer
	var _displayedPage=this._objDefsRepository.retrieve(currentDisplayedPageId_str);
	var _displayedPageXMLStructure= (currentDisplayedPageId_str==tabsId_str) ? _displayedPage._exulis._objDef : _displayedPage._exulis._guiSource;
		
	// Find TextField
	var findTfield_tf=this._objDefsRepository.retrieve(tFieldFind_str).text;
	this._objDefsRepository.searchWBCount=0;
	
	var event_str:String = "WB_SEARCH_QUERY";
	var payload_str:String = "query"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+findTfield_tf.text;
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // WB_SEARCH_QUERY
	
	var _strucXML=new XML(_displayedPageXMLStructure);
	
	
	
	var _fieldDescr_ar=XPath.selectNodes(_strucXML,"//description");
	var _fieldTextField_ar=XPath.selectNodes(_strucXML,"//textbox");
	
	
	var _allFields_ar:Array=_fieldDescr_ar.concat(_fieldTextField_ar);
	var _idList=new Array();
	var _allFieldsLength=_allFields_ar.length;
	
	for (var a=0;a<_allFieldsLength;a++)
	{
		_idList.push(this._objDefsRepository.retrieve(_allFields_ar[a].attributes.id));
	}
	
	trace("mySEARCH >> "+this._objDefsRepository.retrieve(tabsId_str).tField);
	
	if (_strucXML.firstChild.attributes.type=="fsview")
	{
		_idList.push(this._objDefsRepository.retrieve(tabsId_str).tField);
	}
	
	
	var _findResults_ar=new Find(_idList, findTfield_tf);
	
	
	
	
	var _zeResults_ar=_findResults_ar.getResults();
	
	//trace("feedTrace for WB_SEARCH_RESULTS, "WB search with " + _zeResults_ar.length + "results" +",stimulus");
	_level0.currentItemRootLevel.feedTrace("WB_SEARCH_RESULTS","nbofresults" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + _zeResults_ar.length,"stimulus");
	
	// boutons
	var _myBtNext = this._objDefsRepository.retrieve(_myBtNext_str);
	var _myBtPrev = this._objDefsRepository.retrieve(_myBtPrev_str);
	_myBtNext.onPress=Relegate.create(this,onPressNextSearchWB,_zeResults_ar,_displayedPage);
	_myBtPrev.onPress=Relegate.create(this,onPressPreviousSearchWB,_zeResults_ar,_displayedPage);
	if (_zeResults_ar.length>1){
		_myBtNext._visible=_myBtPrev._visible=true;
	} 
  	else{
		_myBtNext._visible=_myBtPrev._visible=false;
	}
	var _clipToFocus_mc=_zeResults_ar[0][0];
	var _beginSelection_nb:Number=Number(_zeResults_ar[0][1]);
	var _endSelection_nb:Number=Number(_zeResults_ar[0][2]);
	// display first founded word
	Selection.setFocus(_clipToFocus_mc);
	Selection.setSelection(_beginSelection_nb,_endSelection_nb);	
	// coordonées
	var coords_obj=coordonatesNewRef(_clipToFocus_mc,_displayedPage);
	// scroll good position
	if (_displayedPage._parent._exulis._objParent.vScroll!=undefined)
	{
	_displayedPage._parent._exulis.setVscrollPosition(coords_obj.y);
	}
}

function coordonatesNewRef(path_mc,displayedPage_mc)
{
	if (!path_mc || !displayedPage_mc) return;
	var coordonates=new Object();
	var Ycoord_nb=0;
	var Xcoord_nb=0;
	while (path_mc!=displayedPage_mc)
	{
		Ycoord_nb+=path_mc._y;
		Xcoord_nb+=path_mc._x;
		path_mc=path_mc._parent;
		if (path_mc==_root)
		{
			break;
		}
	}
	coordonates.x=Xcoord_nb;
	coordonates.y=Ycoord_nb;
	return coordonates;
}

function onPressNextSearchWB(zeResults,container_mc)
{
	this._objDefsRepository.searchWBCount++;
	this._objDefsRepository.searchWBCount = (this._objDefsRepository.searchWBCount>=zeResults.length) ? zeResults.length-1 : this._objDefsRepository.searchWBCount;
	var _clipToFocus_mc=zeResults[this._objDefsRepository.searchWBCount][0];
	var _beginSelection_nb:Number=Number(zeResults[this._objDefsRepository.searchWBCount][1]);
	var _endSelection_nb:Number=Number(zeResults[this._objDefsRepository.searchWBCount][2]);
	this.displayedPage=container_mc;
	this.clip=_clipToFocus_mc;
	this.deb=_beginSelection_nb;
	this.fin=_endSelection_nb;
	
	this.onEnterFrame=function()
	{
		Selection.setFocus(this.clip);
		if (eval(Selection.getFocus())==this.clip)
		{
			Selection.setSelection(this.deb,this.fin);
			// coordonées
			var coords_obj=coordonatesNewRef(this.clip,this.displayedPage);
			if (this.displayedPage._parent._exulis._objParent.vScroll!=undefined)
			{
			this.displayedPage._parent._exulis.setVscrollPosition(coords_obj.y);
			}
			delete this.onEnterFrame;
		}
	}
	
}

function onPressPreviousSearchWB(zeResults,container_mc)
{
	this._objDefsRepository.searchWBCount--;
	this._objDefsRepository.searchWBCount = (this._objDefsRepository.searchWBCount<0) ? 0 : this._objDefsRepository.searchWBCount;
	var _clipToFocus_mc=zeResults[this._objDefsRepository.searchWBCount][0];
	var _beginSelection_nb:Number=Number(zeResults[this._objDefsRepository.searchWBCount][1]);
	var _endSelection_nb:Number=Number(zeResults[this._objDefsRepository.searchWBCount][2]);
	this.displayedPage=container_mc;
	this.clip=_clipToFocus_mc;
	this.deb=_beginSelection_nb;
	this.fin=_endSelection_nb;
	this.onEnterFrame=function()
	{	
		Selection.setFocus(this.clip);
		if (eval(Selection.getFocus())==this.clip)
		{
			Selection.setSelection(this.deb,this.fin);
			// coordonées
			var coords_obj=coordonatesNewRef(this.clip,this.displayedPage);
			if (this.displayedPage._parent._exulis._objParent.vScroll!=undefined)
			{
			this.displayedPage._parent._exulis.setVscrollPosition(coords_obj.y);
			}
			delete this.onEnterFrame;
		}
	}
}

function splitH()
{
	var args:Array = arguments[0];
	var elementsUp_str=args[0];
	var elementsDown_str=args[1];
	// display the two environments
	var params_array = new Array(elementsUp_str);
	show(params_array);
	var params_array = new Array(elementsDown_str);
	show(params_array);
	// create mask
	var containerUp=this._objDefsRepository.retrieve(elementsUp_str);
	var containerDown=this._objDefsRepository.retrieve(elementsDown_str);
	var zeMaskUp=createMaskedZone(containerUp,719,339,0,0);	
	updateScroll(containerUp,zeMaskUp,"H");
	var zeMaskDown=createMaskedZone(containerDown,719,340,0,339);
	updateScroll(containerDown,zeMaskDown,"H");
	containerDown._y=339;
}

function splitV()
{
	var args:Array = arguments[0];
	var elementsLeft_str=args[0];
	var elementsRight_str=args[1];
	// display the two environments
	var params_array = new Array(elementsLeft_str);
	show(params_array);
	var params_array = new Array(elementsRight_str);
	show(params_array);
	// create mask
	var containerLeft=this._objDefsRepository.retrieve(elementsLeft_str);
	var containerRight=this._objDefsRepository.retrieve(elementsRight_str);
	var zeMaskLeft=createMaskedZone(containerLeft,359,679,0,0);	
	updateScroll(containerLeft,zeMaskLeft,"V");
	var zeMaskRight=createMaskedZone(containerRight,360,679,360,0);
	updateScroll(containerRight,zeMaskRight,"V");
	containerRight._x=360;
}

function createMaskedZone(clipToMask,zewidth,zeheight,xCoord,yCoord)
{
			if (!eval(clipToMask._parent["mask_"+clipToMask._name]))
			{
			var _zeClip=clipToMask._parent.createEmptyMovieClip("mask_"+clipToMask._name,clipToMask._parent._childNextDepth);
			clipToMask._parent._childNextDepth++;
			_zeClip.beginFill(0x00FF00,100);
			_zeClip.moveTo(0,0);
			_zeClip.lineTo(50,0);
			_zeClip.lineTo(50,50);
			_zeClip.lineTo(0,50);
			_zeClip.lineTo(0,0);
			_zeClip.endFill();
			} else
			{
				var _zeClip=eval(clipToMask._parent["mask_"+clipToMask._name]);
			}
			_zeClip._x=xCoord;
			_zeClip._y=yCoord;
			_zeClip._width= zewidth;
			_zeClip._height= zeheight;
			clipToMask.setMask(_zeClip);
			return eval(clipToMask._parent["mask_"+clipToMask._name]);
}

// destroy split
function resetScroll(){
	var args:Array = arguments[0];
	var elementsUp_str=args[0];
	var elementsDown_str=args[1];
	var elementUp=this._objDefsRepository.retrieve(elementsUp_str);
	var elementDown=this._objDefsRepository.retrieve(elementsDown_str);
	elementUp._y=0;
	elementUp._x=0;
	elementDown._y=0;
	elementDown._x=0;
	eval(elementUp._parent["mask_"+elementUp._name]).removeMovieClip();
	eval(elementDown._parent["mask_"+elementDown._name]).removeMovieClip();
	elementUp._parent.destroyObject("scroll_"+elementUp._name);
	elementDown._parent.destroyObject("scroll_"+elementDown._name);
}

function updateScroll(clipToMask,zeMask,scrollType):Void{
		if (!eval(clipToMask._parent["scroll_"+clipToMask._name]))
		{
			var myScroll=clipToMask._parent.createClassObject(mx.controls.UIScrollBar, "scroll_"+clipToMask._name, clipToMask._parent._childNextDepth);
			clipToMask._parent._childNextDepth++;
			// content height
			if (scrollType=="H")
			{
				var deltaHeight_nbr=679-zeMask._height;
				myScroll._visible= (deltaHeight_nbr<=0) ? false : true;
				myScroll.move(zeMask._width+zeMask._x-16,zeMask._y);
				myScroll.setSize(zeMask._width,zeMask._height);
				myScroll.setScrollProperties(30, 0, deltaHeight_nbr);
				var _scrollEvent=new Object();
				_scrollEvent.zeTarget=clipToMask;
				_scrollEvent.zeZone=zeMask._height;
				_scrollEvent.yOffset=zeMask._y;
				_scrollEvent.scroll=function(eventObject)
				{								
				this.zeTarget._y = -eventObject.target.scrollPosition+this.yOffset;
				}
			} else
				{
						myScroll.horizontal=true;
						var deltaWidth_nbr=clipToMask._width-zeMask._width;
						myScroll._visible= (deltaWidth_nbr<=0) ? false : true;
						myScroll.move(zeMask._x,zeMask._height-16);
						myScroll.setSize(zeMask._width,zeMask._width+zeMask._x);
						myScroll.setScrollProperties(30, 0, deltaWidth_nbr);
						var _scrollEvent=new Object();
						_scrollEvent.zeTarget=clipToMask;
						_scrollEvent.zeZone=zeMask._width;
						_scrollEvent.yOffset=zeMask._x;
						_scrollEvent.scroll=function(eventObject)
						{								
						this.zeTarget._x = -eventObject.target.scrollPosition+this.yOffset;
						}
				}
			myScroll.addEventListener("scroll", _scrollEvent);
		}					
}

function addBookmark(){
	var args:Array = arguments[0];	
	var tabsId_str:String = args[0];
	var bookmarkListId_str:String = args[1];
	var tFieldCurrentPageTitleId_str:String = args[2];
	var tFieldCurrentURLId_str:String = args[3];
	var addButtonId_str:String = args[4];
	var cancelButtonId_str:String = args[5];
	var addBookMarkPopupId_str:String = args[6];
	// put currentPage Current title and URL on textFields.
	var currentDisplayedPageId_str=this._objDefsRepository.retrieve(tabsId_str)._exulis.leaderIndicator_ref.linkedPanel_str;
	// displayed page
	var _displayedPage=this._objDefsRepository.retrieve(currentDisplayedPageId_str)._exulis;
	// tField ref
	var _tFieldTitle_tf=this._objDefsRepository.retrieve(tFieldCurrentPageTitleId_str);
	_tFieldTitle_tf.text=_displayedPage.holdTitle_str;
	// tField url
	var _tFieldTitle_tf=this._objDefsRepository.retrieve(tFieldCurrentURLId_str);
	_tFieldTitle_tf.text=_displayedPage.holdURL_str;
	// add infos to bookmark manager list
	var _bookmarkManagerList_ref=this._objDefsRepository.retrieve(bookmarkListId_str);
	// addButton
	var _addButton=this._objDefsRepository.retrieve(addButtonId_str);
	_addButton.onPress=Relegate.create(this,onPressAddBookmark,_bookmarkManagerList_ref,_displayedPage,currentDisplayedPageId_str);
	// populate listbox
	//_bookmarkManagerList_ref.addItem({label:_displayedPage.holdTitle_str,data:_displayedPage.holdURL_str,id:currentDisplayedPageId_str});
}

function onPressAddBookmark(bookmarkManagerList_ref,currentPage_ref,currentPageId_str,currentPopupId_str){
	var opt:String;
	if (currentPage_ref.holdTitle_str != undefined)
		opt = _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "title" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + currentPage_ref.holdTitle_str;
	if (currentPage_ref.holdURL_str != undefined)
		opt += _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "url" + _root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR + currentPage_ref.holdURL_str; 
	var event_str:String = "BOOKMARK_ADD";
	var payload_str:String = "pageid"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+currentPageId_str + opt;
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // BOOKMARK_ADD
	
	bookmarkManagerList_ref.addItem({label:currentPage_ref.holdTitle_str,data:currentPage_ref.holdURL_str,id:currentPageId_str});
}

function displayBookMark(){
	var args:Array = arguments[0];
	var listBmark_str:String = args[0];
	var tFieldTitle_str:String = args[1];
	var tFieldUrl_str:String = args[2];
	var oKButton_str:String =args[3];
	var list_Bmark_str = this._objDefsRepository.retrieve(listBmark_str);
	var tField_Tilte_tf = this._objDefsRepository.retrieve(tFieldTitle_str);
	var tField_Url_tf = this._objDefsRepository.retrieve(tFieldUrl_str);
	tField_Tilte_tf.text=list_Bmark_str.selectedItem.label;
	tField_Url_tf.text=list_Bmark_str.selectedItem.data;
	var zeURL=list_Bmark_str.selectedItem.id;
	// goto URL ("OK") button
	var _oKbutton=this._objDefsRepository.retrieve(oKButton_str);
	_oKbutton.onPress=Relegate.create(this,onPressBkGoToUrl,zeURL);
	
	var opt:String;
	if (tField_Tilte_tf.text != undefined)
		opt = _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "title"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+tField_Tilte_tf.text;
	if (tField_Url_tf.text != undefined)
		opt += _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "url"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+tField_Url_tf.text; 
	var event_str:String = "BOOKMARK_VIEW";
	var payload_str:String = "pageid"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+zeURL + opt;
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // BOOKMARK_VIEW
}

function onPressBkGoToUrl(zeURL){
	var params_array = new Array(zeURL,"_self","unknown");
	gotoURL(params_array);
}

function processMcSort(){
	var args:Array = arguments[0];
	var _mcListName_str:String = args[0];
	var _combo1_str:String = args[1];
	var _radio1_str:String = args[2];
	var methode_str;
	var _myList = this._objDefsRepository.retrieve(_mcListName_str)._exulis;
	var _combo1Value = this._objDefsRepository.retrieve(_combo1_str).myCombo.selectedItem.data;
	var _radio1Value = this._objDefsRepository.retrieve(_radio1_str).selected;
	if (_combo1Value!=""){
		methode_str = (_radio1Value==true) ? "ASC" : "DESC";
	}
	_myList.fileSystm.fileSystListView.sortList(_combo1Value,methode_str);
	
	var event_str:String = "MC_SORT";
	var payload_str:String = "sortby"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_combo1Value +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "order"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+((methode_str != undefined) ? methode_str : "ASCENDING");
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // MC_SORT
}

function displayChoice(){
	var args:Array = arguments[0];
	var _testedRef_str:String = args[0];
	var globalVars:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
/*
	if (globalVars.data.myChoice)
	{
	//var intervalId:Number;
	_global.intervalId30=setInterval(this, "doDisplay", 200, globalVars.data.myChoice);
	}
*/
	if (globalVars.data.graphViews_box){
		var latestLeaderId_str:String = String(globalVars.data["graphViews_box"]);
		_level0.currentItemRootLevel.feedTrace("GLOBAL_VAR","graphid="+latestLeaderId_str,"service");
		_global.intervalId30=setInterval(this, "doDisplay", 200, latestLeaderId_str);
	}
}

function displaySite(){
	var args:Array = arguments[0];
	var _idQuestion_str = args[0];
	var _target_str = args[1];
	var globalVars:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
	var _myChoice_str=globalVars.data[_idQuestion_str];
	trace("mySite myChoice_str : "+_myChoice_str);
	_global.intervalId102=setInterval(this, "doGoToSite", 200, _myChoice_str, _target_str);
}

function doDisplay(target){
	var _goTo_str="";
	switch (target){
		case "u04b_pg1_txt1":
		_goTo_str="graph1";
		break;
		case "u04b_pg2_txt1":
		_goTo_str="graph2";
		break;
		case "u04b_pg3_txt1":
		_goTo_str="graph3";
		break;
		case "u04b_pg4_txt1":
		_goTo_str="graph4";
		break;
		case "u04b_pg5_txt1":
		_goTo_str="graph5";
		break;
		case "u04b_pg6_txt1":
		_goTo_str="graph6";
		break;
		case "u04b_pg7_txt1":
		_goTo_str="graph7";
		break;
	}
	var _toDo= this._objDefsRepository.retrieve(_goTo_str);
	if (_toDo)
	{
		clearInterval(intervalId30);
		_toDo._visible=true;
	}
}

function doGoToSite(idSite, target){	
	var _toDo= this._objDefsRepository.retrieve(target);
		if (_toDo)
		{
				clearInterval(intervalId102);
				var _goTo_str="";
				switch (idSite)
				{
					case 1:
					_goTo_str="unit06cpage1";
					break;
					case 2:
					_goTo_str="unit06cpage3";
					break;
					case 3:
					_goTo_str="unit06cpage5";
					break;
					case 4:
					_goTo_str="unit06cpage7";
					break;
					case 5:
					_goTo_str="unit06cpage8";
					break;
		}
				_level0.currentItemRootLevel.feedTrace("GLOBAL_VAR","website="+idSite,"service");
				_level0.currentItemRootLevel.feedTrace("TRANSLATION","address="+_goTo_str,"service");
				trace("mySite _goTo_str "+_goTo_str);
				var params_array = new Array(_goTo_str,"_self","unknown");
				gotoURL(params_array);
		}
}

function setChoice(){
	var args:Array = arguments[0];
	var choice_str=args[0];
	var globalVars:SharedObject = SharedObject.getLocal("/" + "globalVars","/");
	globalVars.data.myChoice = choice_str;
	globalVars.flush();
}

function populateListBox(){
	var args:Array = arguments[0];
	var _myTarget:String = args[0];
	var _myDatas_str:String = args[1];
	var _myDataSet = this._objDefsRepository.retrieve(_myDatas_str)._exulis.listBoxDatas_xml;
	//var intervalId:Number;
	_global.intervalId=setInterval(this, "addItemToList", 200, _myTarget, _myDataSet, "intervalId");
}

function addTabsDatas(){
	var args:Array = arguments[0];
	var _myTarget:String = args[0];
	var _myDatas_str:String = args[1];
	
	
	
	var _myDataSet = this._objDefsRepository.retrieve(_myDatas_str)._exulis.tabsheader_xml;
	
	
	var _myTargetToPopulate=this._objDefsRepository.retrieve(_myTarget)._exulis;
	_global.intervalId1=setInterval(this, "addItemToTabsManager", 200, _myTarget, _myDataSet, "intervalId1");
}

function addItemToTabsManager(target, datas, interval){
		trace("myTAB addItemToTabsManager target : "+target+" datas : "+datas+" : "+interval);
	
	
	var _toPopulate= this._objDefsRepository.retrieve(target)._exulis;
	if (_toPopulate)
		{
				clearInterval(intervalId1);		
				_toPopulate.populateTabChooser(datas);
		}
}

function doSum(){
	var args:Array = arguments[0];
	var _myTarget:String = args[0];
	var _myTfield:String = args[1];
	//var intervalId:Number;
	_global.intervalIdSum=setInterval(this, "addDgSum", 200, _myTarget, _myTfield, "intervalIdSum");
}

function addDgSum(target,tField,interval){
	var _dg= this._objDefsRepository.retrieve(target)._exulis;
	if (_dg)
		{
				clearInterval(intervalIdSum);
				var mytField=this._objDefsRepository.retrieve(tField);
				mytField.selectable=false;
				var _totalSize=0;
				var cellPress_obj=new Object();
				cellPress_obj.tField=mytField;
				cellPress_obj.cellPress=function(event)
				{
					var _size=Number(event.target.getItemAt(event.target.selectedIndex).spreadSheet_colB);
					
					//var _testedSize=event.target.getItemAt(0).spreadSheet_colB_dataToDisplay;
					//var _separator=_testedSize.substr(2,1); 
				
					
					if (event.target.getItemAt(event.target.selectedIndex).rowSelect==true)
					{   
					_totalSize+=_size;
					} else
						{
							_totalSize-=_size;
						}
						
					var _zeRezult=processMP3ManagerSum(event.target);
					
					
					var _displayedResult=String(_zeRezult).split(".").join(_dg._targetExecutionLayer.separator);
					
					
					
					this.tField.text=_displayedResult;
					
					//trace("totalPress "+event.target.getItemAt(event.target.selectedIndex).rowSelect);
					//trace("totalPress "+processMP3ManagerSum(event.target));
				}
				trace("cellPress "+this._objDefsRepository.retrieve(target)._exulis.ssheet.spreadDisplayer.xulWindow_mc.mySpreadSheet);
				this._objDefsRepository.retrieve(target)._exulis.ssheet.spreadDisplayer.xulWindow_mc.mySpreadSheet.addEventListener("cellPress",cellPress_obj);
		}
}

function processMP3ManagerSum(mpManager):Number{
	var _count=0;
	var _totalSize=0;
	while (mpManager.getItemAt(_count).spreadSheet_colB!=undefined)
	{
		if (mpManager.getItemAt(_count).rowSelect==true)
		{
			_totalSize+=Number(mpManager.getItemAt(_count).spreadSheet_colB);
		}
		_count++;
	}
	_level0.currentItemRootLevel.feedTrace("CALCULATED_VAR","total_size=" + String(_totalSize),"service");
	return _totalSize;
}

function addItemToList(target, datas, interval){
	var _toPopulate= this._objDefsRepository.retrieve(target)._exulis;
	trace("myDatas > ok"+_toPopulate);
/*
	if (_toPopulate){
		clearInterval(intervalId);
		_toPopulate.populateList(datas);
	}
	if(_toPopulate == undefined){
		clearInterval(intervalId);
	}
*/
}
/*
function openPSConfirmationPopup(){
	var confirmationType_str:String;
	var vTag_str:String = this._objDefsRepository.get(0,1)[0]._exulis.id;
	trace("openPSConfirmationPopup opened with unit: " + vTag_str);
	switch(vTag_str){
		case "unit01a":
		case "unit03a":
		case "unit03b":
		case "unit04a":
		case "unit04b":
		case "unit06a":
		case "unit06b":
		case "unit10a":
		case "unit11a":
		case "unit19a":{
			confirmationType_str = "TASK";
			break;
		}
		case "unit01b":
		case "unit02":
		case "unit03c":
		case "unit04c":
		case "unit06c":
		case "unit07":
		case "unit10b":
		case "unit11b":
		case "unit12":
		case "unit16":
		case "unit19b":
		case "unit21":
		case "unit22":
		case "unit23":{
			confirmationType_str = "UNIT";
			break;
		}
		default:
		{
			confirmationType_str = "UNIT";
		}
	}
	_level0.currentItemRootLevel.feedTrace("CONFIRMATION_OPENED","type="+confirmationType_str,"stimulus");
	var renderedSVG:Object;
	var renderedNode_xml:XML;
	if(confirmationType_str == "TASK"){
		renderedNode_xml = new XML('<box id="psConfirmEndTask" top="0" left="0" visible="true" type="content"><![CDATA[../common_BLACK/popup_endtask_BLACK.xml]]></box>');
	}
	else{ // confirmationType_str == "UNIT"
		renderedNode_xml = new XML('<box id="psConfirmEndUnit" top="0" left="0" visible="true" type="content"><![CDATA[../common_BLACK/popup_endunit_BLACK.xml]]></box>');
	}
	trace("openPSConfirmationPopup node: " + renderedNode_xml.firstChild);
	renderedBox = new XULbox(this,renderedNode_xml.firstChild);
	var local_mc = renderedBox.create();
}
*/
function closeWindow(){
	trace("Window close");
	var args:Array = arguments[0];
	var windowId_str:String = args[0];
	var referer_obj = args[1];
	var _myWindow = this._objDefsRepository.retrieve(windowId_str)._exulis;
	_myWindow.closeWindow(referer_obj);
	return("");
}

function abendWindow(){
	trace("Confirmation popup closed with CANCEL");
	var args:Array = arguments[0];
	var windowId_str:String = args[0];
	var referer_obj = args[1];
	_level0.currentItemRootLevel.feedTrace("CONFIRMATION_CLOSED","action=CANCEL","stimulus");
	renderedBox.destroy();
	_level0.currentItemRootLevel.ConfirmationPopupClosed("cancel");
	return("");
}

function proceedWindow(){
	trace("Confirmation popup closed with OK");
	var args:Array = arguments[0];
	var windowId_str:String = args[0];
	var referer_obj = args[1];
	_level0.currentItemRootLevel.feedTrace("CONFIRMATION_CLOSED","action=OK","stimulus");
	renderedBox.destroy();
	_level0.currentItemRootLevel.ConfirmationPopupClosed("ok");
	return("");
}

function searchSS(){
	var args:Array = arguments[0];
	var sView_str:String = args[0];
	var tField_str:String = args[1];
	var btNext_str=args[3];
	var btPrevious_str=args[2];
	var _mySearch_str:String = this._objDefsRepository.retrieve(tField_str).text;
	var _mySSinst = this._objDefsRepository.retrieve(sView_str)._exulis;
	var tmpToto = Selection.getFocus();
	_mySSinst.ssheet.spreadDisplayer.searchWord(String(_mySearch_str));
	var _myBtNext = this._objDefsRepository.retrieve(btNext_str);
	var _myBtPrev = this._objDefsRepository.retrieve(btPrevious_str);
	var nbOfResults:Number = _mySSinst.ssheet.spreadDisplayer.getSearchAnswerNumber();
	
	//no result, do something (blink)
	if (nbOfResults == 0) {
		var _zeField = this._objDefsRepository.retrieve(tField_str);
		var _contener = _zeField._parent;
		blinkTween.stop();
		var blinkclip_mc = _contener.createEmptyMovieClip("blinkClip", 989898);
		blinkclip_mc.beginFill(0xFF0000, 80);
		blinkclip_mc.moveTo(0, 0);
		blinkclip_mc.lineTo(5, 0);
		blinkclip_mc.lineTo(5, 5);
		blinkclip_mc.lineTo(0, 5);
		blinkclip_mc.lineTo(0, 0);
		blinkclip_mc.endFill();
		
		
		
		
		blinkclip_mc._x = _zeField._x;
		blinkclip_mc._y = _zeField._y;
		blinkclip_mc._width = _zeField._width;
		blinkclip_mc._height = _zeField._height;
		
		var blinkTween = new Tween(blinkclip_mc, "_alpha", null, 0, 100, 1);
		blinkTween.a = 0;
		blinkTween.onMotionFinished  = function()  {
			
		this.a++;
		
			if (this.a<6){
			this.yoyo();
			} else {
				blinkclip_mc.removeMovieClip();
				blinkTween.stop();
				blinkTween = null;
				delete blinkTween;
				
			}
		}
			
	}
	
	
	if (nbOfResults>1){
		_myBtNext._visible=_myBtPrev._visible=true;
	} 
	else{		
		_myBtNext._visible = _myBtPrev._visible = false;		
	}
	
	var event_str:String = "SS_SEARCH";
	var payload_str:String = "keyword"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+String(_mySearch_str) +
	_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "nbofresults"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+nbOfResults;
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // SS_SEARCH
	
	return("");
}

function previousItemSearchSS(){
	var args:Array = arguments[0];
	var ssName_str:String = args[0];
	var tField_str:String = args[1];
	var tButton_str:String = args[2];
	var _mySearch_tf:TextField = this._objDefsRepository.retrieve(tField_str);
	var _mySSinst = this._objDefsRepository.retrieve(ssName_str)._exulis;
	_mySSinst.ssheet.spreadDisplayer.displayPreviousFounded();
	var _myTBinst = this._objDefsRepository.retrieve(tButton_str);
	Selection.setFocus(_myTBinst);
	return("");
}

function nextItemSearchSS(){
	var args:Array = arguments[0];
	var ssName_str:String = args[0];
	var tField_str:String = args[1];
	var tButton_str:String = args[2];
	var _mySearch_tf:TextField = this._objDefsRepository.retrieve(tField_str);
	var _mySSinst = this._objDefsRepository.retrieve(ssName_str)._exulis;
	_mySSinst.ssheet.spreadDisplayer.displayNextFounded();
	var _myTBinst = this._objDefsRepository.retrieve(tButton_str);
	Selection.setFocus(_myTBinst);
	return("");
}

function createFolder(){
	var args:Array = arguments[0];
	var viewFolderId_str:String = args[0];	
	var tField_str:String = args[1];	
	var _myFolderView = this._objDefsRepository.retrieve(viewFolderId_str)._exulis;
	var _inputName_tf=this._objDefsRepository.retrieve(tField_str);
	var _targetFolderId_str=_myFolderView.fileSystm.currentFolderId_str;
	if (_inputName_tf.text.length<1) return;
	if (_targetFolderId_str == undefined)
		_targetFolderId_str = "";
	var zeNewFolder=_myFolderView.fileSystm.fileSystModel.addFolder(_targetFolderId_str, _inputName_tf.text);
	if (zeNewFolder==undefined) return;
	trace("feedTrace for NEW_FOLDER, Stimulus " + "target"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_targetFolderId_str+ _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "name"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_inputName_tf.text+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"id"+zeNewFolder);
	_level0.currentItemRootLevel.feedTrace("NEW_FOLDER","target"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_targetFolderId_str + _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "name"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_inputName_tf.text+_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR+"id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+zeNewFolder,"stimulus");
	return("");
}

function moveTo(){
	var args:Array = arguments[0];
	var viewMailId_str:String = args[0];	
	var targetFolderId_str:String = args[1];
	var _myMVinst = this._objDefsRepository.retrieve(viewMailId_str)._exulis;
	var _myTargetTree = this._objDefsRepository.retrieve(targetFolderId_str)._exulis;
	var _currentElementId_str=_myMVinst.fileSystm.currentMailId_str;
	var _targetFolderId_str=_myTargetTree.fileSystm.currentFolderId_str;
	_myMVinst.fileSystm.fileSystModel.moveItem(_currentElementId_str,_targetFolderId_str);
	_myMVinst.fileSystm.menuState(this,"inactive");
	
	var event_str:String = "MAIL_MOVED";
	var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_currentElementId_str +
		_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "target"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_targetFolderId_str;
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // MAIL_MOVED
	
	return("");
}

function deleteMail(){
	var args:Array = arguments[0];
	var mailView_str:String = args[0];
	var folderId_str:String = args[1];
	var _myMVinst = this._objDefsRepository.retrieve(mailView_str)._exulis.fileSystm;
	_myMVinst.fileSystModel.moveItem(_myMVinst.currentMailId_str,folderId_str);
	_myMVinst.menuState(this,"inactive");
	
	var event_str:String = "MAIL_DELETED";
	var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_myMVinst.currentMailId_str;
		/*+ FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "source"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+folderId_str;*/
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // MAIL_DELETED
	
	return("");
}

function copyTo(){
	var args:Array = arguments[0];
	var viewMailId_str:String = args[0];	
	var targetFolderId_str:String = args[1];
	var _myMVinst = this._objDefsRepository.retrieve(viewMailId_str)._exulis;
	var _myTargetTree = this._objDefsRepository.retrieve(targetFolderId_str)._exulis;
	var _currentElementId_str=_myMVinst.fileSystm.currentMailId_str;
	var _targetFolderId_str=_myTargetTree.fileSystm.currentFolderId_str;
	var _zenode=_myMVinst.fileSystm.fileSystModel.getNode(_currentElementId_str);
	if (_zenode.attributes.type=="file"){
	var zeNewMail=_myMVinst.fileSystm.fileSystModel.copyItem(_currentElementId_str,_targetFolderId_str);
	
	var event_str:String = "MAIL_COPIED";
	var payload_str:String = "id"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_currentElementId_str + _root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "target"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_targetFolderId_str+_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR+"idnew"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+zeNewMail;
	
	trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
	_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // MAIL_COPIED
	}
	return("");
}

function moveToSender(){
	var args:Array = arguments[0];
	var viewMailId_str:String = args[0];	
	var _myMVinst = this._objDefsRepository.retrieve(viewMailId_str)._exulis;
	_myMVinst.fileSystm.fileSystModel.setMailVisible("item103","false");
	return("");
}

function receiveFrom(){
	var args:Array = arguments[0];
	var viewMailId_str:String = args[0];	
	var _myMVinst = this._objDefsRepository.retrieve(viewMailId_str)._exulis;
	_myMVinst.fileSystm.fileSystModel.setMailVisible("item103","true");
	return("");
}

// Planning
// intervPlanning_ar[0] -> starting hour, intervPlanning_ar[1] -> ending hour 
// intervToTest_ar[0] -> whished starting hour, intervToTest_ar[1] -> whished ending hour

function testInterval(intervPlanning_ar,intervToTest_ar):Boolean
{
	var _val1a=intervPlanning_ar[0];
	var _val2a=intervPlanning_ar[1];	
	var _val1=intervToTest_ar[0];
	var _val2=intervToTest_ar[1];
	if ((_val1>=_val1a) && (_val1<_val2a)) return false;
	if ((_val2>_val1a) && (_val2<_val2a)) return false;
	if ((_val1<=_val1a) && (_val2>=_val2a)) return false;
	return true;
}

// Default value by room
function initPlanning()
{
	this._objDefsRepository.u2rooms=new Object();
	this._objDefsRepository.u2rooms.room1_ar=[[10,13,false]];
	this._objDefsRepository.u2rooms.room2_ar=[[15,17,false]];
	this._objDefsRepository.u2rooms.room3_ar=[[12,14,false]];
	this._objDefsRepository.u2rooms.room4_ar=[[9,16,false]];
}

function submitPlanning()
{
	var args:Array = arguments[0];
	var idUnit=args[0];
	var _refComboTimeBeg=String(args[1]);
	var _refcomboTimeEnd=String(args[2]);
	var _refcomboRoom=String(args[3]);
	var _refcomboDay=String(args[4]);
	var _refcomboDep=String(args[5]);
	var _popupValidation=String(args[6]);
	var _popupNotComplet=String(args[7]);
	var _popupConflict=String(args[8]);
	// hide popup
	var params_array = new Array(_popupNotComplet);
	hide(params_array);
	// time combos
	var _comboTimeBegIndex=this._objDefsRepository.retrieve(_refComboTimeBeg);
	var _comboTimeEndIndex=this._objDefsRepository.retrieve(_refcomboTimeEnd);
	// room
	var _zeroom=this._objDefsRepository.retrieve(_refcomboRoom);
	// date
	var _zedate=this._objDefsRepository.retrieve(_refcomboDay);
	// department
	var _department=this._objDefsRepository.retrieve(_refcomboDep);
	// Submit New Reservation
	if ("u02_pg3")
	{
		var _beginTime=Number(_comboTimeBegIndex.selectedIndex)+7;
		var _endTime=Number(_comboTimeEndIndex.selectedIndex)+8;
		if (_comboTimeBegIndex.selectedIndex==0
			|| _comboTimeEndIndex.selectedIndex==0
			|| _zeroom.selectedIndex==0
			|| _department.selectedIndex==0
			)
			{
				trace("myPLAN POPUP -> thank you to fill all fields ... ! ");
				trace("myPLAN POPUP _comboTimeBegIndex "+_comboTimeBegIndex.selectedIndex);
				trace("myPLAN POPUP _zeroom "+_zeroom.selectedIndex);
				trace("myPLAN POPUP _department "+_department.selectedIndex);
				trace("myPLAN POPUP _comboTimeBegIndex "+_comboTimeBegIndex.selectedIndex);
				// form not complet
				
				var event_str:String = "SUBMIT_RESERVATION_FAILURE";
				var payload_str:String = "reason"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"missing field";
				trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
				_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // SUBMIT_RESERVATION_FAILURE
				
				var params_array = new Array(_popupNotComplet);
				show(params_array);
				return;
			}
	// test the proposal
	var _storedDate_ar=this._objDefsRepository.u2rooms["room"+_zeroom.selectedIndex+"_ar"];
	var _proposalDate_ar=[_beginTime,_endTime];
	var _proposalOk=true;
		for (var a in _storedDate_ar)
		{
			var _zeDateTest=testInterval(_storedDate_ar[a],_proposalDate_ar);	
			if (!_zeDateTest)
			{
				trace("myPLAN PAS OK");
				
				var event_str:String = "SUBMIT_RESERVATION_FAILURE";
				var payload_str:String = "reason"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"conflict";
				trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
				_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // SUBMIT_RESERVATION_FAILURE
				
				// conflict popup
				var params_array = new Array(_popupConflict);
				show(params_array);
				
				
				
				break;
			}
		}
		// Reservation ok
		if (_zeDateTest)
		{
			this._objDefsRepository.u2rooms["room"+_zeroom.selectedIndex+"_ar"].push([_beginTime,_endTime,true,_department.text,_zeroom.selectedIndex,_department.selectedIndex]);
			// validation popup
			var params_array = new Array(_popupValidation);
			show(params_array);
			drawReservation("box");
			
			var event_str:String = "SUBMIT_RESERVATION_SUCCESS";
			var payload_str:String = "room_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_zeroom.selectedIndex +
				_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "start_time_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_comboTimeBegIndex.selectedIndex +
				_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "end_time_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_comboTimeEndIndex.selectedIndex +
				_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "department_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+_department.selectedIndex;			
			trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
			_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // SUBMIT_RESERVATION_SUCCESS
		}
	}
}

function drawReservation()
{
	var args:Array = arguments[0];
	var idCanvas=(args[0]==undefined) ? arguments[0] : args[0];
	_global.myCanvas=setInterval(this, "doOnCanvas", 200, idCanvas);
}

function doOnCanvas(zeCanvas)
{
	var _canvas=this._objDefsRepository.retrieve(String(zeCanvas));
	
	// clean Previous RV
	if (this._objDefsRepository.u2reservation!=undefined && this._objDefsRepository.u2reservation>0)
	{
		var params_array = new Array("u02_pg1_popup8");
		hide(params_array);
		for (var s=777777;s<this._objDefsRepository.u2reservation;s++)
		{
			trace("myDELETE delete "+_canvas["square"+s]);
			_canvas["square"+s].removeMovieClip();
		}
	}
	this._objDefsRepository.u2reservation=777777;
	var _zelevel=777777;
	if (_canvas)
	{
		clearInterval(myCanvas);
		// create RV
		for (var z in this._objDefsRepository.u2rooms)
		{
			var _roomz=this._objDefsRepository.u2rooms[z];
			var _roomzId=Number(z.substr(4,1));
			for (var k in _roomz)
			{
				if (_roomz[k][2]==true)
				{
					_myRv=_canvas.createEmptyMovieClip("square"+_zelevel,_zelevel);
					_myRvBack=_myRv.createEmptyMovieClip("backG",1);
					_myRv.onPress=Relegate.create(this,goToModification,_roomz[k][0],_roomz[k][1],_roomz[k][4],_roomz[k][5]);
					_myTfield=_myRv.createTextField("backG",2,5,0,120,10);
					_myTfield.autoSize="Left";
					_myTfield.wordWrap=true;
					_myTfield.html=true;			
					_myTfield.htmlText="<font face='Arial' size='14px'>"+_roomz[k][3]+"</font>";
					_zelevel++;
					_myRvBack.beginFill(0xffff66,100);
					_myRvBack.lineStyle(0,0x000000);
					_myRvBack.moveTo(0,0);
					_myRvBack.lineTo(50,0);
					_myRvBack.lineTo(50,50);
					_myRvBack.lineTo(0,50);
					_myRvBack.lineTo(0,0);
					_myRvBack.endFill();
					_myRvBack._width=121;
					_myRvBack._height=42*(Number(_roomz[k][1])-Number(_roomz[k][0]));
					_myTfield._y=(_myRvBack._height-_myTfield._height)/2;
					_myRv._y=44+((Number(_roomz[k][0])-8)*42);
					_myRv._x=82+((_roomzId-1)*121);
					this._objDefsRepository.u2reservation++;
				}
			}
		}
	}
}

// Calendar
function goToModification(beginhIndex, endhIndex, roomIndex, depIndex)
{
	// hidepopup
	var params_array = new Array("u02_pg4_popup2");
	hide(params_array);
	if (roomIndex==undefined) return;
	//var params_array = new Array("unit02page4","_self","unknown");
	//gotoURL(params_array);
	var params_array = new Array("u02_pg1_popup8");
	show(params_array);
	var _tempObj=new Object();
	_tempObj.room1_ar=this._objDefsRepository.u2rooms.room1_ar.slice();
	_tempObj.room2_ar=this._objDefsRepository.u2rooms.room2_ar.slice();
	_tempObj.room3_ar=this._objDefsRepository.u2rooms.room3_ar.slice();
	_tempObj.room4_ar=this._objDefsRepository.u2rooms.room4_ar.slice();
	var _comboRoom_str="menulist1p4";
	var _beginH_str="menulist4p4";
	var _endH_str="menulist5p4";
	var _dep_str="menulist6p4";
	var _validationBton="u02_pg4_txt90";
	var comboRoom_mc=this._objDefsRepository.retrieve(_comboRoom_str);
	var begin_mc=this._objDefsRepository.retrieve(_beginH_str);
	var end_mc=this._objDefsRepository.retrieve(_endH_str);
	var depart_mc=this._objDefsRepository.retrieve(_dep_str);
	// remove this case of _temporary Array;
	var _id;
	var _roomTab_ar=_tempObj["room"+(roomIndex)+"_ar"];
	for (var f=0;f<=_roomTab_ar.length;f++){
		var _currentResult_ar=_roomTab_ar[f];
		if (_currentResult_ar[0]==beginhIndex
			&& _currentResult_ar[1]==endhIndex
			&& _currentResult_ar[5]==depIndex)
		{
			_id=f;
			break;
		}
	}
	
	if (_id!=undefined)
	{
		_roomTab_ar=_roomTab_ar.splice(_id,1);
	}
	comboRoom_mc.selectedIndex=roomIndex;
	begin_mc.selectedIndex=beginhIndex-7;
	end_mc.selectedIndex=endhIndex-8;
	depart_mc.selectedIndex=depIndex;
	this._objDefsRepository.infosToValidate_ar=[_tempObj,comboRoom_mc,begin_mc,end_mc,depart_mc];
}

function valideToModification()
{	
	var args=this._objDefsRepository.infosToValidate_ar;
	var _beginTime=Number(args[2].selectedIndex)+7;
	var _endTime=Number(args[3].selectedIndex)+8;
	if (args[2].selectedIndex==0
		|| args[3].selectedIndex==0
		|| args[1].selectedIndex==0
		|| args[4].selectedIndex==0
		)
		{
			// form not complet
			var params_array = new Array("u02_pg4_popup5");
			show(params_array);
			
			var event_str:String = "CHANGE_RESERVATION_FAILURE";
			var payload_str:String = "reason"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"field missing";
			trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
			_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // CHANGE_RESERVATION_FAILURE
			
			return;
		}
	// test the proposal
	var _storedDate_ar=args[0]["room"+args[1].selectedIndex+"_ar"];
	var _proposalDate_ar=[_beginTime,_endTime];
	var _proposalOk=true;
	for (var a in _storedDate_ar)
	{
		var _zeDateTest=testInterval(_storedDate_ar[a],_proposalDate_ar);	
		if (!_zeDateTest)
		{
			// conflict popup
			var params_array = new Array("u02_pg4_popup7");
			show(params_array);
			
			var event_str:String = "CHANGE_RESERVATION_FAILURE";
			var payload_str:String = "reason"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+"date";
			trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
			_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // CHANGE_RESERVATION_FAILURE
			
			break;
		}
	}
	// Reservation ok
	if (_zeDateTest)
	{
		args[0]["room"+args[1].selectedIndex+"_ar"].push([_beginTime,_endTime,true,args[4].text,args[1].selectedIndex,args[4].selectedIndex]);
		this._objDefsRepository.u2rooms.room1_ar=args[0].room1_ar.slice();
		this._objDefsRepository.u2rooms.room2_ar=args[0].room2_ar.slice();
		this._objDefsRepository.u2rooms.room3_ar=args[0].room3_ar.slice();
		this._objDefsRepository.u2rooms.room4_ar=args[0].room4_ar.slice();
		goToModification(args[2].selectedIndex, args[3].selectedIndex, args[1].selectedIndex, args[4].selectedIndex);
		doOnCanvas("box");
		// validation popup
		var params_array = new Array("u02_pg4_popup2");
		show(params_array);
		this._objDefsRepository.retrieve("u02_popup2_txt2").onPress=Relegate.create(this,goToPlanning);
		drawReservation("box");
		
		goToPlanning();
		
		var event_str:String = "CHANGE_RESERVATION_SUCCESS";
		var payload_str:String = "room_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+args[1] +
			_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "start_time_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+args[2] +
			_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "end_time_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+args[3]  +
			_root.FEEDTRACE_PAYLOAD_ATTRIBUTES_SEPARATOR + "department_index"+_root.FEEDTRACE_PAYLOAD_ATTRIBUTE_NAME_VALUE_SEPARATOR+args[4];			
		trace("feedTrace for " + event_str + ", Stimulus " + payload_str);
		_level0.currentItemRootLevel.feedTrace(event_str,payload_str,"stimulus"); // CHANGE_RESERVATION_SUCCESS
	}
}

function deleteRoomReservation()
{	
	var args=this._objDefsRepository.infosToValidate_ar;
	this._objDefsRepository.u2rooms.room1_ar=args[0].room1_ar.slice();
	this._objDefsRepository.u2rooms.room2_ar=args[0].room2_ar.slice();
	this._objDefsRepository.u2rooms.room3_ar=args[0].room3_ar.slice();
	this._objDefsRepository.u2rooms.room4_ar=args[0].room4_ar.slice();
	goToModification(args[2].selectedIndex, args[3].selectedIndex, args[1].selectedIndex, args[4].selectedIndex);
	doOnCanvas("box");
	goToPlanning();
	
	customFeedTrace("DELETE_RESERVATION");
}

function goToPlanning()
{
	var params_array = new Array("u02_pg1_popup8");
	hide(params_array);
}


function switchLayout() {
	
	
	trace("CESOIR " + r4ref);
	trace("CESOIR " + r5ref);
	
	if (this._objDefsRepository.u21p2_bool == true) {
		r4ref._visible = false;
		r5ref._visible = false;
		
		var comboRoom1_mc = this._objDefsRepository.retrieve("u021_pg2_menu3");
		var comboRoom2_mc=this._objDefsRepository.retrieve("u021_pg2_menu4");
		comboRoom1_mc.selectedIndex=0;
		comboRoom2_mc.selectedIndex=0;
		
	} else {
		r4ref._visible = true;
		r5ref._visible = true;
		
	}
	
	
		
}


