/**
* class StringAdv - AS2.0 [ 08/07/2004 ]
*
* Last Change: 28/07/2004
* Last Fix: md5() didn't work correctly
*
* Version 1.0 Copyright (C) Andrea Giammarchi 2004
*
* NOTE 1: this class is compatible with Flash Player 6 r65
*
* This class contains methods to manage strings.
* ALl methods are static, then you don't need to declare this class.
* Methods name and params are the same of PHP's functions
* ( just omitted "str_" in some cases )
* -------------------------------------------------------------------
* METHODS LIST:
*               methodsList()
*               trim( s:String ):String
*               rtrim( s:String ):String
*               ltrim( s:String ):String
*               rpos( s:String, src:String ):Object
*               ripos( s:String, src:String ):Object
*               pos( s:String, src:String [, ofs:Number ] ):Object
*               ipos( s:String, src:String [, ofs:Number ] ):Object
*               nl2br( s:String ):String
*               replace( src:String, rpl:String, s:String ):String
*               ireplace( src:String, rpl:String, s:String ):String
*               word_count( s:String ):Array
*               pad( s:String, p:Number [, toAd:String [, t:String ] ] ):String
*               repeat( s:String, many:Number ):String
*               addslashes( s:String ):String
*               stripslashes( s:String ):String
*               ucfirst( s:String ):String
*               ucwords( s:String ):String
*               strip_tags( s:String [, allow:Object ] ):String
*               md5( s:String [, StringAdv.b64pad:String [, StringAdv.chrsz:Number ] ] ):String
*
* NOTE: you can read this methods list using StringAdv.methodsList();
*               directly from your .fla
* -------------------------------------------------------------------
*/
class StringAdv {

        private static var b64pad:String  = new String( "" );
        private static var chrsz:Number = new Number( 8 );

        /**
        * static public method, trace all public methods with theyr options
        * @param        String  string to parse
        * @return       String  parsed string
        */
        public static function methodsList():Void {
                trace( "trim( s:String ):String" );
                trace( "rtrim( s:String ):String" );
                trace( "ltrim( s:String ):String" );
                trace( "rpos( s:String, src:String ):Object" );
                trace( "ripos( s:String, src:String ):Object" );
                trace( "pos( s:String, src:String [, ofs:Number ] ):Object" );
                trace( "ipos( s:String, src:String [, ofs:Number ] ):Object" );
                trace( "nl2br( s:String ):String" );
                trace( "replace( src:String, rpl:String, s:String ):String" );
                trace( "ireplace( src:String, rpl:String, s:String ):String" );
                trace( "word_count( s:String ):Array" );
                trace( "pad( s:String, p:Number [, toAd:String [, t:String ] ] ):String" );
                trace( "repeat( s:String, many:Number ):String" );
                trace( "addslashes( s:String ):String" );
                trace( "stripslashes( s:String ):String" );
                trace( "ucfirst( s:String ):String" );
                trace( "ucwords( s:String ):String" );
                trace( "md5( s:String [, StringAdv.b64pad:String [, StringAdv.chrsz:Number ] ] ):String" );
                trace( "strip_tags( s:String [, allow:Object ] ):String" );
        }

        /**
        * static public method, remove spaces from right
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function rtrim( s:String ):String {
                var a:Number = new Number( s.length );
                while( s.substr( a--, 1 ) == " " ) {}
                return s.substr( 0, ( a + 1 ) );
        }

        /**
        * static public method, remove spaces from left
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function ltrim( s:String ):String {
                var a:Number = 0;
                while( s.substr( a++, 1 ) == " " ) {}
                return s.substr( ( a - 1 ), ( s.length ) );
        }

        /**
        * static public method, remove spaces from left and right
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function trim( s:String ):String {
                return StringAdv.ltrim( StringAdv.rtrim( s ) );
        }

        /**
        * static private method, called from rpos and ripos
        * @param        String  string to parse
        * @param        String  string to search
        * @param        Boolean sensitive or insensitive case [ false / true ]
        * @return       Object  Boolean false value if no match or Number position
        */
        static private function __rpos( s:String, src:String, ci:Boolean ):Object {
                var found:Boolean = new Boolean( false );
                var position:Number = new Number();
                if( ci == true ) {
                        s = s.toUpperCase();
                        src = src.toUpperCase();
                }
                for( var a:Number = s.length - 1; a >= 0; a-- ) {
                        if( s.substr( a, src.length ) == src ) {
                                found = true;
                                position = a;
                                break;
                        }
                }
                if( found == true ) {
                        return position;
                }
                return found;
        }

