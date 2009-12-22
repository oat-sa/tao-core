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

class com.xfactorstudio.xml.xpath.XPath2Functions{
	private static var XPath2FunctionsInited = false;
	
	public static function registerXPath2Functions(){
		if(!XPathFunctions.XSLFunctionsInited){
			XPathFunctions.registerFunction("abs",XPath2Functions.abs);
			XPathFunctions.registerFunction("round-half-to-even",XPath2Functions.roundHalfToRven);
			//XPathFunctions.registerFunction("codepoints-to-string",XPath2Functions.codepoints-to-string);
			//XPathFunctions.registerFunction("depoints-to-string",XPath2Functions.depoints-to-string);
			XPathFunctions.registerFunction("string-join",XPath2Functions.string-join);
			XPathFunctions.registerFunction("upper-case",XPath2Functions.upper-case);
			XPathFunctions.registerFunction("lower-case",XPath2Functions.lower-case);
			XPathFunctions.registerFunction("ends-with",XPath2Functions.ends-with);
			XPathFunctions.registerFunction("replace",XPath2Functions.replace);
			XPathFunctions.registerFunction("tokenize",XPath2Functions.tokenize);
			//XPathFunctions.registerFunction("years-from-duration",XPath2Functions.years-from-duration);
			//XPathFunctions.registerFunction("months-from-duration",XPath2Functions.months-from-duration);
			//XPathFunctions.registerFunction("days-from-duration",XPath2Functions.days-from-duration);
			//XPathFunctions.registerFunction("hours-from-duration",XPath2Functions.hours-from-duration);
			//XPathFunctions.registerFunction("minutes-from-duration",XPath2Functions.minutes-from-duration);
			//XPathFunctions.registerFunction("seconds-from-duration",XPath2Functions.seconds-from-duration);
			XPathFunctions.registerFunction("year-from-dateTime",XPath2Functions.year-from-dateTime);
			XPathFunctions.registerFunction("month-from-dateTime",XPath2Functions.month-from-dateTime);
			XPathFunctions.registerFunction("day-from-dateTime",XPath2Functions.day-from-dateTime);
			XPathFunctions.registerFunction("hours-from-dateTime",XPath2Functions.hours-from-dateTime);
			XPathFunctions.registerFunction("minutes-from-dateTime",XPath2Functions.minutes-from-dateTime);
			XPathFunctions.registerFunction("seconds-from-dateTime",XPath2Functions.seconds-from-dateTime);
			XPathFunctions.registerFunction("timezone-from-dateTime",XPath2Functions.timezone-from-dateTime);
			//XPathFunctions.registerFunction("year-from-date",XPath2Functions.year-from-date);
			//XPathFunctions.registerFunction("month-from-date",XPath2Functions.month-from-date);
			//XPathFunctions.registerFunction("day-from-date",XPath2Functions.day-from-date);
			//XPathFunctions.registerFunction("timezone-from-date",XPath2Functions.timezone-from-date);
			//XPathFunctions.registerFunction("hours-from-time",XPath2Functions.hours-from-time);
			//XPathFunctions.registerFunction("minutes-from-time",XPath2Functions.minutes-from-time);
			//XPathFunctions.registerFunction("seconds-from-time",XPath2Functions.seconds-from-time);
			//XPathFunctions.registerFunction("seconds-from-time",XPath2Functions.seconds-from-time);
			//XPathFunctions.registerFunction("timezone-from-time",XPath2Functions.timezone-from-time);
			//XPathFunctions.registerFunction("adjust-dateTime-to-timezone",XPath2Functions.adjust-dateTime-to-timezone);
			//XPathFunctions.registerFunction("adjust-time-to-timezone",XPath2Functions.adjust-time-to-timezone);
			XPathFunctions.registerFunction("namespace-uri-for-prefix",XPath2Functions.namespace-uri-for-prefix);
			XPathFunctions.registerFunction("in-scope-prefixes",XPath2Functions.in-scope-prefixes);
			XPathFunctions.registerFunction("root",XPath2Functions.root);
			XPathFunctions.registerFunction("index-of",XPath2Functions.index-of);
			XPathFunctions.registerFunction("empty",XPath2Functions.empty);
			XPathFunctions.registerFunction("exists",XPath2Functions.exists);
			XPathFunctions.registerFunction("distinct-values",XPath2Functions.distinct-values);
			XPathFunctions.registerFunction("insert-before",XPath2Functions.insert-before);
			XPathFunctions.registerFunction("remove",XPath2Functions.remove);
			XPathFunctions.registerFunction("reverse",XPath2Functions.reverse);
			XPathFunctions.registerFunction("subsequence",XPath2Functions.subsequence);
			XPathFunctions.registerFunction("unordered",XPath2Functions.unordered);
			XPathFunctions.registerFunction("zero-or-one",XPath2Functions.zero-or-one);
			XPathFunctions.registerFunction("one-or-more",XPath2Functions.one-or-more);
			XPathFunctions.registerFunction("exactly-one",XPath2Functions.exactly-one);
			XPathFunctions.registerFunction("deep-equal",XPath2Functions.deep-equal);
			XPathFunctions.registerFunction("count",XPath2Functions.count);
			XPathFunctions.registerFunction("avg",XPath2Functions.avg);
			XPathFunctions.registerFunction("max",XPath2Functions.max);
			XPathFunctions.registerFunction("min",XPath2Functions.min);
			XPathFunctions.registerFunction("sum",XPath2Functions.sum);
			XPathFunctions.registerFunction("id",XPath2Functions.id);
			XPathFunctions.registerFunction("idref",XPath2Functions.idref);
			XPathFunctions.registerFunction("doc",XPath2Functions.doc);
			XPathFunctions.registerFunction("doc-available",XPath2Functions.doc-available);
			XPathFunctions.registerFunction("collection",XPath2Functions.collection);
			XPathFunctions.registerFunction("current-dateTime",XPath2Functions.current-dateTime);
			XPathFunctions.registerFunction("current-date",XPath2Functions.current-date);
			XPathFunctions.registerFunction("current-time",XPath2Functions.current-time);
			//XPathFunctions.registerFunction("implicit-timezone",XPath2Functions.implicit-timezone);
			//XPathFunctions.registerFunction("default-collation",XPath2Functions.default-collation);
			//XPathFunctions.registerFunction("static-base-uri",XPath2Functions.static-base-uri);


			XPathFunctions.XPath2FunctionsInited = true;
		}
	}

}
