import com.xfactorstudio.xml.xpath.TokenTypes;

class com.xfactorstudio.xml.xpath.Token
{
    private var tokenType:Number;
    private var parseText:String;
    private var tokenBegin:Number;
    private var tokenEnd:Number;

    public function Token(tokenType:Number,
                 parseText:String,
                 tokenBegin:Number,
                 tokenEnd:Number)
    {
        setTokenType( tokenType );
        setParseText( parseText );
        setTokenBegin( tokenBegin );
        setTokenEnd( tokenEnd );
    }

    private function setTokenType(tokenType:Number)
    {
        this.tokenType = tokenType;
    }

    public function  getTokenType():Number
    {
        return this.tokenType;
    }

    private function  setParseText(parseText:String)
    {
        this.parseText = parseText;
    }

    public function  getTokenText():String
    {
        return this.parseText.substring( getTokenBegin(),
                                         getTokenEnd() );
    }

    private function setTokenBegin(tokenBegin:Number)
    {
        this.tokenBegin = tokenBegin;
    }

    public function  getTokenBegin()
    {
        return this.tokenBegin;
    }

    private function setTokenEnd(tokenEnd:Number)
    {
        this.tokenEnd = tokenEnd;
    }

    public function  getTokenEnd()
    {
        return this.tokenEnd;
    }

    public function toString():String
    {
        return ("[ (" + TokenTypes.getName(tokenType) + ") (" + getTokenText() + ")");
    }
}
