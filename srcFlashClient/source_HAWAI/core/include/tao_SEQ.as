// SEQUENCE METHODS *********************************************************************

function SEQUENTIAL_NEXT():Number
{
	trace("SEQUENTIAL sequence function entered for NEXT item with");

	var my_toolbox:tao_toolbox = new tao_toolbox();
	var nextItem_num:Number = -1;
	var returnedResult_num:Number = -1;

	var initialItems_array:Array = new Array();
	var passedItems_array:Array = new Array();
	var futureItems_array:Array = new Array();
	var currentItem_num:Number = -1;

	initialItems_array = arguments[0];
	passedItems_array = arguments[1];
	futureItems_array = arguments[2];
	currentItem_num = arguments[3];

	trace("           current index = " + currentItem_num);

	nextItem_num = currentItem_num + 1;
	var vMaxItems_num:Number = initialItems_array[0]["tao:TEST"][0]["tao:CITEM"].length;

// does next item exist?
	if(nextItem_num < vMaxItems_num){
		returnedResult_num = nextItem_num; // yes, we can continue with the next item
	}
	else{
		returnedResult_num = -1; // no, we continue with the result calculation
	}

	trace("           new index = " + returnedResult_num);

	return(returnedResult_num);
}