import com.xfactorstudio.xml.xpath.XPathAxes;

class com.xfactorstudio.xml.xpath.XmlNodeSet{
	private static var nodeSetAxesInited = false;
	
	private function XmlNodeSet(){
	
	}
	
	public static function registerNodeSetAxesFunctions(){
		if(!XmlNodeSet.nodeSetAxesInited){
			
			Array.prototype.ancestor = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.ancestor(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.namespace = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.namespace(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.ancestorOrSelf = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.ancestorOrSelf(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.attribute = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.attribute(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.child = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.child(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.stringValue = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.stringValue(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.descendant = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.descendant(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.descendantOrSelf = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.descendantOrSelf(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.following = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.following(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.followingSibling = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.followingSibling(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.parent = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.parent(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.preceding = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.preceding(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.precedingSibling = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.precedingSibling(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.self = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.self(this[i]));
				}
				return nodeArray;
			}
			
			Array.prototype.root = function(){
				var nodeArray = new Array();
				for(var i=0;i<this.length;i++){
					nodeArray = nodeArray.concat(XPathAxes.root(this[i]));
				}
				return nodeArray;
			}
		
			XmlNodeSet.nodeSetAxesInited = true;
		}
	}

}