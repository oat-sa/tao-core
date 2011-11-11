/**
 * This class is a grid column adapter used to format cells to fit specific needs

 * @see TaoGridClass
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * 
 * @author Alfonsi Cédric, <taosupport@tudor.lu>
 */

/**
 * The TaoGridClassCurrentActivitiesAdapter constructor
 */
function TaoGridCurrentActivitiesAdapter(){}

TaoGridCurrentActivitiesAdapter.formatter = function(cellvalue, options, rowObject){
	var returnValue = '';
	
	for(var i in cellvalue){
		returnValue += cellvalue[i]['http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsCurrentUser'] 
			+ ' - ' + cellvalue[i]['http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsExecutionOf'] + '<br/>' ;
	}
	returnValue += '</a>';
	
	return returnValue;
}
