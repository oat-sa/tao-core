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

import com.xfactorstudio.xml.xpath.XPath;
import com.xfactorstudio.xml.xpath.XPathAxes;

class com.xfactorstudio.xml.xpath.XPathFunctions{
	public static var Tokens = new Object();
	public static var Names = []
	public static var Functions = [];
	private static var defaultFunctionsInited = false;


	private function XPathFunctions(){
	
	}
	
	public static function registerFunction(id,func){
		if(XPathFunctions.Tokens[id] == null){
			XPathFunctions.Functions.push(func);
			XPathFunctions.Tokens[id] = XPathFunctions.Functions.length-1;
		}else{
			throw new Error("XPath Error: The function identifier (" + XPathFunctions.Tokens[id] + ") is already in use");
		}
	}
	
	public static function registerDefaultFunctions(){
		if(!XPathFunctions.defaultFunctionsInited){
			XPathFunctions.registerFunction("last",XPathFunctions.last);
			XPathFunctions.registerFunction("position",XPathFunctions.position);
			XPathFunctions.registerFunction("count",XPathFunctions.count);
			XPathFunctions.registerFunction("id",XPathFunctions.id);
			XPathFunctions.registerFunction("name",XPathFunctions.name);
			XPathFunctions.registerFunction("string",XPathFunctions.string);
			XPathFunctions.registerFunction("concat",XPathFunctions.concat);
			XPathFunctions.registerFunction("starts-with",XPathFunctions.startsWith);
			XPathFunctions.registerFunction("contains",XPathFunctions.contains);
			XPathFunctions.registerFunction("substring-before",XPathFunctions.substringBefore);
			XPathFunctions.registerFunction("substring-after",XPathFunctions.substringAfter);
			XPathFunctions.registerFunction("substring",XPathFunctions.substring);
			XPathFunctions.registerFunction("string-length",XPathFunctions.stringLength);
			XPathFunctions.registerFunction("normalize-space",XPathFunctions.normalizeSpace);
			XPathFunctions.registerFunction("translate",XPathFunctions.translate);
			XPathFunctions.registerFunction("boolean",XPathFunctions.boolean);
			XPathFunctions.registerFunction("not",XPathFunctions.Not);
			XPathFunctions.registerFunction("true",XPathFunctions.True);
			XPathFunctions.registerFunction("false",XPathFunctions.False);
			XPathFunctions.registerFunction("lang",XPathFunctions.lang);
			XPathFunctions.registerFunction("number",XPathFunctions.number);
			XPathFunctions.registerFunction("sum",XPathFunctions.sum);
			XPathFunctions.registerFunction("floor",XPathFunctions.floor);
			XPathFunctions.registerFunction("ceiling",XPathFunctions.ceiling);
			XPathFunctions.registerFunction("round",XPathFunctions.round);
			XPathFunctions.registerFunction("local-name",XPathFunctions.localName);
			XPathFunctions.registerFunction("namespaceURI",XPathFunctions.namespaceURI);
			XPathFunctions.defaultFunctionsInited = true;
		}
	}

	public static function getFunction(i:Number){
		return XPathFunctions.Functions[i];
	}
	
	
	
	//////////////////////
	// Node Set Functions
	//////////////////////
	static function last(args:Array,context:XMLNode,nodeSet:Array){
		return Number(nodeSet.length);
	}
	static function position(args:Array,context:XMLNode,nodeSet:Array){
		return XPath.getChildIndex(context);
	}
	static function count(args:Array,context:XMLNode,nodeSet:Array){
		return args[0].length;
	}
	static function id(args:Array,context:XMLNode,nodeSet:Array){
		//not implemented
	}
	static function name(args:Array,context:XMLNode,nodeSet:Array){
		var targetNode = (args.length == 0)? context : args[0][0];
		return targetNode.nodeName;
	}
	static function localName(args:Array,context:XMLNode,nodeSet:Array){
		var targetNode = (args.length == 0)? context : args[0][0];
		var p = targetNode.nodeName.split(":");
		return (p.length>1)?  p[1] : p[0];
	}
	static function namespaceURI(args:Array,context:XMLNode,nodeSet:Array){
		var targetNode = (args.length == 0)? context : args[0][0];
		var prefix = targetNode.nodeName.split(":")[0];
		var inScopeNS = XPathAxes.namespace(targetNode);
		for(var i=0;i<inScopeNS.length;i++){
			if(XPathFunctions.localName([[inScopeNS[i]]]) == prefix){
				return inScopeNS[i].nodeValue;
			}
		}
		
	}
	//////////////////////
	// String Functions
	//////////////////////
	static function toString(args){
		if(args instanceof Array){
			args = XPathAxes.stringValue(args[0]).join("");
		}
		return String(args);
	}
	static function string(args:Array,context:XMLNode,nodeSet:Array){
		return XPathFunctions.toString(args[0]);
	}
	
