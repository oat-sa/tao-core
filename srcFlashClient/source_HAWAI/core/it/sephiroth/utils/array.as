class utils.array
{
        /* offset copy */
        static function offset (anArray:Array, amount:Number):Array
        {
                amount = -amount % anArray.length;
                return (amount) ? anArray.slice (amount).concat (anArray.slice (0, amount)) : anArray.slice ();
        }
        /* simple array copy */
        static function copy (oldArray:Array):Array
        {
                var tmp:Array;
                tmp = oldArray.slice (0, oldArray.length - 1);
                return tmp;
        }
        /* mirror copy */
        static function shadow_copy (oldArray:Object):Object
        {
                var tmp:Array = new Array ();
                for (var a in oldArray) {
                        if (typeof oldArray[a] == "array" or typeof oldArray[a] == "object") {
                                tmp[a] = utils.array.shadow_copy (oldArray[a]);
                        } else {
                                tmp[a] = oldArray[a];
                        }
                }
                return tmp;
        }
        /* Checks if a value exists in an array */
        static function in_array (arr:Array, search:Object, strict:Boolean, recursive:Boolean):Boolean
        {
                var found:Boolean = false;
                for (var i = 0; i < arr.length; i++) {
                        if (arr[i].length != undefined and (typeof arr[i] != "string") and recursive == true) {
                                var res = utils.array.in_array (arr[i], search, strict, true);
                                if(res == true){
                                        return true
                                }
                        } else {
                                if (strict) {
                                        if (arr[i] === search) {
                                                return true;
                                        }
                                } else {
                                        if (arr[i] == search) {
                                                return true;
                                        }
                                }
                        }
                }
                return false;
        }
        /* Searches for something in an array and returns the index of the first occurence*/
        static function indexOf (arr:Array, search:Object):Number
        {
                for (var i = 0; i < arr.length; i++)
                {
                        if (arr[i] == search) {
                                return i;
                        }
                }
                return -1;
        }

        /* Searches for something in an array and returns the index of the last occurence*/
        static function lastIndexOf (arr:Array, search:Object):Number
        {
                for (var i = arr.length -1; i > -1; i--)
                {
                        if (arr[i] == search)
                        {
                                return i;
                        }
                }
                return -1;
        }
}