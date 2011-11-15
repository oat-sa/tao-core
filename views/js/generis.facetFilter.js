/**
 * GenerisFacetFilterClass is a widget which provides easy screen to filter data by facet
 * 
 * @example new GenerisFacetFilterClass('#facetfilter-container', {});
 * @see GenerisFacetFilterClass.defaultOptions for options example
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * 
 * @author Alfonsi Cédric, <taosupport@tudor.lu>
 */

/**
 * The GenerisFacetFilterClass constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {Object} options {}
 */
function GenerisFacetFilterClass(selector, filterNodes, options)
{
	this.options = $.extend(true, {}, GenerisFacetFilterClass.defaultOptions, options);
	this.selector = selector;
	this.filterNodes = {};
	this.filterNodesOptions = {};
	this.trees = [];	
	
	//If filter nodes
	if(filterNodes){
		for(var i in filterNodes){
			this.filterNodes[filterNodes[i]['id']] = filterNodes[i];
			this.filterNodesOptions[filterNodes[i]['id']] = filterNodes[i].options;
		}
		this.renderLayout();
		for(var i in filterNodes){
			this.addFilterNode(filterNodes[i]);
		}
	}
}

/**
 * GenerisFacetFilterClass default options
 */
GenerisFacetFilterClass.defaultOptions = {
	template	:	'hbox'		// hbox, vbox, accordion
	, callback	: 
	{
		onFilter	: null
	} 
};

GenerisFacetFilterClass.prototype.renderLayout = function()
{
	var ouput = '';
	
	var ucfirst = this.options['template'][0].toUpperCase() + this.options['template'].slice(1)
	var templateAdapterName = 'GenerisFacetFilter'+ucfirst+'Adapter';
	if(typeof window[templateAdapterName] == 'undefined'){
		throw new Error('GenerisFacetFilterClass error : template '+templateAdapterName+' does not exist');
	}
	var templateAdapter = new window[templateAdapterName]();
	templateAdapter.render(this.selector, this.filterNodes);
};

/**
 * Add a filter node to the widget
 * 		=> create a tree
 */
GenerisFacetFilterClass.prototype.addFilterNode = function(filterNode)
{	
	var self = this;
	var filterNodeId = filterNode['id'];
	var filterNodeLabel = filterNode['label'];
	var filterNodeUrl = filterNode['url'];
	var filterNodeOptions = filterNode['options'];

	//instantiate the filter node widget, here a jstree
	
	//pass to the server options of others filter nodes
	var options = $.extend(true, {}, filterNodeOptions);
	var filterNodesOptions = $.extend(true, {}, this.filterNodesOptions);
	options['filterNodesOptions'] = filterNodesOptions;
	
	//instantiate the tree
	this.trees[filterNodeId] = new GenerisTreeFormClass('#tree-'+filterNodeId, filterNodeUrl, {
		'actionId'			: 'filter',
		'serverParameters' 	: options,
		'onChangeCallback' 	: function(NODE, TREE_OBJ)
		{
			self.propagateChoice();
		}
	});
};

/**
 * Propagate a choice
 */
GenerisFacetFilterClass.prototype.propagateChoice = function()
{
	var self = this;
	var filter = {};
	
	//get the checked nodes
	for(var treeId in this.trees){
		var checked = this.trees[treeId].getChecked();
		filter[treeId] = checked;
	}
	//refresh all trees with the new filter
	for (var treeId in this.trees){
		// Set the server parameter
		this.trees[treeId].setServerParameter('filter', filter, true);
	}
	//call the callback function
	if(this.options.callback.onFilter != null){
		this.options.callback.onFilter(filter, this.filterNodesOptions);
	}
};
