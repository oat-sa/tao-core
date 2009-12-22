import com.xfactorstudio.xml.xpath.types.QueryPart;
import com.xfactorstudio.xml.xpath.XPathFunctions;

class com.xfactorstudio.xml.xpath.types.Func extends QueryPart{
	public var nodeName = "function";
	public function Func(name:Number){
		super();
		this.nodeValue = name;
	}
	
	public function register(){
		this.parentNode.hasFunctions = true;
	}
	
	public function clone(){
		var obj = new Func(this.nodeValue);
		super.clone(obj);
		return obj;
	}
	public function execute(context:Array,axis:Array){

		for(var i=0;i<this.childNodes.length;i++){
			switch(typeof(this.childNodes[i])){
				case "string":
				case "boolean":
				case "number":
					break;
				default:
					this.childNodes[i] = this.childNodes[i].execute(context);
					break
			}
		}
		return XPathFunctions.getFunction(this.nodeValue).call(this,this.childNodes,context[0],axis);
	}
	
}