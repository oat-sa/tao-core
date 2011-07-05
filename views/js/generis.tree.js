/**
 * GenerisTreeClass is a easy to use container for the tree widget, 
 * it provides the common behavior for a Class/Instance Rdf resource tree
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
 * @var {Array} instances the list of tree instances 
 */
GenerisTreeClass.instances = [];

/**
 * The GenerisTreeClass constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {String} dataUrl the url to call, it must provide the json data to populate the tree 
 * @param {Object} options {formContainer, actionId, instanceClass, instanceName, selectNode,
 * 							editClassAction, editInstanceAction, createInstanceAction,  
 * 							moveInstanceAction, subClassAction, deleteAction, duplicateAction}
 */
function GenerisTreeClass(selector, dataUrl, options){
	try{
		if(!options){
			options = GenerisTreeClass.defaultOptions;
		}
		this.selector = selector;
		this.options = options;
		//Url used to get tree data
		this.dataUrl = dataUrl;
		//Store meta data of opened classes
		this.metaClasses = new Array();
		//Keep a reference of the last opened node
		this.lastOpened = null;
		
		/**
		 * global access into sub scopes
		 */
		var instance = this;
		
		if(!options.instanceName){
			options.instanceName = 'instance';
		}
		if (typeof options.paginate != 'undefined'){
			this.paginate = options.paginate;
		} else {
			this.paginate = 0;
		}
		
		GenerisTreeClass.instances[GenerisTreeClass.instances.length + 1] = instance;
		
		/**
		 * @var {Object} jsTree options
		 * @see http://www.jstree.com/documentation for the options documentation 
		 */
		this.treeOptions = {
			//how to retrieve the data
			data: {
				type: "json",
				async : true,
				opts: {
					method : "POST",
					url: instance.dataUrl
				}
			},
			//what we can do with the elements
			types: {
			 "default" : {
					renameable	: false,
					deletable	: true,
					creatable	: true,
					draggable	: function(NODE){
						if($(NODE).hasClass('node-instance') && instance.options.moveInstanceAction){
							return true;
						}
						return false;
					}
				}
			},
			ui: {
				theme_name : "custom"
			},
			callback : {
				//data to send to the server
				beforedata:function(NODE, TREE_OBJ) {
					if(NODE){
						return {
							hideInstances:  instance.options.hideInstances | false,
							filter: 		$("#filter-content-" + options.actionId).val(),
							classUri: 		$(NODE).attr('id'),
							offset:			0,
							limit:			instance.options.paginate
						};
					}
					return {
						hideInstances: 	instance.options.hideInstances | false,
						filter: 		$("#filter-content-" + options.actionId).val(),
						offset:			0,
						limit:			instance.options.paginate
					};
				},
				//once the tree is loaded
				onload: function(TREE_OBJ){
					if (instance.options.selectNode) {
						TREE_OBJ.select_branch($("li[id='"+instance.options.selectNode+"']"));
						instance.options.selectNode = false;
					}
					else{
						TREE_OBJ.open_branch($("li.node-class:first"));
					}
				},
				//before open a branch
				beforeopen:function(NODE, TREE_OBJ){
					instance.lastOpened = NODE;
				},
				//when we receive the data
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
					//Function display instances
					
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
					
					if(instance.options.instanceClass){
						function addClassToNodes(nodes, clazz){
							$.each(nodes, function(i, node){
								if(/node\-instance/.test(node.attributes['class'])){
									node.attributes['class'] = node.attributes['class'] + ' ' + clazz;
								}
								
								if(node.children){
									addClassToNodes(node.children, clazz);
								}
							});
						}
						if(DATA.children){
							addClassToNodes(DATA.children, instance.options.instanceClass);
							if(instance.options.moveInstanceAction){
								addClassToNodes(DATA.children, 'node-draggable');
							}
						}
						else if(DATA.length > 0){
							addClassToNodes(DATA, instance.options.instanceClass);
							if(instance.options.moveInstanceAction){
								addClassToNodes(DATA, 'node-draggable');
							}
						}
					}
					
					//Add Pagination actions if required
					if (instance.metaClasses[currentNodeId].displayed < instance.metaClasses[currentNodeId].length){
						var paginateNodes = [{	
								data : '/ &nbsp;&nbsp;&nbsp;'+__('all')
								, attributes : { 'class':'paginate paginate-all' }
							},{	
								data : instance.paginate+' '+__('more')
								, attributes : { 'class':'paginate paginate-more' }
							}];
						//Receive a node
						if (DATA.children){
							DATA.children.push(paginateNodes);
						} 
						//Receive an array of node
						else {
							DATA.push (paginateNodes);
						}
					}
					
					return DATA;
				},
				//when a node is selected
				onselect: function(NODE, TREE_OBJ){
					var nodeId = $(NODE).attr('id');
					$("a.clicked").each(function(){
						if($(this).parent('li').attr('id') != nodeId){
							$(this).removeClass('clicked');
						}
					});
					
					if( ($("input:hidden[name='uri']").val() == nodeId || $("input:hidden[name='classUri']").val() == nodeId) && nodeId == instance.options.selectNode){
						return false;
					}
					
					if($(NODE).hasClass('node-class') && instance.options.editClassAction){
						
						if($(NODE).hasClass('closed')){
							TREE_OBJ.open_branch(NODE);
						}
						
						//load the editClassAction into the formContainer
						_load(instance.options.formContainer, 
							instance.options.editClassAction,
							instance.data(null, nodeId)
						);
					}
					if($(NODE).hasClass('node-instance') && instance.options.editInstanceAction){
						//load the editInstanceAction into the formContainer
						PNODE = TREE_OBJ.parent(NODE);
						_load(instance.options.formContainer, 
							instance.options.editInstanceAction, 
							instance.data(nodeId, $(PNODE).attr('id'))
						);
					}
					if($(NODE).hasClass('paginate-more')) {
						instance.paginateInstances ($(NODE).parent().parent(), TREE_OBJ);
					}
					if($(NODE).hasClass('paginate-all')) {
						instance.paginateInstances ($(NODE).parent().parent(), TREE_OBJ, {limit:0});
					}
					return false;
				},
				//when a node is move by drag n'drop
				onmove: function(NODE, REF_NODE, TYPE, TREE_OBJ, RB){
					if(!instance.options.moveInstanceAction){
						return false;
					}
					if($(REF_NODE).hasClass('node-instance') && TYPE == 'inside'){
						$.tree.rollback(RB);
						return false;
					}
					else{
						if(TYPE == 'after' || TYPE == 'before'){
							REF_NODE = TREE_OBJ.parent(REF_NODE);
						}
						//call the server with the new node position to save the new position
						function moveNode(url, data){
							
							var NODE 		= data.NODE;
							var REF_NODE	= data.REF_NODE;
							var RB 			= data.RB;
							var TREE_OBJ 	= data.TREE_OBJ;
							(data.confirmed == true) ? confirmed = true :  confirmed = false;
							
							$.postJson(url, {
								'uri': data.uri,
								'destinationClassUri':  data.destinationClassUri,
								'confirmed' : confirmed
								},
								function(response){
									
									if(response == null){
										$.tree.rollback(RB);
										return;
									}
									if(response.status == 'diff'){
										message = __("Moving this element will remove the following properties:");
										message += "\n";
										for(i = 0; i < response.data.length; i++){
											if(response.data[i].label){
												message += "- " + response.data[i].label + "\n";
											}
										}
										message += "Please confirm this operation.\n";
										if(confirm(message)){
											data.confirmed = true;
											moveNode(url, data);
										}
										else{
											$.tree.rollback(RB);
										}
									}
									else if(response.status == true){
										$('li a').removeClass('clicked');
										TREE_OBJ.open_branch(NODE);	
									}
									else{
										$.tree.rollback(RB);
									}
							});
						}
						moveNode(instance.options.moveInstanceAction, {
								'uri': $(NODE).attr('id'),
								'destinationClassUri': $(REF_NODE).attr('id'),
								'NODE'		: NODE,
								'REF_NODE'	: REF_NODE,
								'RB'		: RB,
								'TREE_OBJ'	: TREE_OBJ
							});
					}
				}
			},
			plugins: {
				
				//the right click menu
				contextmenu : {
					items : {
						//edit action
						select: {
							label: __("edit"),
							icon: taobase_www +"img/pencil.png",
							visible : function (NODE, TREE_OBJ) {
								if( ($(NODE).hasClass('node-instance') &&  instance.options.editInstanceAction)  || 
									($(NODE).hasClass('node-class') &&  instance.options.editClassAction) ){
									return true;
								}
								return false;
							},
							action  : function(NODE, TREE_OBJ){
								TREE_OBJ.select_branch(NODE);		//call the onselect callback
							},
		                    separator_before : true
						},
						//new class action
						subclass: {
							label: __("new class"),
							icon	: taobase_www + "img/class_add.png",
							visible: function (NODE, TREE_OBJ) {
								if(NODE.length != 1) {
									return false; 
								}
								if(!$(NODE).hasClass('node-class') || !instance.options.subClassAction){ 
									return false;
								}
								return TREE_OBJ.check("creatable", NODE);
							},
							action  : function(NODE, TREE_OBJ){
								
								//specialize the selected class
								GenerisTreeClass.addClass({
									id: $(NODE).attr('id'),
									url: instance.options.subClassAction,
									NODE: NODE,
									TREE_OBJ: TREE_OBJ
								});
							},
		                    separator_before : true
						},
						//new instance action
						instance:{
							label: __("new") + ' ' +  __(instance.options.instanceName),
							icon	: taobase_www + "img/instance_add.png",
							visible: function (NODE, TREE_OBJ) {
								if(NODE.length != 1) {
									return false; 
								}
								if(!$(NODE).hasClass('node-class') || !instance.options.createInstanceAction){ 
									return false;
								}
								return TREE_OBJ.check("creatable", NODE);
							},
							action: function (NODE, TREE_OBJ) { 
								
								//add a new instance of the selected class
								GenerisTreeClass.addInstance({
									url: instance.options.createInstanceAction,
									id: $(NODE).attr('id'),
									NODE: NODE,
									TREE_OBJ: TREE_OBJ,
									cssClass: instance.options.instanceClass
								});
							}
						},
						//move action
						move:{
							label	: __("move"),
							icon	: taobase_www + "img/move.png",
							visible	: function (NODE, TREE_OBJ) { 
									if($(NODE).hasClass('node-instance')  && instance.options.moveInstanceAction){
										return true;
									}
									return false;
								}, 
							action	: function (NODE, TREE_OBJ) { 
								
								//move the node
								GenerisTreeClass.moveInstance({
										url: instance.options.moveInstanceAction,
										NODE: NODE,
										TREE_OBJ: TREE_OBJ
									});
							},
		                    separator_before : true
						},
						//clone action
						duplicate:{
							label	: __("duplicate"),
							icon	: taobase_www + "img/duplicate.png",
							visible	: function (NODE, TREE_OBJ) { 
									if($(NODE).hasClass('node-instance')  && instance.options.duplicateAction){
										return true;
									}
									return false;
								}, 
							action	: function (NODE, TREE_OBJ) { 
									
								//clone the node
								GenerisTreeClass.cloneNode({
									url: instance.options.duplicateAction,
									NODE: NODE,
									TREE_OBJ: TREE_OBJ
								});
							}
						},
						//delete action
						del:{
							label	: __("delete"),
							icon	: taobase_www + "img/delete.png",
							visible	: function (NODE, TREE_OBJ) { 
								var ok = true; 
								$.each(NODE, function () { 
									if(TREE_OBJ.check("deletable", this) == false || !instance.options.deleteAction) 
										ok = false; 
										return false; 
									}); 
									return ok; 
								}, 
							action	: function (NODE, TREE_OBJ) { 
									
								//remove the node
								GenerisTreeClass.removeNode({
									url: instance.options.deleteAction,
									NODE: NODE,
									TREE_OBJ: TREE_OBJ
								});
								return false;
							} 
						},
						//unset the default entries
						remove: false,
						create: false,
						rename: false
					}
				}
			}
		};
		
		if(this.options.selectNode){
			this.treeOptions.selected = this.options.selectNode;
		}
		
		tmpTree = $.tree.reference(selector);
		if(tmpTree != null){
			tmpTree.destroy();
		}
		tmpTree = null;
		
		/*
		 * Create and initialize the tree here
		 */
		$(selector).tree(this.treeOptions);
		
		//open all action
		$("#open-action-" + options.actionId).click(function(){
			$.tree.reference(instance.selector).open_all();
		});
		
		//close all action
		$("#close-action-" + options.actionId).click(function(){
			$.tree.reference(instance.selector).close_all();
		});
		
		//filter action
		$("#filter-action-" + options.actionId).click(function(){
			$.tree.reference(instance.selector).refresh();
		});
		$("#filter-content-" + options.actionId).bind('keypress', function(e) {
	        if(e.keyCode==13 && this.value.length > 0){
				$.tree.reference(instance.selector).refresh();
	        }
		});

	}
	catch(exp){
		//console.log(exp);
	}

	/**
	 * Formats sendable data with the defined options
	 * ONLY USED FOR SELECT ACTION
	 * @param {String} uri
	 * @param {String} classUri
	 * @return {Object}
	 */
	this.data = function(uri, classUri){
		data = {};
		
		(this.options.instanceKey) 	? instanceKey = this.options.instanceKey :  instanceKey = 'uri';
		(this.options.classKey) 	? classKey = this.options.classKey :  classKey = 'classUri';
		
		if(uri){
			data[instanceKey] = uri;
		}
		if(classUri){
			data[classKey] = classUri;
		}
		
		return data;
	};
}

