/*
   This class is use to define all the scoring attributes
*/
class lu.tao.result.Cscoring {

        private var _name:String;
        private var _param:String;
        private var _value:String;
/*
  Constructor
*/
        public function Cscoring(name,param,value) {
                _name = name;
                _param=param;
                _value=value;
        }
/*
  Accessors
*/
        public function get name():String {
                return _name;
        }
        public function get param():String {
                return _param;
        }
        public function get value():String {
                return _value;
        }
/*
  Mutators
*/
        public function set name(name:String):Void{
                _name=name;
        }
        public function set param(param:String):Void {
               _param=param;
        }
        public function set value(value:String):Void {
               _value=value;
        }
/*
  Methods
*/
         public function toString():String{
                return "<tao:"+_name+" tao:"+_param+"=\""+_value+"\"/>";
        }
}


