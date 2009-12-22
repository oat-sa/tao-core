/*
  Class use for describe a subject
*/
class lu.tao.result.Csubject{

        private var _rdfid:String;
        private var _rdfs_Label:String;
        private var _rdfs_Comment:String;

/*
  Constructor
*/
        function Csubject(rdfid,rdfs_Label,rdfs_Comment) {
                _rdfid  = rdfid;
                _rdfs_Label  = rdfs_Label;
                _rdfs_Comment  = rdfs_Comment;

        }

/*
  Accessors
*/
        public function get rdfid():String {
                return _rdfid;
        }
        public function get rdfs_Label():String {
                return _rdfs_Label;
        }
        public function get rdfs_Comment():String {
                return _rdfs_Comment;
        }
/*
  Mutators
*/
        public function set rdfid(rdfid:String):Void {
               _rdfid=rdfid;
        }
        public function set rdfs_Label(rdfs_Label:String):Void {
               _rdfs_Label=rdfs_Label;
        }
        public function set rdfs_Comment(rdfs_Comment:String):Void {
               _rdfs_Comment=rdfs_Comment;
        }

/*
  Methods
*/

        public function toSting():String
        {
           return "<tao:SUBJECT rdfid=\""+_rdfid+"\" rdfs:Label=\""+_rdfs_Label+"\" rdfs:Comment=\""+_rdfs_Comment+"\"/>\n";
        }
}



