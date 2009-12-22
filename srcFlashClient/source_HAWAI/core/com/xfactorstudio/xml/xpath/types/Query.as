import com.xfactorstudio.xml.xpath.XPathLexer;
import com.xfactorstudio.xml.xpath.Axes;
import com.xfactorstudio.xml.xpath.types.Path;
import com.xfactorstudio.xml.xpath.types.QueryPart;
import com.xfactorstudio.xml.xpath.types.Axis;
import com.xfactorstudio.xml.xpath.types.Predicate;
import com.xfactorstudio.xml.xpath.types.Identifier;
import com.xfactorstudio.xml.xpath.types.Operator;
import com.xfactorstudio.xml.xpath.types.Func;
import com.xfactorstudio.xml.xpath.types.Group;
import com.xfactorstudio.xml.xpath.XPathFunctions;


class com.xfactorstudio.xml.xpath.types.Query extends QueryPart{
	public var pathString;
	public var currentChild;

	public function Query(query:String){
		super();
		XPathFunctions.registerDefaultFunctions();
		currentChild = this;
		this.nodeName = "query";
		this.pathString = query;
		var lex = new XPathLexer();
		lex.handler = this;
		var s = getTimer();
		var e;
		lex.parse(this.pathString);
	}	
	
	public function onFunctionStart(name:Number){
		onNotPath();
		currentChild = currentChild.appendChild(new Func(name));
	}
	
	public function onComma(){
	}
	
	public function onGroupStart(){
		onNotPath();
		currentChild = currentChild.appendChild(new Group());
	}
	
	public function onGroupEnd(){
		while(!(currentChild instanceof Group)){
			currentChild = currentChild.parentNode;
		}
		currentChild = currentChild.parentNode;
	}
	public function onFunctionEnd(){
		while(!(currentChild instanceof Func)){
			currentChild = currentChild.parentNode;
		}
		currentChild = currentChild.parentNode;
	}
	
	public function onIdentifier(name:String){
		if(name == "."){
			onAxis(Axes.SELF);
			onAxis(Axes.CHILD);
		}else{
			onPathPart();
			currentChild.appendChild(new Identifier(name));
		}
	}
	
	public function onAxis(axis:Number){
		onPathPart();
		currentChild.appendChild(new Axis(axis));
	}
	
	public function onPredicateStart(){
		currentChild = currentChild.appendChild(new Predicate());
	}
	
	public function onPredicateEnd(){
		while(!(currentChild instanceof Predicate)){
			currentChild = currentChild.parentNode;
		}
		currentChild = currentChild.parentNode;
	}
	
	public function onOperator(type:Number){
		onNotPath();
		currentChild.appendChild(new Operator(type));
	}

	public function onLitteral(litteral:String){
		onNotPath();
		currentChild.appendChild(litteral);
	}
	
	public function onNumber(num:Number){
		onNotPath();
		currentChild.appendChild(num);
	}
	
	//
	public function onPathPart(){
		if(!(currentChild instanceof Path)){
			currentChild = currentChild.appendChild(new Path());
		}
	}
	
	public function onNotPath(){
		if(currentChild instanceof Path){
			currentChild = currentChild.parentNode;
		}
	}
	
	/////////////////////
	
	public function clone(){
		var obj = new Query();
		super.clone(obj);
		return obj;
	}
	
	public function execute(context:Array){
		var p = new Predicate();
		for(var j=0;j<this.childNodes.length;j++){
			p.appendChild(this.childNodes[j]);
		}
		
		var retArray = new Array();
		for(var i=0;i<context.length;i++){
			var test = p.clone();
			var val = Predicate.staticEvaluate(test,context[i]);
			if(val instanceof Array){
				retArray = retArray.concat(Predicate.staticEvaluate(test,context[i],context));
			}else{
				retArray.push(val);
			}
		}
		return retArray;
	}
	
	
}