/**
 * This class is a grid column adapter used to format cells to fit specific needs
 * Download file

 * @see TaoGridClass
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * 
 * @author Alfonsi Cédric, <taosupport@tudor.lu>
 */

/**
 * The TaoGridDownloadAdapter constructor
 */
function TaoGridDownloadAdapter(){}

TaoGridDownloadAdapter.formatter = function(cellvalue, options, rowObject){
	var returnValue = '';
	
	returnValue = '<span>'+cellvalue+'</span>'
	
	return returnValue;
}

TaoGridDownloadAdapter.postCellFormat = function(grid, cell, rowId, columnId)
{
	var fileUri = $(cell).find('span').html();
	var fileVersion = grid.jqGrid.getCell(rowId, "xliff_version");
	console.log(fileVersion);
}
