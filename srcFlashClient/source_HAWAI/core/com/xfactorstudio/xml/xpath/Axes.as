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
class com.xfactorstudio.xml.xpath.Axes{
	public static var ROOT 				= 0;
	public static var ANCESTOR 			= 1;
	public static var ANCESTOR_OR_SELF 	= 2;
	public static var ATTRIBUTE 		= 3;
	public static var CHILD 			= 4;
	public static var DECENDANT 		= 5;
	public static var DECENDANT_OR_SELF = 6;
	public static var FOLLOWING 		= 7;
	public static var FOLLOWING_SIBLING = 8;
	public static var PARENT 			= 9;
	public static var PRECEDING 		= 10;
	public static var PRECEDING_SIBLING = 11;
	public static var SELF 				= 12;
	public static var NAMESPACE 		= 13;

	public function Axes(){
		
	}
	
	public static function getName(index:Number):String{
		switch(index){
			case 0:
				return "root";
				break;
			case 1:
				return "ancestor";
				break;
			case 2:
				return "ancestorOrSelf";
				break;
			case 3:
				return "attribute";
				break;
			case 4:
				return "child";
				break;
			case 5:
				return "descendant";
				break;
			case 6:
				return "descendantOrSelf";
				break;
			case 7:
				return "following";
				break;
			case 8:
				return "followingSibling";
				break;
			case 9:
				return "parent";
				break;
			case 10:
				return "preceding";
				break;
			case 11:
				return "precedingSibling";
				break;
			case 12:
				return "self";
				break;
			case 13:
				return "namespace";
				break;
		}
	}
}