
class com.xfactorstudio.xml.xpath.TokenTypes{
    static var LEFT_PAREN:Number  = 1;
    static var RIGHT_PAREN:Number = 2;

    static var LEFT_BRACKET:Number  = 3;
    static var RIGHT_BRACKET:Number = 4;

    static var PLUS:Number = 5;
    static var MINUS:Number = 6;
    static var LESS_THAN:Number = 7;
    static var LESS_THAN_EQUALS:Number = 8;
    static var GREATER_THAN:Number = 9;
    static var GREATER_THAN_EQUALS:Number = 10;

    static var SLASH:Number = 11;
    static var DOUBLE_SLASH:Number = 12;
    static var DOT:Number = 13;
    static var DOT_DOT:Number = 14;

    static var IDENTIFIER:Number = 15;

    static var AT:Number = 16;
    static var PIPE:Number = 17;
    static var COLON:Number = 18;
    static var DOUBLE_COLON:Number = 19;
    static var STAR:Number = 20;

    static var EQUALS:Number = 21;
    static var NOT_EQUALS:Number = 22;
    static var NOT:Number = 23;

    static var DIV:Number = 24;
    static var MOD:Number = 25;

    static var DOLLAR:Number = 26;

    static var LITERAL:Number = 27;

    static var AND:Number = 28;
    static var OR:Number = 29;

    static var INTEGER:Number = 30;
    static var DOUBLE:Number = 31;

    static var COMMA:Number = 32;

    static var SKIP:Number = -2;
    static var EOF:Number = -1;
    
    public static function getName(i:Number):String{
    	switch(i){
    		case TokenTypes.AND:
    			return "AND";
    			break;
    		case TokenTypes.AT:
    			return "AT";
    			break;
    		case TokenTypes.COLON:
    			return "COLON";
    			break;
    		case TokenTypes.COMMA:
    			return "COMMA";
    			break;
    		case TokenTypes.DIV:
    			return "DIV";
    			break;
    		case TokenTypes.DOLLAR:
    			return "DOLLAR";
    			break;
    		case TokenTypes.DOT:
    			return "DOT";
    			break;
    		case TokenTypes.DOT_DOT:
    			return "DOT_DOT";
    			break;
    		case TokenTypes.DOUBLE:
    			return "DOUBLE";
    			break;
    		case TokenTypes.DOUBLE_COLON:
    			return "DOUBLE_COLON";
    			break;
    		case TokenTypes.DOUBLE_SLASH:
    			return "DOUBLE_SLASH";
    			break;
    		case TokenTypes.EOF:
    			return "EOF";
    			break;
    		case TokenTypes.EQUALS:
    			return "EQUALS";
    			break;
    		case TokenTypes.GREATER_THAN:
    			return "GREATER_THAN";
    			break;
    		case TokenTypes.GREATER_THAN_EQUALS:
    			return "GREATER_THAN_EQUALS";
    			break;
    		case TokenTypes.IDENTIFIER:
    			return "IDENTIFIER";
    			break;
			case TokenTypes.INTEGER:
    			return "INTEGER";
    			break;
    		case TokenTypes.LEFT_BRACKET:
    			return "LEFT_BRACKET";
    			break;
    		case TokenTypes.LEFT_PAREN:
    			return "LEFT_PAREN";
    			break;
    		case TokenTypes.LESS_THAN:
    			return "LESS_THAN";
    			break;
    		case TokenTypes.LESS_THAN_EQUALS:
    			return "LESS_THAN_EQUALS";
    			break;
    		case TokenTypes.LITERAL:
    			return "LITERAL";
    			break;
			case TokenTypes.MINUS:
    			return "MINUS";
    			break;
    		case TokenTypes.MOD:
    			return "MOD";
    			break;
    		case TokenTypes.NOT:
    			return "NOT";
    			break;
    		case TokenTypes.NOT_EQUALS:
    			return "NOT_EQUALS";
    			break;
    		case TokenTypes.OR:
    			return "OR";
    			break;
    		case TokenTypes.PIPE:
    			return "PIPE";
    			break;
    		case TokenTypes.PLUS:
    			return "PLUS";
    			break;
    		case TokenTypes.RIGHT_BRACKET:
    			return "RIGHT_BRACKET";
    			break;
    		case TokenTypes.RIGHT_PAREN:
    			return "RIGHT_PAREN";
    			break;
    		case TokenTypes.SKIP:
    			return "SKIP";
    			break;
    		case TokenTypes.SLASH:
    			return "SLASH";
    			break;
    		case TokenTypes.STAR:
    			return "STAR";
    			break;
    	}
    }
}
