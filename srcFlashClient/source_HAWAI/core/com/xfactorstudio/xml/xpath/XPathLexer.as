/**
   ActionScript Class File -- Created with SAPIEN Technologies PrimalScript 3.1
   
   @class  
   @package
   @author neeld
   @codehint 
   @example 
   @tooltip 
*/
import com.xfactorstudio.xml.xpath.Axes;
import com.xfactorstudio.xml.xpath.XPathAxisNames;
import com.xfactorstudio.xml.xpath.XPathFunctions;
import com.xfactorstudio.xml.xpath.XPathPredicateOperator;
import com.xfactorstudio.xml.xpath.Tokenizer;
import com.xfactorstudio.xml.xpath.TokenTypes;

class com.xfactorstudio.xml.xpath.XPathLexer {
	private var functionNames:Object;
	private var axisNames:XPathAxisNames;
	private var operatorNames:XPathPredicateOperator;
	public var path:String;
	public var currPos;
	public var buff:String;
	public var lastTokenType:Number = -1;
	public var lastToken;
	public var inFunction:Number = 0;
	public var inGroup:Number = 0;
	public var inPredicate:Number = 0;
	public var handler:Object;
	public var tokenizer:Tokenizer;
	private var c:String;
	private var lastOpenPeran:Number;
	
	public static var AXIS 			= 0;
	public static var PREDICATESTART 	= 1;
	public static var PREDICATEEND 		= 2;
	public static var IDENTIFIER 		= 3;
	public static var FUNCTIONSTART 	= 4;
	public static var FUNCTIONEND 		= 5;
	public static var OPERATOR 		= 6;
	public static var COMMA 		= 7;
	public static var GROUPSTART 		= 8;
	public static var GROUPEND 		= 9;
	public static var NUMBER 		= 10;
	public static var LITTERAL		= 11;
	
	public function XPathLexer(path:String){
		functionNames = XPathFunctions.Tokens;
		axisNames = new XPathAxisNames();
		operatorNames = new XPathPredicateOperator();
	}
	
