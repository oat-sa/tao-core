import com.xfactorstudio.xml.xpath.types.QueryPart;
import com.xfactorstudio.xml.xpath.Operators;

class com.xfactorstudio.xml.xpath.types.Operator extends QueryPart{
	public var nodeName = "operator";
	public function Operator(type:Number){
		super();
		this.nodeValue = type;
	}
	
	public function register(){
		switch(nodeValue){
			case  Operators.EQUALS:
			case  Operators. NOT_EQUALS:
				this.parentNode.hasEqualityExpressions = true;
				break;
			case  Operators.MULTIPLY:
			case  Operators.MOD:
			case  Operators.DIV:
				this.parentNode.hasMultiplicativeExpressions = true;
				break;
			case  Operators.PLUS:
			case  Operators.MINUS:
				this.parentNode.hasAdditiveExpressions = true;
				break;
			case  Operators.AND:
				this.parentNode.hasLogicalAndExpressions = true;
				break;
			case  Operators.OR:
				this.parentNode.hasLogicalOrExpressions = true;
				break;
			case  Operators.GREATER_THAN:
			case  Operators.LESS_THAN:
			case  Operators.GREATER_THAN_OR_EQUAL_TO:
			case  Operators.LESS_THAN_OR_EQUAL_TO:
				this.parentNode.hasRelationalExpressions = true;
				break;
			case  Operators.UNION:
				this.parentNode.hasUnions = true;
				break;
		}
	}
	
	public function clone(){
		return this;
	}

}