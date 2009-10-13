function GenerisTreeClass(selector, dataUrl, options){
	try{
		this.options = options;
		this.dataUrl = dataUrl;
		var instance = this;
		
		$(selector).tree({
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
		});
	}
	catch(exp){
		alert(exp);
	}
}