	public function parse(path){
	
		this.path = path;
		tokenizer = new Tokenizer(this.path);
		var t;
		while(tokenizer.hasMoreChars()){
			t = tokenizer.nextToken();
			switch(t.type){
				case TokenTypes.SLASH:
	    			onSlash(t);
	    			break;
	    		case TokenTypes.IDENTIFIER:
	    			onIdentifier(t);
	    			break;
	    		case TokenTypes.AT:
	    			onAt();
	    			break;
	    		case TokenTypes.AND:
	    		case TokenTypes.DIV:
				case TokenTypes.EQUALS:
				case TokenTypes.GREATER_THAN:
				case TokenTypes.GREATER_THAN_EQUALS:
				case TokenTypes.LESS_THAN:
				case TokenTypes.LESS_THAN_EQUALS:
				case TokenTypes.MINUS:
				case TokenTypes.MOD:
				case TokenTypes.NOT:
				case TokenTypes.NOT_EQUALS:
				case TokenTypes.OR:
				case TokenTypes.PIPE:
				case TokenTypes.PLUS:
					onOperator(t);
	    			break;
	    		case TokenTypes.INTEGER:
	    		case TokenTypes.DOUBLE:
	    			onNumber(t);
	    			break;
	    		case TokenTypes.DOT:
	    			onDot();
	    			break;
	    		case TokenTypes.DOT_DOT:
	    			onDoubleDot();
	    			break;
	    		case TokenTypes.COLON:
	    		case TokenTypes.DOUBLE_COLON:
	    			break;
	    		case TokenTypes.DOUBLE_SLASH:
	    			onDoubleSlash(t);
	    			break;
	    		case TokenTypes.EOF:
	    			break;
	    		case TokenTypes.LEFT_BRACKET:
	    			onLeftBracket(t);
	    			break;
	    		case TokenTypes.LEFT_PAREN:
	    			onLeftParen();
	    			break;
	    		case TokenTypes.LITERAL:
	    			onLitteral(t);
	    			break;
	    		case TokenTypes.RIGHT_BRACKET:
	    			onRightBracket();
	    			break;
	    		case TokenTypes.RIGHT_PAREN:
	    			onRightParen();
	    			break;
	    		
	    		case TokenTypes.STAR:
				if(tokenizer.LA(1)==":"){
					//This is a namespace wildcard
					t.type = TokenTypes.IDENTIFIER;
					t.text += tokenizer.nextToken().text;
					t.text += tokenizer.nextToken().text;
					onIdentifier(t);
					break;
				}
	    			onStar();
	    			break;
	    		case TokenTypes.DOLLAR: //not implemented
	    		case TokenTypes.COMMA: //not implemented
	    		case TokenTypes.SKIP: //not implemented
	    		
	    			break;
	    	}
    		this.lastToken = t;
		}
	}



public function onSlash(t){
	switch(this.lastTokenType){
		case XPathLexer.IDENTIFIER:
		case XPathLexer.PREDICATEEND:
		case XPathLexer.AXIS:
			this.lastTokenType = XPathLexer.OPERATOR;
			break;
	
		default:
			handler.onAxis(Axes.ROOT);
			this.lastTokenType = XPathLexer.AXIS;
			break;
	}
}

public function onStar(){
	switch(this.lastToken.type){
		case TokenTypes.AT:
		case TokenTypes.DOUBLE_COLON:
		case TokenTypes.LEFT_PAREN:
		case TokenTypes.LEFT_BRACKET:
		case TokenTypes.AND:
   		case TokenTypes.DIV:
		case TokenTypes.EQUALS:
		case TokenTypes.GREATER_THAN:
		case TokenTypes.GREATER_THAN_EQUALS:
		case TokenTypes.LESS_THAN:
		case TokenTypes.LESS_THAN_EQUALS:
		case TokenTypes.MINUS:
		case TokenTypes.MOD:
		case TokenTypes.NOT:
		case TokenTypes.NOT_EQUALS:
		case TokenTypes.OR:
		case TokenTypes.PIPE:
		case TokenTypes.PLUS:
			handler.onOperator("*");
			break;
		default:
			handler.onIdentifier("*");
			break;
		
	}
}

public function onAt(){
	handler.onAxis(Axes.ATTRIBUTE);
	this.lastTokenType = XPathLexer.AXIS;
}

public function onDot(){
	handler.onAxis(Axes.SELF);
	this.lastTokenType = XPathLexer.AXIS;
}

public function onDoubleDot(){
	handler.onAxis(Axes.PARENT);
	this.lastTokenType = XPathLexer.AXIS;
}

public function onDoubleSlash(t){
	handler.onAxis(Axes.DECENDANT_OR_SELF);
	this.lastTokenType = XPathLexer.AXIS;
}

public function onNumber(t){
	handler.onNumber(Number(t.text));
	this.lastTokenType = XPathLexer.NUMBER;
}

public function onIdentifier(t){
	var tokenText = t.text;
	 if (tokenizer.LA(1) == '(' ){ // node/text identifier | function
	 	switch(t.text){
	 		case "node":
	 		case "text":
		 		handler.onIdentifier(t.text+"()");
			 	this.lastTokenType = XPathLexer.IDENTIFIER;
			 	tokenizer.nextToken();
			 	tokenizer.nextToken();
		 		return;
		 		break;
	 		default:
		 		handler.onFunctionStart(functionNames[t.text]);
			 	this.lastTokenType = XPathLexer.FUNCTIONSTART;
			 	this.lastOpenPeran = XPathLexer.FUNCTIONSTART;
			 	tokenizer.nextToken();
	 		return;
	 		break;
	 	}
    }else if(tokenizer.LA(1) == ':' ){
    	if (tokenizer.LA(2) == ':' ){//AXIS
    		tokenText += tokenizer.nextToken().text
    		handler.onAxis(axisNames[tokenText]);
			this.lastTokenType = XPathLexer.AXIS;
			return;
    	}else{//QName
    		tokenText += tokenizer.nextToken().text;
    		tokenText += tokenizer.nextToken().text;
    	}
    }
    
	switch(this.lastTokenType){
		case XPathLexer.AXIS:
		case XPathLexer.PREDICATESTART:
		case XPathLexer.GROUPSTART:
		case XPathLexer.FUNCTIONSTART:
		case XPathLexer.OPERATOR:
		case -1:
			handler.onIdentifier(tokenText);
			this.lastTokenType = XPathLexer.IDENTIFIER;
			break;
		default:
			break;
	}
}

public function onRightParen(){
	switch(this.lastOpenPeran){
		case XPathLexer.GROUPSTART:
			handler.onGroupEnd();
			this.lastTokenType = XPathLexer.GROUPEND;
			break;		
		case XPathLexer.FUNCTIONSTART:
			handler.onFunctionEnd();
			this.lastTokenType = XPathLexer.FUNCTIONEND;
			break;
	}
}

public function onLeftParen(){
	handler.onGroupStart();
	this.lastTokenType = XPathLexer.GROUPSTART;
	this.lastOpenPeran = XPathLexer.GROUPSTART;
}

public function onLitteral(t){
	handler.onLitteral(t.text);
	this.lastTokenType = XPathLexer.LITTERAL;
}

public function onLeftBracket(t){
	handler.onPredicateStart()
	this.lastTokenType = XPathLexer.PREDICATESTART;
}

public function onRightBracket(t){
	handler.onPredicateEnd()
	this.lastTokenType = XPathLexer.PREDICATEEND;
}

public function onOperator(t){
	handler.onOperator(operatorNames[t.text]);
	this.lastTokenType = XPathLexer.OPERATOR;

}


}
