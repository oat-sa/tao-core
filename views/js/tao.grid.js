/**
 * TaoGridClass is an easy way to display tao grid with the jqGrid jquery widget 
 * 
 * @example new TaoGridClass('#grid-container', 'myData.php', {});
 * @see TaoGridClass.defaultOptions for options example
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require jqgrid = 4.1.0 [http://www.trirand.com/blog/]
 * 
 * @author Cédric Alfonsi, <taosupport@tao.lu>
 */ 


/**
 * Constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {Object} model the model of the grid as defined by the server side script (see 
 * @param {String} dataUrl the url to call, it must provide the json data to populate the tree 
 * @param {Object} options
 */
function TaoGridClass(selector, model, dataUrl, options)
{
	this.selector = selector;
	this.jqGrid = null;
	this.model = model;
	this.dataUrl = dataUrl;
	this.jqGridModel = new Array();
	this.jqGridColumns = new Array();
	this.adapters = new Array();		//adapters used in this grid
	this.options = $.extend([], TaoGridClass.defaultOptions, options);
	
	//Default dimension
	if(this.options.height==null){
		this.options.height = $(this.selector).parent().height();
	}
	if(this.options.width == null){
		this.options.width = $(this.selector).parent().width();
	}
	
	this.initModel();
	this.initGrid();
}

/**
 * Init the jqGridModel
 */
TaoGridClass.prototype.initModel = function()
{
	var columnsWeight = 0;
	var gridWidth = this.options.width - 42;
	
	//pre model analysis
	for(var id in this.model){
		var weight = typeof this.model[id]['weight'] != 'undefined' ? this.model[id]['weight'] : 1;
		columnsWeight += weight;
	}
	
	//jqgrid model
	var i = 0;
	for(var id in this.model){

		//add a column
		this.jqGridColumns[i] = this.model[id]['title']

		//add the model relative to the column
		this.jqGridModel[i] = {
			name		:this.model[id]['id']
			, index		:this.model[id]['id']
		};

		//a specific formatter has to be applied to the column
		if(typeof this.model[id]['widget'] != 'undefined' && this.model[id]['widget']!='Label'){
			//get adapter for the given widget
			var adapter = this.getAdapter(this.model[id]['widget']);
			//reference the adapter
			this.adapters[this.model[id]['id']] = adapter;
			//add adapter function to the column model options
			this.jqGridModel[i]['formatter'] = adapter.formatter;
		}

		//fix the width of the column functions of its weight
		var weight = typeof this.model[id]['weight'] != 'undefined' ? this.model[id]['weight'] : 1;
		var width = gridWidth * weight / columnsWeight;
		this.jqGridModel[i]['width'] = width;
		i++;
	}
}

/**
 * Init the grid
 */
TaoGridClass.prototype.initGrid = function()
{
	//if data url, data have to be formated to fit the following sample
	//	$test = array(
	//		"page"		=> 1
	//		, "total"	=> 1
	//		, "records"	=> 10
	//		, "rows" 	=> $returnValue
	//	);
	var self = this;
	this.jqGrid = $(this.selector).jqGrid({
		url			: this.dataUrl,
	    datatype	: "json",
	    mtype		: 'GET',
		colNames	: this.jqGridColumns, 
		colModel	: this.jqGridModel, 
		shrinkToFit	: true,
		//width		: parseInt($("#result-list").parent().width()) - 15, 
		//sortname	: 'id', 
		//sortorder	: "asc", 
		caption		: this.options.title,
		jsonReader: {
			repeatitems : false,
			id: "0"
		},
		height 		: this.options.height - 54,
		onSelectRow: function(id){
		    if(self.options.callback.onSelectRow != 'null'){
		    	self.options.callback.onSelectRow(id);
		    }
		}
		
	});
}

/**
 * Get formatter relative to a widget
 */
TaoGridClass.prototype.getAdapter = function(widget)
{
	var returnValue = null;
	var adapterClassName = 'TaoGrid'+widget+'Adapter';
	var adapterClass = window[adapterClassName];
	returnValue = adapterClass;
	
	return returnValue;
}

/**
 * Empty the grid
 */
TaoGridClass.prototype.empty = function()
{
	var gridBody = $(this.selector).children("tbody");
	var firstRow = gridBody.children("tr.jqgfirstrow");
	gridBody.empty().append(firstRow);
}

/**
 * Add data to the grid
 * @param {Array} data 
 */
TaoGridClass.prototype.add = function(data)
{
	var crtLine = 0;
	for(var id in data) {
		jQuery(this.selector).jqGrid('addRowData', id, data[id]);
		for(var columnId in this.adapters){
			if(typeof this.adapters[columnId].postCellFormat != 'undefined'){
				var cell = $('td[aria-describedby="'+this.selector.slice(1)+'_'+columnId+'"]').get(crtLine);
				this.adapters[columnId].postCellFormat(this, cell, crtLine, columnId);
			}
		}
		crtLine ++;
	}
}

/**
 * TaoGridClass default options
 */
TaoGridClass.defaultOptions = {
	'height' 	: null
	, 'width'	: null
	, 'title'	: 'GRID'
};