/**
 * @return {Object} the tree instance
 */
GenerisTreeClass.prototype.getTree = function(){
	return $.tree.reference(this.selector);
};

/**
 * Paginate function, display more instances
 */
GenerisTreeClass.prototype.paginateInstances = function(NODE, TREE_OBJ, pOptions){
	var instance = this;
	
	// Show paginate options
	function showPaginate (NODE, TREE_OBJ){
		var DATA = [{	
			data : '/ &nbsp;&nbsp;&nbsp;'+__('all')
			, attributes : { 'class':'paginate paginate-all' }
		},{	
			data : instance.paginate+' '+__('more')
			, attributes : { 'class':'paginate paginate-more' }
		}];
		for (var i=0; i<DATA.length; i++){
			TREE_OBJ.create(DATA[i], TREE_OBJ.get_node(NODE[0]));
		}
	}
	// hide paginate options
	function hidePaginate (NODE, TREE_OBJ){
		$(NODE).find('.paginate').each(function(){
			$(this).remove();
			//TREE_OBJ.remove(this);
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
	}, "json");
};

/**
 * @var GenerisTreeClass.defaultOptions is an example of options to provide to the tree
 */
GenerisTreeClass.defaultOptions = {
	formContainer: '#form-container'
};

/**
 * select a node in the current tree
 * @param {String} id
 * @return {Boolean}
 */
GenerisTreeClass.selectTreeNode = function(id){
	i=0;
	while(i < GenerisTreeClass.instances.length){
		aGenerisTree = GenerisTreeClass.instances[i];
		if(aGenerisTree){
			aJsTree = aGenerisTree.getTree();
			if(aJsTree){
				if(aJsTree.select_branch($("li[id='"+id+"']"))){
					return true;
				}
			}
		}
		i++;
	}
	return false;
};

/**
 * Enable you to retrieve the right tree instance and node instance from an Uri
 * @param {String} uri is the id of the tree node
 * @return {Object}
 */
function getTreeOptions(uri){
	if (uri) {
		i = 0;
		while (i < GenerisTreeClass.instances.length) {
			aGenerisTree = GenerisTreeClass.instances[i];
			if (aGenerisTree) {
				aJsTree = aGenerisTree.getTree();
				if (aJsTree) {
					if (aJsTree.get_node($("li[id='" + uri + "']"))) {
						return {
							NODE: aJsTree.get_node($("li[id='" + uri + "']")),
							TREE_OBJ: aJsTree,
							cssClass: aGenerisTree.options.instanceClass
						};
					}
				}
			}
			i++;
		}
	}
	return false;
}

/**
 * Sub class action
 * @param {Object} options
 */
GenerisTreeClass.addClass = function(options){
	var TREE_OBJ = options.TREE_OBJ;
	var NODE = options.NODE;
	$.ajax({
		url: options.url,
		type: "POST",
		data: {classUri: options.id, type: 'class'},
		dataType: 'json',
		success: function(response){
			if(response.uri){
				TREE_OBJ.select_branch(
					TREE_OBJ.create({
						data: response.label,
						attributes: {
							id: response.uri,
							'class': 'node-class'
						}
					}, TREE_OBJ.get_node(NODE[0])));
			}
		}
	});
};

/**
 * add an instance
 * @param {Object} options
 */
GenerisTreeClass.addInstance = function(options){
	var TREE_OBJ = options.TREE_OBJ;
	var NODE = options.NODE;
	var  cssClass = 'node-instance';
	if(options.cssClass){
		 cssClass += ' ' + options.cssClass;
	}

	$.ajax({
		url: options.url,
		type: "POST",
		data: {classUri: options.id, type: 'instance'},
		dataType: 'json',
		success: function(response){
			if (response.uri) {
				TREE_OBJ.select_branch(TREE_OBJ.create({
					data: response.label,
					attributes: {
						id: response.uri,
						'class': cssClass
					}
				}, TREE_OBJ.get_node(NODE[0])));
			}
		}
	});
};


/**
 * remove a resource
 * @param {Object} options
 */
GenerisTreeClass.removeNode = function(options){
	var TREE_OBJ = options.TREE_OBJ;
	var NODE = options.NODE;
	if(confirm(__("Please confirm deletion"))){
		$.each(NODE, function () { 
			data = false;
			var selectedNode = this;
			if($(selectedNode).hasClass('node-class')){
				data =  {classUri: $(selectedNode).attr('id')};
			}
			if($(selectedNode).hasClass('node-instance')){
				PNODE = TREE_OBJ.parent(selectedNode);
				data =  {uri: $(selectedNode).attr('id'), classUri: $(PNODE).attr('id')};
			}
			if(data){
				$.ajax({
					url: options.url,
					type: "POST",
					data: data,
					dataType: 'json',
					success: function(response){
						if(response.deleted){
							TREE_OBJ.remove(selectedNode); 
						}
					}
				});
			}
		}); 
	}
};

/**
 * clone a resource
 * @param {Object} options
 */
GenerisTreeClass.cloneNode = function(options){
	var TREE_OBJ = options.TREE_OBJ;
	var NODE = options.NODE;
	var PNODE = TREE_OBJ.parent(NODE);
	$.ajax({
		url: options.url,
		type: "POST",
		data: {classUri: $(PNODE).attr('id'), uri: $(NODE).attr('id')},
		dataType: 'json',
		success: function(response){
			if(response.uri){
				TREE_OBJ.select_branch(
					TREE_OBJ.create({
						data: response.label,
						attributes: {
							id: response.uri,
							'class': $(NODE).attr('class')
						}
					},
					TREE_OBJ.get_node(PNODE)
					)
				);
			}
		}
	});
};

/**
 * Rename a node
 * @param {Object} options
 */
GenerisTreeClass.renameNode = function(options){
	var TREE_OBJ = options.TREE_OBJ;
	var NODE = options.NODE;
	var data = {
			uri: $(NODE).attr('id'), 
			newName: TREE_OBJ.get_text(NODE)
		};
	if(options.classUri){
		data.classUri = options.classUri;
	}
	$.ajax({
		url: options.url,
		type: "POST",
		data: data,
		dataType: 'json',
		success: function(response){
			if(!response.renamed){
				TREE_OBJ.rename(NODE, response.oldName);	
			}
		}
	});
};

/**
 * Move an instance node
 * @param {Object} options
 */
GenerisTreeClass.moveInstance = function(options){

	//to prevent scope crossing
	var myTREE_OBJ = options.TREE_OBJ;
	var myNODE = options.NODE;
	
	//create the dialog content 
	$('body').append(
		$("<div id='tmp-moving' style='display:none;'>" +
				"<span class='ui-state-highlight' style='margin:15px;'>" + __('Select the element destination') + "</span><br />" +
				"<div id='tmp-moving-tree'></div>" +
				"<div style='text-align:center;margin-top:30px;'> " +
					"<a id='tmp-moving-closer' class='ui-state-default ui-corner-all' href='#'>" + __('Cancel') + "</a> " +
				"</div> " +
			"</div>")
	);
	
	//create a new tree
	var TMP_TREE = {
			
			//with the same data than the parent tree
			data: myTREE_OBJ.settings.data,
			
			//but only the ability to click on a node
			types: {
			 "default" : {
				clickable: function(NODE){
						if($(NODE).hasClass('node-class')){
							return true;
						}
						return false;
					},
					renameable	: false,
					deletable	: false,
					creatable	: false,
					draggable	: false
				}
			},
			ui: {
				theme_name : "custom"
			},
			callback: {
				//add the type param to the server request to get only the classes
				beforedata:function(NODE, TREE_OBJ) { 
					if(NODE){
						return { 
							hideInstances : true,
							subclasses: true,
							classUri: $(NODE).attr('id')
						};
					}
					return { 
						hideInstances :true,
						subclasses: true
					};
				},
				
				//expand the tree on load
				onload: function(TREE_OBJ){
					TREE_OBJ.open_branch($("li.node-class:first", $("#tmp-moving-tree")));//TREE_OBJ.open_branch($("li.node-class:first"));
				},
				ondata: function(DATA, TREE_OBJ){
					return DATA;
				},
				
				//call the tree onmove callback by selecting a class
				onselect: function(NODE, TREE_OBJ){
					var rollback = {};
					rollback[$(myTREE_OBJ.container).attr('id')] = myTREE_OBJ.get_rollback();
					myTREE_OBJ.settings.callback.onmove(myNODE, NODE, 'inside', myTREE_OBJ, rollback);
					myTREE_OBJ.refresh();
					$("#tmp-moving").dialog('close');
				}
			}
	};
	
	//create a dialog window to embed the tree
	position = $(getMainContainerSelector()).offset();	
	$("#tmp-moving-tree").tree(TMP_TREE);
	$("#tmp-moving").dialog({
		width: 350,
		height: 400,
		position: [position.left, position.top],
		autoOpen: false,
		title: __('Move to')
	});
	$("#tmp-moving").bind('dialogclose', function(event, ui){
		$.tree.reference("#tmp-moving-tree").destroy();
		$("#tmp-moving").dialog('destroy');
		$("#tmp-moving").remove();
	});
	$("#tmp-moving-closer").click(function(){
		$("#tmp-moving").dialog('close');
	});
	//open the dialog
	$("#tmp-moving").dialog('open');
};

