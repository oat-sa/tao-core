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
					
					if($(NODE).hasClass('node-class')){
						_load(instance.options.formContainer, 
							instance.options.editClassAction,
							{classUri:$(NODE).attr('id')}
						);
					}
					if($(NODE).hasClass('node-instance')){
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
						edit: {
							label: "Edit",
							icon: "",
							visible : function (NODE, TREE_OBJ) {
								if($(NODE).hasClass('node-instance') || instance.options.classEditable ){
									return true;
								}
								return false;
							},
							action  : function(NODE, TREE_OBJ){
								TREE_OBJ.select_branch(NODE);
							},
		                    separator_before : true
						},
						create:{
							label: "Create instance",
							visible: function (NODE, TREE_OBJ) {
								if(NODE.length != 1) {
									return false; 
								}
								if(!$(NODE).hasClass('node-class')){ 
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
						rename: false,
					}
				}
			}
		};
		
		//create the tree
		$(selector).tree(this.treeOptions);
		
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