        /**
        * static public method, found last position of a string
        * @param        String  string to parse
        * @param        String  string to search
        * @return       Object  Boolean false value if no match or Number position
        */
        static public function rpos( s:String, src:String ):Object {
                return StringAdv.__rpos( s, src, false );
        }

        /**
        * static public method, found last position of a string in case insensitive mode
        * @param        String  string to parse
        * @param        String  string to search
        * @return       Object  Boolean false value if no match or Number position
        */
        static public function ripos( s:String, src:String ):Object {
                return StringAdv.__rpos( s, src, true );
        }

        /**
        * static private method, called from pos and ipos
        * @param        String  string to parse
        * @param        String  string to search
        * @param        Number  offset of occurrence
        * @param        Boolean sensitive or insensitive case [ false / true ]
        * @return       Object  Boolean false value if no match or Number position
        */
        static private function __pos( s:String, src:String, ofs:Number, ci:Boolean ):Object {
                var found:Boolean = new Boolean( false );
                var position:Number = new Number();
                var foundOfs:Number = 0;
                ofs = ofs == undefined ? 0 : ofs;
                if( ci == true ) {
                        s = s.toUpperCase();
                        src = src.toUpperCase();
                }
                for( var a:Number = 0; a < s.length; a++ ) {
                        if( s.substr( a, src.length ) == src ) {
                                if( foundOfs == ofs ) {
                                        found = true;
                                        position = a;
                                        break;
                                }
                                foundOfs++;
                        }
                }
                if( found == true ) {
                        return position;
                }
                return found;
        }

        /**
        * static public method, found first position of a string
        * @param        String  string to parse
        * @param        String  string to search
        * @return       Object  Boolean false value if no match or Number position
        */
        static public function pos( s:String, src:String, ofs:Number ):Object {
                return StringAdv.__pos( s, src, ofs, false );
        }

        /**
        * static public method, found first position of a string in case insensitive mode
        * @param        String  string to parse
        * @param        String  string to search
        * @return       Object  Boolean false value if no match or Number position
        */
        static public function ipos( s:String, src:String, ofs:Number ):Object {
                return StringAdv.__pos( s, src, ofs, true );
        }

        /**
        * static public method, remove \n & \r and add <br />
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function nl2br( s:String ):String {
                var a:String = "<br />";
                return s.split( "\n" ).join( a ).split( "\r" ).join( a ).split( a + a ).join( a );
        }

        /**
        * static public method, replace a string with new value
        * @param        String  string to search
        * @param        String  string to replace
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function replace( src:String, rpl:String, s:String ):String {
                return s.split( src ).join( rpl );
        }

        /**
        * static public method, replace a string with new value in case insensitive mode
        * @param        String  string to search
        * @param        String  string to replace
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function ireplace( src:String, rpl:String, s:String ):String {
                var sClone:String = s.toUpperCase();
                var srcClone:String = src.toUpperCase();
                var remPosition:Array = new Array();
                for( var a:Number = 0; a < ( sClone.length - src.length + 2 ); a++ ) {
                        if( sClone.substr( a, src.length ) == srcClone ) {
                                remPosition.push( a );
                        }
                }
                for( var a:Number = 0; a < remPosition.length; a++ ) {
                        s =  s.substr( 0, remPosition[a] ) + rpl + s.substr( remPosition[a] + src.length, s.length );
                }
                return s;
        }

        /**
        * static public method, count all words in a string
        * @param        String  string to parse
        * @return       Array   array with all words
        */
        static public function word_count( s:String ):Array {
                var tWords:Array = new Array();
                var sNew:Array = s.split( " " );
                for( var a:Number = 0; a < sNew.length; a++ ) {
                        var nowS:String = StringAdv.trim( sNew[a] );
                        if( nowS != "" ) {
                                tWords.push( sNew[a] );
                        }
                }
                return tWords;
        }

