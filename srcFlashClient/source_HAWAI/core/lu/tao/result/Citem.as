/*
  Class wich define the items of a test
*/
class lu.tao.result.Citem {

        private var _listePoperty:Array;
        private var _listebehavior:Array;
/*
  Constructor
*/
        public function Citem() {
               _listePoperty= new Array((Cbehavior));
               _listebehavior= new Array((Cbehavior));
        }
/*
  Accessors
*/
        public function get listePoperty():Array {
                return _listePoperty;
        }
        public function get listebehavior():Array {
                return _listePoperty;
        }
/*
  Mutators
*/
        public function set listePoperty(listePoperty:Array):Void{
                _listePoperty=listePoperty;
        }
        public function set listebehavior(listebehavior:Array):Void{
                _listebehavior=listebehavior;
        }
/*
  Methods
*/

// Properties management
        public function addPoperty(name:String,value:String):Void{

                 var tmp= new Cbehavior(name,value);
                 _listePoperty.push(tmp);
        }

        public function SupprPoperty(name:String):Void{
                var i:Number;
                var size= _listePoperty.length;
                var index=-1;
                for (i=1;i<size;i++)
                {
                    if (_listePoperty[i].listenername==name)
                            index=i;
                }
                if (i!=-1)
                {
                   for (i=index;i<size;i++)
                   {
                    _listePoperty[i]=_listePoperty[i+1]
                   }
                }
            _listePoperty.pop()
        }
        
// Behavior management
        public function addBehavior(name:String,value:String):Void{
                 var tmp= new Cbehavior(name,value);
                 _listebehavior.push(tmp);
        }
        public function SupprBehavior(name:String):Void{
                var i:Number;
                var size= _listebehavior.length;
                var index=-1;
                for (i=1;i<size;i++)
                {
                    if (_listebehavior[i].listenername==name)
                            index=i;
                }
                if (i!=-1)
                {
                   for (i=index;i<size;i++)
                   {
                    _listebehavior[i]=_listebehavior[i+1]
                   }
                }
            _listebehavior.pop()
        }

// convert item into string       
        public function toSting():String{
            var i:Number;
            // poperties
            var prop="<tao:CITEM ";
            var size= _listePoperty.length;
            var index=-1;
            for (i=1;i<size;i++)
            {
                prop=prop+_listePoperty[i].propertyToSting();
            }
            prop=prop+">";

            // bihaviors
            var behav="";
            var size= _listebehavior.length;
            var index=-1;
            for (i=1;i<size;i++)
            {
                behav=behav+_listebehavior[i].itemToSting()+"\n";
            }

            return prop+"\n"+behav+"</tao:CITEM>\n";
       }
}


