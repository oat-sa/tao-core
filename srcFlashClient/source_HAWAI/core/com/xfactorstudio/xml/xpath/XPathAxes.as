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
	
	"This product includes software developed by Neeld Tanksley
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
import com.xfactorstudio.xml.xpath.XPathUtils;


class com.xfactorstudio.xml.xpath.XPathAxes{
	
	private function XPathAxes(){

	}
	
	/////////////////////////////////
	//
	//     XPath Axes
	//
	//     These are static methods that are used to select an array 
	//     of nodes. If you want to have direct access to an axis (as 
	//     opposed to using myNode.selectNodes(<XPathQuery>), then I 
	//     recomend using the XMLNode prototypes that are provided as 
	//     wrapper to these functions.
	//
	////////////////////////////////
	/**
		 ancestor
	
		 the ancestor axis contains the ancestors 
		 of the context node; the ancestors of the 
		 context node consist of the parent of 
		 context node and the parent's parent and 
		 so on; thus, the ancestor axis will always 
		 include the root node, unless the context 
		 node is the root node
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function ancestor(contextNode:XMLNode):Array{
		var nodeArray = new Array();
		var currNode = contextNode;
		while((currNode = currNode.parentNode) != null){
			nodeArray.push(currNode);
		}
		nodeArray.reverse();
		return nodeArray;
	}
	
	/**
		 namespace
	
		 the namespace axis contains the namespace nodes 
		 of the context node; the axis will be empty 
		 unless the context node is an element
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function namespace(contextNode:XMLNode):Array{
		trace("namspace")
		var newNamespace,nodeArray,NSExistsAt
		var parent = contextNode.parentNode;
		//get namespace from parent
		if(parent != null){
			nodeArray = XPathAxes.namespace(parent);
		}else{
			nodeArray = new Array();
			// this is root, add the default xml namespace
			var defaultNS = {	parentNode:contextNode,
								nodeName:"xmlns:xml",
								nodeType:5,
								nodeValue:"http://www.w3.org/XML/1998/namespace",
								toString:function(){
									return this.nodeValue;
								}
							}
			nodeArray.push(defaultNS);
	
		}
		
		//namespace is a new type and should not be the same as attribute (5)
		for(var i in contextNode.attributes){
			if(i.substr(0,5) == "xmlns"){
				newNamespace = {
					parentNode:contextNode,
					nodeName:i,
					nodeType:5,
					nodeValue:contextNode.attributes[i],
					toString:function(){
						return this.nodeValue;
					}
				}
				//find preexisting namespace declaration
				NSExistsAt = -1;
				for(var j=0;j<nodeArray.length;j++){
					if(nodeArray[j].nodeName == newNamespace.nodeName){
						NSExistsAt = j;
						break;
					}
				}
				
				if(NSExistsAt != -1){
					if(newNamespace.nodeValue == ""){
						nodeArray.splice(NSExistsAt,1);
					}else{
						nodeArray[NSExistsAt] = newNamespace;
					}
				}else{
					if(newNamespace.nodeValue != ""){
						nodeArray.push(newNamespace);
					}
				}			
			}
		}
		return nodeArray;
	}
	
	
	/**
		 ancestorOrSelf
	
		 the ancestor-or-self axis contains the 
		 context node and the ancestors of the 
		 context node; thus, the ancestor axis 
		 will always include the root node
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function ancestorOrSelf(contextNode:XMLNode):Array{
		var nodeArray = XPathAxes.ancestor(contextNode);
		nodeArray.push(contextNode);
		return nodeArray;
	}
	
	/**
		 attribute
	
		 the attribute axis contains the attributes 
		 of the context node; the axis will be 
		 empty unless the context node is an element
		 
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function attribute(contextNode:XMLNode):Array{
		var nodeArray = new Array();
			for(var i in contextNode.attributes){
				nodeArray.push({
					parentNode:contextNode,
					nodeName:i,
					nodeType:5,
					nodeValue:contextNode.attributes[i],
					toString:function(){
						return this.nodeValue;
					}
				});
			}
	
		return nodeArray;
	}
	
	/**
		 child
		 
		 the child axis contains the children of 
		 the context node
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function child(contextNode:XMLNode):Array{
		return contextNode.childNodes;
	}
	
	/**
		 text
	
		TODO:This needs to be renamed stringValue. It returns the string value 
		of a node. The string value is not the same a the DOM nodeValue. For 
		elements, the string value is the concatanation of all decendent nodes 
		string values. For attributes, the string value is the value of the 
		attribute. For namespace nodes, the string value is the URL bound to 
		the namespace, basically the same as for an attribute, but it is also 
		supposed to resolve relative URLs. 	 
		 
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function stringValue(contextNode:XMLNode):Array{
		var kids = contextNode.childNodes;
		var nodeList = new Array();
		var strText;
		//get text value of an attribute / namespace too at the moment, 
		//although namespaces should have a differnet nodeType they 
		//currently don't
		switch(contextNode.nodeType){
			case 1: //ELEMENT
				strText = new Array();
				for(var i=0;i<contextNode.childNodes.length;i++){
					switch(contextNode.childNodes[i].nodeType){
						case 3: //text node
							if(!XPathUtils.checkEmpty(contextNode.childNodes[i])){
								strText.push(contextNode.childNodes[i].nodeValue);
							}
							break;
						case 1:
							strText.push(XPathAxes.stringValue(contextNode.childNodes[i]));
							break;
					}
				}
				nodeList.push(strText.join(""));
				break;
			case 3://ATTRIBUTE | TEXT NODE
			case 5:
				nodeList.push(contextNode.nodeValue);
				break;
		}
		return nodeList;
	}
	
	/**
		 descendant
	
		 the descendant axis contains the descendants 
		 of the context node; a descendant is a child 
		 or a child of a child and so on; thus the 
		 descendant axis never contains attribute or 
		 namespace nodes
		 
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function descendant(contextNode:XMLNode):Array{
		var currNode = contextNode;
		var nodeArray = new Array();
		for(var i=0;i<currNode.childNodes.length;i++){
			if(currNode.childNodes[i].nodeType == 1){
				nodeArray.push(currNode.childNodes[i]);
					var kids = XPathAxes.descendant(currNode.childNodes[i]);
					for(var j=0;j<kids.length;j++){
						nodeArray.push(kids[j]);
					}
			}
		}
		return nodeArray;
	}
	
	/**
		 descendantOrSelf
	
		 the ancestor-or-self axis contains the context 
		 node and the ancestors of the context node; 
		 thus, the ancestor axis will always include 
		 the root node
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function descendantOrSelf(contextNode:XMLNode):Array{
		var nodeArray = XPathAxes.descendant(contextNode);
		nodeArray.splice(0,0,contextNode);
		return nodeArray;
	}
	
	/**
		 following
	
		 the following axis contains all nodes in 
		 the same document as the context node 
		 that are after the context node in 
		 document order, excluding any descendants 
		 and excluding attribute nodes and 
		 namespace nodes
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function following(contextNode:XMLNode):Array{
		var tmpNodeArray = new Array();
		var folSibs = XPathAxes.followingSibling(contextNode);
		var ancestorNodes = XPathAxes.ancestor(contextNode);
		for(var i=0;i<folSibs.length;i++){
			var folSibDec = XPathAxes.descendantOrSelf(folSibs[i]);
			for(var j=0;j<folSibDec.length;j++){
				tmpNodeArray.push(folSibDec[j]);
			}
		}
		for(var i=0;i<ancestorNodes.length;i++){
			var ancFolSibs = XPathAxes.followingSibling(ancestorNodes[i]);
			for(var j=0;j<ancFolSibs.length;j++){
				var ancFolSibsDec = XPathAxes.descendantOrSelf(ancFolSibs[j]);
				for(var k=0;k<ancFolSibsDec.length;k++){
					tmpNodeArray.push(ancFolSibsDec[k]);
				}
			}
		}
		return tmpNodeArray;
	}
	
	/**
		 followingSibling
	
		 the following-sibling axis contains all the 
		 following siblings of the context node; if 
		 the context node is an attribute node or 
		 namespace node, the following-sibling axis 
		 is empty
	
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function followingSibling(contextNode:XMLNode):Array{
		var tmpNodeArray = new Array();
		var currNode = contextNode;
		while((currNode = currNode.nextSibling) != null){
			tmpNodeArray.push(currNode);
		}
		return tmpNodeArray;
	}
	
	/**
		 parent
	
		 the parent axis contains the parent of 
		 the context node, if there is one
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function parent(contextNode:XMLNode):Array{
		return new Array(contextNode.parentNode);
	}
	
	/**
		 preceding
	
		 the preceding axis contains all nodes in the 
		 same document as the context node that are 
		 before the context node in document order, 
		 excluding any ancestors and excluding 
		 attribute nodes and namespace nodes
	
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function preceding(contextNode:XMLNode):Array{
		var tmpNodeArray = new Array();
		var preSibs = XPathAxes.precedingSibling(contextNode);
		var ancestorNodes = XPathAxes.ancestor(contextNode);
		for(var i=0;i<ancestorNodes.length;i++){
			var ancPreSibs = XPathAxes.precedingSibling(ancestorNodes[i]);
			for(var j=0;j<ancPreSibs.length;j++){
				var ancPreSibsDec = XPathAxes.descendantOrSelf(ancPreSibs[j]);
				for(var k=0;k<ancPreSibsDec.length;k++){
					tmpNodeArray.push(ancPreSibsDec[k]);
				}
			}
		}
		for(var i=0;i<preSibs.length;i++){
			var preSibDec = XPathAxes.descendantOrSelf(preSibs[i]);
			for(var j=0;j<preSibDec.length;j++){
				tmpNodeArray.push(preSibDec[j]);
			}
		}
		return tmpNodeArray;
	}
	
	/**
		 precedingSibling
	
		 the preceding-sibling axis contains all the preceding 
		 siblings of the context node; if the context node 
		 is an attribute node or namespace node, the 
		 preceding-sibling axis is empty
	
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function precedingSibling(contextNode:XMLNode):Array{
		var tmpNodeArray = new Array();
		var currNode = contextNode;
		while((currNode = currNode.previousSibling) != null){
			tmpNodeArray.push(currNode);
		}
		tmpNodeArray.reverse();
		return tmpNodeArray;
	}
	
	/**
		 self
	
		 the self axis contains just the context 
		 node itself
	
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function self(contextNode:XMLNode):Array{
		return [contextNode];
	}
	
	/**
		 root
	
		 This is not officially an axis, but is used internally 
		 to return an "axis" proxy for the XML document array 
		 containing one element, the first node in the XML 
		 document.
		 
		 @param (XMLNode)contextNode 
		 @return (Array) and array containing 
				matching nodes in document order
	**/
	static function root(contextNode:XMLNode):Array{
		while(contextNode.parentNode != null){
			contextNode = contextNode.parentNode;
		}
		return [contextNode.firstChild];
	}
}