        /**
        * static public method, add something to a string
        * @param        String  string to parse
        * @param        Number  max length to add chosed string
        * @param        String  string to empty spasces less than p:Number
        * @param        String  type of parsing ( "PAD_RIGHT", "PAD_LEFT", "PAD_BOTH" ) default "PAD_RIGHT"
        * @return       String  parsed string
        */
        static public function pad( s:String, p:Number, toAd:String, t:String ):String {
                if( s.length < p ) {
                        t = t == undefined ? "PAD_RIGHT" : t.toUpperCase();
                        toAd = toAd == undefined ? " " : toAd;
                        if( ( toAd.length + s.length ) > p ) {
                                toAd = toAd.substr( 0, ( p - s.length ) );
                        }
                        var repeated:String = StringAdv.repeat( toAd, Math.ceil( p / toAd.length ) );
                        if( t == "PAD_RIGHT" ) {
                                s = s + repeated.substr( s.length, p );
                        }
                        else if( t == "PAD_LEFT" ) {
                                s = repeated.substr( 0, ( repeated.length - s.length ) ) + s;
                        }
                        else if( t == "PAD_BOTH" ) {
                                var toStart:Number = repeated.length - Math.ceil( repeated.length / 2 ) - Math.ceil( s.length / 2 );
                                var toEnd:Number = toStart + s.length;
                                s = repeated.substr( 0, toStart ) + s + repeated.substr( toEnd, p );
                        }
                }
                return s.substr( 0, p );
        }

        /**
        * static public method, repeat a string for many times
        * @param        String  string to parse
        * @param        Number  how many times to repeat
        * @return       String  parsed string
        */
        static public function repeat( s:String, many:Number ):String {
                var sNew:String = new String( "" );
                for( var a:Number = 0; a < many; a++ ) {
                        sNew += s;
                }
                return sNew;
        }

        /**
        * static public method, add slashes in a string
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function addslashes( s:String ):String {
                return s.split( '"' ).join( '\\"' ).split( "'" ).join( "\\'" );
        }

        /**
        * static public method, remove slashes in a string
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function stripslashes( s:String ):String {
                return s.split( '\\' ).join( '' );
        }

        /**
        * static public method, change to upper case first char of a string
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function ucfirst( s:String ):String {
                return s.substr( 0, 1 ).toUpperCase() + s.substr( 1, s.length );
        }

        /**
        * static public method, switch to Upper Case all words in a string
        * @param        String  string to parse
        * @return       String  parsed string
        */
        static public function ucwords( s:String ):String {
                var sNew:Array = s.split( " " );
                for( var a:Number = 0; a < sNew.length; a++ ) {
                        sNew[a] = StringAdv.ucfirst( sNew[a] );
                }
                return sNew.join( " " );
        }

