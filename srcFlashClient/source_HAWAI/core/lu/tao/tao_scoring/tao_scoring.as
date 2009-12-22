 // evaluates the scoring of the test

import lu.tao.utils.tao_toolbox;

class lu.tao.tao_scoring.tao_scoring
{
	var scoring : String;
	function tao_scoring ()
	{
		scoring = "";
	}

	function calculateScoring(initialItems_array:Array, passedItems_array:Array, theta): String
	{
		trace ("tao_scoring: calculateScoring entered");

		var my_toolbox:tao_toolbox = new tao_toolbox();

		var scoringMethod_str:String = new String(initialItems_array[0]["tao:TEST"][0]["tao:HASSCORINGMETHOD"][0].data);

		var scoringArgs_array:Array = new Array();
		scoringArgs_array.push(initialItems_array);
		scoringArgs_array.push(passedItems_array);
		scoringArgs_array.push(theta);

		scoring = my_toolbox.run(scoringMethod_str, scoringArgs_array);

		return(scoring);
	}
}		
