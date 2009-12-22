import com.xfactorstudio.xml.xpath.types.QueryPart;
import com.xfactorstudio.xml.xpath.XPathAxes;
import com.xfactorstudio.xml.xpath.Axes;


class com.xfactorstudio.xml.xpath.types.Axis extends QueryPart{
	public var nodeName = "axis";

	public function Axis(axis:Number){
		super();
		this.nodeValue = axis;
	
	}
	
	public function clone(){
		var obj = new Axis();
		super.clone(obj);
		return obj;
	}
	
	public function execute(context:Array){
		var retArray = new Array();
		for(var i=0;i<context.length;i++){
			retArray = retArray.concat(XPathAxes[Axes.getName(Number(this.nodeValue))].call(this,context[i]));
		}
		return retArray;
	}
}