class tao_languageExtender extends mx.lang.Locale {
	function tao_languageExtender(){
	}
	function setLangLoadCallback(fctToCall:Function){
		setLoadCallback(fctToCall);
	}
	function setXmlLang(newGUILang:String){
		xmlLang = newGUILang;
		initialize();
	}
	function reinitialize(){
		initialize();
	}
	function getString(variableLabel:String):String {
		var myRetStr = new String("");
		var myTmpStr = new String("");
		myTmpStr = loadString(variableLabel);
		while(myTmpStr != ""){
			var testUnicodeIndex;
			var slicedStr = new String("");
			testUnicodeIndex = myTmpStr.indexOf("\\u");
			if(testUnicodeIndex > -1){
				slicedStr = myTmpStr.slice(0,testUnicodeIndex);
				myRetStr = myRetStr + slicedStr;
				myTmpStr = myTmpStr.substr(testUnicodeIndex);
				slicedStr = String.fromCharCode(parseInt("0x" + myTmpStr.slice(2,6)));
				myRetStr = myRetStr + slicedStr;
				myTmpStr = myTmpStr.substr(6);
			}
			else{
				myRetStr = myRetStr + myTmpStr;
				myTmpStr = "";
			}
		}
		return(myRetStr);
	}
}