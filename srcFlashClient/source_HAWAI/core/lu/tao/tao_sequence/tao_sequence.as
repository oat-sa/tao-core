// Sequence management file

import lu.tao.utils.tao_toolbox;

class lu.tao.tao_sequence.tao_sequence
{
	var sequenceIndex_num : Number;
	function tao_sequence ()
	{
		sequenceIndex_num = -1; //means that the test is finished
	}

	function getNextIndex(initialItems_array:Array, passedItems_array:Array, futureItems_array:Array, currentIndex_num:Number): Number
	{
		trace ("tao_sequence: getNextIndex entered");

		var my_toolbox:tao_toolbox = new tao_toolbox();

		var sequenceMethod_str:String = new String(initialItems_array[0]["tao:TEST"][0]["tao:HASSEQUENCEMODE"][0].data);

		var sequenceArgs_array:Array = new Array();
		sequenceArgs_array.push(initialItems_array);
		sequenceArgs_array.push(passedItems_array);
		sequenceArgs_array.push(futureItems_array);
		sequenceArgs_array.push(currentIndex_num);

		sequenceMethod_str += "_next";
		sequenceIndex_num = my_toolbox.run(sequenceMethod_str, sequenceArgs_array);

		return(sequenceIndex_num);
	}
}		