	static function concat(args:Array,context:XMLNode,nodeSet:Array){
		for(var i=0;i<args.length;i++){
			args[i] = XPathFunctions.toString(args[i]);
		}
		return args.join("");
	}
	static function startsWith(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toString(args[0]);
		args[1] = XPathFunctions.toString(args[1]);
		return (args[0].substr(0,args[1].length) == args[1])? true : false;
	}
	static function contains(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toString(args[0]);
		args[1] = XPathFunctions.toString(args[1]);
		return (args[0].indexOf(args[1]) != -1)? true : false;
	}
	static function substringBefore(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toString(args[0]);
		args[1] = XPathFunctions.toString(args[1]);
		return args[0].substr(0,args[0].indexOf(args[1]));
	}
	static function substringAfter(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toString(args[0]);
		args[1] = XPathFunctions.toString(args[1]);
		return args[0].substr(args[0].indexOf(args[1])+args[1].length,args[0].length);
	}
	static function substring(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toString(args[0]);
		args[1] = XPathFunctions.toString(args[1]);
		return args[0].substr(args[1]-1,Math.min(args[2],args[0].length));
	}
	static function stringLength(args:Array,context:XMLNode,nodeSet:Array){
		args = XPathFunctions.toString(args[0]);
		return (args != null)? args.length : XPathAxes.stringValue(context).length;
	}
	static function normalizeSpace(args:Array,context:XMLNode,nodeSet:Array){
		args = XPathFunctions.toString(args[0]);
		var i,s
		for(i=0;i<args.length;i++){
			if(args.charCodeAt(i) < 33){
				s=i;
				while(args.charCodeAt(s) < 33){
					s++;
				}
				if(s > i+1){
					args = args.split(args.substr(i,s-i)).join(" ");
				}
			}
		}
		//leading
		i=0;
		while(args.charCodeAt(i) < 33){
			i++;
		}
		args = args.substr(i,args.length);
		//trailing
		i=args.length-1;
		while(args.charCodeAt(i) < 33){
			i--;
		}
		args = args.substr(0,i+1);
		return args;
	}
	
	//THIS IS NOT CORRECT READ DOC AND FIX
	static function translate(args:Array,context:XMLNode,nodeSet:Array){
		var inStr = XPathFunctions.toString(args[0]);
		var arg1 = XPathFunctions.toString(args[1]);
		var arg2 = XPathFunctions.toString(args[2]);
		return inStr.split(arg1).join(arg2);
	}
	//}/
		
		
		
	//////////////////////
	// Number Functions
	//////////////////////
	static function toNumber(args:Array){
		//return XPathFunctions.number([args]);
		if(args instanceof Array){
			args = XPathFunctions.toString(args);
		}
		switch(typeof(args)){
			case "string":
				return Number(args);
			case "boolean":
				return (args)? 1 : 0;
			default:
				return Number(args.toString());
		}
	}
	static function number(args:Array,context:XMLNode,nodeSet:Array){
		return XPathFunctions.toNumber(args[0]);
	}
	static function sum(args:Array,context:XMLNode,nodeSet:Array){
		var total = 0;
		for(var i=0;i<args[0].length;i++){
			total += Number(XPathAxes.stringValue(args[0][i])[0]);
		}
		return total;
	}
	static function floor(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toNumber(args[0]);
		return Math.floor(Number(args[0]));
	}
	static function ceiling(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toNumber(args[0]);
		return Math.ceil(Number(args[0]));
	}
	static function round(args:Array,context:XMLNode,nodeSet:Array){
		args[0] = XPathFunctions.toNumber(args[0]);
		return Math.round(Number(args[0]));
	}
	
	
	//////////////////////
	// Boolean Functions
	//////////////////////
	static function toBoolean(args){
		return XPathFunctions.boolean([args]);
	}
	
	static function boolean(args:Array,context:XMLNode,nodeSet:Array){
		args = args[0];	
		if(args instanceof Array){
			return (args.length > 0)? true : false;
		}
		switch(typeof(args)){
			case "number":
				return (args != 0)? true : false;
			case "string":
				return (args.length > 0)? true : false;
			default:
				return args;
		}
	}
	static function Not(args:Array,context:XMLNode,nodeSet:Array){
		args = args[0];
		if(args == "false" || args == false){
			return true;
		}else{
			return false;
		}
	}
	static function True(args:Array,context:XMLNode,nodeSet:Array){
		return true;
	}
	static function False(args:Array,context:XMLNode,nodeSet:Array){
		return false;
	}
	static function lang(args:Array,context:XMLNode,nodeSet:Array){
		return (XPath.getNamedNodes(XPathAxes.attribute(context),"*:lang")[0].toString() == args[0].toString())? true : false;
	}
}
