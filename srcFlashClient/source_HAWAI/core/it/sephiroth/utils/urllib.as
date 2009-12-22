/**
* @class utils.urrlib
*/
class utils.urllib
{
        static public function OpenWin(url:String, features:String, centered:Boolean):Void
        {
                if(features == undefined)
                {
                        features = "";
                }
                if(centered == true && features != "")
                {
                        var left:Number;
                        var top:Number;
                        left = int(features.substring(features.indexOf("width=") + length("width="), features.indexOf(",", features.indexOf("width=")) == -1 ? features.length : features.indexOf(",", features.indexOf("width="))))
                        top = int(features.substring(features.indexOf("height=") + length("height="), features.indexOf(",", features.indexOf("height=")) == -1 ? features.length : features.indexOf(",", features.indexOf("height="))))
                        var x:Number = (System.capabilities.screenResolutionX - left) / 2;
                        var y:Number = (System.capabilities.screenResolutionY - top) / 2;
                        features += ",left=" add x add ",top=" add y;
                }
                getURL("javascript:void(window.open('" add url add "','','" add features add "'));");
        }
}