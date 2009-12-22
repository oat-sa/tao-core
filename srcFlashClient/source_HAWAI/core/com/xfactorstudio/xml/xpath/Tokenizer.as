import com.xfactorstudio.xml.xpath.Token;
import com.xfactorstudio.xml.xpath.TokenTypes;

class com.xfactorstudio.xml.xpath.Tokenizer{
    private var xpath:String;
    private var currentPosition:Number;
    private var endPosition:Number;

    private var previousToken = null;

    public function Tokenizer(xpath:String){
        setXPath( xpath );
    }

    public function setXPath(xpath:String){
        this.xpath           = xpath;
        this.currentPosition = 0;
        this.endPosition     = xpath.length;
    }

    public function nextToken(){
        var token = null;
        do{
            token = null;
            switch ( LA(1) ){
                case '$':
                    token = dollar();
                    break;
                case '"':
                case '\'':
                    token = literal();
                    break;
                case '/':
                    token = slashes();
                    break;
                case ',':
                    token = comma();
                    break;
                case '(':
                    token = leftParen();
                    break;
                case ')':
                    token = rightParen();
                    break;
                case '[':
                    token = leftBracket();
                    break;
                case ']':
                    token = rightBracket();
                    break;
                case '+':
                    token = plus();
                    break;
                case '-':
                    token = minus();
                    break;
                case '<':
                case '>':
                    token = relationalOperator();
                    break;
               	case '=':
                    token = equals();
                    break;
                case '!':
                    if ( LA(2) == '=' ){
                        token = notEquals();
                    }else{
                        token = Not();
                    }
                    break;
                case '|':
                    token = pipe();
                    break;
                case '@':
                    token = at();
                    break;
                case ':':
                    if ( LA(2) == ':' ){
                        token = doubleColon();
                    }else{
                        token = colon();
                    }
                    break;
                case '*':
			token = star();
			break;
               	case '.':
                    switch ( LA(2) ){
                        case '0':
                        case '1':
                        case '2':
                        case '3':
                        case '4':
                        case '5':
                        case '6':
                        case '7':
                        case '8':
                        case '9':
                            token = numberTok();
                            break;
                        default:
                            token = dots();
                            break;
                    }
                    break;
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                    token = numberTok();
                    break;
                case ' ':
                case '\t':
                case '\n':
                case '\r':
                    token = whitespace();
                    break;
                default:
                    if ( isIdentifierStartChar( LA(1) ) ){
                        token = identifierOrOperatorName();
                    }
            }

            if ( token == null ) {
                token = {type:TokenTypes.EOF,text:xpath.substring(currentPosition,endPosition)}
            }
        }while ( token.type == TokenTypes.SKIP);
        
        this.previousToken = token;
        
        return token;
    }

public function identifierOrOperatorName():Token
{
    var token = null;
    if ( this.previousToken != null ){
        // For some reason, section 3.7, Lexical structure,
        // doesn't seem to feel like it needs to mention the
        // SLASH, DOUBLE_SLASH, and COLON tokens for the test
        // if an NCName is an operator or not.
        //
        // Accoring to section 3.7, "/foo" should be considered
        // as a SLASH following by an OperatorName being 'foo'.
        // Which is just simply, clearly, wrong, in my mind.
        //
        //     -bob
        
        switch ( this.previousToken.type ){
            case TokenTypes.AT:
            case TokenTypes.DOUBLE_COLON:
            case TokenTypes.LEFT_PAREN:
            case TokenTypes.LEFT_BRACKET:
            case TokenTypes.AND:
            case TokenTypes.OR:
            case TokenTypes.MOD:
            case TokenTypes.DIV:
            case TokenTypes.COLON:
            case TokenTypes.SLASH:
            case TokenTypes.DOUBLE_SLASH:
            case TokenTypes.PIPE:
            case TokenTypes.DOLLAR:
            case TokenTypes.PLUS:
            case TokenTypes.MINUS:
            case TokenTypes.STAR:
            case TokenTypes.COMMA:
            case TokenTypes.LESS_THAN:
            case TokenTypes.GREATER_THAN:
            case TokenTypes.LESS_THAN_EQUALS:
            case TokenTypes.GREATER_THAN_EQUALS:
            case TokenTypes.EQUALS:
            case TokenTypes.NOT_EQUALS:
            	
                token = identifier();
                break;
            default:
                token = operatorName();
                break;
        }
    }else{
        token = identifier();
    }

    return token;
}

public function identifier(){
    var token = null;

    var start = this.currentPosition;

    while ( this.hasMoreChars() ){
        if ( isIdentifierChar( LA(1) ) ){
            consume();
        }else{
            break;
        }
    }
    token = {type:TokenTypes.IDENTIFIER,text:xpath.substring(start,currentPosition)}

    return token;
}

public function operatorName(){	
    var token = null;

    switch (LA(1)){
        case 'a':
            token = And();
            break;
        case 'o':
            token = Or();
            break;
        case 'm':
            token = mod();
            break;
        case 'd':
            token = div();
            break;
    }

    return token;
}

public function mod(){
    var token = null;
       token = {type:TokenTypes.MOD,text:"mod"}

        consume();
        consume();
        consume();
    return token;
}

public function div()
{
    var token = null;
        token = {type:TokenTypes.DIV,text:xpath.substring(currentPosition,currentPosition+3)}

        consume();
        consume();
        consume();
    return token;
}

public function And() 
{
    var token = null;

    //if ( ( LA(1) == 'a' )
    //     &&
    //     ( LA(2) == 'n' )
    //     &&
    //     ( LA(3) == 'd' )
    //     &&
    //     ( ! isIdentifierChar( LA(4) ) ) )
    //{
        token = {type:TokenTypes.AND,text:"and"}
        consume();
        consume();
        consume();
   // }

    return token;
}

public function Or()
{
    var token = null;
        token = {type:TokenTypes.OR,text:"or"}

        consume();
        consume();
    return token;
}

public function numberTok(){
    var start         = currentPosition;
    var periodAllowed = true;
    var keepGoing = true;

    while( keepGoing ){
        switch ( LA(1) ){
            case '.':
                if ( periodAllowed ){
                    periodAllowed = false;
                    consume();
                }else{
                    break;
                }
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                consume();
                break;
            default:
            	keepGoing = false;
                break;
        }
    }

    var token = null;

    if ( periodAllowed )
    {
        token = {type:TokenTypes.INTEGER,text:xpath.substring(start,currentPosition)}
    }
    else
    {
        token = {type:TokenTypes.DOUBLE,text:xpath.substring(start,currentPosition)}
    }
 
    return token;
}

public function whitespace(){
    consume();
    while(hasMoreChars()){
        switch ( LA(1) ){
            case ' ':
            case '\t':
            case '\n':
            case '\r':
                consume();
                break;
            default:
			    return  {type:TokenTypes.SKIP,text:xpath.substring(0,0)}
        }
    }
}

public function comma()
{
    var token =  {type:TokenTypes.COMMA,text:","}
    consume();

    return token;
}

public function equals()
{
    var token =  {type:TokenTypes.EQUALS,text:"="}
    consume();

    return token;
}

public function minus()
{
    var token = {type:TokenTypes.MINUS,text:"-"}
    consume();
        
    return token;
}

public function plus()
{
    var token = {type:TokenTypes.PLUS,text:"+"}
    consume();

    return token;
}

public function dollar()
{
    var token = {type:TokenTypes.DOLLAR,text:"$"};
    consume();

    return token;
}

public function pipe()
{
    var token = {type:TokenTypes.PIPE,text:"|"}
    consume();

    return token;
}

public function at()
{
    var token = {type:TokenTypes.AT,text:"@"};
    consume();

    return token;
}

public function colon()
{
    var token = {type:TokenTypes.COLON,text:":"}
    consume();

    return token;
}

public function doubleColon()
{
    var token = {type:TokenTypes.DOUBLE_COLON,text:"::"}
    consume();
    consume();

    return token;
}

public function Not()
{
    var token = {type:TokenTypes.NOT,text:"!"}
    consume();

    return token;
}

public function notEquals() 
{
    var token = {type:TokenTypes.NOT_EQUALS,text:"!="}
    consume();
    consume();

    return token;
}

public function relationalOperator()
{
    var token = null;

    switch ( LA(1) )
    {
        case '<':
        {
            if ( LA(2) == '=' )
            {
                token = {type:TokenTypes.LESS_THAN_EQUALS,text:"<="}
                consume();
            }
            else
            {
                token = {type:TokenTypes.LESS_THAN,text:"<"}
            }

            consume();
            break;
        }
        case '>':
        {
            if ( LA(2) == '=' )
            {
                token = {type:TokenTypes.GREATER_THAN_EQUALS,text:">="}
                consume();
            }
            else
            {
                token = {type:TokenTypes.GREATER_THAN,text:">"}
            }

            consume();
            break;
        }
    }

    return token;
            
}

public function star(){
    var token = {type:TokenTypes.STAR,text:"*"}
    consume();
    return token;
}

public function literal(){
    var token = null;
    var match  = LA(1);
    consume();
    var start = currentPosition;
        
    while ( ( token == null ) && hasMoreChars() ){
        if ( LA(1) == match ){
            token = {type:TokenTypes.LITERAL,text:xpath.substring(start,currentPosition)}
        }
        consume();
    }

    return token;
}

public function dots(){
    var token = null;

    switch ( LA(2) ){
        case '.':
            token = {type:TokenTypes.DOT_DOT,text:".."}
            consume();
            consume();
            break;
        default:
            token = {type:TokenTypes.DOT,text:"."}
            consume();
            break;
    }
    return token;
}

public function leftBracket(){
    var token = {type:TokenTypes.LEFT_BRACKET,text:"["}
    consume();
    return token;
}

public function rightBracket(){
    var token = {type:TokenTypes.RIGHT_BRACKET,text:"]"}
    consume();
    return token;
}

public function leftParen(){
    var token = {type:TokenTypes.LEFT_PAREN,text:"("}
    consume();
    return token;
}

public function rightParen(){
    var token = {type:TokenTypes.RIGHT_PAREN,text:")"}
    consume();
    return token;
}

public function slashes(){
    var token = null;
    switch ( LA(2) ){
        case '/':
            token = {type:TokenTypes.DOUBLE_SLASH,text:"//"}
            consume();
            consume();
            break;
        default:
            token = {type:TokenTypes.SLASH,text:"/"}
            consume();
    }

    return token;
}

public function  LA(i:Number){
    //if ( this.currentPosition + ( i - 1 ) >= this.endPosition ){
    //    return -1;
    //}
    return this.xpath.charAt( this.currentPosition + (i - 1) );
}

public function consume(){
	++this.currentPosition;
}


public function hasMoreChars():Boolean{
    return (this.currentPosition < this.endPosition);
}

public function isIdentifierChar(c:String):Boolean{
    //switch ( c ){
    //    case '-':
    //    case '.':
    //        return true;
    //}
    return Tokenizer.isUnicodeIdentifierPart( c );
}

public function  isIdentifierStartChar(c:String):Boolean{
    return ("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_".indexOf(c) != -1);
}

public static function isUnicodeIdentifierPart(c:String){
	return ("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.".indexOf(c) != -1);
}

//public static function isLetter(c:String){
//	return ("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz".indexOf(c) != -1);
//}

public static function isNumber(c:String){
	return !isNaN(c);
}

//public static function isNonLetterIdentifierPart(c:String){
//	return ("_-".indexOf(c) != -1);
//}

}
