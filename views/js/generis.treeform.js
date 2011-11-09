/**
 * GenerisTreeFormClass is a easy to use container for the checkbox tree widget, 
 * it provides the common behavior for a selectable Class/Instance Rdf resource tree
 * 
 * @example new GenerisTreeClass('#tree-container', 'myData.php', {});
 * @see GenerisTreeClass.defaultOptions for options example
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require jstree = 0.9.9 [http://jstree.com/]
 * 
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */ 


/**
 * Constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {String} dataUrl the url to call, it must provide the json data to populate the tree 
 * @param {Object} options
 */
function GenerisTreeFormClass(selector, dataUrl, options){
	try{
		//jsquery selector of the tree
		this.selector = selector;
		//options
		this.options = options;
		//Url used to get tree data
		this.dataUrl = dataUrl;
		//Store meta data of opened classes
		this.metaClasses = new Array();
		//Keep a reference of the last opened node
		this.lastOpened = null;
		//Checked nodes memory
		this.checkedNodes = (typeof options.checkedNodes != "undefined") ? options.checkedNodes.slice(0) : new Array ();
		//Paginate the tree or not
		this.paginate = typeof options.paginate != 'undefined' ? options.paginate : 0;
		//Options to pass to the server
		this.serverParameters = (typeof options.serverParameters != "undefined") ? options.serverParameters : new Array ();
		//Default server parameters
		this.defaultServerParameters = {
			hideInstances:  this.options.hideInstances | false,
			filter: 		$("#filter-content-" + options.actionId).val(),
			offset:			0,
			limit:			this.options.paginate
		};		
		// Global access of the instance in the sub scopes
		var instance = this;
		
		this.treeOptions = {
			data: {
				type: "json",
				async : true,
				opts: {
					method : "POST",
					url: instance.dataUrl
				}
			},
			types: {
				"default" : {
					renameable	: false,
					deletable	: true,
					creatable	: true,
					draggable	: false
				}
			},
			ui: {
				theme_name : "checkbox"
			},
			callback: {
				//before check
				beforecheck:function(NODE, TREE_OBJ)
				{
					console.log('BEFORE CHECK '+instance.selector);
					var nodeId = $(NODE).attr('id');
					console.log('checked node '+nodeId);
					if(instance.isRefreshing){
						if($.inArray(nodeId, instance.checkedNodes) == -1){
							return false;
						}
					}
					if(NODE.hasClass('node-class')){
						if (instance.getMeta (nodeId, 'displayed')!=instance.getMeta (nodeId, 'count')){
							instance.paginateInstances (NODE, TREE_OBJ, {limit:0, checkedNodes:"*"});
							return false;
						}
					} 
					else {
						var indice = $.inArray(nodeId, instance.checkedNodes);
						if(indice == -1){
							console.log("ADD "+nodeId)
							instance.checkedNodes.push(nodeId);
						}else{
							// The widget should stop the propagation
							console.log('should stop the event propagation for this node');
							return true;
						}
						console.log(instance.checkedNodes);
					}
					return true;
				},
				//before check
				beforeuncheck:function(NODE, TREE_OBJ)
				{
					var nodeId = $(NODE).attr('id');
					console.log('BEFORE UNCHECK '+nodeId);
					var indice = $.inArray(nodeId, instance.checkedNodes);
					if(indice != -1){
						console.log(instance.checkedNodes);
						instance.checkedNodes.splice(indice,1);
						console.log(instance.checkedNodes);
						//delete instance.checkedNodes[indice];
					}
					return true;
				},
				//before open a branch
				beforeopen:function(NODE, TREE_OBJ)
				{
					console.log('BEFORE OPEN');
					instance.lastOpened = NODE;
				},
				//Before receive data from server, return the POST parameters
				beforedata:function(NODE, TREE_OBJ)
				{
					console.log('BEFORE DATA');
					var returnValue = instance.defaultServerParameters;
					//If a NODE is given, send its identifier to the server
					if(NODE){
						returnValue['classUri'] = $(NODE).attr('id');
					}
					//Augment with the serverParameters
					for (var key in instance.serverParameters){
						if(instance.serverParameters[key] != null){
							returnValue[key] = instance.serverParameters[key];
						}
					}
					//Augment with the selected nodes
					returnValue['selected'] = instance.checkedNodes;
					return returnValue;
				},
				//
				onopen: function (NODE, TREE_OBJ)
				{
					console.log('ON OPEN');
					if(instance.checkedNodes){
						console.log('CHECK NODES ON OPEN');
						instance.check(instance.checkedNodes);
					}
				},
				//
				onload: function(TREE_OBJ)
				{
					console.log('ONLOAD '+instance.selector);
					//console.log(instance.checkedNodes);
					if(instance.checkedNodes.length){
						console.log('CHECK NODES ON LOAD');
						console.log(instance.checkedNodes);
						instance.check(instance.checkedNodes);
					}
					if(instance.options.loadCallback){
						instance.options.loadCallback();
					}
					console.log('LOADED '+instance.selector)

					instance.isRefreshing = false;
					
				},
				onchange: function(NODE, TREE_OBJ)
				{
					console.log('ON CHANGE '+instance.isRefreshing);
					if(instance.options.onChangeCallback && !instance.isRefreshing){
						instance.options.onChangeCallback(NODE, TREE_OBJ);
					}
				},
				//when a node is selected
				onselect: function(NODE, TREE_OBJ)
				{
					if($(NODE).hasClass('paginate-more')) {
						instance.paginateInstances ($(NODE).parent().parent(), TREE_OBJ);
						return;
					}
					if($(NODE).hasClass('paginate-all')) {
						var parentNodeId = $(NODE).parent().parent().attr('id');
						var limit = instance.getMeta (parentNodeId, 'count') - instance.getMeta (parentNodeId, 'displayed');
						instance.paginateInstances ($(NODE).parent().parent(), TREE_OBJ, {'limit':limit});
						return;
					}

					var nodeId = $(NODE).attr('id');
					console.log('ON SELECT '+nodeId+' IN '+instance.selector);
/*
					console.log('SELECT '+nodeId);
					
					var indice = $.inArray(nodeId, instance.checkedNodes);
					if(indice == -1){
						console.log('ADD TO VARS 2');
						instance.checkedNodes.push(nodeId);
					}else{
						console.log('DEL TO VARS 2');
						delete instance.checkedNodes[indice];
					}
*/
					return true;
				},
				//
				ondata: function(DATA, TREE_OBJ)
				{
					console.log('ON DATA '+instance.selector);
					console.log(DATA);
					//automatically open the children of the received node
					if(DATA.children){
						DATA.state = 'open';
					}
					//extract meta data from children
					instance.extractMeta (DATA);
					return DATA;
				}
			},
			plugins : {
				checkbox : { three_state : true }
			}
		};
		
		//Add server parameters to the treeOptions variable
		for (var i in this.serverParameters){
			this.treeOptions.data.opts[i] = this.serverParameters[i];
		}
		
		//create the tree
		$(selector).tree(this.treeOptions);
		
		$("#saver-action-" + this.options.actionId).click(function(){
			instance.saveData();
		});
	}
	catch(exp){
		console.log(exp);
	}
}

