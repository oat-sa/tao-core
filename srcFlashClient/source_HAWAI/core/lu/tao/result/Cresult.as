/*
  This is the main classes of the application of result .
  This classes will generate the rdf file by using all the other classes
*/
import lu.tao.result.*;

class lu.tao.result.Cresult {

        private var _header:String;
        private var _rdfid:String;
        private var _rdfs_Label:String;
        private var _rdfs_Comment:String;
        private var _scoring:Array;
        private var _subject:Csubject;
        private var _listTestbehavior:Array; // array of behavior
        private var _listItem:Array; // array of item
        private var _footer:String;

/*
  Constructor
*/
        function Cresult(rdfid,rdfs_Label,rdfs_Comment) {
                _header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE rdf:RDF[ \n<!ENTITY rdf 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n<!ENTITY rdfs 'http://www.w3.org/TR/1999/PR-rdf-schema-19990303#'>\n<!ENTITY tao 'http://www.tao.lu/tao.rdfs#'>]>\n<rdf:RDF xmlns:tao=\"&tao;\"  xmlns:rdf=\"&rdf;\" xmlns:rdfs=\"&rdfs;\">\n<tao:Result>\n";
                _rdfid  = rdfid;
                _rdfs_Label  = rdfs_Label;
                _rdfs_Comment  = rdfs_Comment;
                _scoring= new Array(Cscoring);
                _subject= new Csubject();
                _listTestbehavior=new Array(Cbehavior);
                _listItem=new Array(Citem);
                _footer="</tao:Result>\n</rdf:RDF>";
        }
/*
  Accessors
*/
        public function get header():String {
                return _header;
        }
        public function get rdfid():String {
                return _rdfid;
        }
        public function get rdfs_Label():String {
                return _rdfs_Label;
        }
        public function get rdfs_Comment():String {
                return _rdfs_Comment;
        }
        public function get scoring ():Array{
                return _scoring ;
        }
        public function get subject():Csubject {
                return _subject;
        }
        public function get listTestbehavior():Array {
                return _listTestbehavior;
        }
        public function get listItem():Array {
                return _listItem;
        }
        public function get footer():String {
                return _footer;
        }
/*
  Mutators
*/
        public function set header(header:String):Void {
               _header=header;
        }
        public function set rdfid(rdfid:String):Void {
               _rdfid=rdfid;
        }
        public function set rdfs_Label(rdfs_Label:String):Void {
               _rdfs_Label=rdfs_Label;
        }
        public function set rdfs_Comment(rdfs_Comment:String):Void {
               _rdfs_Comment=rdfs_Comment;
        }
        public function set scoring(scoring:Array):Void {
               _scoring=scoring;
        }
        public function set subject(subject:Csubject):Void {
               _subject=subject;
        }
        public function set listTestbehavior(listTestbehavior:Array):Void {
               _listTestbehavior=listTestbehavior;
        }
        public function set listItem(listItem:Array):Void {
               _listItem=listItem;
        }
        public function set footer(footer:String):Void {
               _footer=footer;
        }
/*
  Methods
*/

        // this methode return the string contain in the header
        public function testHeader():String{
               return "<tao:TEST  rdfid=\""+_rdfid+"\" rdfs:Label=\""+ _rdfs_Label+"\" rdfs:Comment=\""+_rdfs_Comment+"\">\n";
        }
        public function testfooter():String{
               return "</tao:TEST>\n";
        }
       // methode to add a scoring poperty
        public function addScoring(name,param,val)
        {
        	var tmp=new Cscoring(name,param,val);
        	_scoring.push(tmp);
         }

        // methode to add a behavior to the test
        public function addTestbehavior(name:String,val)
        {
        	var tmp=new Cbehavior(name,val);
        	_listTestbehavior.push(tmp);
         }

        // methode to add an item to the test
        public function addItem(item:Citem)
        {
        	_listItem.push(item);
         }
         
        // methode to convert the result of the test in a sting
        public function toSting():String
        {
           var scor="";
           var behavi="";
           var item="";
           var i:Number;
           for (i=1;i<_scoring.length;i++)
           {
               scor=scor+_scoring[i].toString()+"\n";
           }
           for (i=1;i<_listTestbehavior.length;i++)
           {
               behavi=behavi+_listTestbehavior[i].testToSting()+"\n";
           }
           for (i=1;i<_listItem.length;i++)
           {
               item=item+_listItem[i].toSting()+"\n";
           }

           return testHeader()+"\n"+_subject.toSting()+"\n"+scor+"\n"+behavi+"\n"+item+"\n"+testfooter();
        }
}


