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
					if($(NODE).hasClass('node-instance') && instance.options.gridAction && instance.options.gridContainer){
						
						if($(instance.options.gridContainer).css('display') == 'none'){
							$(instance.options.gridContainer).html('');
							$(instance.options.gridContainer).fadeIn();
						}
						
						PNODE = TREE_OBJ.parent(NODE);
						_load(instance.options.gridContainer, 
							instance.options.gridAction, 
							{classUri: $(PNODE).attr('id'),  uri: $(NODE).attr('id')}
						);
					}
					else if(instance.options.gridContainer){
						$(instance.options.gridContainer).fadeOut();
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
								$.ajax({
									url: instance.options.subClassAction,
									type: "POST",
									data: {classUri: $(NODE).attr('id')},
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
												}, 
												TREE_OBJ.get_node(NODE[0])
												)
											);
										}
									}
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
								$.ajax({
									url: instance.options.createInstanceAction,
									type: "POST",
									data: {classUri: $(NODE).attr('id')},
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
												TREE_OBJ.get_node(NODE[0])
												)
											);
										}
									}
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
								PNODE = TREE_OBJ.parent(NODE);
								$.ajax({
									url: instance.options.duplicateAction,
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
						},
						delete:{
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
								if(confirm("Please confirm deletion")){
									$.each(NODE, function () { 
										var selectedNode = this;
										if($(selectedNode).hasClass('node-class')){
											data =  {classUri: $(selectedNode).attr('id')}
										}
										if($(selectedNode).hasClass('node-instance')){
											PNODE = TREE_OBJ.parent(selectedNode);
											data =  {uri: $(selectedNode).attr('id'), classUri: $(PNODE).attr('id')}
										}
										$.ajax({
											url: instance.options.deleteAction,
											type: "POST",
											data: data,
											dataType: 'json',
											success: function(response){
												if(response.deleted){
													TREE_OBJ.remove(selectedNode); 
												}
											}
										});
										
									}); 
								}
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
			//alert(instance.selector);
			$.tree.reference(instance.selector).open_all();
		});
		$("#close-action-" + options.actionId).click(function(){
			$.tree.reference(instance.selector).close_all();
		});
	}
	catch(exp){
		alert(exp);
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
	formContainer: '#form-container',
	editClassAction: '/editClass',
	editInstanceAction: '/editInstance', 
	classEditable: false,
	createInstanceAction: '/createInstance'
};