        /** EXPERIMENTAL ::
        * static public method, remove html tags from a string
        * @param        String  string to parse
        * @param        Object  string or array tags to leave
        * @return       String  parsed string
        */
        static public function strip_tags( s:String, allow:Object ):String {
                var allowable:Array;
                if( typeof(allow) == "string" ) {
                        allowable = new Array( allow );
                }
                else if( typeof(allow) == "array" ) {
                        allowable = Array( allow );
                }
                if( allowable.length != undefined ) {
                        var newAllowable:Array = new Array();
                        for( var a:Number = 0; a < allowable.length; a++ ) {
                                var closeTag:String = "</" + allowable[a].substr( 1, allowable[a].length )
                                newAllowable.push( closeTag );
                        }
                        allowable = allowable.concat( newAllowable );
                }
                var in_array:Function = function( who:Array, what:String ):Object {
                        // andr3a [ 25 / 03 / 2004 ]
                        // riadapted on 08/07/2004
                        // check if a value is inside an array
                        // EXAMPLE:
                        //      var myArray = new Array( "hello", "world", Array("one", "two") );
                        //      trace( myArray.in_array( "hello" ) ); // true
                        //      trace( myArray.in_array( "hi" ) ); // false
                        //      trace( myArray.in_array( "two" ) ); // true
                        for( var a = 0; a < who.length; a++ ) {
                                if( who[a] == what ) {
                                        return true;
                                }
                                else if( who[a] instanceof Array ) {
                                        return in_array( who[a], what );
                                }
                        }
                        return false;
                }
                var removeTags:Function = function( s:String, allowable:Array, allow:Object ):String {
                        var sNew:Array = s.split( "<" );
                        var modified:Boolean = new Boolean( false );
                        for( var a:Number = 0; a < sNew.length; a++ ) {
                                if( StringAdv.pos( sNew[a], ">" ) !== false ) {
                                        var htmlTag:String = "<" + sNew[a].substr( 0, StringAdv.pos( sNew[a], ">" ) ) + ">";
                                        if( htmlTag != "<>" ) {
                                                if( ( allowable.length != undefined && in_array( allowable, htmlTag ) == false ) || allow == undefined ) {
                                                        sNew[a] = sNew[a].substr( StringAdv.pos( sNew[a], ">" ) + 1, sNew[a].length );
                                                        modified = true;
                                                }
                                                else {
                                                        sNew[a] = "<" + sNew[a];
                                                }
                                        }
                                }
                        }
                        s = sNew.join( "" );
                        if( modified == true ) {
                                return removeTags( s, allowable, allow );
                        }
                        return s;
                }
                return removeTags( s, allowable, allow );
        }

        /**
        * function md5 - AS2.0 - porting from original Paul Johnston JavaScript functions
        * This is just a porting from Paul Johnston's JavaScript md5 implementation.
        * Porting created by Andrea Giammarchi [ andr3a ] [ www.3site.it ] on 05/07/2004
        *
        * Version 1.0 Copyright (C) Andrea Giammarchi 2004
        *
        * NOTE 1: this class is compatible with Flash Player 6 r65
        * NOTE 2: this is not an official class, use it carefully
        * ------------------------------------------------------------------------------
        *
        * [ ORIGINAL JAVASCRIPT COPYRIGHT AND SIGNATURE ]
        * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
        * Digest Algorithm, as defined in RFC 1321.
        * Version 2.1 Copyright (C) Paul Johnston 1999 - 2002.
        * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
        * Distributed under the BSD License
        * See http://pajhome.org.uk/crypt/md5 for more info.
        * ------------------------------------------------------------------------------
        */

        /**
        * static public method, convert string in md5 hash and return them.
        * @param        String  string to hash in md5
        * @param        String  base-64 pad character. "=" for strict RFC compliance
        * @param        Number  bits per input character. 8 - ASCII; 16 - Unicode
        * @return       String  md5 hashed string
        */
        static public function md5( s:String, b64pad:String, chrsz:Number ):String {
                if( b64pad != undefined ) {
                        StringAdv.b64pad = b64pad;
                }
                if( chrsz != undefined && chrsz == 8 || chrsz == 16 ) {
                        StringAdv.chrsz = chrsz;
                }
                return StringAdv.hex_md5( s );
        }

