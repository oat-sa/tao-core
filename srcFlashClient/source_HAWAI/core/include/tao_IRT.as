// SCORING METHODS *********************************************************************

function CLASSICAL_RATIO():String
{
	trace("CLASSICAL RATIO scoring function entered");

	var my_toolbox:tao_toolbox = new tao_toolbox();
	var returnedResult_str:String = new String("");
	var returnedResult_num:Number = new Number(0);

	var initialItems_array:Array = new Array();
	var passedItems_array:Array = new Array();
	var theta;

	initialItems_array = arguments[0];
	passedItems_array = arguments[1];
	theta = arguments[2];

	var cumulModel_str:String = new String(initialItems_array[0]["tao:TEST"][0]["tao:CUMULMODEL"][0].data);
	var actualCumulativeModel_str:String = new String("");
	var idealCumulativeModel_str:String = new String("");
	var resultScoringModel_str:String = new String("");

	var totWeight_num:Number = new Number(0);
	for(var vCpt=0; vCpt < passedItems_array.length; vCpt++){
		var currentWeight_str:String = new String("");
		currentWeight_str = initialItems_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt].attributes["weight"];
		currentWeight_str = ((currentWeight_str == undefined) || (isNaN(Number(currentWeight_str)))) ? "0" : currentWeight_str;
		totWeight_num += Number(currentWeight_str);
	}

    totWeight_num = (totWeight_num == 0)?1:totWeight_num; // to avoid a DIVIDE_BY_ZERO

	idealCumulativeModel_str = String(totWeight_num);

	actualCumulativeModel_str = my_toolbox.run(cumulModel_str, arguments);

trace("CLASSICAL RATIO: compute S(t):");
// S(t) = M(t,theta) / M_plus(t,theta)
//                where M is the applied cumul model at a given step t for a defined theta and
//                      M_plus is the perfect endorsement at a given step t for a defined theta
	resultScoringModel_str = "(" + actualCumulativeModel_str + ")/(" + idealCumulativeModel_str + ")";
	returnedResult_num = Number(my_toolbox.calculate(resultScoringModel_str));

	returnedResult_str = String((Math.round(returnedResult_num * 1000))/10); // to obtain aercentage
	returnedResult_str = returnedResult_str.concat("%"); // format scoring in percents

	return(returnedResult_str);
}

// CUMULATIVE MODELS *******************************************************************

function CLASSICAL():String
{
	trace("CLASSICAL cumul model function entered");

	var my_toolbox:tao_toolbox = new tao_toolbox();
	var returnedResult_str:String = new String("");

	var initialItems_array:Array = new Array();
	var passedItems_array:Array = new Array();
	var theta;

	initialItems_array = arguments[0];
	passedItems_array = arguments[1];
	theta = arguments[2];

	var logisticModel_str:String = ""
	logisticModel_str = initialItems_array[0]["tao:TEST"][0]["tao:CITEM"][1].attributes.model;
	logisticModel_str = (logisticModel_str == undefined) ? "discrete" : logisticModel_str;
	var resultCumulModel_str:String = new String("");
	var endorsement:String = new String("");
	var cItem;

// M(t,theta) = C(r(t)|theta) = SUM_i(d_i . P_i(theta))
//                where i is the step from 1 to the t item, 
//                      d_i is the corresponding endorsement and
//                      P_i is the corresponding probability of a right answer for a given theta ability
	for(var vCpt=0; vCpt < passedItems_array.length; vCpt++){

		endorsement = passedItems_array[vCpt].itemContext[0].itemEndorsmentsResult[0].data;
		cItem = initialItems_array[0]["tao:TEST"][0]["tao:CITEM"][vCpt];

		var argLogisticModel_array:Array = new Array();
		argLogisticModel_array.push(cItem);
		argLogisticModel_array.push(theta);

		resultCumulModel_str += "+(" + endorsement + "*" + my_toolbox.run(logisticModel_str, argLogisticModel_array) + ")";
	}

	returnedResult_str = resultCumulModel_str.substr(1);

	return(returnedResult_str);
}

// LOGISTIC MODELS *********************************************************************

// pseudo logistic model 'cause it returns discrete points and NOT a curve based on theta
function DISCRETE():String
{
	trace("DISCRETE points model function entered");

	var my_toolbox:tao_toolbox = new tao_toolbox();
	var returnedResult_str:String = new String("");

	var weight:String;

	var cItem = arguments[0];

	weight = cItem.attributes.weight;
	weight = ((weight == undefined) || (isNaN(Number(weight)))) ? "0" : weight;

	var resultLogisticModel_str:String = new String("");

// P_t(theta) = weight
//                where theta is the given ability and has nothing to do here and
//                      weight is the weight given to the item at moment t
	resultLogisticModel_str = weight;
	returnedResult_str = resultLogisticModel_str;
	return(returnedResult_str);
}