/*
 * 
 * DEFINE CONSTANT
 *
 */

function trace(){
	console.log('TRACE '+
			arguments.callee.caller
			.arguments.callee.caller
			.arguments.callee.caller
			.arguments.callee.caller
	);
}

/**
 * Display priority DISPLAY_SELECTED.
 * Display in priority the previously selected instances ..
 */
GenerisTreeFormClass.DISPLAY_SELECTED = 1;

/*
 * 
 * DEFINE GENERIS TREE FORM CLASS FUNCTIONS
 * 
 */

/**
 * Extract meta data from received data
 */
GenerisTreeFormClass.prototype.extractMeta = function(DATA) {
	var nodes = new Array ();
	var nodeId = null;
	var instance = this;
	
	/**
	 * Create meta from class node
	 * @private
	 */
	function createMeta (meta) {
		instance.metaClasses[meta.id] = {
			displayed :  meta.displayed ? meta.displayed :0			// Total of elements displayed
			, count :     meta.count ? meta.count :0				// Total of elements in the class
			, position :  meta.position ? meta.position :0			// Position of the last element displayed
		};
	}
	
	//An object is received
	if ( !(DATA instanceof Array) ){
		nodeId = DATA.attributes.id;
		if (typeof DATA.children != 'undefined'){
			nodes = DATA.children;
		}
		createMeta ({id:DATA.attributes.id, count:DATA.count});
	}
	//An array of nodes is received
	else {
		// Get the last opened node
		if (this.lastOpened){
			nodeId = this.lastOpened.id;
		} else {
			nodeId = "DEFAULT_ROOT";
			createMeta ({id:nodeId, count:0});
		}
		nodes = DATA;
	}
	
	//Extract meta from children
	if (nodes) {
		//Number of classes found
		var countClass =0;
		for (var i=0; i<nodes.length; i++) {
			// if the children is a class, create meta for this class
			if (nodes[i].type == 'class'){
				this.extractMeta (nodes[i]);
				countClass++;
			}
		}
		var countInstances = nodes.length - countClass;
		this.setMeta (nodeId, 'position', countInstances); // Position of the last element displayed
		this.setMeta (nodeId, 'displayed',countInstances); // Total of elements displayed
		
		if (!(DATA instanceof Array) && DATA.state && DATA.state != 'closed'){
			if (this.getMeta(nodeId, 'displayed') < this.getMeta(nodeId, 'count')){
				nodes.push(instance.getPaginateActionNodes());
			}
		} else if ((DATA instanceof Array) && this.getMeta(nodeId, 'displayed') < this.getMeta(nodeId, 'count')){
			nodes.push(instance.getPaginateActionNodes());
		}
	}
}