        /**
        * These are the functions you'll usually want to call
        * They take string arguments and return either hex or base-64 encoded strings
        */
        private static function hex_md5( s:String ):String {
                return StringAdv.binl2hex(core_md5(str2binl(s), s.length*StringAdv.chrsz));
        }
        private static function b64_md5( s:String ):String {
                return StringAdv.binl2b64(core_md5(str2binl(s), s.length*StringAdv.chrsz));
        }
        private static function str_md5( s:String ):String {
                return StringAdv.binl2str(core_md5(str2binl(s), s.length*StringAdv.chrsz));
        }
        private static function hex_hmac_md5( key:String, data:String ):String {
                return StringAdv.binl2hex(core_hmac_md5(key, data));
        }
        private static function b64_hmac_md5( key:String, data:String ):String {
                return StringAdv.binl2b64(core_hmac_md5(key, data));
        }
        private static function str_hmac_md5( key:String, data:String ):String {
                return StringAdv.binl2str(core_hmac_md5(key, data));
        }

        /**
        * These functions implement the four basic operations the algorithm uses.
        */
        private static function md5_cmn( q:Number, a:Number, b:Number, x:Number, s:Number, t:Number ):Number {
                return StringAdv.safe_add(bit_rol(safe_add(safe_add(a, q), safe_add(x, t)), s), b);
        }
        private static function md5_ff( a:Number, b:Number, c:Number, d:Number, x:Number, s:Number, t:Number ):Number {
                return StringAdv.md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);
        }
        private static function md5_gg( a:Number, b:Number, c:Number, d:Number, x:Number, s:Number, t:Number ):Number {
                return StringAdv.md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);
        }
        private static function md5_hh( a:Number, b:Number, c:Number, d:Number, x:Number, s:Number, t:Number ):Number {
                return StringAdv.md5_cmn(b ^ c ^ d, a, b, x, s, t);
        }
        private static function md5_ii( a:Number, b:Number, c:Number, d:Number, x:Number, s:Number, t:Number ):Number {
                return StringAdv.md5_cmn(c ^ (b | (~d)), a, b, x, s, t);
        }

        /**
        * Calculate the MD5 of an array of little-endian words, and a bit length
        */
        private static function core_md5( x:Array, len:Number ):Array {
                x[len >> 5] |= 0x80 << ((len)%32);
                x[(((len+64) >>> 9) << 4)+14] = len;
                var a:Number = 1732584193;
                var b:Number = -271733879;
                var c:Number = -1732584194;
                var d:Number = 271733878;
                for( var i:Number = 0; i < x.length; i += 16 ) {
                        var olda:Number = a;
                        var oldb:Number = b;
                        var oldc:Number = c;
                        var oldd:Number = d;
                        a = md5_ff(a, b, c, d, x[i+0], 7, -680876936);
                        d = md5_ff(d, a, b, c, x[i+1], 12, -389564586);
                        c = md5_ff(c, d, a, b, x[i+2], 17, 606105819);
                        b = md5_ff(b, c, d, a, x[i+3], 22, -1044525330);
                        a = md5_ff(a, b, c, d, x[i+4], 7, -176418897);
                        d = md5_ff(d, a, b, c, x[i+5], 12, 1200080426);
                        c = md5_ff(c, d, a, b, x[i+6], 17, -1473231341);
                        b = md5_ff(b, c, d, a, x[i+7], 22, -45705983);
                        a = md5_ff(a, b, c, d, x[i+8], 7, 1770035416);
                        d = md5_ff(d, a, b, c, x[i+9], 12, -1958414417);
                        c = md5_ff(c, d, a, b, x[i+10], 17, -42063);
                        b = md5_ff(b, c, d, a, x[i+11], 22, -1990404162);
                        a = md5_ff(a, b, c, d, x[i+12], 7, 1804603682);
                        d = md5_ff(d, a, b, c, x[i+13], 12, -40341101);
                        c = md5_ff(c, d, a, b, x[i+14], 17, -1502002290);
                        b = md5_ff(b, c, d, a, x[i+15], 22, 1236535329);
                        a = md5_gg(a, b, c, d, x[i+1], 5, -165796510);
                        d = md5_gg(d, a, b, c, x[i+6], 9, -1069501632);
                        c = md5_gg(c, d, a, b, x[i+11], 14, 643717713);
                        b = md5_gg(b, c, d, a, x[i+0], 20, -373897302);
                        a = md5_gg(a, b, c, d, x[i+5], 5, -701558691);
                        d = md5_gg(d, a, b, c, x[i+10], 9, 38016083);
                        c = md5_gg(c, d, a, b, x[i+15], 14, -660478335);
                        b = md5_gg(b, c, d, a, x[i+4], 20, -405537848);
                        a = md5_gg(a, b, c, d, x[i+9], 5, 568446438);
                        d = md5_gg(d, a, b, c, x[i+14], 9, -1019803690);
                        c = md5_gg(c, d, a, b, x[i+3], 14, -187363961);
                        b = md5_gg(b, c, d, a, x[i+8], 20, 1163531501);
                        a = md5_gg(a, b, c, d, x[i+13], 5, -1444681467);
                        d = md5_gg(d, a, b, c, x[i+2], 9, -51403784);
                        c = md5_gg(c, d, a, b, x[i+7], 14, 1735328473);
                        b = md5_gg(b, c, d, a, x[i+12], 20, -1926607734);
                        a = md5_hh(a, b, c, d, x[i+5], 4, -378558);
                        d = md5_hh(d, a, b, c, x[i+8], 11, -2022574463);
                        c = md5_hh(c, d, a, b, x[i+11], 16, 1839030562);
                        b = md5_hh(b, c, d, a, x[i+14], 23, -35309556);
                        a = md5_hh(a, b, c, d, x[i+1], 4, -1530992060);
                        d = md5_hh(d, a, b, c, x[i+4], 11, 1272893353);
                        c = md5_hh(c, d, a, b, x[i+7], 16, -155497632);
                        b = md5_hh(b, c, d, a, x[i+10], 23, -1094730640);
                        a = md5_hh(a, b, c, d, x[i+13], 4, 681279174);
                        d = md5_hh(d, a, b, c, x[i+0], 11, -358537222);
                        c = md5_hh(c, d, a, b, x[i+3], 16, -722521979);
                        b = md5_hh(b, c, d, a, x[i+6], 23, 76029189);
                        a = md5_hh(a, b, c, d, x[i+9], 4, -640364487);
                        d = md5_hh(d, a, b, c, x[i+12], 11, -421815835);
                        c = md5_hh(c, d, a, b, x[i+15], 16, 530742520);
                        b = md5_hh(b, c, d, a, x[i+2], 23, -995338651);
                        a = md5_ii(a, b, c, d, x[i+0], 6, -198630844);
                        d = md5_ii(d, a, b, c, x[i+7], 10, 1126891415);
                        c = md5_ii(c, d, a, b, x[i+14], 15, -1416354905);
                        b = md5_ii(b, c, d, a, x[i+5], 21, -57434055);
                        a = md5_ii(a, b, c, d, x[i+12], 6, 1700485571);
                        d = md5_ii(d, a, b, c, x[i+3], 10, -1894986606);
                        c = md5_ii(c, d, a, b, x[i+10], 15, -1051523);
                        b = md5_ii(b, c, d, a, x[i+1], 21, -2054922799);
                        a = md5_ii(a, b, c, d, x[i+8], 6, 1873313359);
                        d = md5_ii(d, a, b, c, x[i+15], 10, -30611744);
                        c = md5_ii(c, d, a, b, x[i+6], 15, -1560198380);
                        b = md5_ii(b, c, d, a, x[i+13], 21, 1309151649);
                        a = md5_ii(a, b, c, d, x[i+4], 6, -145523070);
                        d = md5_ii(d, a, b, c, x[i+11], 10, -1120210379);
                        c = md5_ii(c, d, a, b, x[i+2], 15, 718787259);
                        b = md5_ii(b, c, d, a, x[i+9], 21, -343485551);
                        a = safe_add(a, olda);
                        b = safe_add(b, oldb);
                        c = safe_add(c, oldc);
                        d = safe_add(d, oldd);
                }
                return Array(a, b, c, d);
        }

        /**
        * Calculate the HMAC-MD5, of a key and some data
        */
        private static function core_hmac_md5( key:String, data:String ):Array {
                var bkey:Array = new Array( str2binl( key ) );
                if( bkey.length > 16 ) {
                        bkey = core_md5(bkey, key.length*StringAdv.chrsz);
                }
                var ipad:Array = new Array(16)
                var opad:Array = new Array(16);
                for( var i:Number = 0; i < 16; i++ ) {
                        ipad[i] = bkey[i] ^ 0x36363636;
                        opad[i] = bkey[i] ^ 0x5C5C5C5C;
                }
                var hash:Array = new Array( core_md5( ipad.concat( str2binl( data  )), 512 + data.length*StringAdv.chrsz ) );
                return StringAdv.core_md5(opad.concat(hash), 512+128);
        }

        /**
        * Add integers, wrapping at 2^32. This uses 16-bit operations internally
        * to work around bugs in some JS interpreters.
        */
        private static function safe_add( x:Number, y:Number ):Number {
                var lsw:Number = new Number( (x & 0xFFFF) + (y & 0xFFFF) );
                var msw:Number = new Number( (x >> 16) + (y >> 16) + (lsw >> 16) );
                return (msw << 16) | (lsw & 0xFFFF);
        }

        /**
        * Bitwise rotate a 32-bit number to the left.
        */
        private static function bit_rol( num:Number, cnt:Number ):Number {
                return (num << cnt) | (num >>> (32-cnt));
        }

        /**
        * Convert a string to an array of little-endian words
        * If StringAdv.chrsz is ASCII, characters >255 have their hi-byte silently ignored.
        */
        private static function str2binl( str:String ):Array {
                var bin:Array = new Array();
                var mask:Number = ( 1 << StringAdv.chrsz ) - 1;
                for( var i:Number = 0; i < str.length * StringAdv.chrsz; i += StringAdv.chrsz ) {
                        bin[i >> 5] |= (str.charCodeAt(i/StringAdv.chrsz) & mask) << (i%32);
                }
                return bin;
        }

        /**
        * Convert an array of little-endian words to a string
        */
        private static function binl2str( bin:Array ):String {
                var str:String = new String( "" );
                var mask:Number = ( 1 << StringAdv.chrsz )-1;
                for( var i:Number = 0; i < bin.length * 32; i += StringAdv.chrsz ) {
                        str += String.fromCharCode( ( bin[i >> 5] >>> ( i % 32 ) ) & mask );
                }
                return str;
        }

        /**
        * Convert an array of little-endian words to a hex string.
        */
        private static function binl2hex( binarray:Array ):String {
                var hex_tab:String = "0123456789abcdef";
                var str:String = new String( "" );
                for( var i:Number = 0; i < binarray.length * 4; i++ ) {
                        str += hex_tab.charAt( ( binarray[i>>2] >> ( ( i%4 ) * 8 + 4 ) ) & 0xF ) +
                        hex_tab.charAt( ( binarray[i>>2] >> ( ( i%4 ) * 8  ) ) & 0xF );
                }
                return str;
        }

        /**
        * Convert an array of little-endian words to a base-64 string
        */
        private static function binl2b64( binarray:Array ):String {
                var tab:String = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
                var str:String = new String( "" );
                for( var i:Number = 0; i < binarray.length * 4; i += 3 ) {
                        var triplet:Object = (((binarray[i   >> 2] >> 8 * ( i   %4)) & 0xFF) << 16)
                        | (((binarray[i+1 >> 2] >> 8 * ((i+1)%4)) & 0xFF) << 8 )
                        | ((binarray[i+2 >> 2] >> 8 * ((i+2)%4)) & 0xFF);
                        for(var j:Number = 0; j < 4; j++) {
                                if( i * 8 + j * 6 > binarray.length * 32 ) {
                                        str += StringAdv.b64pad;
                                }
                                else {
                                        str += tab.charAt( ( triplet >> 6 * ( 3 - j ) ) & 0x3F );
                                }
                        }
                }
                return str;
        }

}