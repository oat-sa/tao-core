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
		this.selector = selector;
		this.options = options;
		//Url used to get tree data
		this.dataUrl = dataUrl;
		//Store meta data of opened classes
		this.metaClasses = new Array();
		//Keep a reference of the last opened node
		this.lastOpened = null;
		//Get paginate options
		if (typeof options.paginate != 'undefined'){
			this.paginate = options.paginate;
		} else {
			this.paginate = 0;
		}
		var instance = this;
		
		this.treeOptions = {
			data: {
				type: "json",
				async : true,
				opts: {
					method : "POST",
					url: instance.dataUrl,
					paginate: this.paginate
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
				//before open a branch
				beforeopen:function(NODE, TREE_OBJ){
					instance.lastOpened = NODE;
				},
				//Before get data from server
				beforedata:function(NODE, TREE_OBJ) { 
					if(NODE){
						return {
							classUri: $(NODE).attr('id'),
							selected: instance.options.checkedNodes,
							offset:			0,
							limit:			instance.options.paginate
						};
					}
					return {
						selected: instance.options.checkedNodes,
						offset:			0,
						limit:			instance.options.paginate
					};
				},
				//
				onload: function(TREE_OBJ) {
					if(instance.options.checkedNodes){
						instance.check(instance.options.checkedNodes);
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
					if($(NODE).hasClass('paginate-all')) {
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
						DATA.children.push([{	
							data : '/ &nbsp;&nbsp;&nbsp;all'
							, attributes : { 'class':'paginate paginate-all' }
						},{	
							data : instance.paginate+' more'
							, attributes : { 'class':'paginate paginate-more' }
						}]);
					}
					
					return DATA;
				}
			},
			plugins : {
				checkbox : { three_state : true }
			}
		};
		
		//create the tree
		$(selector).tree(this.treeOptions);
		
		$("#saver-action-" + this.options.actionId).click(function(){
			instance.saveData();
		});
	}
	catch(exp){
	//	console.log(exp);
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
 * Paginate function, display more instances
 */
GenerisTreeFormClass.prototype.paginateInstances = function(NODE, TREE_OBJ, pOptions){
	var instance = this;
	
	// Show paginate options
	function showPaginate (NODE, TREE_OBJ){
		var DATA = [{	
			data : '/ &nbsp;&nbsp;&nbsp;all'
			, attributes : { 'class':'paginate paginate-all' }
		},{	
			data : instance.paginate+' more'
			, attributes : { 'class':'paginate paginate-more' }
		}];
		for (var i=0; i<DATA.length; i++){
			TREE_OBJ.create(DATA[i], TREE_OBJ.get_node(NODE[0]));
		}
	}
	// hide paginate options
	function hidePaginate (NODE, TREE_OBJ){
		$(NODE).find('.paginate').each(function(){
			//TREE_OBJ.remove(this);
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
		//Display instances
		for (var i=0; i<DATA.length; i++){
			DATA[i].attributes['class'] = instance.options.instanceClass+" node-instance node-draggable";
			TREE_OBJ.create(DATA[i], TREE_OBJ.get_node(NODE[0]));
		}
		instance.metaClasses[nodeId].displayed += DATA.length;
		//If it rests some instances, show paginate options
		if (instance.metaClasses[nodeId].displayed < instance.metaClasses[nodeId].length){
			showPaginate(NODE, TREE_OBJ);
		}
		instance.check(instance.options.checkedNodes);
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