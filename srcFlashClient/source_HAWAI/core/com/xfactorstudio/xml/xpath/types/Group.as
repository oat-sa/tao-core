import com.xfactorstudio.xml.xpath.types.QueryPart;
import com.xfactorstudio.xml.xpath.types.Predicate;

class com.xfactorstudio.xml.xpath.types.Group extends QueryPart{
	public var nodeName = "group";
	public function Group(){
		super();
	}
	
	public function clone(){
		var obj = new Group();
		super.clone(obj);
		return obj;
	}
	
	public function execute(context:Array){
		var result;
		var p = new Predicate();
		for(var j=0;j<this.childNodes.length;j++){
			p.appendChild(this.childNodes[j]);
		}
		var retArray = new Array();
		for(var i=0;i<context.length;i++){
			var test = p.clone();
			result = Predicate.staticEvaluate(test,context[i]);
		}
		return result;
	}
	
}