/*
  This is the class for all the test and item behavior.
  This is the class for the item propertie too.
*/
class lu.tao.result.Cbehavior {

        private var _listenername:String;
        private var _listenervalue:String;
/*
  Constructor
*/
        public function Cbehavior(name,value) {
                _listenername = name;
                _listenervalue=value;
        }
/*
  Accessors
*/
        public function get listenername():String {
                return _listenername;
        }
        public function get listenervalue():String {
                return _listenervalue;
        }
/*
  Mutators
*/
        public function set listenername(name:String):Void{
                _listenername=name;
        }
        public function set listenervalue(name:String):Void {
               _listenervalue=name;
        }
/*
  Methods
*/
     // use this methode when the object is an item behavior
       public function itemToSting():String {
               return "<tao:ITEMBEHAVIOR tao:LISTENERNAME=\""+_listenername+"\" tao:LISTENERVALUE=\""+_listenervalue+"\"/>";
      }
           // use this methode when the object is a test behavior
       public function testToSting():String {
              return "<tao:TESTBEHAVIOR tao:LISTENERNAME=\""+_listenername+"\" tao:LISTENERVALUE=\""+_listenervalue+"\"/>";
      }
           // use this methode when the object is a propety 
       public function propertyToSting():String {
              return " "+_listenername+"=\""+_listenervalue+"\"";
      }

}
