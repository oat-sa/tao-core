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
		this.checkedNodes = (typeof options.checkedNodes != "undefined") ? options.checkedNodes : new Array ();
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
			callback : {
				//before check
				beforecheck:function(NODE, TREE_OBJ){
					if(NODE.hasClass('node-class')){
						if (instance.metaClasses['displayed']!= instance.metaClasses['length']){
							instance.paginateInstances (NODE, TREE_OBJ, {limit:0, checkedNodes:"*"});
							return false;
						}
					} else {
						instance.checkedNodes.push($(NODE).attr('id'));
					}
					return true;
				},
				//before check
				beforeuncheck:function(NODE, TREE_OBJ){
					var nodeId = $(NODE).attr('id');
					for (var i in instance.checkedNodes){
						if (instance.checkedNodes[i] == nodeId){
							delete instance.checkedNodes[i];
						}
					}
					return true;
				},
				//before open a branch
				beforeopen:function(NODE, TREE_OBJ){
					instance.lastOpened = NODE;
				},
				//Before receive data from server, return the POST parameters
				beforedata:function(NODE, TREE_OBJ) {
					var returnValue = instance.defaultServerParameters;
					// If a NODE is given, send its identifier to the server
					if(NODE){
						returnValue['classUri'] = $(NODE).attr('id');
					}
					//Get the selected nodes, and store them
					//instance.checkedNodes = instance.getChecked();
					//Pass them to the server
					returnValue['selected'] = instance.checkedNodes;
					// Augment with the serverParameters
					for (var key in instance.serverParameters){
						returnValue[key] = instance.serverParameters[key];
					}
					
					return returnValue;
				},
				//
				onload: function(TREE_OBJ) {
					if(instance.checkedNodes){
						instance.check(instance.checkedNodes);
					}
					if(instance.options.loadCallback){
						instance.options.loadCallback();
					}
				},
				//when a node is selected
				onselect: function(NODE, TREE_OBJ){
					if($(NODE).hasClass('paginate-more')) {
						instance.paginateInstances ($(NODE).parent().parent(), TREE_OBJ);
					}
					else if($(NODE).hasClass('paginate-all')) {
						instance.paginateInstances ($(NODE).parent().parent(), TREE_OBJ, {limit:0});
					}
					return false;
				},
				//
				ondata: function(DATA, TREE_OBJ){
					// current node
					var currentNodeId = null;
					
					//Create meta from class node
					function createMeta (DATA) {
						instance.metaClasses[DATA.attributes.id] = {
							displayed : 0
							, length : DATA.count
						};
					}
					//Extract meta from class' children
					function extractMetaFromChildren (id, children){
						if (children) {
							for (var i=0; i<children.length; i++) {
								if (children[i].type == 'class'){
									createMeta (children[i]);
								} else {
									instance.metaClasses[id].displayed ++;
								}
							}
						}
					}
					
					//Extract meta data from server return
					//If data is an array -> The user open a branch (reverse engeeniring, maybe not the reality, take care)
					if (DATA instanceof Array) {
						currentNodeId = instance.lastOpened.id;
						extractMetaFromChildren (currentNodeId, DATA);
					} 
					else {
						currentNodeId = DATA.attributes.id;
						createMeta (DATA);
						extractMetaFromChildren (DATA.attributes.id, DATA.children);
					}
					
					if(DATA.children){
						DATA.state = 'open';
					}
					
					//Add Pagination actions if required
					if (instance.metaClasses[currentNodeId].displayed < instance.metaClasses[currentNodeId].length){
						var obj = DATA instanceof Array ? DATA : DATA.children;
						obj.push(instance.getPaginateActionNodes());
					}
					
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

/**
 * Set a server parameter
 * @param {string} key
 * @param {string} value
 * @param {boolean} reload Reload the tree after parameter updated
 */
GenerisTreeFormClass.prototype.setServerParameter = function (key, value, reload){

	this.serverParameters[key] = value;
	if (typeof (reload)!='undefined' && reload){
		this.getTree().refresh();
	}
}

/**
 * get the tree reference
 * @return tree
 */
GenerisTreeFormClass.prototype.getTree = function(){
	
	return $.tree.reference(this.selector);
}

/**
 * Get paginate nodes
 * @return {array}
 */
GenerisTreeFormClass.prototype.getPaginateActionNodes = function () {
	returnValue = [{	
		'data' : '/ &nbsp;&nbsp;&nbsp;all'
			, 'attributes' : { 'class':'paginate paginate-all' }
		},{	
			'data' : this.paginate+' more'
			, 'attributes' : { 'class':'paginate paginate-more' }
		}];
	return returnValue;
}

/**
 * Paginate function, display more instances
 */
GenerisTreeFormClass.prototype.paginateInstances = function(NODE, TREE_OBJ, pOptions, callback){
	var instance = this;
	
	/**
	 * Show paginate options
	 * @param NODE
	 * @param TREE_OBJ
	 * @private
	 */
	function showPaginate (NODE, TREE_OBJ){
		var DATA = instance.getPaginateActionNodes();
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
	function hidePaginate (NODE, TREE_OBJ){
		$(NODE).find('.paginate').each(function(){
			$(this).remove();
		});
	}

	var nodeId = NODE[0].id;
	var options = {
		"classUri":		nodeId,
		"subclasses": 	0,
		"offset": 		this.metaClasses[nodeId].displayed,
		"limit":		this.options.paginate
	};
	options = $.extend(options, pOptions);
	$.post(this.dataUrl, options, function(DATA){
		//Hide paginate options
		hidePaginate(NODE, TREE_OBJ);
		//Display incoming nodes
		for (var i=0; i<DATA.length; i++){
			DATA[i].attributes['class'] = instance.options.instanceClass+" node-instance node-draggable";
			TREE_OBJ.create(DATA[i], TREE_OBJ.get_node(NODE[0]));
			// If the check all options. Add the incoming nodes to the list of node to check
			if (options.checkedNodes == "*"){
				instance.checkedNodes.push (DATA[i].attributes.id);
			}
		}
		// Update meta data
		instance.metaClasses[nodeId].displayed += DATA.length;
		//If it rests some instances, show paginate options
		if (instance.metaClasses[nodeId].displayed < instance.metaClasses[nodeId].length){
			showPaginate(NODE, TREE_OBJ);
		}
		//If options checked nodes
		if (options.checkedNodes){
			// If options check all
			if (options.checkedNodes == "*"){
				$.tree.plugins.checkbox.get_unchecked(instance.getTree()).each(function(){
					instance.checkedNodes.push(this.id); // Add unchecked nodes to the nodes to check
				});
			} else {
				instance.checkedNodes = options.checkedNodes;
			}
		}
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
GenerisTreeFormClass.prototype.check = function(elements){
	
	$.each(elements, function(i, elt){
		if(elt != null){
			NODE = $("li[id="+elt+"]");
			if(NODE.length > 0){
				if($(NODE).hasClass('node-instance'))
					$.tree.plugins.checkbox.check(NODE);
			}
		}
	});
}

/**
 * Get the checked nodes
 * @return {array}
 */
GenerisTreeFormClass.prototype.getChecked = function () {
	
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
GenerisTreeFormClass.prototype.saveData = function(){
	
	loading();
	var instance = this;
	var toSend = {};
	var index = 0;
	$.each($.tree.plugins.checkbox.get_checked(this.getTree()), function(i, NODE){
		if ($(NODE).hasClass('node-instance')) {
			toSend['instance_' + index] = $(NODE).attr('id');
			index++;
		}
	});
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