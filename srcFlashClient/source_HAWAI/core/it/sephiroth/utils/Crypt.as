/**
 * utils.Crypt
 *
 * @author
 * @version
 */
class utils.Crypt {
        /**
         * asc2bin
         *
         * @param       ascii   String
         * @return      String
         */
        static function asc2bin (ascii:String):String{
                var binary:String = "";
                var i:Number = 0;
                var byte:String = "";
                while ( ascii.length > 0 ){
                        byte = "";
                        i = 0;
                        byte = ascii.substr(0, 1);
                        while ( byte != chr(i)) {
                                i++;
                        }
                        byte = utils.string.dec2bin(i);
                        byte = utils.string.string_repeat("0", (8 - length(byte)) ) + byte;
                        ascii = ascii.substr(1);
                        binary = binary add byte;
                }
                return binary;
        }
}