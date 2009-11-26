/**
 * init common form controls
 */
function _initFormNavigation(){
	
	//submit the form by ajax into the form container
	$("form").submit(function(){
		try{
			loading();
			$(getMainContainerSelector()).load(
				_href($(this).attr('action')),
				$(this).serializeArray(),
				loaded()
			);
			window.location = '#form-title';
		}
		catch(exp){console.log(exp);}
		return false;
	});
	
	//revert form button
	$(".form-reverter").click(function(){
		if ($("#uri")) {
			GenerisTreeClass.selectTreeNode($("#uri").val());
		}
		else if ($("#classUri")) {
			GenerisTreeClass.selectTreeNode($("#classUri").val());
		}
	})
}

/**
 * init special forms controls
 */
function _initForms(){
	
	//map the wysiwyg editor to the html-area fields
	$('.html-area').each(function(){
		if($(this).css('display') != 'none') {
			$(this).wysiwyg();
		}
	});
	/*
	$('textarea.html-area').each(function(){
		if($(this).css('display') != 'none') {
			$('textarea.html-area').tinymce(simpleEditorOptions);
		}
	});
	*/
	
	//open the authoring tool on the authoringOpener button
	$('.authoringOpener').click(function(){
		index = getTabIndexByName('item_authoring');
		if(index > -1){
			if($("#uri") && $("#classUri")){
				tabs.tabs('url', index, '/taoItems/Items/authoring?uri=' + $("#uri").val() +'&classUri=' + $("#classUri").val());
				tabs.tabs('select', index);
			}
		}
	});
	
	//property delete button 
	 $(".property-deleter").click(function(){
	 	groupNode = $(this).parents(".form-group").get(0);
		if(groupNode){
			$(groupNode).empty();
			$(groupNode).remove();
		}
	 })
	 
	 //property add button
	 $(".property-adder").click(function(){
		 var groupNode = $(this).parents(".form-group").get(0);
		 if(ctx_extension){
		 	url = '/' + ctx_extension + '/' + ctx_module + '/';
		 }
		 url += 'addClassProperty';
		 var eltId = this.id;
		 if (groupNode) {
		 	$.ajax({
		 		url: url,
		 		type: "POST",
		 		data: {
		 			index: $(".property-uri").size(),
		 			classUri: $("#classUri").val()
		 		},
		 		dataType: 'html',
		 		success: function(response){
		 			$(groupNode).before(response)
		 			initNavigation();
					window.location = '#'+eltId;
		 		}
		 	});
		 }
	 })
	 
	 /**
	  * display or not the list regarding the property type
	  */
	 function showPropertyList(){
	 	if(/list$/.test($(this).val())){
			elt = $(this).parent("div").next("div");
			if(elt.css('display') == 'none'){
				elt.show("slow");
			}
		}
		else{
			elt = $(this).parent("div").next("div")
			if(elt.css('display') != 'none'){
				elt.hide("slow");
			}
		}
	 }
	 
	 /**
	  * by selecting a list the values are displayed or the list editor open
	  */
	 function showPropertyListValues(){
	 	
		if($(this).val() == 'new'){
		
			/*
			 * Open the list editor: a tree in a dialog popup 
			 */
			var rangeId = $(this).attr('id');
			var dialogId = rangeId.replace('_range', '_dialog');
			var treeId = rangeId.replace('_range', '_tree');
			var closerId = rangeId.replace('_range', '_closer');
			
			//dialog content
			elt = $(this).parent("div");
			elt.append("<div id='"+dialogId+"' style='display:none;' >\
							<div id='"+treeId+"' ></div>\
							<div style='text-align:center;'>\
								<a id='"+closerId+"' class='ui-state-default ui-corner-all' href='#'>Ok</a>\
							</div>\
						</div>");
						
			//init dialog events
			$("#"+dialogId).dialog({
				width: 300,
				height: 300,
				autoOpen: false,
				title: 'Manage data list'
			});
			
			$("#"+dialogId).bind('dialogclose', function(event, ui){
				$.tree.reference("#"+treeId).destroy();
				$("#"+dialogId).dialog('destroy');
				$("#"+dialogId).remove();
			});
			$("#"+closerId).click(function(){
				$("#"+dialogId).dialog('close');
			});
			$("#"+dialogId).bind('dialogopen', function(event, ui){
				if(ctx_extension){
				 	url = '/' + ctx_extension + '/' + ctx_module + '/';
				}
				dataUrl 	= url + 'getLists';
				createUrl	= url + 'createList';
				removeUrl	= url + 'removeList';
				renameUrl	= url + 'renameList';
				 
				//create tree
				$("#"+treeId).tree({
					data: {
						type: "json",
						async : true,
						opts: {
							method : "POST",
							url: dataUrl
						}
					},
					types: {
					 "default" : {
							renameable	: true,
							deletable	: true,
							creatable	: true,
							draggable	: false
						}
					},
					ui: {
						theme_name : "custom"
					},
					callback: {
						onrename: function(NODE, TREE_OBJ, RB){
							GenerisTreeClass.renameNode({
								url: renameUrl,
								NODE: NODE,
								TREE_OBJ: TREE_OBJ
							});
						},
						ondestroy: function(TREE_OBJ){
							
							//empty and build again the list drop down on tree destroying
							$("#"+rangeId+" option").each(function(){
								if($(this).val() != "" && $(this).val() != "new"){
									$(this).remove();
								}
							})
							$("#"+treeId+" .node-root .node-class").each(function(){
								$("#"+rangeId+" option[value='new']").before("<option value='"+$(this).attr('id')+"'>"+$(this).children("a:first").text()+"</option>");
							});
							$("#"+rangeId).parent("div").children("ul.form-elt-list").remove();
							$("#"+rangeId).val('');
						}
					},
					plugins: {
						contextmenu: {
							items: {
								remove: {
									visible: function(NODE, TREE_OBJ){
										if($(NODE).hasClass('node-root')){
											return false; 
										}
										return TREE_OBJ.check("deletable", NODE);
									},
									action  : function(NODE, TREE_OBJ){
										GenerisTreeClass.removeNode({
											url: removeUrl,
											NODE: NODE,
											TREE_OBJ: TREE_OBJ
										});
										return false;
									}
								},
								create: {
									visible: function(NODE, TREE_OBJ){
										if($(NODE).hasClass('node-instance')){
											return false; 
										}
										return TREE_OBJ.check("creatable", NODE);
									},
									action  : function(NODE, TREE_OBJ){
										if ($(NODE).hasClass('node-class')) {
											GenerisTreeClass.addInstance({
												url: createUrl,
												id: $(NODE).attr('id'),
												NODE: NODE,
												TREE_OBJ: TREE_OBJ
											});
										}
										if ($(NODE).hasClass('node-root')) {
											GenerisTreeClass.addClass({
												id: 'root',
												url: createUrl,
												NODE: NODE,
												TREE_OBJ: TREE_OBJ
											});
										}
										return false;
									}
								},
								rename: {
									visible: function(NODE, TREE_OBJ){
										if($(NODE).hasClass('node-root')){
											return false; 
										}
										return TREE_OBJ.check("renameable", NODE);
									}
								}
							}
						}
					}
				});
			});
			$("#"+dialogId).dialog('open');
		}
		else{
			
			//load the instances and display them (the list items)
			$(this).parent("div").children("ul.form-elt-list").remove();
			var classUri = $(this).val();
			if(classUri != ''){
				var elt = this;
				if(ctx_extension){
				 	url = '/' + ctx_extension + '/' + ctx_module + '/';
				}
				url += 'getInstances';
				$.ajax({
					url: url,
					type: "POST",
					data: {classUri: classUri},
					dataType: 'json',
					success: function(response){
						html = "<ul class='form-elt-list'>";
						for(i in response){
							html += '<li>'+response[i]+'</li>'
						}
						html += '</ul>';
						$(elt).parent("div").append(html);
					}
				});
			}
		}
	 }
	 
	 //bind function to the drop down
	 $(".property-type").change(showPropertyList);
	 $(".property-type").each(showPropertyList);
	 
	 $(".property-listvalues").change(showPropertyListValues);
	 $(".property-listvalues").each(showPropertyListValues);
	 
	 $(".property-listvalues").each(function(){
	 	elt = $(this).parent("div");
		if(!elt.hasClass('form-elt-highlight') && elt.css('display') != 'none'){
			elt.addClass('form-elt-highlight');
		}
	 });
}