/**
 * Set a server parameter
 * @param {string} key
 * @param {string} value
 * @param {boolean} reload Reload the tree after parameter updated
 */
GenerisTreeFormClass.prototype.setServerParameter = function(key, value, reload)
{
	this.serverParameters[key] = value;
	if (typeof(reload)!='undefined' && reload){
		this.isRefreshing = true;
		this.getTree().refresh();
	}
}

/**
 * get the tree reference
 * @return tree
 */
GenerisTreeFormClass.prototype.getTree = function()
{
	return $.tree.reference(this.selector);
}

/**
 * Get node's meta data
 */
GenerisTreeFormClass.prototype.getMeta = function(classId, metaName, value) 
{
	return this.metaClasses[classId][metaName];
}

/**
 * Set node's meta data
 */
GenerisTreeFormClass.prototype.setMeta = function(classId, metaName, value) 
{
	this.metaClasses[classId][metaName] = value;
}

/**
 * Get paginate nodes
 * @return {array}
 */
GenerisTreeFormClass.prototype.getPaginateActionNodes = function() 
{
	returnValue = [{	
		'data' : __('all')
			, 'attributes' : { 'class':'paginate paginate-all' }
		},{	
			'data' : this.paginate+__(' next')
			, 'attributes' : { 'class':'paginate paginate-more' }
		}];
	return returnValue;
}

/**
 * Show paginate options
 * @param NODE
 * @param TREE_OBJ
 * @private
 */
GenerisTreeFormClass.prototype.showPaginate = function (NODE, TREE_OBJ)
{
	var DATA = this.getPaginateActionNodes();
	for (var i=0; i<DATA.length; i++){
		TREE_OBJ.create(DATA[i], TREE_OBJ.get_node(NODE[0]));
	}
}

/**
 * Hide paginate options
 * @param NODE
 * @param TREE_OBJ
 * @private
 */
GenerisTreeFormClass.prototype.hidePaginate  = function (NODE, TREE_OBJ)
{
	$(NODE).find('.paginate').each(function(){
		$(this).remove();
	});
}

/**
 * Refresh pagination, hide and show if required
 * @param NODE
 * @param TREE_OBJ
 * @private
 */
GenerisTreeFormClass.prototype.refreshPaginate  = function (NODE, TREE_OBJ)
{
	var nodeId = $(NODE)[0].id;
	this.hidePaginate (NODE, TREE_OBJ);
	if (this.getMeta(nodeId, "displayed") < this.getMeta(nodeId, "count")){
		this.showPaginate (NODE, TREE_OBJ);
	}
}

/**
 * Paginate function, display more instances
 */
