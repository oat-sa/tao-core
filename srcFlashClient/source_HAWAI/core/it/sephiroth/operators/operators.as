class it.sephiroth.operators {
        // static rgb2hex
        // convert an array of rgb values into an hex string
        static function rgb2hex(nArray:Array):String{
                var sRet:String = "";
                var x:Number = nArray[0] << 16 | nArray[1] << 8 | nArray[2]
                sRet = x.toString(16).toUpperCase()
                while(sRet.length < 6){
                        sRet = "0" add sRet
                }
                sRet = "#" add sRet
                return sRet;
        }
}