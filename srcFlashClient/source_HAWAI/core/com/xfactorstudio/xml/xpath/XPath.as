/**
	Copyright (c) 2002 Neeld Tanksley.  All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	
	1. Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.
	
	2. Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	
	3. The end-user documentation included with the redistribution, if any, must
	include the following acknowledgment:
	
	This product includes software developed by Neeld Tanksley
	(http://xfactorstudio.com)."
	
	Alternately, this acknowledgment may appear in the software itself, if and
	wherever such third-party acknowledgments normally appear.
	
	4. The name Neeld Tanksley must not be used to endorse or promote products 
	derived from this software without prior written permission. For written 
	permission, please contact neeld@xfactorstudio.com.
	
	THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
	FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL NEELD TANKSLEY
	BE LIABLE FOR ANY DIRECT, INDIRECT,	INCIDENTAL, SPECIAL, EXEMPLARY, OR 
	CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
	GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
	HOWEVER CAUSED AND ON ANY THEORY OF	LIABILITY, WHETHER IN CONTRACT, STRICT 
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT 
	OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
**/
/**
	Some of the comments included in this file were taken from the XML Path 
	Language (XPath) Version 1.0 W3C Recommendation 16 November 1999
	"Copyright © World Wide Web Consortium, (Massachusetts Institute of Technology, 
	European Research Consortium for Informatics and Mathematics, Keio University). 
	All Rights Reserved. http://www.w3.org/Consortium/Legal/2002/copyright-documents-20021231" 

**/
import com.xfactorstudio.xml.xpath.*;
import com.xfactorstudio.xml.xpath.types.*;

class com.xfactorstudio.xml.xpath.XPath{
	
	private  function XPath(){

	}

	/**
		 selectNodes
	
		 returns an array of nodes that match the given XPath 
		 expression using the the XMLNode (context) as the 
		 starting context for the expression.
	
		 This is the description
		 @param (XMLNode)context
		 @param (String)XPath expression
		 @return (Array) matching nodes
	**/
	static function selectNodes(context:XMLNode,path:String):Array{
		var q:Query = new Query(path);
		return q.execute([context]);
	}
	
	static function selectSingleNode(context:XMLNode,path:String):XMLNode{
		return XPath.selectNodes(context,path)[0];
	}
	
	static function selectNodesAsString(context:XMLNode,path:String):Array{
		var nl = XPath.selectNodes(context,path);
		for(var i=0;i<nl.length;i++){
			nl[i] = XPathAxes.stringValue(nl[i])[0];
		}
		return nl;
	}
	
	static function selectNodesAsNumber(context:XMLNode,path:String):Array{
		var nl = XPath.selectNodes(context,path);
		for(var i=0;i<nl.length;i++){
			nl[i] = XPathFunctions.toNumber(XPathAxes.stringValue(nl[i])[0]);
		}
		return nl;
	}
	
	static function selectNodesAsBoolean(context:XMLNode,path:String):Array{
		var nl = XPath.selectNodes(context,path);
		for(var i=0;i<nl.length;i++){
			var strVal = XPathAxes.stringValue(nl[i])[0];
			nl[i] = (strVal.toUpperCase() == "TRUE" || Number(strVal) == 1)? true : false;
		}
		return nl;
	}
	
	static function getNamedNodes(axis:Array,name:String):Array{
		var nodeArray = new Array();

		switch(name){
			case "*":
				for(var i=0;i<axis.length;i++){
					if(axis[i].nodeType == 1){
						nodeArray.push(axis[i]);
					}
				}
				break;
			case "text()":
				for(var i=0;i<axis.length;i++){
					if(axis[i].nodeType == 3){
						nodeArray.push(axis[i]);
					}
				}
				break;
			case "node()":
				for(var i=0;i<axis.length;i++){
					nodeArray.push(axis[i]);
				}
				break;
			default:
				if(name.indexOf(":*")!=-1){
					var prefix = name.split(":*")[0];
					for(var i=0;i<axis.length;i++){
						if(XPath.prefix(axis[i]) == prefix){
							nodeArray.push(axis[i]);
						}
					}
				}else if(name.indexOf("*:")!=-1){
					var localPart = name.split("*:")[1];
					for(var i=0;i<axis.length;i++){
						if(XPath.localName(axis[i]) == localPart){
							nodeArray.push(axis[i]);
						}
					}
				}else{
					for(var i=0;i<axis.length;i++){
						if(axis[i].nodeName == name){
							nodeArray.push(axis[i]);
						}
					}
				}
				break;
		}
		return nodeArray;
	}
	
	/**
		 getDocumentElement
	
		 Returns the root element of the document.
		 
		 @param (XMLNode)context 
		 @return (XMLNode) the document element
	**/
	static function getDocumentElement(context:XMLNode):XMLNode{
		//get XML object
		while(context.parentNode != null){
			context = context.parentNode;
		}
		if(context.firstChild.nodeName == null){
			//handle the Flash weirdness that allows
			//multiple root nodes (usualy whitespace)
			return context.firstChild.nextSibling;
		}else{
			return context.firstChild;
		}
		return context;
	}
	
	static function getChildIndex(kid:XMLNode):Number{
		var bros = kid.parentNode.childNodes;
		var sibCount = 0;
		for(var i=0;i<bros.length;i++){
			if(bros[i].nodeName == kid.nodeName){
				sibCount++;
			}
			if(bros[i] === kid){
				return sibCount;
			}
		}
		return 0;
	}
	
	static function prefix(node:XMLNode):String{
		return (node.nodeName.indexOf(":") != -1)? node.nodeName.split(":")[0] : null;
	}
	
	static function localName(node:XMLNode):String{
		return (node.nodeName.indexOf(":") != -1)? node.nodeName.split(":")[1] : node.nodeName;
	}
	
	static function getXPath(n:XMLNode,attributeName:String):String{
		var nl = XPathAxes.ancestorOrSelf(n);
		var ret = "";
		for(var i=1;i<nl.length;i++){
			switch(nl[i].nodeType){
				case 1:
					ret += "/" + nl[i].nodeName;
					break;
				case 3:
					ret += "/text()";
					break;
			}
		}
		if(attributeName!=null){
			ret += "/@" + attributeName;
		}
		return ret;
	}
	
	static function getAbsoluteXPath(n:XMLNode,attributeName:String):String{
		var nl = XPathAxes.ancestorOrSelf(n);
		var ret = "";
		for(var i=1;i<nl.length;i++){
			switch(nl[i].nodeType){
				case 1:
					ret += "/" + nl[i].nodeName+ "[" + Predicate.getIndexOfType(nl[i]) + "]";
					break;
				case 3:
					ret += "/text()";
					break;
			}
		}
		if(attributeName!=null){
			ret += "/@" + attributeName;
		}
		return ret;
	}


}






