/**
 * GenerisTreeClass is a easy to use container for the tree widget, 
 * it provides the common behavior for a Class/Instance Rdf resource tree
 * 
 * @example new GenerisTreeClass('#tree-container', 'myData.php', {});
 * @see GenerisTreeClass.defaultOptions for options example
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require jstree >= 0.9.9 [http://jstree.com/]
 * 
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */ 

GenerisTreeClass.instances = [];

/**
 * Constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {String} dataUrl the url to call, it must provide the json data to populate the tree 
 * @param {Object} options
 */
function GenerisTreeClass(selector, dataUrl, options){
	try{
		if(!options){
			options = GenerisTreeClass.defaultOptions;
		}
		this.selector = selector;
		this.options = options;
		this.dataUrl = dataUrl;
		var instance = this;
		
		GenerisTreeClass.instances[GenerisTreeClass.instances.length + 1] = instance;
		
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
				theme_name : "custom"
			},
			callback : {
				beforedata:function(NODE, TREE_OBJ) { 
					return { 
						type : $(TREE_OBJ.container).attr('id') 
					} 
				},
				onselect: function(NODE, TREE_OBJ){
					
					if($(NODE).hasClass('node-class') && instance.options.editClassAction){
						_load(instance.options.formContainer, 
							instance.options.editClassAction,
							{classUri:$(NODE).attr('id')}
						);
					}
					if($(NODE).hasClass('node-instance') && instance.options.editInstanceAction){
						PNODE = TREE_OBJ.parent(NODE);
						_load(instance.options.formContainer, 
							instance.options.editInstanceAction, 
							{classUri: $(PNODE).attr('id'),  uri: $(NODE).attr('id')}
						);
					}
					return false;
				}
			},
			plugins: {
				contextmenu : {
					items : {
						select: {
							label: "Edit",
							icon: "rename",
							visible : function (NODE, TREE_OBJ) {
								if( ($(NODE).hasClass('node-instance') &&  instance.options.editInstanceAction)  || 
									($(NODE).hasClass('node-class') &&  instance.options.editClassAction) ){
									return true;
								}
								return false;
							},
							action  : function(NODE, TREE_OBJ){
								TREE_OBJ.select_branch(NODE);
							},
		                    separator_before : true
						},
						subclass: {
							label: "Add subclass",
							icon	: "create",
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
								GenerisTreeClass.addClass({
									id: $(NODE).attr('id'),
									url: instance.options.subClassAction,
									NODE: NODE,
									TREE_OBJ: TREE_OBJ
								});
							},
		                    separator_before : true
						},
						instance:{
							label: "Add instance",
							icon	: "create",
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
								GenerisTreeClass.addInstance({
									url: instance.options.createInstanceAction,
									id: $(NODE).attr('id'),
									NODE: NODE,
									TREE_OBJ: TREE_OBJ
								});
							}
						},
						duplicate:{
							label	: "Duplicate",
							icon	: "create",
							visible	: function (NODE, TREE_OBJ) { 
									if($(NODE).hasClass('node-instance')  && instance.options.duplicateAction){
										return true;
									}
									return false;
								}, 
							action	: function (NODE, TREE_OBJ) { 
								GenerisTreeClass.cloneNode({
									url: instance.options.duplicateAction,
									NODE: NODE,
									TREE_OBJ: TREE_OBJ
								});
							}
						},
						del:{
							label	: "Remove",
							icon	: "remove",
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
								GenerisTreeClass.removeNode({
									url: instance.options.deleteAction,
									NODE: NODE,
									TREE_OBJ: TREE_OBJ
								});
							} 
						},
						remove: false,
						create: false,
						rename: false,
					}
				}
			}
		};
		
		//create the tree
		$(selector).tree(this.treeOptions);
		
		$("#open-action-" + options.actionId).click(function(){
			$.tree.reference(instance.selector).open_all();
		});
		$("#close-action-" + options.actionId).click(function(){
			$.tree.reference(instance.selector).close_all();
		});
	}
	catch(exp){
		console.log(exp);
	}
}

/**
 * @return {Object} the tree instance
 */
GenerisTreeClass.prototype.getTree = function(){
	return $.tree.reference(this.selector);
}

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
}

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
							TREE_OBJ: aJsTree
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
		data: {classUri: options.id},
		dataType: 'json',
		success: function(response){
			if(response.uri){
				TREE_OBJ.select_branch(
					TREE_OBJ.create({
						data: response.label,
						attributes: {
							id: response.uri,
							class: 'node-class'
						}
					}, TREE_OBJ.get_node(NODE[0])));
			}
		}
	});
}

/**
 * conveniance method to subclass
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
function subClass(uri, classUri, url){
	var options = getTreeOptions(classUri);
	if(options){
		options.id = classUri;
		options.url = url;
		GenerisTreeClass.addClass(options);
	}
}

/**
 * add an instance
 * @param {Object} options
 */
GenerisTreeClass.addInstance = function(options){
	var TREE_OBJ = options.TREE_OBJ;
	var NODE = options.NODE;
	$.ajax({
		url: options.url,
		type: "POST",
		data: {classUri: options.id},
		dataType: 'json',
		success: function(response){
			if (response.uri) {
				TREE_OBJ.select_branch(TREE_OBJ.create({
					data: response.label,
					attributes: {
						id: response.uri,
						class: 'node-instance'
					}
				}, TREE_OBJ.get_node(NODE[0])));
			}
		}
	});
}

/**
 * conveniance method to instanciate
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
function instanciate(uri, classUri, url){
	var options = getTreeOptions(classUri);
	if(options){
		options.id = classUri;
		options.url = url;
		GenerisTreeClass.addInstance(options);
	}
}

/**
 * remove a resource
 * @param {Object} options
 */
GenerisTreeClass.removeNode = function(options){
	var TREE_OBJ = options.TREE_OBJ;
	var NODE = options.NODE;
	if(confirm("Please confirm deletion")){
		console.log(NODE);
		$.each(NODE, function () { 
			data = false;
			var selectedNode = this;
			if($(selectedNode).hasClass('node-class')){
				data =  {classUri: $(selectedNode).attr('id')}
			}
			if($(selectedNode).hasClass('node-instance')){
				PNODE = TREE_OBJ.parent(selectedNode);
				data =  {uri: $(selectedNode).attr('id'), classUri: $(PNODE).attr('id')}
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
}

/**
 * conveniance method to instanciate
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
function removeNode(uri, classUri, url){
	var options = getTreeOptions(uri);
	if(!options){
		options = getTreeOptions(classUri);
	}
	if(options){
		options.url = url;
		GenerisTreeClass.removeNode(options);
	}
}

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
							class: 'node-instance'
						}
					},
					TREE_OBJ.get_node(PNODE)
					)
				);
			}
		}
	});
}

/**
 * conveniance method to clone
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
function cloneNode(uri, classUri, url){
	var options = getTreeOptions(uri);
	if(options){
		options.url = url;
		GenerisTreeClass.cloneNode(options);
	}
}

function fullScreen(uri, classUri, url){
	url += '?uri='+uri+'&classUri='+classUri;
	window.open(url, 'tao', 'width=800,height=600,menubar=no,toolbar=no');
}