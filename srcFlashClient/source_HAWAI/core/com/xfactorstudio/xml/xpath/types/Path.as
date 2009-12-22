import com.xfactorstudio.xml.xpath.types.QueryPart;
import com.xfactorstudio.xml.xpath.types.Axis;
import com.xfactorstudio.xml.xpath.Axes;
import com.xfactorstudio.xml.xpath.types.Predicate;

class com.xfactorstudio.xml.xpath.types.Path extends QueryPart{
	public var nodeName = "path";
	
	public function Path(){
		super();
	}
	
	public function register(){
		this.parentNode.hasPaths = true;
	}

	public function appendChild(child:QueryPart){
		if(this.childNodes.length == 0 && child.nodeValue == Axes.SELF){
			this.childNodes.push(new Axis(Axes.CHILD));
			this.childNodes[this.childNodes.length-1].parentNode = this;
		}
		
		if(!(this.childNodes[this.childNodes.length-1] instanceof Axis) && !(child instanceof Axis)&& !(child instanceof Predicate)){
			this.childNodes.push(new Axis(Axes.CHILD));
			this.childNodes[this.childNodes.length-1].parentNode = this;
		}
		
		
		
		this.childNodes.push(child);
		this.childNodes[this.childNodes.length-1].parentNode = this;
		return this.childNodes[this.childNodes.length-1];
	}
	
	public function clone(){
		var obj = new Path();
		super.clone(obj);
		return obj;
	}
	
	public function execute(context:Array){
		for(var j=0;j<this.childNodes.length;j++){
			context = this.childNodes[j].execute(context);
		}
		return context;
	}
	
}