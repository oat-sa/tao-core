/*
dates utilities
*/
class utils.date
{
        /*
                @method GetDaysDiff
                @param Date ela date from check difference
                @param Date elb optional. if this param is omitted "today" will be used
                @return Number days difference between 2 dates

        */
        static public function GetDaysDiff(ela:Date, elb:Date):Number{
                if(elb == undefined){
                        elb = new Date();
                }
                var timeDiff:Number = ela.getTime() - elb.getTime()
                var daysDiff:Number = Math.ceil(((((timeDiff)/1000)/60)/60)/24)
                return daysDiff;
        }
}