GenerisTreeFormClass.prototype.paginateInstances = function(NODE, TREE_OBJ, pOptions, callback)
{
	console.log('CA PAGINATE LA');
	var instance = this;
	var nodeId = NODE[0].id;
	var instancesLeft = instance.getMeta(nodeId, "count") - instance.getMeta(nodeId, "displayed");
	var options = {
		"classUri":		nodeId,
		"subclasses": 	0,
		"offset": 		instance.getMeta(nodeId, "position"),
		"limit":		instancesLeft < this.paginate ? instancesLeft : this.paginate
	};
	options = $.extend(options, pOptions);
	$.post(this.dataUrl, options, function(DATA){
		//Hide paginate options
		instance.hidePaginate(NODE, TREE_OBJ);
		//Display incoming nodes
		for (var i=0; i<DATA.length; i++){
			DATA[i].attributes['class'] = instance.options.instanceClass+" node-instance node-draggable";
			TREE_OBJ.create(DATA[i], TREE_OBJ.get_node(NODE[0]));
			// If the check all options. Add the incoming nodes to the list of node to check
			if (options.checkedNodes == "*"){
				console.log('check nodes on paginate *');
				instance.checkedNodes.push (DATA[i].attributes.id);
			}
		}
		// Update meta data
		instance.setMeta(nodeId, "displayed", instance.getMeta(nodeId, "displayed")+DATA.length);
		instance.setMeta(nodeId, "position", instance.getMeta(nodeId, "position")+DATA.length);
		//refresh pagination options
		instance.refreshPaginate(NODE, TREE_OBJ);
		
		//If options checked nodes
		if (options.checkedNodes){
			// If options check all, check not checked nodes
			if (options.checkedNodes == "*"){
				$(NODE).find('ul:first').children().each(function(){
					if ($(this).hasClass('node-instance')) {
						$(this).find("a:not(.checked, .undetermined)").each (function () {
							console.log('check nodes on paginate');
							instance.checkedNodes.push($(this).parent().attr('id'));
						});
					}					
				});
			} else {
				instance.checkedNodes = options.checkedNodes;
			}
		}
		console.log('CA CHECK ENCORE LA');
		instance.check(instance.checkedNodes);
		
		//Execute callback;
		if (callback){
			callback (NODE, TREE_OBJ);
		}
	}, "json");
};

/**
 * Check the tree instances
 * @param {Array} elements the list of ids of instances to check
 */
GenerisTreeFormClass.prototype.check = function(elements)
{
	console.log('LE MANUEL CHECK DE LA MORTE');
	var self = this;
	$.each(elements, function(i, elt){
		if(elt != null){
			NODE = $(self.selector).find("li[id="+elt+"]");
			if(NODE.length > 0){
				if($(NODE).hasClass('node-instance'))
					console.log('le roublard de chck '+elt);
					$.tree.plugins.checkbox.check(NODE);
			}
		}
	});
}

/**
 * Get the checked nodes
 * @return {array}
 */
GenerisTreeFormClass.prototype.getChecked = function () 
{
	var returnValue = new Array ();
	$.each($.tree.plugins.checkbox.get_checked(this.getTree()), function(i, NODE){
		if ($(NODE).hasClass('node-instance')) {
			returnValue.push( $(NODE).attr('id') );
		}
	});
	return returnValue;
}


/**
 * save the checked instances in the tree by sending the ids using an ajax request
 */
GenerisTreeFormClass.prototype.saveData = function()
{
	console.log('SAVE');
	loading();
	var instance = this;
	var toSend = {};
	var index = 0;
	/*$.each($.tree.plugins.checkbox.get_checked(this.getTree()), function(i, NODE){
		if ($(NODE).hasClass('node-instance')) {
			toSend2['instance_' + index2] = $(NODE).attr('id');
			index2++;
		}
	});*/
	for(var i in instance.checkedNodes){
		toSend['instance_'+index] = instance.checkedNodes[i];
		index++;
	}
	
	if(this.options.relatedFormId){
		uriField = $("#" + this.options.relatedFormId + " :input[name=uri]");
		classUriField = $("#" + this.options.relatedFormId + " :input[name=classUri]");
	}
	else{
		uriField = $("input[name=uri]");
		classUriField = $("input[name=classUri]");
	}
	
	if (uriField) {
		toSend.uri = uriField.val();
	}
	
	if (classUriField) {
		toSend.classUri = classUriField.val();
	}
	
	$.ajax({
		url: this.options.saveUrl,
		type: "POST",
		data: toSend,
		dataType: 'json',
		success: function(response){
			if (response.saved) {
				if(instance.options.saveCallback){
					 instance.options.saveCallback(toSend);
				}
				createInfoMessage(__('Selection saved successfully'));
			}
		},
		complete: function(){
			loaded();
		}
	});
}