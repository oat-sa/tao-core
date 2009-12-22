class com.xfactorstudio.xml.xpath.types.QueryPart{
	public var childNodes:Array;
	public var parentNode;
	public var nodeValue;
	public var nodeName = "querypart";;
	
	public function QueryPart(){
		this.childNodes = new Array();
	}
	
	public function appendChild(child){
		this.childNodes.push(child);
		this.childNodes[this.childNodes.length-1].parentNode = this;
		return this.childNodes[this.childNodes.length-1];
	}
	
	public function clone(obj){
		for(var i=0;i<this.childNodes.length;i++){
			if(this.childNodes[i].nodeValue != null){
				obj.parentNode = this.parentNode;
				obj.nodeValue = this.nodeValue;
				obj.nodeName = this.nodeName;
				obj.childNodes.push(this.childNodes[i].clone());
			}else{
				obj.childNodes.push(this.childNodes[i]);
			}
		}
	}
	
	public function toString(tabs:String){
		if(tabs == null){
			tabs = "\t";
		}
		var ret = new Array();
		ret.push("\n");
		ret.push(tabs);
		ret.push("<");
		ret.push(nodeName);
		ret.push(">");
		if(this.nodeValue != null){
			ret.push(this.nodeValue);
		}
		for(var i=0;i<this.childNodes.length;i++){
			if(this.childNodes[i] instanceof Array){
				ret.push("\n" + tabs + "\t<nodeset>" + this.childNodes[i] + "</nodset>");
			}else{
				ret.push(this.childNodes[i].toString(tabs+"\t"));
			}
		}
		if(this.childNodes.length>0){
			ret.push("\n");
			ret.push(tabs);
		}
		ret.push("</");
	 	ret.push(nodeName);
	 	ret.push(">");
	 	return ret.join("");
	}
	
	public function execute(context:Object){
	
	}
}