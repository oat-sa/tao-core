import com.xfactorstudio.xml.xpath.types.QueryPart;
import com.xfactorstudio.xml.xpath.XPath;

class com.xfactorstudio.xml.xpath.types.Identifier extends QueryPart{
	public var nodeName = "identifier";
	public function Identifier(name:String){
		super();
		this.nodeValue = name;
	}
	
	public function execute(context:Array){
		var ret = XPath.getNamedNodes(context,this.nodeValue);
		return ret;
	}
	
	public function clone(){
		var obj = new Identifier();
		super.clone(obj);
		return obj;
	}
	
}