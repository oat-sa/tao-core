class it.sephiroth.XMLObject extends XML {
   
    private var oResult:Object;
    private var oXML:XML;
   
    /**
    *
    * @usage  
    * @param   xmlinput
    * @param   allArray if set to true will put all nodes into an array, even if a single childNode
    * @return Object representing the XML
    */
    public function parseXML( xmlinput:XML, allArray:Boolean):Object
    {
        if(allArray == undefined){
            allArray = false;
        }
        this.oResult = new Object();
        this.oXML      = xmlinput;
        this.oResult = this.translateXML(null,null,null,null,allArray);
        return this.oResult;
    }
   
    /**
    * Transform a Flash Object into An XML
    * @usage   new XMLObject().parseObject( object, 'root_name')
    * @param   obj object to be parsed
    * @param   name name of the first root element
    * @return xml
    */
    public function parseObject( obj:Object, name:String ):XML
    {
        var tempXML:XML               = new XML("<?xml version=\"1.0\"?>");
        var rootNode:XMLNode = tempXML.createElement(name);
        var returnedNode = this.translateObject( obj, tempXML, rootNode)
        rootNode.appendChild(returnedNode);
        tempXML.appendChild(rootNode);
        return tempXML;
    }
   
    private function translateObject( obj:Object, tempXML:XML, parentNode:XMLNode )
    {
        var node:XMLNode
        switch(obj.__proto__){
            case Array.prototype:
                var firstVal = obj.shift()
                this.translateObject(firstVal, tempXML, parentNode);
                for(var a in obj){
                    node = parentNode.cloneNode(false)
                    parentNode.parentNode.appendChild(node)
                    this.translateObject(obj[a], tempXML, node);
                }
                break
            case Object.prototype:
                for(var a in obj){
                    if(a == "attributes"){
                        this.parseAttributes(obj[a], parentNode)
                    } else {
                        node = tempXML.createElement(a)
                        parentNode.appendChild(node)
                    }
                    this.translateObject(obj[a], tempXML, node);
                }
                break
            case String.prototype:
            case Boolean.prototype:
            case Number.prototype:
            case Date.prototype:
            default:
                var textNode = tempXML.createTextNode( obj.toString() );
                parentNode.appendChild(textNode);
                break
        }
        return parentNode
    }
   
    private function parseAttributes(obj:Object,parentNode:XMLNode){
        for(var a in obj){
            parentNode.attributes[a] = obj[a]
        }
    }
   
    /**
    *
    * @usage  
    * @param   from         
    * @param   path         
    * @param   name         
    * @param   position
    * @param   allarray if set to true will put all nodes into an array, even if a single childNode
    * @return
    */
    private function translateXML (from:XML, path:Object, name:String, position:Number, allarray:Boolean):Object {
        var xmlName:String;
        var nodes, node, old_path;
        if (path == undefined) {
            path = this;
            name = "oResult";
        }
        path = path[name];
        if (from == undefined) {
            from = new XML (this.xml);
            from.ignoreWhite = true;
        }
        if (from.hasChildNodes ()) {
            nodes = from.childNodes;
            if (position != undefined) {
                var old_path = path;
                path = path[position];
            }
            while (nodes.length > 0) {
                node = nodes.shift ();
                xmlName = node.nodeName.split("-").join("_");
                if (xmlName != undefined) {
                    var __obj__ = new Object ();
                    __obj__.attributes = node.attributes;
                    __obj__.data = node.firstChild.nodeValue;
                    if (position != undefined) {
                        var old_path = path;
                    }
                    if (path[xmlName] != undefined) {
                        if (path[xmlName].__proto__ == Array.prototype) {
                            path[xmlName].push (__obj__);
                            name = node.nodeName;
                            position = path[xmlName].length - 1;
                        } else {
                            var copyObj = path[xmlName];
                            path[xmlName] = new Array ();
                            path[xmlName].push (copyObj);
                            path[xmlName].push (__obj__);
                            name = xmlName;
                            position = path[xmlName].length - 1;
                        }
                    } else {
                        if(allarray){
                            path[xmlName] = new Array();
                            path[xmlName].push( __obj__ );
                        } else {
                            path[xmlName] = __obj__;
                        }
                        name = xmlName;
                        position = undefined;
                    }
                }
                if (node.hasChildNodes ()) {
                    this.translateXML (node, path, name, position, allarray);
                }
            }
        }
        return this.oResult;
    }
   
   
   
    /**
    *
    * @usage  
    * @return
    */
    public function get xml():XML{
        return this.oXML
    }
}